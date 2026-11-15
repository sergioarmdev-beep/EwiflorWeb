<?php

namespace Modules\Sucursales\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SucursalesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // CRÍTICO: middleware('web') explícito. NUNCA loadRoutesFrom().
        Route::middleware('web')->group(__DIR__.'/../Routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../Views', 'sucursales');
    }
}
