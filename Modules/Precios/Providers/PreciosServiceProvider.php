<?php

namespace Modules\Precios\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Precios\Console\SincronizarTipoCambioCommand;

class PreciosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Views', 'precios');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([SincronizarTipoCambioCommand::class]);
        }

        // Sincronización automática: cada 30 min, de 06:00 a 22:00 (hora de La Paz).
        $this->app->booted(function () {
            $this->app->make(Schedule::class)
                ->command('precios:sincronizar-tipo-cambio')
                ->everyThirtyMinutes()
                ->between('06:00', '22:00')
                ->timezone('America/La_Paz')
                ->withoutOverlapping();
        });
    }
}
