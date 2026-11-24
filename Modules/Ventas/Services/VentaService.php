<?php

namespace Modules\Ventas\Services;

use App\Exceptions\BusinessRuleException;
use App\Support\DbFunction;
use App\Support\LineasDocumento;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Catalogo\Models\VarianteProducto;
use Modules\Cotizaciones\Models\Cotizacion;
use Modules\Inventario\Models\Almacen;
use Modules\Precios\Services\TipoCambioService;
use Modules\Sucursales\Support\AlcanceDocumentos;
use Modules\Ventas\Models\Comprobante;
use Modules\Ventas\Models\Pago;
use Modules\Ventas\Models\Venta;

class VentaService
{
    public function __construct(
        private TipoCambioService $tipoCambio,
        private LineasDocumento $lineas,
    ) {}

    /**
     * Convierte una cotización EMITIDA y VIGENTE en venta.
     * Usa la función PostgreSQL convertir_cotizacion_a_venta().
     */
    public function convertirCotizacion(int $idCotizacion, int $idAlmacen, User $actor): Venta
    {
        if (! AlcanceDocumentos::esGerente($actor) && ! $actor->id_sucursal) {
            throw new BusinessRuleException('Tu usuario no tiene sucursal asignada.');
        }

        $cot = Cotizacion::find($idCotizacion);
        if (! $cot) {
            throw new BusinessRuleException("La cotización #{$idCotizacion} no existe.");
        }
        if (! $cot->puedeVer($actor)) {
            throw new BusinessRuleException(
                AlcanceDocumentos::puedeOperarEnSucursal($actor, (int) $cot->id_sucursal)
                    ? 'Solo puedes convertir tus propias cotizaciones.'
                    : 'Esta cotización pertenece a otra sucursal; no puedes convertirla.'
            );
        }
        if ($cot->estado !== 'EMITIDA') {
            throw new BusinessRuleException("La cotización #{$idCotizacion} no está EMITIDA (estado: {$cot->estado}).");
        }
        if (now()->startOfDay()->gt($cot->fecha_emision->copy()->addDays($cot->vigencia_dias))) {
            throw new BusinessRuleException("La cotización #{$idCotizacion} está vencida.");
        }

        // La venta nace en la sucursal de la cotización: el almacén debe ser de esa misma sucursal.
        $almacen = Almacen::find($idAlmacen);
        if (! $almacen) {
            throw new BusinessRuleException('El almacén indicado no existe.');
        }
        if ((int) $almacen->id_sucursal !== (int) $cot->id_sucursal) {
            throw new BusinessRuleException(
                'El almacén de salida debe pertenecer a la sucursal de la cotización.'
            );
        }

        return DB::transaction(function () use ($idCotizacion, $idAlmacen, $actor) {
            $idVenta = (int) DbFunction::scalar('convertir_cotizacion_a_venta', [
                $idCotizacion,
                $actor->id_usuario,
                $idAlmacen,
            ]);
            $venta = Venta::findOrFail($idVenta);
            $this->postCreacion($venta);

            return $venta->fresh();
        });
    }

    public function crearDirecta(array $data, User $actor): Venta
    {
        if (! AlcanceDocumentos::esGerente($actor) && ! $actor->id_sucursal) {
            throw new BusinessRuleException('Tu usuario no tiene sucursal asignada.');
        }

        // La venta se registra en la sucursal del almacén de salida: es la que
        // realmente despacha el stock. Un no-Gerente solo puede usar la suya.
        $almacen = Almacen::find((int) $data['id_almacen']);
        if (! $almacen) {
            throw new BusinessRuleException('El almacén indicado no existe.');
        }
        if (! AlcanceDocumentos::puedeOperarEnSucursal($actor, (int) $almacen->id_sucursal)) {
            throw new BusinessRuleException('Solo puedes vender desde almacenes de tu sucursal asignada.');
        }
        $idSucursal = (int) $almacen->id_sucursal;

        $tc = $this->tipoCambio->actuales()['paralelo'];
        if (! $tc) {
            throw new BusinessRuleException(
                'No hay tipo de cambio paralelo vigente. Registra uno en /precios/tipo-cambio antes de vender.'
            );
        }

        // NUEVO: valida stock ANTES de llamar a la función, para dar un mensaje útil por variante.
        $this->validarDisponibilidad($data['lineas'], (int) $data['id_almacen']);

        $payload = $this->lineas->normalizar($data['lineas'], (int) $data['id_almacen'], $actor);

        return DB::transaction(function () use ($data, $actor, $tc, $payload, $idSucursal) {
            $rows = DbFunction::query(
                'select registrar_venta(?, ?, ?, ?, ?::jsonb, null) as id',
                [
                    (int) $data['id_cliente'],
                    $actor->id_usuario,
                    $idSucursal,
                    $tc->id_tipo_cambio,
                    json_encode($payload),
                ]
            );

            $venta = Venta::findOrFail((int) $rows[0]->id);
            $this->postCreacion($venta);

            return $venta->fresh();
        });
    }

