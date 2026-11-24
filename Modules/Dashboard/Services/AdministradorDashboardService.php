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
        // ADR-006: el administrador nunca ve métricas fuera de su sucursal.
        // Sin sucursal asignada, todo queda en cero (fail-closed).
        $alcance = DashboardAlcance::desdeUsuario(
            $user->nombre_rol,
            $user->id_sucursal === null ? null : (int) $user->id_sucursal
        );

        $data = [
            'rol' => $user->nombre_rol,
            'tipo_cambio' => $this->tipoCambioActual(),
            'tipo_cambio_historico' => $this->tipoCambioHistorico(),
        ];

        // --- Resumen ejecutivo ---
        $data['total_productos'] = $this->totalProductos();
        $data['stock_bajo_total'] = $this->totalExistenciasBajoMinimo($alcance);
        $data['productos_bajo_minimo'] = $this->productosBajoMinimo(6, $alcance);
        $data['cotizaciones_pendientes'] = $this->cotizacionesPendientes(null, $alcance);
        $data['ventas'] = $this->resumenVentas(null, $alcance);
        $data['ventas_comparativo'] = $this->ventasPeriodoComparativo(null, $alcance);
        $data['valor_inventario'] = $this->valorInventario($alcance);
        $data['ticket_promedio'] = $this->ticketPromedio(null, $alcance);

        // --- Inteligencia comercial ---
        $data['productos_mas_vendidos'] = $this->productosMasVendidos(null, 6, $alcance);
        $data['conversion_cotizaciones'] = $this->conversionCotizaciones(null, $alcance);

        // --- Inteligencia de inventario ---
        $data['existencias_por_almacen'] = $this->existenciasPorAlmacen(8, $alcance);

        // --- Resumen financiero ---
        $data['ingresos_mes'] = $data['ventas']['total_mes'];
        $data['cuentas_pendientes'] = $data['ventas']['saldo_pendiente'];

        // --- Últimas actividad ---
        $data['ultimas_ventas'] = $this->ultimasVentas(null, 6, $alcance);
        $data['ultimos_movimientos'] = $this->ultimosMovimientos(6, $alcance);
        $data['ultimas_cotizaciones'] = $this->ultimasCotizaciones(null, 5, $alcance);

        // --- Seguimiento operativo ---
        $data['alertas_sistema'] = $this->alertasSistema($user);
        $data['solicitudes_servicio_pendientes'] = $this->solicitudesServicioPendientes($user);
        $data['ultimas_solicitudes_servicio'] = $this->ultimasSolicitudesServicio($user);
        $data['solicitudes_atrasadas'] = $this->solicitudesAtrasadas($user);
        $data['operaciones_pendientes'] = $this->operacionesPendientes($alcance);

        return $data;
    }

    /** Comparativa de ventas diarias entre dos meses (gráfico de líneas). */
    public function ventasLineasComparativo(string $mes1, string $mes2, ?int $idSucursal = null): array
    {
        return $this->calcularVentasLineasComparativo($mes1, $mes2, $idSucursal);
    }
}
