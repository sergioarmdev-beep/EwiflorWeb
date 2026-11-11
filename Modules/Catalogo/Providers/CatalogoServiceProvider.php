<?php

namespace Modules\Catalogo\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CatalogoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Aplicamos el grupo 'web' a todas las rutas del módulo
        Route::middleware('web')
            ->group(__DIR__ . '/../Routes/web.php');

        $this->loadViewsFrom(__DIR__ . '/../Views', 'catalogo');
    }
}
