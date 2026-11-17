<?php

namespace Modules\Dashboard\Services;

use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Precios\Services\TipoCambioService;

class SoporteDashboardService
{
    use DashboardStatsTrait;

    public function __construct(private TipoCambioService $tipoCambio) {}

    public function para(User $user): array
    {
        $data = [
            'rol' => $user->nombre_rol,
            'tipo_cambio' => $this->tipoCambioActual(),
            'tipo_cambio_historico' => [
                'paralelo' => [],
                'oficial' => [],
            ],
        ];

        // --- Resumen ejecutivo (valores por defecto para soporte) ---
        $data['total_productos'] = 0;
        $data['stock_bajo_total'] = 0;
        $data['productos_bajo_minimo'] = [];
        $data['productos_sin_vender'] = [];
        $data['cotizaciones_pendientes'] = 0;
        $data['ventas'] = [
            'total_mes' => 0.0,
            'cantidad_mes' => 0,
            'pagado_mes' => 0.0,
            'saldo_pendiente' => 0.0,
            'ventas_por_mes' => [],
        ];
        $data['ventas_comparativo'] = [
            'total_actual' => 0.0,
            'total_anterior' => 0.0,
            'cantidad_actual' => 0,
            'cantidad_anterior' => 0,
            'variacion_pct' => 0.0,
            'es_positiva' => false,
        ];
        $data['valor_inventario'] = 0.0;
        $data['ticket_promedio'] = 0.0;

        // --- Inteligencia comercial ---
        $data['top_vendedores'] = [];
        $data['productos_mas_vendidos'] = [];
        $data['conversion_cotizaciones'] = [
            'emitidas' => 0,
            'convertidas' => 0,
            'porcentaje' => 0.0,
        ];

        // --- Inteligencia de inventario ---
        $data['existencias_por_almacen'] = [];

        // --- Resumen financiero ---
        $data['ingresos_mes'] = 0.0;
        $data['cuentas_pendientes'] = 0.0;

        // --- Últimas actividad ---
        $data['ultimas_ventas'] = [];
        $data['ultimos_movimientos'] = [];
        $data['ultimas_cotizaciones'] = [];

        // --- Seguimiento operativo ---
        $data['alertas_sistema'] = [];
        $data['solicitudes_servicio_pendientes'] = DB::table('solicitud_servicio')
            ->where('estado', 'PENDIENTE')
            ->whereNull('id_tecnico')
            ->count();
        $data['ultimas_solicitudes_servicio'] = [];
        $data['solicitudes_atrasadas'] = DB::table('solicitud_servicio')
            ->whereIn('estado', ['PENDIENTE', 'PROGRAMADA'])
            ->whereDate('fecha_solicitud', '<', now()->subDays(3)->toDateString())
            ->count();
        $data['operaciones_pendientes'] = [
            'cotizaciones_vigentes' => 0,
            'ventas_en_proceso' => 0,
            'total' => 0,
        ];

        // --- Métricas específicas de soporte técnico ---
        $data['garantias_por_vencer'] = DB::table('v_garantia_vigente')
            ->where('vigente', true)
            ->whereDate('fecha_vencimiento', '>=', now()->toDateString())
            ->whereDate('fecha_vencimiento', '<=', now()->addDays(30)->toDateString())
            ->count();

        $data['ultimas_solicitudes'] = DB::table('solicitud_servicio as s')
            ->join('cliente as c', 'c.id_cliente', '=', 's.id_cliente')
            ->where(fn ($q) => $q->where('s.id_tecnico', $user->id_usuario)->orWhereNull('s.id_tecnico'))
            ->orderByDesc('s.fecha_solicitud')
            ->limit(6)
            ->get([
                's.id_solicitud',
                's.estado',
                's.descripcion_falla',
                's.fecha_solicitud',
                'c.nombre as cliente',
            ])
            ->toArray();

        $data['solicitud_activa'] = DB::table('solicitud_servicio as s')
            ->join('cliente as c', 'c.id_cliente', '=', 's.id_cliente')
            ->where('s.estado', 'PROGRAMADA')
            ->where('s.id_tecnico', $user->id_usuario)
            ->orderByDesc('s.fecha_solicitud')
            ->limit(1)
            ->get([
                's.id_solicitud',
                's.estado',
                's.descripcion_falla',
                's.fecha_solicitud',
                's.id_garantia',
                'c.nombre as cliente',
                'c.telefono as telefono',
            ])
            ->first();

        return $data;
    }
}
