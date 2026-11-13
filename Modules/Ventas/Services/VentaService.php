<?php

namespace Modules\Ventas\Services;

use App\Exceptions\BusinessRuleException;
use App\Support\DbFunction;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Catalogo\Models\VarianteProducto;
use Modules\Cotizaciones\Models\Cotizacion;
use Modules\Precios\Services\TipoCambioService;
use Modules\Ventas\Models\Comprobante;
use Modules\Ventas\Models\Pago;
use Modules\Ventas\Models\Venta;


class VentaService
{
    public function __construct(private TipoCambioService $tipoCambio) {}

    /**
     * Convierte una cotización EMITIDA y VIGENTE en venta.
     * Usa la función PostgreSQL convertir_cotizacion_a_venta().
     */
    public function convertirCotizacion(int $idCotizacion, int $idAlmacen, User $actor): Venta
    {
        if (! $actor->id_sucursal) {
            throw new BusinessRuleException('Tu usuario no tiene sucursal asignada.');
        }

        $cot = Cotizacion::find($idCotizacion);
        if (! $cot) {
            throw new BusinessRuleException("La cotización #{$idCotizacion} no existe.");
        }
        if ($cot->estado !== 'EMITIDA') {
            throw new BusinessRuleException("La cotización #{$idCotizacion} no está EMITIDA (estado: {$cot->estado}).");
        }
        if (now()->startOfDay()->gt($cot->fecha_emision->copy()->addDays($cot->vigencia_dias))) {
            throw new BusinessRuleException("La cotización #{$idCotizacion} está vencida.");
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
        if (! $actor->id_sucursal) {
            throw new BusinessRuleException('Tu usuario no tiene sucursal asignada.');
        }

        $tc = $this->tipoCambio->actuales()['paralelo'];
        if (! $tc) {
            throw new BusinessRuleException(
                'No hay tipo de cambio paralelo vigente. Registra uno en /precios/tipo-cambio antes de vender.'
            );
        }

        // NUEVO: valida stock ANTES de llamar a la función, para dar un mensaje útil por variante.
        $this->validarDisponibilidad($data['lineas'], (int) $data['id_almacen']);

        $payload = $this->construirLineasJson($data['lineas'], (int) $data['id_almacen']);

        return DB::transaction(function () use ($data, $actor, $tc, $payload) {
            $rows = DbFunction::query(
                'select registrar_venta(?, ?, ?, ?, ?::jsonb, null) as id',
                [
                    (int) $data['id_cliente'],
                    $actor->id_usuario,
                    $actor->id_sucursal,
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
        if ($venta->estado === 'CANCELADA') {
            throw new BusinessRuleException('No se pueden registrar pagos en una venta cancelada.');
        }

        return Pago::create([
            'id_venta'   => $venta->id_venta,
            'forma_pago' => $data['forma_pago'],
            'monto_bs'   => $data['monto_bs'],
            'referencia' => $data['referencia'] ?? null,
        ]);
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

    private function construirLineasJson(array $lineas, int $idAlmacen): array
    {
        // Si el producto es BAJO_PEDIDO, la línea va sin id_almacen (lo exige registrar_venta).
        $variantes = VarianteProducto::with('producto:id_producto,modalidad')
            ->whereIn('id_variante', array_column($lineas, 'id_variante'))
            ->get()
            ->keyBy('id_variante');

        return collect($lineas)->map(function ($l) use ($idAlmacen, $variantes) {
            $v = $variantes[(int) $l['id_variante']] ?? null;
            $bajoPedido = $v && $v->producto && $v->producto->modalidad === 'BAJO_PEDIDO';

            return [
                'id_variante'         => (int) $l['id_variante'],
                'cantidad'            => (int) $l['cantidad'],
                'precio_unitario_usd' => (float) $l['precio_unitario_usd'],
                'descuento_pct'       => (float) ($l['descuento_pct'] ?? 0),
                'id_almacen'          => $bajoPedido ? null : $idAlmacen,
            ];
        })->values()->all();
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
        $ids = array_map(fn($l) => (int) $l['id_variante'], $lineas);

        $variantes = VarianteProducto::with('producto:id_producto,nombre,modalidad')
            ->whereIn('id_variante', $ids)
            ->get()
            ->keyBy('id_variante');

        // Disponibilidad actual (cantidad_total - cantidad_reservada) para este almacén.
        $disponibles = DB::table('existencia')
            ->where('id_almacen', $idAlmacen)
            ->whereIn('id_variante', $ids)
            ->get()
            ->mapWithKeys(fn($e) => [
                (int) $e->id_variante => (int) $e->cantidad_total - (int) $e->cantidad_reservada,
            ])
            ->all();

        $errores = [];
        foreach ($lineas as $l) {
            $id  = (int) $l['id_variante'];
            $v   = $variantes[$id] ?? null;
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
