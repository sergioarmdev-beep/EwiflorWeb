<?php

namespace Modules\Precios\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Modules\Auth\Models\User;
use Modules\Precios\Exceptions\TipoCambioNoDisponibleException;
use Modules\Precios\Models\TipoCambio;

class TipoCambioService
{
    private const CACHE_KEY_ESTADO  = 'precios.estado_apis';
    private const CACHE_TTL_MINUTOS = 10;
    private const TIMEOUT_ESTADO    = 5;  // segundos, al verificar al cargar la página
    private const TIMEOUT_REFRESCO  = 15; // segundos, al sincronizar

    private const TIPOS = ['PARALELO', 'OFICIAL'];

    public function __construct(
        private BcbApiService $bcb,       // OFICIAL
        private DolarApiService $dolar,   // PARALELO
    ) {}

    /* ------------------------------------------------------------------ *
     *  Lectura para la vista
     * ------------------------------------------------------------------ */

    /**
     * Cotización vigente por tipo (el último manual si existe; si no, el último de la API).
     *
     * @return array{paralelo: ?TipoCambio, oficial: ?TipoCambio}
     */
    public function actuales(): array
    {
        return $this->resumen()['actuales'];
    }

    /**
     * Todo lo que necesita la cabecera de la vista en una sola pasada:
     * vigentes, variación contra el registro anterior y brecha paralelo/oficial.
     *
     * @return array{
     *   actuales: array{paralelo: ?TipoCambio, oficial: ?TipoCambio},
     *   variaciones: array{paralelo: ?array, oficial: ?array},
     *   brecha: ?array
     * }
     */
    public function resumen(): array
    {
        $actuales    = [];
        $variaciones = [];

        foreach (self::TIPOS as $tipo) {
            $key = strtolower($tipo);

            $vigente  = $this->vigente($tipo);
            $anterior = $vigente ? $this->anteriorA($vigente) : null;

            $actuales[$key]    = $vigente;
            $variaciones[$key] = $this->calcularVariacion($vigente, $anterior);
        }

        return [
            'actuales'    => $actuales,
            'variaciones' => $variaciones,
            'brecha'      => $this->calcularBrecha($actuales['paralelo'], $actuales['oficial']),
        ];
    }

    /**
     * Registro vigente de un tipo.
     *
     * Regla: si existe algún registro MANUAL, el vigente es el manual más reciente
     * y se mantiene hasta que se registre otro manual (la API sigue guardando
     * historial pero no lo reemplaza). Si nunca hubo uno manual, rige el más
     * reciente de la API. "Más reciente" = por fecha, luego fecha_registro, luego id.
     */
    private function vigente(string $tipo): ?TipoCambio
    {
        $base = fn () => TipoCambio::query()
            ->where('tipo', $tipo)
            ->orderByDesc('fecha')
            ->orderByDesc('fecha_registro')
            ->orderByDesc('id_tipo_cambio')
            ->with('usuario');

        return $base()->where('origen', 'MANUAL')->first()
            ?? $base()->first();
    }

    /** Registro inmediatamente anterior (mismo tipo) al dado, según el mismo orden. */
    private function anteriorA(TipoCambio $actual): ?TipoCambio
    {
        $fecha    = $actual->fecha?->toDateString();
        $registro = $actual->fecha_registro;

        return TipoCambio::query()
            ->where('tipo', $actual->tipo)
            ->where(function ($q) use ($fecha, $registro, $actual) {
                $q->whereDate('fecha', '<', $fecha)
                  ->orWhere(function ($q) use ($fecha, $registro, $actual) {
                      $q->whereDate('fecha', $fecha)
                        ->where(function ($q) use ($registro, $actual) {
                            $q->where('fecha_registro', '<', $registro)
                              ->orWhere(function ($q) use ($registro, $actual) {
                                  $q->where('fecha_registro', $registro)
                                    ->where('id_tipo_cambio', '<', $actual->id_tipo_cambio);
                              });
                        });
                  });
            })
            ->orderByDesc('fecha')
            ->orderByDesc('fecha_registro')
            ->orderByDesc('id_tipo_cambio')
            ->first();
    }

    private function calcularVariacion(?TipoCambio $actual, ?TipoCambio $anterior): ?array
    {
        if (!$actual || !$anterior) {
            return null;
        }

        $valorActual   = (float) $actual->valor;
        $valorAnterior = (float) $anterior->valor;
        $diferencia    = $valorActual - $valorAnterior;

        return [
            'anterior'   => $valorAnterior,
            'diferencia' => $diferencia,
            'porcentaje' => $valorAnterior > 0 ? ($diferencia / $valorAnterior) * 100 : null,
        ];
    }

