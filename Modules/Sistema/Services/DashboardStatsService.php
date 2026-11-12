<?php

namespace Modules\Sistema\Services;

use Illuminate\Support\Facades\DB;
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
            => $this->paraTecnico($data),
            default => null,
        };

        return $data;
    }

    private function tipoCambioActual(): array
    {
        try {
            return $this->tipoCambio->actuales();
        } catch (\Throwable $e) {
            return ['paralelo' => null, 'oficial' => null];
        }
    }

    /* ------------------------------------------------------------------ *
     *  Gerente / Administrador
     * ------------------------------------------------------------------ */

    private function paraGerente(array &$data): void
    {
        $data['total_productos']         = $this->totalProductos();
        $data['productos_bajo_minimo']   = $this->productosBajoMinimo();
        $data['cotizaciones_pendientes'] = $this->cotizacionesPendientes();
        $data['ultimas_cotizaciones']    = $this->ultimasCotizaciones();
        $data['alertas_sistema']         = $this->alertasSistema();
    }

    private function totalProductos(): int
    {
        return DB::table('producto')->where('estado', 'ACTIVO')->count();
    }

    private function productosBajoMinimo(int $limite = 5): array
    {
        return DB::table('v_reporte_existencias')
            ->where('alerta', 'REPONER')
            ->orderBy('disponible')
            ->limit($limite)
            ->get()
            ->toArray();
    }

    private function alertasSistema(): array
    {
        $alertas = [];

        // Alerta: equipo muy pequeño
        $usuariosActivos = DB::table('usuario')->where('estado', 'ACTIVO')->count();
        if ($usuariosActivos < 2) {
            $alertas[] = [
                'tipo'      => 'usuarios',
                'titulo'    => 'Solo hay un usuario activo',
                'mensaje'   => 'Considera crear cuentas para tu equipo antes de operar en producción.',
                'url'       => route('usuarios.create'),
                'url_texto' => 'Crear usuario',
            ];
        }

        // Alerta: tipo de cambio desactualizado
        $ultimaSync = $this->tipoCambio->ultimaSincronizacion();
        if ($ultimaSync === null) {
            $alertas[] = [
                'tipo'      => 'tipo_cambio',
                'titulo'    => 'Tipo de cambio sin sincronizar',
                'mensaje'   => 'Aún no se ha sincronizado el tipo de cambio desde la API ni se ha registrado uno manual.',
                'url'       => route('precios.tipo-cambio.index'),
                'url_texto' => 'Ir a tipo de cambio',
            ];
        } elseif ($ultimaSync->diffInHours(now()) > 24) {
            $alertas[] = [
                'tipo'      => 'tipo_cambio',
                'titulo'    => 'Tipo de cambio desactualizado',
                'mensaje'   => 'El último registro es de ' . $ultimaSync->diffForHumans() . '. Verifica la sincronización con la API.',
                'url'       => route('precios.tipo-cambio.index'),
                'url_texto' => 'Ir a tipo de cambio',
            ];
        }

        return $alertas;
    }

    /* ------------------------------------------------------------------ *
     *  Cotizaciones (Gerente y Vendedor)
     * ------------------------------------------------------------------ */

    private function cotizacionesPendientes(?int $idVendedor = null): int
    {
        return DB::table('cotizacion as c')
            ->join('v_cotizacion_estado as ce', 'ce.id_cotizacion', '=', 'c.id_cotizacion')
            ->where('ce.condicion_temporal', 'VIGENTE')
            ->when($idVendedor, fn($q) => $q->where('c.id_usuario', $idVendedor))
            ->count();
    }

    private function ultimasCotizaciones(?int $idVendedor = null, int $limite = 5): array
    {
        return DB::table('cotizacion as c')
            ->join('v_cotizacion_estado as ce', 'ce.id_cotizacion', '=', 'c.id_cotizacion')
            ->join('cliente as cl', 'cl.id_cliente', '=', 'c.id_cliente')
            ->when($idVendedor, fn($q) => $q->where('c.id_usuario', $idVendedor))
            ->orderByDesc('c.fecha_emision')
            ->limit($limite)
            ->select([
                'c.id_cotizacion',
                'c.fecha_emision',
                'ce.fecha_vencimiento',
                'ce.condicion_temporal',
                'cl.nombre as cliente',
            ])
            ->get()
            ->toArray();
    }

    /* ------------------------------------------------------------------ *
     *  Vendedor
     * ------------------------------------------------------------------ */

    private function paraVendedor(array &$data, User $user): void
    {
        $data['cotizaciones_pendientes'] = $this->cotizacionesPendientes($user->id_usuario);
        $data['ultimas_cotizaciones']    = $this->ultimasCotizaciones($user->id_usuario);
    }

    /* ------------------------------------------------------------------ *
     *  Servicio técnico
     * ------------------------------------------------------------------ */

    private function paraTecnico(array &$data): void
    {
        $data['solicitudes_pendientes'] = $this->solicitudesPendientes();
        $data['garantias_por_vencer']   = $this->garantiasPorVencer();
        $data['ultimas_solicitudes']    = $this->ultimasSolicitudes();
    }

    private function solicitudesPendientes(): int
    {
        return DB::table('solicitud_servicio')
            ->whereIn('estado', ['PENDIENTE', 'PROGRAMADA'])
            ->count();
    }

    private function ultimasSolicitudes(int $limite = 5): array
    {
        return DB::table('solicitud_servicio as s')
            ->join('cliente as c', 'c.id_cliente', '=', 's.id_cliente')
            ->orderByDesc('s.fecha_solicitud')
            ->limit($limite)
            ->select([
                's.id_solicitud',
                's.estado',
                's.descripcion_falla',
                's.fecha_solicitud',
                'c.nombre as cliente',
            ])
            ->get()
            ->toArray();
    }

    private function garantiasPorVencer(int $dias = 30): int
    {
        return DB::table('v_garantia_vigente')
            ->where('vigente', true)
            ->whereDate('fecha_vencimiento', '<=', now()->addDays($dias))
            ->count();
    }
}
