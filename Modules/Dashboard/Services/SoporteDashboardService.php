<?php

namespace Modules\Dashboard\Services;

use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Precios\Services\TipoCambioService;
use Modules\ServicioTecnico\Services\ConsultaPublicaService;

class SoporteDashboardService
{
    use DashboardStatsTrait;

    public function __construct(
        private TipoCambioService $tipoCambio,
        private ConsultaPublicaService $consultaPublica,
    ) {}

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

        $pendientes = DB::table('solicitud_servicio')
            ->where('estado', 'PENDIENTE')
            ->whereNull('id_tecnico');
        $this->aplicarAlcanceSolicitudes($pendientes, $user);
        $data['solicitudes_servicio_pendientes'] = $pendientes->count();

        $data['ultimas_solicitudes_servicio'] = [];

        $atrasadas = DB::table('solicitud_servicio')
            ->whereIn('estado', ['PENDIENTE', 'PROGRAMADA'])
            ->whereDate('fecha_solicitud', '<', now()->subDays(3)->toDateString());
        $this->aplicarAlcanceSolicitudes($atrasadas, $user);
        $data['solicitudes_atrasadas'] = $atrasadas->count();
        $data['operaciones_pendientes'] = [
            'cotizaciones_vigentes' => 0,
            'ventas_en_proceso' => 0,
            'total' => 0,
        ];

        // --- Métricas específicas de soporte técnico ---
        // ConsultaPublicaService ya aplica ADR-006 (fail-closed sin sucursal).
        $data['garantias_por_vencer'] = count($this->consultaPublica->garantiasPorVencer($user, 30));

        $ultimas = DB::table('solicitud_servicio as s')
            ->join('cliente as c', 'c.id_cliente', '=', 's.id_cliente');
        $this->aplicarAlcanceSolicitudes($ultimas, $user, 's');
        $data['ultimas_solicitudes'] = $ultimas
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

        $activa = DB::table('solicitud_servicio as s')
            ->join('cliente as c', 'c.id_cliente', '=', 's.id_cliente')
            ->where('s.estado', 'PROGRAMADA')
            ->where('s.id_tecnico', $user->id_usuario);
        $this->aplicarAlcanceSolicitudes($activa, $user, 's');
        $data['solicitud_activa'] = $activa
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
