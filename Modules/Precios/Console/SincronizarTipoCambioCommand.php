<?php

namespace Modules\Precios\Console;

use Illuminate\Console\Command;
use Modules\Precios\Services\TipoCambioService;

class SincronizarTipoCambioCommand extends Command
{
    protected $signature   = 'precios:sincronizar-tipo-cambio';
    protected $description = 'Sincroniza el tipo de cambio: oficial (API del BCB de cucu) y paralelo (DolarAPI).';

    public function handle(TipoCambioService $service): int
    {
        $resultado = $service->refrescarDesdeApi();

        $this->{$resultado['ok'] ? 'info' : 'error'}($resultado['mensaje']);

        return $resultado['ok'] ? self::SUCCESS : self::FAILURE;
    }
}
