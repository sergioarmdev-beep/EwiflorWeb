<?php

namespace Modules\Promociones\Services;

use App\Exceptions\BusinessRuleException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Catalogo\Models\VarianteProducto;
use Modules\Promociones\Models\Promocion;

/**
 * Reglas de promociones: vigencia, equivalencia del descuento en porcentaje
 * y autorización de descuentos por rol.
 *
 * Los descuentos de venta/cotización se guardan como porcentaje
 * (`detalle_venta.descuento_pct`), así que toda promoción —sea PCT o USD— se
 * normaliza aquí a su porcentaje equivalente sobre el precio unitario.
 */
class PromocionService
{
    /** Únicos roles que pueden fijar un descuento libre. */
    public const ROLES_EDITAN = ['GERENTE', 'ADMINISTRADOR'];

    public function puedeEditar(User $actor): bool
    {
        return in_array($actor->nombre_rol, self::ROLES_EDITAN, true);
    }

    /**
     * Mapa de promociones vigentes para las variantes dadas:
     * [id_variante => ['id_promocion','nombre','tipo','valor','pct']].
     *
     * Lo consumen Ventas, Cotizaciones, Catalogo y Publico; nunca consultan
     * las tablas del módulo por su cuenta.
     *
     * @param  Collection<int, VarianteProducto>  $variantes
     * @return array<int|string, array{id_promocion:int, nombre:string, tipo:string, valor:float, pct:float}>
     */
    public function mapaVigente(Collection $variantes): array
    {
        if ($variantes->isEmpty()) {
            return [];
        }

        $ids = $variantes->pluck('id_variante')->all();

        $promociones = Promocion::vigentes()
            ->whereHas('variantes', fn ($q) => $q->whereIn('variante_producto.id_variante', $ids))
            ->with(['variantes' => fn ($q) => $q->whereIn('variante_producto.id_variante', $ids)])
            ->get();

        if ($promociones->isEmpty()) {
            return [];
        }

        $precios = $variantes->pluck('precio_usd', 'id_variante');

        $mapa = [];
        foreach ($promociones as $promocion) {
            foreach ($promocion->variantes as $variante) {
                $mapa[$variante->id_variante] = [
                    'id_promocion' => $promocion->id_promocion,
                    'nombre' => $promocion->nombre,
                    'tipo' => $promocion->tipo_descuento,
                    'valor' => (float) $promocion->valor_descuento,
                    'pct' => $this->porcentajeEquivalente($promocion, (float) ($precios[$variante->id_variante] ?? 0)),
                ];
            }
        }

        return $mapa;
    }

    /** Porcentaje equivalente de la promoción sobre el precio unitario dado. */
    public function porcentajeEquivalente(Promocion $promocion, float $precioUnitario): float
    {
        if ($promocion->tipo_descuento === 'PCT') {
            return round((float) $promocion->valor_descuento, 2);
        }

        if ($precioUnitario <= 0) {
            return 0.0;
        }

        return round((float) $promocion->valor_descuento / $precioUnitario * 100, 2);
    }

    /**
     * Autorización de un descuento ya convertido a porcentaje.
     *
     * Gerente y Administrador fijan descuentos libremente; el Vendedor puede
     * aplicar como máximo el descuento de la promoción vigente de la variante
     * (o ninguno si no hay promoción).
     *
     * @param  array{id_promocion:int, nombre:string, tipo:string, valor:float, pct:float}|null  $promo
     */
    public function validarDescuento(User $actor, float $pctSolicitado, ?array $promo, string $etiqueta): void
    {
        if ($this->puedeEditar($actor)) {
            return;
        }

        $permitido = round((float) ($promo['pct'] ?? 0), 2);
        $solicitado = round($pctSolicitado, 2);

        if ($solicitado > $permitido + 0.005) {
            throw new BusinessRuleException(sprintf(
                '%s: solo un Gerente o Administrador puede modificar el descuento. Como Vendedor puedes aplicar como máximo el de la promoción vigente (%s).',
                $etiqueta,
                $this->etiquetaDescuento($promo)
            ));
        }
    }

    /** Etiqueta legible del descuento de una promoción («−15%», «USD −10», «sin descuento»). */
    public function etiquetaDescuento(?array $promo): string
    {
        if ($promo === null || (float) ($promo['valor'] ?? 0) <= 0) {
            return 'sin descuento';
        }

        return $promo['tipo'] === 'USD'
            ? sprintf('USD −%s', rtrim(rtrim(number_format((float) $promo['valor'], 2, '.', ''), '0'), '.'))
            : sprintf('−%s %%', rtrim(rtrim(number_format((float) $promo['pct'], 2, '.', ''), '0'), '.'));
    }

    /* =========================================================
     |  CRUD (solo Gerente y Administrador, por las rutas)
     * ========================================================= */

    public function crear(array $datos, array $idsVariantes, User $actor): Promocion
    {
        return DB::transaction(function () use ($datos, $idsVariantes, $actor) {
            $promocion = Promocion::create([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'tipo_descuento' => $datos['tipo_descuento'],
                'valor_descuento' => $datos['valor_descuento'],
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'] ?? null,
                'estado' => $datos['estado'],
                'id_usuario_crea' => $actor->id_usuario,
            ]);

            $this->validarVariantes($idsVariantes, $promocion);
            $this->validarSolape($idsVariantes, $promocion);

            $this->sincronizarVariantes($promocion, $idsVariantes);

            return $promocion;
        });
    }

