<?php

namespace Modules\Dashboard\Services;

use Modules\Auth\Models\User;
use Modules\Precios\Services\TipoCambioService;

class AdministradorDashboardService
{
    use DashboardStatsTrait;

    public function __construct(private TipoCambioService $tipoCambio) {}

    public function para(User $user): array
    {
        $data = [
            'rol' => $user->nombre_rol,
            'tipo_cambio' => $this->tipoCambioActual(),
            'tipo_cambio_historico' => $this->tipoCambioHistorico(),
        ];

        // --- Resumen ejecutivo ---
        $data['total_productos'] = $this->totalProductos();
        $data['stock_bajo_total'] = $this->totalExistenciasBajoMinimo();
        $data['productos_bajo_minimo'] = $this->productosBajoMinimo();
        $data['cotizaciones_pendientes'] = $this->cotizacionesPendientes();
        $data['ventas'] = $this->resumenVentas();
        $data['ventas_comparativo'] = $this->ventasPeriodoComparativo();
        $data['valor_inventario'] = $this->valorInventario();
        $data['ticket_promedio'] = $this->ticketPromedio();

        // --- Inteligencia comercial ---
        $data['productos_mas_vendidos'] = $this->productosMasVendidos();
        $data['conversion_cotizaciones'] = $this->conversionCotizaciones();

        // --- Inteligencia de inventario ---
        $data['existencias_por_almacen'] = $this->existenciasPorAlmacen();

        // --- Resumen financiero ---
        $data['ingresos_mes'] = $data['ventas']['total_mes'];
        $data['cuentas_pendientes'] = $data['ventas']['saldo_pendiente'];

        // --- Últimas actividad ---
        $data['ultimas_ventas'] = $this->ultimasVentas();
        $data['ultimos_movimientos'] = $this->ultimosMovimientos();
        $data['ultimas_cotizaciones'] = $this->ultimasCotizaciones();

        // --- Seguimiento operativo ---
        $data['alertas_sistema'] = $this->alertasSistema();
        $data['solicitudes_servicio_pendientes'] = $this->solicitudesServicioPendientes();
        $data['ultimas_solicitudes_servicio'] = $this->ultimasSolicitudesServicio();
        $data['solicitudes_atrasadas'] = $this->solicitudesAtrasadas();
        $data['operaciones_pendientes'] = $this->operacionesPendientes();

        return $data;
    }
}