    private function calcularBrecha(?TipoCambio $paralelo, ?TipoCambio $oficial): ?array
    {
        if (!$paralelo || !$oficial) {
            return null;
        }

        $p = (float) $paralelo->valor;
        $o = (float) $oficial->valor;

        return [
            'diferencia' => $p - $o,
            'porcentaje' => $o > 0 ? (($p - $o) / $o) * 100 : null,
        ];
    }

    /* ------------------------------------------------------------------ *
     *  Historial (filtros, paginación y exportación)
     * ------------------------------------------------------------------ */

    /** @param array{tipo?: string, origen?: string, desde?: string, hasta?: string} $filtros */
    private function consultaHistorial(array $filtros): Builder
    {
        return TipoCambio::query()
            ->with('usuario')
            ->when($filtros['tipo'] ?? null, fn ($q, $v) => $q->where('tipo', $v))
            ->when($filtros['origen'] ?? null, fn ($q, $v) => $q->where('origen', $v))
            ->when($filtros['desde'] ?? null, fn ($q, $v) => $q->whereDate('fecha', '>=', $v))
            ->when($filtros['hasta'] ?? null, fn ($q, $v) => $q->whereDate('fecha', '<=', $v))
            ->orderByDesc('fecha_registro')
            ->orderByDesc('id_tipo_cambio');
    }

    public function historialPaginado(array $filtros = [], int $porPagina = 20): LengthAwarePaginator
    {
        return $this->consultaHistorial($filtros)->paginate($porPagina)->withQueryString();
    }

    public function historialParaExportar(array $filtros = []): LazyCollection
    {
        return $this->consultaHistorial($filtros)->lazy(500);
    }

    /* ------------------------------------------------------------------ *
     *  Estado de la API
     * ------------------------------------------------------------------ */

    /**
     * Estado combinado de las dos fuentes (OFICIAL: API del BCB, PARALELO: DolarAPI), cacheado.
     * `disponible` es true solo si ambas responden; `estado` nombra las que fallan.
     * Si no hay nada en caché se verifica en el momento con un timeout corto.
     *
     * @return array{disponible: bool, estado: string, verificado: Carbon, fuentes: array}
     */
    public function estadoApi(): array
    {
        return Cache::remember(
            self::CACHE_KEY_ESTADO,
            now()->addMinutes(self::CACHE_TTL_MINUTOS),
            fn () => $this->verificarFuentes(self::TIMEOUT_ESTADO)
        );
    }

