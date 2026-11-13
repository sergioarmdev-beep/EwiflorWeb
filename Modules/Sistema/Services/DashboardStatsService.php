<?php

namespace Modules\Sistema\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Models\User;
use Modules\Precios\Services\TipoCambioService;

class DashboardStatsService
{
    public function __construct(
        private TipoCambioService $tipoCambio,
    ) {}

    public function para(User $user): array
    {
        $data = [
            'rol'         => $user->nombre_rol,
            'tipo_cambio' => $this->tipoCambioActual(),
        ];

        match (true) {
            in_array($user->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true)
                => $this->paraGerente($data),
            $user->nombre_rol === 'VENDEDOR'
                => $this->paraVendedor($data, $user),
            $user->nombre_rol === 'SERVICIO_TECNICO'
                => $this->paraTecnico($data, $user),
            default => null,
        };

        return $data;
    }

    private function tipoCambioActual(): array
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

    /* ------------------------------------------------------------------ *
     * Gerencia / Administración
     * ------------------------------------------------------------------ */

    private function paraGerente(array &$data): void
    {
        $data['total_productos']         = $this->totalProductos();
        $data['stock_bajo_total']        = $this->totalExistenciasBajoMinimo();
        $data['productos_bajo_minimo']   = $this->productosBajoMinimo();
        $data['cotizaciones_pendientes'] = $this->cotizacionesPendientes();
        $data['ventas']                  = $this->resumenVentas();
        $data['ultimas_ventas']          = $this->ultimasVentas();
        $data['ultimos_movimientos']     = $this->ultimosMovimientos();
        $data['ultimas_cotizaciones']    = $this->ultimasCotizaciones();
        $data['alertas_sistema']         = $this->alertasSistema();
    }

    private function totalProductos(): int
    {
        return DB::table('producto')->where('estado', 'ACTIVO')->count();
    }

    /** Cuenta filas de existencia (variante + almacén), no productos únicos. */
    private function totalExistenciasBajoMinimo(): int
    {
        return DB::table('v_reporte_existencias')
            ->where('alerta', 'REPONER')
            ->count();
    }

    private function productosBajoMinimo(int $limite = 6): array
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

    private function alertasSistema(): array
    {
        $alertas = [];

        $usuariosActivos = DB::table('usuario')->where('estado', 'ACTIVO')->count();
        if ($usuariosActivos < 2) {
            $alertas[] = [
                'tipo'      => 'usuarios',
                'titulo'    => 'Equipo con pocos usuarios activos',
                'mensaje'   => 'Hay menos de dos cuentas activas. Verifica que el equipo tenga los accesos necesarios.',
                'url'       => route('usuarios.create'),
                'url_texto' => 'Crear usuario',
            ];
        }

        try {
            $ultimaSync = $this->tipoCambio->ultimaSincronizacion();

            if ($ultimaSync === null) {
                $alertas[] = [
                    'tipo'      => 'tipo_cambio',
                    'titulo'    => 'Tipo de cambio sin registro',
                    'mensaje'   => 'No se encontró una sincronización ni un registro manual de tipo de cambio.',
                    'url'       => route('precios.tipo-cambio.index'),
                    'url_texto' => 'Revisar tipo de cambio',
                ];
            } elseif ($ultimaSync->diffInHours(now()) > 24) {
                $alertas[] = [
                    'tipo'      => 'tipo_cambio',
                    'titulo'    => 'Tipo de cambio posiblemente desactualizado',
                    'mensaje'   => 'El último registro fue ' . $ultimaSync->diffForHumans() . '. Comprueba la sincronización.',
                    'url'       => route('precios.tipo-cambio.index'),
                    'url_texto' => 'Revisar tipo de cambio',
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('No fue posible comprobar la sincronización del tipo de cambio.', [
                'exception' => $e->getMessage(),
            ]);
        }

        return $alertas;
    }

    /* ------------------------------------------------------------------ *
     * Indicadores comerciales
     * ------------------------------------------------------------------ */

    private function resumenVentas(?int $idVendedor = null): array
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

        return [
            'cantidad_hoy' => (clone $ventasHoy)->count(),
            'total_hoy'    => (clone $ventasHoy)->sum('rv.total_bs'),
            'cantidad_mes' => (clone $ventasMes)->count(),
            'total_mes'    => (clone $ventasMes)->sum('rv.total_bs'),
            'pagado_mes'   => (float) $pagosMes,
            'saldo_pendiente' => (float) $saldoPendiente,
        ];
    }

    private function ultimasVentas(?int $idVendedor = null, int $limite = 6): array
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

    private function ultimosMovimientos(int $limite = 6): array
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

    /* ------------------------------------------------------------------ *
     * Cotizaciones
     * ------------------------------------------------------------------ */

    private function cotizacionesPendientes(?int $idVendedor = null): int
    {
        return DB::table('cotizacion as c')
            ->join('v_cotizacion_estado as ce', 'ce.id_cotizacion', '=', 'c.id_cotizacion')
            ->where('ce.condicion_temporal', 'VIGENTE')
            ->when($idVendedor !== null, fn ($q) => $q->where('c.id_usuario', $idVendedor))
            ->count();
    }

    private function ultimasCotizaciones(?int $idVendedor = null, int $limite = 5): array
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

    /* ------------------------------------------------------------------ *
     * Vendedor
     * ------------------------------------------------------------------ */

    private function paraVendedor(array &$data, User $user): void
    {
        $data['ventas'] = $this->resumenVentas($user->id_usuario);
        $data['ultimas_ventas'] = $this->ultimasVentas($user->id_usuario);
        $data['cotizaciones_pendientes'] = $this->cotizacionesPendientes($user->id_usuario);
        $data['ultimas_cotizaciones'] = $this->ultimasCotizaciones($user->id_usuario);
    }

    /* ------------------------------------------------------------------ *
     * Servicio técnico
     * ------------------------------------------------------------------ */

    private function paraTecnico(array &$data, User $user): void
    {
        $data['solicitudes_pendientes'] = $this->solicitudesPendientes($user->id_usuario);
        $data['garantias_por_vencer'] = $this->garantiasPorVencer();
        $data['ultimas_solicitudes'] = $this->ultimasSolicitudes($user->id_usuario);
    }

    private function solicitudesPendientes(int $idTecnico): int
    {
        return DB::table('solicitud_servicio')
            ->whereIn('estado', ['PENDIENTE', 'PROGRAMADA'])
            ->where(fn ($q) => $q->where('id_tecnico', $idTecnico)->orWhereNull('id_tecnico'))
            ->count();
    }

    private function ultimasSolicitudes(int $idTecnico, int $limite = 6): array
    {
        return DB::table('solicitud_servicio as s')
            ->join('cliente as c', 'c.id_cliente', '=', 's.id_cliente')
            ->where(fn ($q) => $q->where('s.id_tecnico', $idTecnico)->orWhereNull('s.id_tecnico'))
            ->orderByDesc('s.fecha_solicitud')
            ->limit($limite)
            ->get([
                's.id_solicitud',
                's.estado',
                's.descripcion_falla',
                's.fecha_solicitud',
                'c.nombre as cliente',
            ])
            ->toArray();
    }

    private function garantiasPorVencer(int $dias = 30): int
    {
        return DB::table('v_garantia_vigente')
            ->where('vigente', true)
            ->whereDate('fecha_vencimiento', '>=', now()->toDateString())
            ->whereDate('fecha_vencimiento', '<=', now()->addDays($dias)->toDateString())
            ->count();
    }
}
