<?php

namespace Modules\Inventario\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class InventarioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ⚠️ CRÍTICO: usar Route::middleware('web')->group(...)
        //    NO usar $this->loadRoutesFrom().
        //    Si se usa loadRoutesFrom(), Laravel NO aplica el grupo web,
        //    no inicia sesión, y las rutas redirigen al login sin razón.
        Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');

        // Vistas del módulo con namespace propio
        $this->loadViewsFrom(__DIR__ . '/../Views', 'Inventario');
    }
}