    public function actualizar(Promocion $promocion, array $datos, array $idsVariantes): Promocion
    {
        return DB::transaction(function () use ($promocion, $datos, $idsVariantes) {
            $promocion->update([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'tipo_descuento' => $datos['tipo_descuento'],
                'valor_descuento' => $datos['valor_descuento'],
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'] ?? null,
                'estado' => $datos['estado'],
            ]);

            $this->validarVariantes($idsVariantes, $promocion);
            $this->validarSolape($idsVariantes, $promocion);

            $this->sincronizarVariantes($promocion, $idsVariantes);

            return $promocion->refresh();
        });
    }

    /** Activa o desactiva. La activación se valida también por trigger en PG. */
    public function alternarEstado(Promocion $promocion): Promocion
    {
        $nuevoEstado = $promocion->estado === 'ACTIVA' ? 'INACTIVA' : 'ACTIVA';

        try {
            $promocion->update(['estado' => $nuevoEstado]);
        } catch (QueryException $e) {
            throw $this->desdeQueryException($e);
        }

        return $promocion;
    }

    /**
     * Valida variantes existentes, activas, de productos activos y compatibles
     * con el tipo de descuento (un descuento USD no puede superar el precio
     * unitario, porque al venderlo daría más del 100 %).
     *
     * @param  array<int, int>  $idsVariantes
     */
    private function validarVariantes(array $idsVariantes, Promocion $promocion): void
    {
        $idsVariantes = array_values(array_unique(array_map('intval', $idsVariantes)));

        if ($idsVariantes === []) {
            throw new BusinessRuleException('Selecciona al menos una variante para la promoción.');
        }

        $variantes = VarianteProducto::with('producto:id_producto,nombre,estado')
            ->whereIn('id_variante', $idsVariantes)
            ->get()
            ->keyBy('id_variante');

        $inexistentes = array_diff($idsVariantes, $variantes->keys()->all());
        if ($inexistentes !== []) {
            throw new BusinessRuleException(sprintf(
                'Estas variantes no existen: #%s.',
                implode(', #', $inexistentes)
            ));
        }

        $inactivas = [];
        $sinPrecio = [];
        $superadas = [];

        foreach ($variantes as $variante) {
            if ($variante->estado !== 'ACTIVO' || $variante->producto?->estado !== 'ACTIVO') {
                $inactivas[] = $variante->sku;

                continue;
            }

            if ($promocion->tipo_descuento === 'USD') {
                $precio = (float) ($variante->precio_usd ?? 0);
                if ($precio <= 0) {
                    $sinPrecio[] = $variante->sku;

                    continue;
                }
                if ((float) $promocion->valor_descuento > $precio) {
                    $superadas[] = sprintf('%s (precio USD %s)', $variante->sku, number_format($precio, 2));
                }
            }
        }

        if ($inactivas !== []) {
            throw new BusinessRuleException(sprintf(
                'Estas variantes o sus productos están inactivos: %s.',
                implode(', ', $inactivas)
            ));
        }

        if ($sinPrecio !== []) {
            throw new BusinessRuleException(sprintf(
                'Estas variantes no tienen precio de lista y no admiten descuento en USD: %s.',
                implode(', ', $sinPrecio)
            ));
        }

        if ($superadas !== []) {
            throw new BusinessRuleException(sprintf(
                'El descuento en USD %s supera el precio de lista de: %s.',
                number_format((float) $promocion->valor_descuento, 2),
                implode(', ', $superadas)
            ));
        }
    }

    /**
     * Prevalidación del solape (el trigger de PostgreSQL lo garantiza igual;
     * aquí se hace para devolver un mensaje claro antes de insertar).
     *
     * @param  array<int, int>  $idsVariantes
     */
    private function validarSolape(array $idsVariantes, Promocion $promocion): void
    {
        if ($promocion->estado !== 'ACTIVA') {
            return;
        }

        $solapa = Promocion::query()
            ->where('estado', 'ACTIVA')
            ->where('id_promocion', '!=', $promocion->id_promocion)
            ->when($promocion->fecha_fin !== null, fn ($q) => $q->where('fecha_inicio', '<=', $promocion->fecha_fin))
            ->where(fn ($q) => $q
                ->whereNull('fecha_fin')
                ->orWhere('fecha_fin', '>=', $promocion->fecha_inicio))
            ->whereHas('variantes', fn ($q) => $q->whereIn('variante_producto.id_variante', $idsVariantes))
            ->first();

        if ($solapa) {
            throw new BusinessRuleException(sprintf(
                'Las fechas de «%s» se solapan con la promoción activa «%s» para alguna de las variantes seleccionadas.',
                $promocion->nombre,
                $solapa->nombre
            ));
        }
    }

    private function sincronizarVariantes(Promocion $promocion, array $idsVariantes): void
    {
        $idsVariantes = array_values(array_unique(array_map('intval', $idsVariantes)));

        try {
            DB::table('promocion_variante')
                ->where('id_promocion', $promocion->id_promocion)
                ->delete();

            DB::table('promocion_variante')->insert(array_map(
                fn ($idVariante) => [
                    'id_promocion' => $promocion->id_promocion,
                    'id_variante' => $idVariante,
                ],
                $idsVariantes
            ));
        } catch (QueryException $e) {
            throw $this->desdeQueryException($e);
        }

        $promocion->unsetRelation('variantes');
    }

    /** Convierte un RAISE EXCEPTION de negocio (P0001) en BusinessRuleException. */
    private function desdeQueryException(QueryException $e): BusinessRuleException
    {
        $mensaje = $e->getCode() === 'P0001'
            ? ($e->errorInfo[2] ?? $e->getMessage())
            : $e->getMessage();

        return new BusinessRuleException($mensaje, previous: $e);
    }
}
