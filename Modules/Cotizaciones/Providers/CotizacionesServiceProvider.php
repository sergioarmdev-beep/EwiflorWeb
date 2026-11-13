<?php

namespace Modules\Cotizaciones\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CotizacionesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Views', 'cotizaciones');

        // middleware('web') EXPLÍCITO. Nunca loadRoutesFrom().
        Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
    }
}
