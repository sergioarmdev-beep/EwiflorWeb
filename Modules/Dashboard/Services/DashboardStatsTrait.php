<?php

namespace Modules\Dashboard\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

trait DashboardStatsTrait
{
    public function tipoCambioActual(): array
    {
        try {
            return $this->tipoCambio->actuales();
        } catch (\Throwable $e) {
            Log::warning('No fue posible obtener el tipo de cambio para el dashboard.', [
                'exception' => $e->getMessage(),
            ]);

            return ['paralelo' => null, 'oficial' => null];
        }
    }

    protected function totalProductos(): int
    {
        return DB::table('producto')->where('estado', 'ACTIVO')->count();
    }

    protected function totalExistenciasBajoMinimo(): int
    {
        return DB::table('v_reporte_existencias')
            ->where('alerta', 'REPONER')
            ->count();
    }

    protected function productosBajoMinimo(int $limite = 6): array
    {
        return DB::table('v_reporte_existencias')
            ->where('alerta', 'REPONER')
            ->orderBy('disponible')
            ->limit($limite)
            ->get([
                'codigo',
                'producto',
                'sku',
                'almacen',
                'disponible',
                'stock_minimo',
            ])
            ->toArray();
    }

    protected function resumenVentas(?int $idVendedor = null): array
    {
        $inicioHoy = now()->startOfDay();
        $finHoy = now()->endOfDay();
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();

        $ventasBase = fn () => DB::table('v_reporte_ventas as rv')
            ->join('venta as v', 'v.id_venta', '=', 'rv.id_venta')
            ->where('rv.estado', 'REGISTRADA')
            ->when($idVendedor !== null, fn ($q) => $q->where('v.id_usuario', $idVendedor));

        $ventasHoy = $ventasBase()
            ->whereBetween('v.fecha_hora', [$inicioHoy, $finHoy]);

        $ventasMes = $ventasBase()
            ->whereBetween('v.fecha_hora', [$inicioMes, $finMes]);

        $pagosMes = DB::table('pago as p')
            ->join('venta as v', 'v.id_venta', '=', 'p.id_venta')
            ->where('v.estado', 'REGISTRADA')
            ->whereBetween('p.fecha_hora', [$inicioMes, $finMes])
            ->when($idVendedor !== null, fn ($q) => $q->where('v.id_usuario', $idVendedor))
            ->sum('p.monto_bs');

        $saldoPendiente = $ventasBase()
            ->selectRaw('COALESCE(SUM(GREATEST(COALESCE(rv.total_bs, 0) - COALESCE(rv.pagado_bs, 0), 0)), 0) as saldo')
            ->value('saldo');

        // Ventas por mes (últimos 12 meses) para gráfico comparativo
        $ventasPorMes = DB::table('v_reporte_ventas as rv')
            ->join('venta as v', 'v.id_venta', '=', 'rv.id_venta')
            ->where('rv.estado', 'REGISTRADA')
            ->where('v.fecha_hora', '>=', now()->subMonths(11)->startOfMonth())
            ->when($idVendedor !== null, fn ($q) => $q->where('v.id_usuario', $idVendedor))
            ->selectRaw("to_char(v.fecha_hora, 'YYYY-MM') as mes, SUM(rv.total_bs) as total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->toArray();

        return [
            'cantidad_hoy' => (clone $ventasHoy)->count(),
            'total_hoy' => (clone $ventasHoy)->sum('rv.total_bs'),
            'cantidad_mes' => (clone $ventasMes)->count(),
            'total_mes' => (clone $ventasMes)->sum('rv.total_bs'),
            'pagado_mes' => (float) $pagosMes,
            'saldo_pendiente' => (float) $saldoPendiente,
            'ventas_por_mes' => $ventasPorMes,
        ];
    }

    protected function ultimasVentas(?int $idVendedor = null, int $limite = 6): array
    {
        return DB::table('v_reporte_ventas as rv')
            ->join('venta as v', 'v.id_venta', '=', 'rv.id_venta')
            ->where('rv.estado', 'REGISTRADA')
            ->when($idVendedor !== null, fn ($q) => $q->where('v.id_usuario', $idVendedor))
            ->orderByDesc('v.fecha_hora')
            ->limit($limite)
            ->get([
                'rv.id_venta',
                'rv.fecha',
                'rv.cliente',
                'rv.vendedor',
                'rv.total_bs',
                'rv.pagado_bs',
            ])
            ->toArray();
    }

    protected function ultimosMovimientos(int $limite = 6): array
    {
        return DB::table('movimiento_inventario as m')
            ->join('usuario as u', 'u.id_usuario', '=', 'm.id_usuario')
            ->leftJoin('almacen as ao', 'ao.id_almacen', '=', 'm.id_almacen_origen')
            ->leftJoin('almacen as ad', 'ad.id_almacen', '=', 'm.id_almacen_destino')
            ->orderByDesc('m.fecha_hora')
            ->limit($limite)
            ->get([
                'm.id_movimiento',
                'm.tipo',
                'm.fecha_hora',
                'm.motivo',
                'u.nombre_completo as usuario',
                'ao.nombre as almacen_origen',
                'ad.nombre as almacen_destino',
            ])
            ->toArray();
    }

    protected function cotizacionesPendientes(?int $idVendedor = null): int
    {
        return DB::table('cotizacion as c')
            ->join('v_cotizacion_estado as ce', 'ce.id_cotizacion', '=', 'c.id_cotizacion')
            ->where('ce.condicion_temporal', 'VIGENTE')
            ->when($idVendedor !== null, fn ($q) => $q->where('c.id_usuario', $idVendedor))
            ->count();
    }

    protected function ultimasCotizaciones(?int $idVendedor = null, int $limite = 5): array
    {
        return DB::table('cotizacion as c')
            ->join('v_cotizacion_estado as ce', 'ce.id_cotizacion', '=', 'c.id_cotizacion')
            ->join('cliente as cl', 'cl.id_cliente', '=', 'c.id_cliente')
            ->when($idVendedor !== null, fn ($q) => $q->where('c.id_usuario', $idVendedor))
            ->orderByDesc('c.fecha_emision')
            ->limit($limite)
            ->get([
                'c.id_cotizacion',
                'c.fecha_emision',
                'ce.fecha_vencimiento',
                'ce.condicion_temporal',
                'cl.nombre as cliente',
            ])
            ->toArray();
    }

    protected function alertasSistema(): array
    {
        $alertas = [];

        $usuariosActivos = DB::table('usuario')->where('estado', 'ACTIVO')->count();
        if ($usuariosActivos < 2) {
            $alertas[] = [
                'tipo' => 'usuarios',
                'titulo' => 'Equipo con pocos usuarios activos',
                'mensaje' => 'Hay menos de dos cuentas activas. Verifica que el equipo tenga los accesos necesarios.',
                'url' => route('usuarios.create'),
                'url_texto' => 'Crear usuario',
            ];
        }

        try {
            $ultimaSync = $this->tipoCambio->ultimaSincronizacion();

            if ($ultimaSync === null) {
                $alertas[] = [
                    'tipo' => 'tipo_cambio',
                    'titulo' => 'Tipo de cambio sin registro',
                    'mensaje' => 'No se encontró una sincronización ni un registro manual de tipo de cambio.',
                    'url' => route('precios.tipo-cambio.index'),
                    'url_texto' => 'Revisar tipo de cambio',
                ];
            } elseif ($ultimaSync->diffInHours(now()) > 24) {
                $alertas[] = [
                    'tipo' => 'tipo_cambio',
                    'titulo' => 'Tipo de cambio posiblemente desactualizado',
                    'mensaje' => 'El último registro fue '.$ultimaSync->diffForHumans().'. Comprueba la sincronización.',
                    'url' => route('precios.tipo-cambio.index'),
                    'url_texto' => 'Revisar tipo de cambio',
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('No fue posible comprobar la sincronización del tipo de cambio.', [
                'exception' => $e->getMessage(),
            ]);
        }

        try {
            $pendientesServicio = DB::table('solicitud_servicio')
                ->where('estado', 'PENDIENTE')
                ->whereNull('id_tecnico')
                ->count();

            if ($pendientesServicio > 0) {
                $alertas[] = [
                    'tipo' => 'servicio_tecnico',
                    'titulo' => 'Encargos de servicio técnico sin asignar',
                    'mensaje' => "Hay {$pendientesServicio} solicitud(es) de servicio técnico pendiente(s) de aceptación.",
                    'url' => route('serviciotecnico.gestion'),
                    'url_texto' => 'Ver gestión',
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('No fue posible comprobar solicitudes de servicio técnico.', [
                'exception' => $e->getMessage(),
            ]);
        }

        return $alertas;
    }

    protected function productosSinVender(int $dias = 30, int $limite = 6): array
    {
        $limiteFecha = now()->subDays($dias)->toDateString();

        return DB::table('variante_producto as vp')
            ->join('producto as p', 'p.id_producto', '=', 'vp.id_producto')
            ->where('vp.estado', 'ACTIVO')
            ->where('p.estado', 'ACTIVO')
            ->whereNotExists(function ($query) use ($limiteFecha) {
                $query->select(DB::raw(1))
                    ->from('detalle_venta as dv')
                    ->join('venta as v', 'v.id_venta', '=', 'dv.id_venta')
                    ->whereColumn('dv.id_variante', 'vp.id_variante')
                    ->where('v.fecha_hora', '>=', $limiteFecha);
            })
            ->select('vp.sku', 'p.nombre as producto', 'p.tipo_producto')
            ->distinct()
            ->limit($limite)
            ->get()
            ->toArray();
    }

    protected function topVendedores(int $limite = 5): array
    {
        $inicioMes = now()->startOfMonth();

        return DB::table('v_reporte_ventas as rv')
            ->join('venta as v', 'v.id_venta', '=', 'rv.id_venta')
            ->join('usuario as u', 'u.id_usuario', '=', 'v.id_usuario')
            ->where('rv.estado', 'REGISTRADA')
            ->whereBetween('v.fecha_hora', [$inicioMes, now()->endOfMonth()])
            ->select('u.nombre_completo as vendedor', 'u.nombre_rol')
            ->selectRaw('COUNT(*) as cantidad_ventas, SUM(rv.total_bs) as total_bs')
            ->groupBy('u.id_usuario', 'u.nombre_completo', 'u.nombre_rol')
            ->orderByDesc('total_bs')
            ->limit($limite)
            ->get()
            ->toArray();
    }

    protected function tipoCambioHistorico(int $dias = 30): array
    {
        $desde = now()->subDays($dias)->toDateString();

        return [
            'paralelo' => DB::table('tipo_cambio')
                ->where('tipo', 'PARALELO')
                ->where('fecha', '>=', $desde)
                ->orderBy('fecha')
                ->get(['fecha', 'valor', 'origen'])
                ->toArray(),
            'oficial' => DB::table('tipo_cambio')
                ->where('tipo', 'OFICIAL')
                ->where('fecha', '>=', $desde)
                ->orderBy('fecha')
                ->get(['fecha', 'valor', 'origen'])
                ->toArray(),
        ];
    }

    /* ------------------------------------------------------------------ *
     *  Métricas nuevas para pestañas del dashboard (alcance reducido)
     * ------------------------------------------------------------------ */

    /** Ventas del período actual (mes) vs período anterior (mes previo). */
    protected function ventasPeriodoComparativo(?int $idVendedor = null): array
    {
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();
        $inicioMesAnterior = now()->subMonth()->startOfMonth();
        $finMesAnterior = now()->subMonth()->endOfMonth();

        $base = fn ($ini, $fin) => DB::table('v_reporte_ventas as rv')
            ->join('venta as v', 'v.id_venta', '=', 'rv.id_venta')
            ->where('rv.estado', 'REGISTRADA')
            ->whereBetween('v.fecha_hora', [$ini, $fin])
            ->when($idVendedor !== null, fn ($q) => $q->where('v.id_usuario', $idVendedor));

        $actual = $base($inicioMes, $finMes);
        $anterior = $base($inicioMesAnterior, $finMesAnterior);

        $totalActual = (float) $actual->sum('rv.total_bs');
        $totalAnterior = (float) $anterior->sum('rv.total_bs');
        $cantidadActual = (int) $actual->count();
        $cantidadAnterior = (int) $anterior->count();

        $variacion = $totalAnterior > 0
            ? (($totalActual - $totalAnterior) / $totalAnterior) * 100
            : ($totalActual > 0 ? 100 : 0);

        return [
            'total_actual' => $totalActual,
            'total_anterior' => $totalAnterior,
            'cantidad_actual' => $cantidadActual,
            'cantidad_anterior' => $cantidadAnterior,
            'variacion_pct' => round($variacion, 2),
            'es_positiva' => $variacion >= 0,
        ];
    }

    /** Valor total del inventario (existencia disponible × precio de lista). */
    protected function valorInventario(): float
    {
        $resultado = DB::table('existencia as e')
            ->join('variante_producto as vp', 'vp.id_variante', '=', 'e.id_variante')
            ->where('vp.estado', 'ACTIVO')
            ->whereNotNull('vp.precio_usd')
            ->selectRaw('COALESCE(SUM((e.cantidad_total - e.cantidad_reservada) * vp.precio_usd), 0) as total')
            ->value('total');

        return (float) $resultado;
    }

    /** Ticket promedio (monto promedio por venta del mes). */
    protected function ticketPromedio(?int $idVendedor = null): float
    {
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();

        $stats = DB::table('v_reporte_ventas as rv')
            ->join('venta as v', 'v.id_venta', '=', 'rv.id_venta')
            ->where('rv.estado', 'REGISTRADA')
            ->whereBetween('v.fecha_hora', [$inicioMes, $finMes])
            ->when($idVendedor !== null, fn ($q) => $q->where('v.id_usuario', $idVendedor))
            ->selectRaw('COUNT(*) as cantidad, SUM(rv.total_bs) as total')
            ->first();

        if (! $stats || (int) $stats->cantidad === 0) {
            return 0.0;
        }

        return (float) ($stats->total / $stats->cantidad);
    }

    /** Conversión de cotizaciones: % de cotizaciones emitidas que se convirtieron en venta. */
    protected function conversionCotizaciones(?int $idVendedor = null): array
    {
        $inicioMes = now()->startOfMonth();

        $emitidas = DB::table('cotizacion as c')
            ->join('v_cotizacion_estado as ce', 'ce.id_cotizacion', '=', 'c.id_cotizacion')
            ->whereBetween('c.fecha_emision', [$inicioMes, now()->endOfMonth()])
            ->when($idVendedor !== null, fn ($q) => $q->where('c.id_usuario', $idVendedor))
            ->count();

        $convertidas = DB::table('venta as v')
            ->whereNotNull('v.id_cotizacion')
            ->whereBetween('v.fecha_hora', [$inicioMes, now()->endOfMonth()])
            ->when($idVendedor !== null, fn ($q) => $q->where('v.id_usuario', $idVendedor))
            ->count();

        $pct = $emitidas > 0 ? round(($convertidas / $emitidas) * 100, 2) : 0;

        return [
            'emitidas' => $emitidas,
            'convertidas' => $convertidas,
            'porcentaje' => $pct,
        ];
    }

    /** Productos más vendidos del mes (por cantidad). */
    protected function productosMasVendidos(?int $idVendedor = null, int $limite = 6): array
    {
        $inicioMes = now()->startOfMonth();

        return DB::table('detalle_venta as dv')
            ->join('venta as v', 'v.id_venta', '=', 'dv.id_venta')
            ->join('variante_producto as vp', 'vp.id_variante', '=', 'dv.id_variante')
            ->join('producto as p', 'p.id_producto', '=', 'vp.id_producto')
            ->where('v.estado', 'REGISTRADA')
            ->whereBetween('v.fecha_hora', [$inicioMes, now()->endOfMonth()])
            ->when($idVendedor !== null, fn ($q) => $q->where('v.id_usuario', $idVendedor))
            ->select('p.nombre as producto', 'vp.sku')
            ->selectRaw('SUM(dv.cantidad) as cantidad, SUM(dv.cantidad * dv.precio_unitario_usd) as total_usd')
            ->groupBy('p.id_producto', 'p.nombre', 'vp.sku')
            ->orderByDesc('cantidad')
            ->limit($limite)
            ->get()
            ->toArray();
    }

    /** Existencias por almacén (stock disponible). */
    protected function existenciasPorAlmacen(int $limite = 8): array
    {
        return DB::table('existencia as e')
            ->join('almacen as a', 'a.id_almacen', '=', 'e.id_almacen')
            ->join('variante_producto as vp', 'vp.id_variante', '=', 'e.id_variante')
            ->join('producto as p', 'p.id_producto', '=', 'vp.id_producto')
            ->where('vp.estado', 'ACTIVO')
            ->where('p.estado', 'ACTIVO')
            ->select('a.nombre as almacen')
            ->selectRaw('COALESCE(SUM(e.cantidad_total - e.cantidad_reservada), 0) as disponible')
            ->groupBy('a.id_almacen', 'a.nombre')
            ->orderByDesc('disponible')
            ->limit($limite)
            ->get()
            ->toArray();
    }

    /** Solicitudes de servicio atrasadas (PENDIENTE/PROGRAMADA con más de X días). */
    protected function solicitudesAtrasadas(int $dias = 3): int
    {
        $limite = now()->subDays($dias)->toDateString();

        return DB::table('solicitud_servicio')
            ->whereIn('estado', ['PENDIENTE', 'PROGRAMADA'])
            ->whereDate('fecha_solicitud', '<', $limite)
            ->count();
    }

    /** Operaciones pendientes (ventas sin finalizar / cotizaciones vigentes sin convertir). */
    protected function operacionesPendientes(): array
    {
        $cotizacionesVigentes = DB::table('cotizacion as c')
            ->join('v_cotizacion_estado as ce', 'ce.id_cotizacion', '=', 'c.id_cotizacion')
            ->where('ce.condicion_temporal', 'VIGENTE')
            ->count();

        $ventasEnProceso = DB::table('venta')
            ->where('estado', 'EN_PROCESO')
            ->count();

        return [
            'cotizaciones_vigentes' => $cotizacionesVigentes,
            'ventas_en_proceso' => $ventasEnProceso,
            'total' => $cotizacionesVigentes + $ventasEnProceso,
        ];
    }

    protected function solicitudesServicioPendientes(): int
    {
        return DB::table('solicitud_servicio')
            ->where('estado', 'PENDIENTE')
            ->whereNull('id_tecnico')
            ->count();
    }

    protected function ultimasSolicitudesServicio(int $limite = 6): array
    {
        return DB::table('solicitud_servicio as s')
            ->join('cliente as c', 'c.id_cliente', '=', 's.id_cliente')
            ->leftJoin('usuario as u', 'u.id_usuario', '=', 's.id_tecnico')
            ->orderByDesc('s.fecha_solicitud')
            ->limit($limite)
            ->get([
                's.id_solicitud',
                's.estado',
                's.descripcion_falla',
                's.fecha_solicitud',
                'c.nombre as cliente',
                'u.nombre_completo as tecnico',
            ])
            ->toArray();
    }

    /**
     * Ventas diarias de dos meses para gráfico de líneas comparativo.
     *
     * @param  string  $mes1  Formato YYYY-MM
     * @param  string  $mes2  Formato YYYY-MM
     * @param  int|null  $idSucursal  null = sin filtro (sumatoria de todas)
     * @return array ['error'=>false, 'mes1'=>[...], 'mes2'=>[...]]
     */
    protected function calcularVentasLineasComparativo(string $mes1, string $mes2, ?int $idSucursal = null): array
    {
        $rango = function (string $mes): array {
            $inicio = Carbon::parse($mes . '-01')->startOfMonth();
            $fin = Carbon::parse($mes . '-01')->endOfMonth();

            return [$inicio, $fin];
        };

        $etiqueta = function (string $mes): string {
            $m = Carbon::parse($mes . '-01');
            $nombres = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
            ];

            return $nombres[(int) $m->month] . ' ' . $m->year;
        };

        $consulta = function (Carbon $inicio, Carbon $fin) use ($idSucursal) {
            $q = DB::table('v_reporte_ventas as rv')
                ->join('venta as v', 'v.id_venta', '=', 'rv.id_venta')
                ->where('rv.estado', 'REGISTRADA')
                ->whereBetween('v.fecha_hora', [$inicio, $fin]);

            if ($idSucursal !== null) {
                $q->where('v.id_sucursal', $idSucursal);
            }

            return $q->selectRaw("EXTRACT(DAY FROM v.fecha_hora) AS dia, SUM(rv.total_bs) AS total")
                ->groupBy('dia')
                ->orderBy('dia')
                ->get();
        };

        $builder = function (string $mes) use ($rango, $consulta, $etiqueta): array {
            [$inicio, $fin] = $rango($mes);
            $diasEnMes = (int) Carbon::parse($mes . '-01')->daysInMonth;
            $filas = $consulta($inicio, $fin);

            $porDia = [];
            foreach ($filas as $f) {
                $porDia[(int) $f->dia] = (float) $f->total;
            }

            $dias = [];
            for ($d = 1; $d <= $diasEnMes; $d++) {
                $dias[] = ['dia' => $d, 'total' => $porDia[$d] ?? 0.0];
            }

            return [
                'etiqueta' => $etiqueta($mes),
                'valor' => $mes,
                'dias' => $dias,
                'total' => array_sum(array_column($dias, 'total')),
            ];
        };

        return [
            'error' => false,
            'mes1' => $builder($mes1),
            'mes2' => $builder($mes2),
        ];
    }
}
