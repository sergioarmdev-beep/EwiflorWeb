<?php

namespace Modules\Clientes\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ClientesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Views', 'clientes');

        // CRÍTICO: middleware('web') explícito. NUNCA loadRoutesFrom().
        Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
    }
}
