<?php

namespace Modules\Precios\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente de la API del BCB de cucu (https://docs.cucu.bo/bcb/oficial).
 *
 * - Solo publica el TC OFICIAL (compra = venta = TCO). No hay paralelo ni endpoint de estado:
 *   la disponibilidad se deduce de la propia respuesta del TC oficial.
 * - Límite: 120 solicitudes/hora por IP. Tras un 429/403 NO se insiste hasta que
 *   pase el Retry-After (insistir escala el bloqueo de 1 h a 24 h y 7 días).
 * - El TC cambia una vez al día (23:30 hora Bolivia), por eso se cachea la respuesta
 *   unos minutos y se comparte entre "estado" y "obtener".
 */
class BcbApiService
{
    private const BASE_URL        = 'https://apibcb.cucu.bo';
    private const ENDPOINT_OFICIAL = '/api/v1/tc/oficial';
    private const TIMEOUT         = 15;
    private const USER_AGENT      = 'MiaImportaciones-Precios/1.0';

    private const CACHE_KEY_OFICIAL = 'cucu_bcb.oficial';
    private const CACHE_KEY_BLOQUEO = 'cucu_bcb.bloqueo_hasta';
    private const CACHE_TTL_OFICIAL = 5; // minutos
    private const BLOQUEO_POR_DEFECTO = 3600; // segundos si no viene Retry-After

    /**
     * Cliente HTTP común.
     * - force_ip_resolve => v4 evita timeouts por IPv6 en Windows.
     * - withoutVerifying() solo si DOLARAPI_SKIP_SSL_VERIFY=true (desarrollo).
     */
    private function httpClient(int $timeout = self::TIMEOUT): PendingRequest
    {
        $client = Http::timeout($timeout)
            ->acceptJson()
            ->withUserAgent(self::USER_AGENT)
            ->withOptions([
                'force_ip_resolve' => 'v4',
            ]);

        if (config('services.dolarapi.skip_ssl_verify', false)) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Consulta (o reutiliza de caché) el TC oficial.
     *
     * @return array{ok: bool, estado: string, data: ?array, raw: ?array, error: ?string}
     */
    private function consultarOficial(int $timeout = self::TIMEOUT): array
    {
        if ($cacheado = Cache::get(self::CACHE_KEY_OFICIAL)) {
            return $cacheado;
        }

        // Si estamos bloqueados/limitados, ni siquiera intentamos.
        $bloqueoHasta = Cache::get(self::CACHE_KEY_BLOQUEO);
        if ($bloqueoHasta && $bloqueoHasta > time()) {
            return $this->fallo('LIMITE_EXCEDIDO', 'API en espera por límite de uso; reintento en ' . ($bloqueoHasta - time()) . ' s.');
        }

        try {
            $response = $this->httpClient($timeout)->get(self::BASE_URL . self::ENDPOINT_OFICIAL);

            if (in_array($response->status(), [429, 403], true)) {
                $retry = $response->header('Retry-After');
                $espera = is_numeric($retry) ? max(1, (int) $retry) : self::BLOQUEO_POR_DEFECTO;

                Cache::put(self::CACHE_KEY_BLOQUEO, time() + $espera, $espera);
                Log::warning("API BCB cucu respondió {$response->status()}; sin consultar durante {$espera} s");

                return $this->fallo('LIMITE_EXCEDIDO', "HTTP {$response->status()}; reintentar en {$espera} s.");
            }

            if (!$response->successful()) {
                return $this->fallo('ERROR_HTTP_' . $response->status(), 'HTTP ' . $response->status() . ' al consultar el TC oficial');
            }

            $json = $response->json();
            $t    = is_array($json) ? ($json['tc_oficial'] ?? null) : null;

            if (!is_array($t) || !is_numeric($t['compra'] ?? null) || !is_numeric($t['venta'] ?? null) || empty($t['fecha'])) {
                Log::warning('API BCB cucu: respuesta sin tc_oficial válido', is_array($json) ? $json : []);

                return $this->fallo('RESPUESTA_INVALIDA', 'La respuesta no trae compra/venta/fecha.');
            }

            $resultado = [
                'ok'     => true,
                'estado' => ($t['desactualizado'] ?? false) ? 'DESACTUALIZADO' : 'Disponible',
                'data'   => $t,
                'raw'    => $json,
                'error'  => null,
            ];

            Cache::put(self::CACHE_KEY_OFICIAL, $resultado, now()->addMinutes(self::CACHE_TTL_OFICIAL));

            return $resultado;
        } catch (ConnectionException $e) {
            Log::warning('API BCB cucu — error de conexión', ['error' => $e->getMessage()]);

            return $this->fallo('SIN_CONEXION', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('API BCB cucu — error inesperado', [
                'error' => $e->getMessage(),
                'clase' => get_class($e),
            ]);

            return $this->fallo('ERROR_INTERNO', $e->getMessage());
        }
    }

    private function fallo(string $estado, string $error): array
    {
        return ['ok' => false, 'estado' => $estado, 'data' => null, 'raw' => null, 'error' => $error];
    }

    /**
     * Disponible = la API respondió con un TC oficial vigente (no vencido).
     *
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
        $r = $this->consultarOficial($timeout);

        $estado = [
            'disponible' => $r['ok'] && $r['estado'] === 'Disponible',
            'estado'     => $r['estado'],
            'verificado' => now(),
        ];

        if ($r['raw'] !== null) {
            $estado['raw'] = $r['raw'];
        }

        if ($r['error'] !== null) {
            $estado['error'] = $r['error'];
        } elseif ($r['estado'] === 'DESACTUALIZADO') {
            $estado['error'] = 'El BCB no publicó un valor nuevo; la API sirve el último conocido.';
        }

        return $estado;
    }

    /**
     * TC oficial. Devuelve null si no se pudo obtener.
     */
    public function obtenerOficial(): ?array
    {
        $r = $this->consultarOficial();

        if (!$r['ok']) {
            return null;
        }

        $t = $r['data'];

        $actualizado = !empty($t['actualizado'])
            ? Carbon::parse($t['actualizado'])->setTimezone('America/La_Paz')
            : null;

        return [
            'tipo'                    => 'OFICIAL',
            'compra'                  => (float) $t['compra'],
            'venta'                   => (float) $t['venta'],
            // Día que rige el valor (el que se guarda como `fecha`).
            'fecha_vigencia'          => Carbon::parse($t['fecha'], 'America/La_Paz')->startOfDay(),
            // Instante en que la API obtuvo el dato del BCB (referencia).
            'fecha_actualizacion'     => $actualizado,
            'fecha_actualizacion_raw' => $t['actualizado'] ?? null,
            'nombre'                  => 'TC oficial (BCB)',
            'desactualizado'          => (bool) ($t['desactualizado'] ?? false),
            'raw'                     => $t,
        ];
    }
}
