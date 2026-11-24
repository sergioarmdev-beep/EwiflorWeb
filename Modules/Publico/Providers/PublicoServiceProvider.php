<?php

namespace Modules\Publico\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Inventario\Models\Sucursal;

class PublicoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(
            __DIR__.'/../Views',
            'publico'
        );

        Route::middleware('web')->group(
            __DIR__.'/../Routes/web.php'
        );

        // El footer público muestra las sucursales activas (y su mapa)
        // en todas las páginas del módulo.
        View::composer('publico::partials.footer', function ($view) {
            $view->with('sucursalesFooter', Sucursal::query()
                ->where('estado', 'ACTIVO')
                ->orderBy('nombre')
                ->get());
        });
    }
}