    public function registrarPago(Venta $venta, array $data): Pago
    {
        return DB::transaction(function () use ($venta, $data) {
            // Bloquea la venta para que dos pagos concurrentes no pasen la validación.
            $venta = Venta::query()
                ->whereKey($venta->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($venta->estado === 'CANCELADA') {
                throw new BusinessRuleException('No se pueden registrar pagos en una venta cancelada.');
            }

            if ($venta->pagos()->exists()) {
                throw new BusinessRuleException(
                    'Esta venta ya tiene un pago registrado; solo se permite un pago por venta.'
                );
            }

            $totales = $venta->totales();
            $totalBs = round((float) ($totales->total_bs ?? 0), 2);

            if ($totalBs <= 0) {
                throw new BusinessRuleException('La venta no tiene un total cobrable.');
            }

            $montoBs = round((float) $data['monto_bs'], 2);
            $totalCents = (int) round($totalBs * 100);
            $montoCents = (int) round($montoBs * 100);

            if ($montoCents <= 0) {
                throw new BusinessRuleException('El monto del pago debe ser mayor a cero.');
            }

            if ($montoCents > $totalCents) {
                throw new BusinessRuleException(sprintf(
                    'El pago no puede superar el total de la venta (Bs %s).',
                    number_format($totalBs, 2)
                ));
            }

            return $venta->pagos()->create([
                'forma_pago' => $data['forma_pago'],
                'monto_bs' => $montoBs,
                'referencia' => $data['referencia'] ?? null,
            ]);
        });
    }

    public function cancelar(Venta $venta, User $actor, string $motivo): void
    {
        if ($venta->estado === 'CANCELADA') {
            throw new BusinessRuleException('La venta ya está cancelada.');
        }
        DbFunction::query('select cancelar_venta(?, ?, ?)', [
            $venta->id_venta,
            $actor->id_usuario,
            $motivo,
        ]);
    }

    private function postCreacion(Venta $venta): void
    {
        if (! $venta->numero_factura) {
            $venta->numero_factura = sprintf('V-%d-%06d', $venta->id_sucursal, $venta->id_venta);
            $venta->save();
        }

        Comprobante::firstOrCreate(
            ['tipo' => 'NOTA_VENTA', 'numero' => $venta->numero_factura],
            ['id_venta' => $venta->id_venta]
        );
    }

    private function validarDisponibilidad(array $lineas, int $idAlmacen): void
    {
        $ids = array_map(fn ($l) => (int) $l['id_variante'], $lineas);

        $variantes = VarianteProducto::with('producto:id_producto,nombre,modalidad')
            ->whereIn('id_variante', $ids)
            ->get()
            ->keyBy('id_variante');

        // Disponibilidad actual (cantidad_total - cantidad_reservada) para este almacén.
        $disponibles = DB::table('existencia')
            ->where('id_almacen', $idAlmacen)
            ->whereIn('id_variante', $ids)
            ->get()
            ->mapWithKeys(fn ($e) => [
                (int) $e->id_variante => (int) $e->cantidad_total - (int) $e->cantidad_reservada,
            ])
            ->all();

        $errores = [];
        foreach ($lineas as $l) {
            $id = (int) $l['id_variante'];
            $v = $variantes[$id] ?? null;
            $req = (int) $l['cantidad'];

            if (! $v) {
                $errores[] = "La variante #{$id} no existe.";

                continue;
            }
            if ($v->producto?->modalidad === 'BAJO_PEDIDO') {
                continue; // no requiere stock
            }

            $disp = $disponibles[$id] ?? 0;
            if ($disp < $req) {
                $errores[] = sprintf(
                    'Stock insuficiente en el almacén para %s (%s): disponible %d, solicitado %d.',
                    $v->sku,
                    $v->producto?->nombre ?? '—',
                    $disp,
                    $req
                );
            }
        }

        if (! empty($errores)) {
            throw new BusinessRuleException(implode(' ', $errores));
        }
    }
}
