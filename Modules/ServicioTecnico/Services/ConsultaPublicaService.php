<?php

namespace Modules\ServicioTecnico\Services;

use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Clientes\Models\Cliente;

class ConsultaPublicaService
{
    /**
     * Compras registradas del cliente con totales y estado de garantía.
     *
     * Cada fila representa una venta y sus líneas con garantía.
     * Se usa v_venta_total para el total y v_garantia_vigente para la garantía.
     */
    public function comprasConGarantia(int $idCliente): array
    {
        $ventas = DB::table('v_reporte_ventas as rv')
            ->join('venta as v', 'v.id_venta', '=', 'rv.id_venta')
            ->where('v.id_cliente', $idCliente)
            ->where('rv.estado', 'REGISTRADA')
            ->orderByDesc('rv.fecha')
            ->get([
                'rv.id_venta',
                'rv.fecha',
                'rv.total_usd',
                'rv.total_bs',
                'rv.pagado_bs',
            ])
            ->toArray();

        foreach ($ventas as &$venta) {
            $detalle = DB::table('v_garantia_vigente as gv')
                ->where('gv.id_venta', $venta->id_venta)
                ->get([
                    'gv.id_garantia',
                    'gv.id_detalle_venta',
                    'gv.sku',
                    'gv.producto',
                    'gv.fecha_inicio',
                    'gv.duracion_meses',
                    'gv.fecha_vencimiento',
                    'gv.vigente',
                ])
                ->toArray();

            $venta->detalles = $detalle;
            $venta->tieneGarantiaVigente = count(array_filter($detalle, fn ($d) => $d->vigente === true)) > 0;
        }

        return $ventas;
    }

    public function solicitudesDelCliente(int $idCliente): array
    {
        return DB::table('solicitud_servicio as s')
            ->leftJoin('usuario as u', 'u.id_usuario', '=', 's.id_tecnico')
            ->where('s.id_cliente', $idCliente)
            ->orderByDesc('s.fecha_solicitud')
            ->get([
                's.id_solicitud',
                's.fecha_solicitud',
                's.descripcion_falla',
                's.estado',
                's.id_tecnico',
                'u.nombre_completo as tecnico',
                's.id_garantia',
                's.resultado',
                's.costo_servicio_bs',
            ])
            ->toArray();
    }

    /**
     * Garantías vigentes que vencen dentro de los próximos $dias días.
     * Usa la vista v_garantia_vigente (ya calcula fecha_vencimiento y vigente).
     */
    public function garantiasPorVencer(User $user, int $dias = 30): array
    {
        $query = DB::table('v_garantia_vigente as gv')
            ->join('venta as v', 'v.id_venta', '=', 'gv.id_venta')
            ->join('cliente as c', 'c.id_cliente', '=', 'v.id_cliente')
            ->where('gv.vigente', true)
            ->where('gv.fecha_vencimiento', '>=', now()->startOfDay())
            ->where('gv.fecha_vencimiento', '<=', now()->startOfDay()->addDays($dias));

        // ADR-006: GERENTE ve todas; el resto solo su sucursal (fail-closed).
        if ($user->nombre_rol !== 'GERENTE') {
            $user->id_sucursal === null
                ? $query->whereRaw('1 = 0')
                : $query->where('v.id_sucursal', (int) $user->id_sucursal);
        }

        return $query
            ->orderBy('gv.fecha_vencimiento')
            ->get([
                'gv.id_garantia',
                'gv.id_detalle_venta',
                'gv.id_venta',
                'gv.sku',
                'gv.producto',
                'gv.fecha_inicio',
                'gv.duracion_meses',
                'gv.fecha_vencimiento',
                'c.nombre as cliente_nombre',
                'c.telefono as cliente_telefono',
                'v.id_cliente',
            ])
            ->toArray();
    }
}
