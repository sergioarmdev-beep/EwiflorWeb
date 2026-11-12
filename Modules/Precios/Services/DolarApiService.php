<?php

namespace Modules\Precios\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente de DolarAPI (https://bo.dolarapi.com), usado SOLO para el tipo de cambio PARALELO (Binance).
 * El oficial viene de BcbApiService.
 */
class DolarApiService
{
    private const BASE_URL = 'https://bo.dolarapi.com';
    private const TIMEOUT  = 15;

    /**
     * Cliente HTTP común.
     * - force_ip_resolve => v4 evita timeouts por IPv6 en Windows.
     * - withoutVerifying() solo si DOLARAPI_SKIP_SSL_VERIFY=true (desarrollo).
     */
    private function httpClient(int $timeout = self::TIMEOUT): PendingRequest
    {
        $client = Http::timeout($timeout)
            ->acceptJson()
            ->withOptions([
                'force_ip_resolve' => 'v4',
            ]);

        if (config('services.dolarapi.skip_ssl_verify', false)) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * @return array{
     *   disponible: bool,
     *   estado: string,
     *   verificado: \Carbon\Carbon,
     *   raw?: array,
     *   error?: string
     * }
     */
    public function verificarEstado(int $timeout = self::TIMEOUT): array
    {
        try {
            $response = $this->httpClient($timeout)->get(self::BASE_URL . '/v1/estado');

            if (!$response->successful()) {
                return [
                    'disponible' => false,
                    'estado'     => 'ERROR_HTTP_' . $response->status(),
                    'verificado' => now(),
                    'error'      => 'HTTP ' . $response->status() . ' al consultar /v1/estado',
                ];
            }

            $data   = $response->json();
            $estado = $data['estado'] ?? null;

            return [
                'disponible' => $estado === 'Disponible',
                'estado'     => $estado ?? 'DESCONOCIDO',
                'verificado' => now(),
                'raw'        => $data,
            ];
        } catch (ConnectionException $e) {
            Log::warning('DolarAPI /v1/estado — error de conexión', [
                'error' => $e->getMessage(),
            ]);

            return [
                'disponible' => false,
                'estado'     => 'SIN_CONEXION',
                'verificado' => now(),
                'error'      => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            Log::error('DolarAPI /v1/estado — error inesperado', [
                'error' => $e->getMessage(),
                'clase' => get_class($e),
            ]);

            return [
                'disponible' => false,
                'estado'     => 'ERROR_INTERNO',
                'verificado' => now(),
                'error'      => $e->getMessage(),
            ];
        }
    }

    public function obtenerParalelo(): ?array
    {
        return $this->obtener('binance', 'PARALELO');
    }

    private function obtener(string $casa, string $tipo): ?array
    {
        try {
            $response = $this->httpClient()->get(self::BASE_URL . '/v1/dolares/' . $casa);

            if (!$response->successful()) {
                Log::warning("DolarAPI /dolares/{$casa} respondió {$response->status()}");
                return null;
            }

            $data = $response->json();

            if (!isset($data['compra'], $data['venta'])) {
                Log::warning("DolarAPI /dolares/{$casa} no trajo compra/venta", $data ?? []);
                return null;
            }

            $fechaActualizacion = isset($data['fechaActualizacion'])
                ? Carbon::parse($data['fechaActualizacion'])->setTimezone('America/La_Paz')
                : null;

            return [
                'tipo'                    => $tipo,
                'compra'                  => (float) $data['compra'],
                'venta'                   => (float) $data['venta'],
                'fecha_actualizacion'     => $fechaActualizacion,
                'fecha_actualizacion_raw' => $data['fechaActualizacion'] ?? null,
                'nombre'                  => $data['nombre'] ?? $casa,
                'raw'                     => $data,
            ];
        } catch (ConnectionException $e) {
            Log::warning("DolarAPI /dolares/{$casa} — error de conexión", [
                'error' => $e->getMessage(),
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::error("DolarAPI /dolares/{$casa} — error inesperado", [
                'error' => $e->getMessage(),
                'clase' => get_class($e),
            ]);
            return null;
        }
    }
}
