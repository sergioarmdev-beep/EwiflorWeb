<?php

namespace Modules\Precios\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Precios\Models\TipoCambio;

class TipoCambioService
{
    private const CACHE_KEY_ESTADO   = 'dolar_api.estado';
    private const CACHE_TTL_MINUTOS  = 10;

    public function __construct(private DolarApiService $api) {}

    /**
     * Cotización vigente por tipo (la más reciente por fecha y fecha_registro).
     *
     * @return array{paralelo: ?TipoCambio, oficial: ?TipoCambio}
     */
    public function actuales(): array
    {
        $resultado = [];

        foreach (['PARALELO', 'OFICIAL'] as $tipo) {
            $resultado[strtolower($tipo)] = TipoCambio::query()
                ->where('tipo', $tipo)
                ->orderByDesc('fecha')
                ->orderByDesc('fecha_registro')
                ->with('usuario')
                ->first();
        }

        return $resultado;
    }

    public function historial(int $limite = 30): Collection
    {
        return TipoCambio::query()
            ->with('usuario')
            ->orderByDesc('fecha_registro')
            ->limit($limite)
            ->get();
    }

    /**
     * Estado cacheado de la API.
     * Devuelve ['disponible' => null] si nunca se verificó.
     */
    public function estadoApi(): array
    {
        $estado = Cache::get(self::CACHE_KEY_ESTADO);

        if ($estado === null) {
            return [
                'disponible' => null,
                'estado'     => 'NO_VERIFICADO',
                'verificado' => null,
            ];
        }

        return $estado;
    }

    /**
     * Última sincronización exitosa con la API, derivada de la base
     * (sobrevive a reinicios y limpiezas de cache).
     */
    public function ultimaSincronizacion(): ?Carbon
    {
        $ultima = TipoCambio::query()
            ->where('origen', 'API')
            ->orderByDesc('fecha_registro')
            ->value('fecha_registro');

        return $ultima ? Carbon::parse($ultima) : null;
    }

    /**
     * Refresca las cotizaciones desde DolarAPI.
     *
     * @return array{
     *   ok: bool,
     *   estado_api: array,
     *   paralelo: ?TipoCambio,
     *   oficial: ?TipoCambio,
     *   mensaje: string
     * }
     */
    public function refrescarDesdeApi(?User $actor = null): array
    {
        $estado = $this->api->verificarEstado();
        Cache::put(self::CACHE_KEY_ESTADO, $estado, now()->addMinutes(self::CACHE_TTL_MINUTOS));

        if (!$estado['disponible']) {
            return [
                'ok'         => false,
                'estado_api' => $estado,
                'paralelo'   => null,
                'oficial'    => null,
                'mensaje'    => 'El servicio de tipo de cambio no está disponible ('
                    . $estado['estado']
                    . '). Se está usando el último valor registrado.',
            ];
        }

        $paraleloData = $this->api->obtenerParalelo();
        $oficialData  = $this->api->obtenerOficial();

        $nuevos = ['paralelo' => null, 'oficial' => null];

        DB::transaction(function () use ($paraleloData, $oficialData, &$nuevos) {
            if ($paraleloData) {
                $nuevos['paralelo'] = $this->persistirDesdeApi($paraleloData);
            }
            if ($oficialData) {
                $nuevos['oficial'] = $this->persistirDesdeApi($oficialData);
            }
        });

        $exitosos = count(array_filter($nuevos));

        return [
            'ok'         => $exitosos > 0,
            'estado_api' => $estado,
            'paralelo'   => $nuevos['paralelo'],
            'oficial'    => $nuevos['oficial'],
            'mensaje'    => $exitosos === 2
                ? 'Tipo de cambio actualizado desde DolarAPI.'
                : ($exitosos === 1
                    ? 'Solo se actualizó una de las dos cotizaciones. Verifique la otra.'
                    : 'No se pudo obtener ninguna cotización desde la API.'),
        ];
    }

    private function persistirDesdeApi(array $data): TipoCambio
    {
        /** @var Carbon $fechaRef */
        $fechaRef = $data['fecha_actualizacion'] ?? now();

        return TipoCambio::create([
            'fecha'                => $fechaRef->copy()->setTimezone('America/La_Paz')->toDateString(),
            'tipo'                 => $data['tipo'],
            'valor'                => $data['venta'],
            'origen'               => 'API',
            'id_usuario'           => null,
            'fecha_referencia_api' => $fechaRef,
        ]);
    }

    public function registrarManual(array $data, User $actor): TipoCambio
    {
        return TipoCambio::create([
            'fecha'                => $data['fecha'],
            'tipo'                 => $data['tipo'],
            'valor'                => $data['valor'],
            'origen'               => 'MANUAL',
            'id_usuario'           => $actor->id_usuario,
            'fecha_referencia_api' => null,
        ]);
    }
}
