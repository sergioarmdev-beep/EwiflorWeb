<?php

namespace Modules\Ventas\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class VentasServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Views', 'ventas');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        // middleware('web') explícito. Nunca loadRoutesFrom().
        Route::middleware('web')->group(__DIR__.'/../Routes/web.php');
    }
}