    private function verificarFuentes(int $timeout): array
    {
        $fuentes = [
            'OFICIAL'  => $this->bcb->verificarEstado($timeout),
            'PARALELO' => $this->dolar->verificarEstado($timeout),
        ];

        $caidas = [];
        foreach ($fuentes as $tipo => $e) {
            if (!$e['disponible']) {
                $caidas[] = "{$tipo}: {$e['estado']}";
            }
        }

        return [
            'disponible' => $caidas === [],
            'estado'     => $caidas === [] ? 'Disponible' : implode(' · ', $caidas),
            'verificado' => now(),
            'fuentes'    => $fuentes,
        ];
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

    /* ------------------------------------------------------------------ *
     *  Sincronización con la API
     * ------------------------------------------------------------------ */

    /**
     * Sincroniza cada fuente por separado: si una está caída se actualiza la otra.
     *
     * @return array{
     *   ok: bool,
     *   estado_api: array,
     *   paralelo: ?TipoCambio,
     *   oficial: ?TipoCambio,
     *   creados: int,
     *   mensaje: string
     * }
     */
    public function refrescarDesdeApi(?User $actor = null): array
    {
        $estado = $this->verificarFuentes(self::TIMEOUT_REFRESCO);
        Cache::put(self::CACHE_KEY_ESTADO, $estado, now()->addMinutes(self::CACHE_TTL_MINUTOS));

        $datos = [
            'oficial'  => $estado['fuentes']['OFICIAL']['disponible'] ? $this->bcb->obtenerOficial() : null,
            'paralelo' => $estado['fuentes']['PARALELO']['disponible'] ? $this->dolar->obtenerParalelo() : null,
        ];
        $registro = ['paralelo' => null, 'oficial' => null];
        $creados  = 0;

        DB::transaction(function () use ($datos, &$registro, &$creados) {
            foreach ($datos as $key => $data) {
                if (!$data) {
                    continue;
                }
                [$modelo, $creado] = $this->persistirDesdeApi($data);
                $registro[$key]    = $modelo;
                $creados          += $creado ? 1 : 0;
            }
        });

        $obtenidos = count(array_filter($registro));

        $mensaje = match (true) {
            $obtenidos === 0 && !$estado['disponible'] => 'El servicio de tipo de cambio no está disponible ('
                . $estado['estado'] . '). Se está usando el último valor registrado.',
            $obtenidos === 0 => 'No se pudo obtener ninguna cotización desde las APIs.',
            $obtenidos === 1 => 'Solo se actualizó una de las dos cotizaciones'
                . ($estado['disponible'] ? '' : ' (' . $estado['estado'] . ')')
                . '. Verifique la otra.',
            $creados === 0   => 'Las cotizaciones ya estaban al día; no hubo cambios.',
            default          => 'Tipo de cambio actualizado.',
        };

        return [
            'ok'         => $obtenidos > 0,
            'estado_api' => $estado,
            'paralelo'   => $registro['paralelo'],
            'oficial'    => $registro['oficial'],
            'creados'    => $creados,
            'mensaje'    => $mensaje,
        ];
    }

    /**
     * Guarda la cotización salvo que ya exista un registro de la API para
     * ese tipo y ese día con exactamente los mismos valores (evita duplicados).
     *
     * @return array{0: TipoCambio, 1: bool} [registro, fueCreado]
     */
    private function persistirDesdeApi(array $data): array
    {
        /** @var Carbon $fechaRef */
        $fechaRef = $data['fecha_actualizacion'] ?? now();

        // `fecha` = día que rige el valor según la API; si no viene, el día de la consulta.
        $fecha = isset($data['fecha_vigencia'])
            ? $data['fecha_vigencia']->toDateString()
            : $fechaRef->copy()->setTimezone('America/La_Paz')->toDateString();

        $venta  = round((float) $data['venta'], 4);
        $compra = round((float) $data['compra'], 4);

        $existente = TipoCambio::query()
            ->where('tipo', $data['tipo'])
            ->where('origen', 'API')
            ->whereDate('fecha', $fecha)
            ->orderByDesc('id_tipo_cambio')
            ->first();

        if (
            $existente
            && $existente->valor_compra !== null
            && round((float) $existente->valor, 4) === $venta
            && round((float) $existente->valor_compra, 4) === $compra
        ) {
            return [$existente, false];
        }

        $nuevo = TipoCambio::create([
            'fecha'                => $fecha,
            'tipo'                 => $data['tipo'],
            'valor'                => $venta,
            'valor_compra'         => $compra,
            'origen'               => 'API',
            'id_usuario'           => null,
            'fecha_referencia_api' => $fechaRef,
        ]);

        return [$nuevo, true];
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

    /* ------------------------------------------------------------------ *
     *  API pública para otros módulos (conversión)
     * ------------------------------------------------------------------ */

    /** Valor vigente (Bs por USD, precio de venta; el manual manda sobre la API) o null si no hay registros. */
    public function valorVigente(string $tipo = 'OFICIAL'): ?float
    {
        $tipo = $this->normalizarTipo($tipo);

        $vigente = $this->vigente($tipo);

        return $vigente ? (float) $vigente->valor : null;
    }

    /** Convierte USD a Bs con el tipo de cambio vigente. */
    public function usdABs(float $usd, string $tipo = 'OFICIAL'): float
    {
        return round($usd * $this->valorOFalla($tipo), 2);
    }

    /** Convierte Bs a USD con el tipo de cambio vigente. */
    public function bsAUsd(float $bs, string $tipo = 'OFICIAL'): float
    {
        return round($bs / $this->valorOFalla($tipo), 2);
    }

    private function valorOFalla(string $tipo): float
    {
        $tipo  = $this->normalizarTipo($tipo);
        $valor = $this->valorVigente($tipo);

        if ($valor === null || $valor <= 0) {
            throw TipoCambioNoDisponibleException::paraTipo($tipo);
        }

        return $valor;
    }

    private function normalizarTipo(string $tipo): string
    {
        $tipo = strtoupper($tipo);

        if (!in_array($tipo, self::TIPOS, true)) {
            throw new \InvalidArgumentException("Tipo de cambio inválido: {$tipo}. Use PARALELO u OFICIAL.");
        }

        return $tipo;
    }
}
