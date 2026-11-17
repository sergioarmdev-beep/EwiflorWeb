<?php

namespace Modules\Dashboard\Services;

use Modules\Auth\Models\User;
use Modules\Precios\Services\TipoCambioService;

class VendedorDashboardService
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

        // --- Resumen ejecutivo (solo propias) ---
        $data['total_productos'] = 0;
        $data['stock_bajo_total'] = 0;
        $data['productos_bajo_minimo'] = [];
        $data['productos_sin_vender'] = [];
        $data['valor_inventario'] = 0.0;
        $data['ventas'] = $this->resumenVentas($user->id_usuario);
        $data['ventas_comparativo'] = $this->ventasPeriodoComparativo($user->id_usuario);
        $data['ticket_promedio'] = $this->ticketPromedio($user->id_usuario);
        $data['ingresos_mes'] = $data['ventas']['total_mes'];
        $data['cuentas_pendientes'] = $data['ventas']['saldo_pendiente'];

        // --- Inteligencia comercial (solo propias) ---
        $data['top_vendedores'] = [];
        $data['productos_mas_vendidos'] = $this->productosMasVendidos($user->id_usuario);
        $data['conversion_cotizaciones'] = $this->conversionCotizaciones($user->id_usuario);

        // --- Inteligencia de inventario ---
        $data['existencias_por_almacen'] = [];

        // --- Últimas actividad ---
        $data['ultimas_ventas'] = $this->ultimasVentas($user->id_usuario);
        $data['ultimos_movimientos'] = [];
        $data['ultimas_cotizaciones'] = $this->ultimasCotizaciones($user->id_usuario);

        // --- Cotizaciones pendientes ---
        $data['cotizaciones_pendientes'] = $this->cotizacionesPendientes($user->id_usuario);

        // --- Seguimiento operativo ---
        $data['alertas_sistema'] = [];
        $data['solicitudes_servicio_pendientes'] = 0;
        $data['ultimas_solicitudes_servicio'] = [];
        $data['solicitudes_atrasadas'] = 0;
        $data['operaciones_pendientes'] = [
            'cotizaciones_vigentes' => 0,
            'ventas_en_proceso' => 0,
            'total' => 0,
        ];

        return $data;
    }
}
