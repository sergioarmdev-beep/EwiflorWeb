<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Auth\Middleware\ForzarCambioContrasena;
use Modules\Auth\Models\Notificacion;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Views', 'auth');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        // Comparte el conteo de notificaciones no leídas con el layout global
        // (campana del encabezado) en cada petición autenticada.
        View::composer('layouts.app', function ($view) {
            $usuario = auth()->user();
            $noLeidas = $usuario
                ? Notificacion::query()->de($usuario)->where('leida', false)->count()
                : 0;

            $view->with('notificacionesNoLeidas', $noLeidas);
        });

        // Se aplica a TODO el grupo "web": así, un usuario con cambio de contraseña pendiente
        // queda bloqueado también en los demás módulos (Inventario, etc.) sin tocar sus rutas.
        $this->app['router']->pushMiddlewareToGroup('web', ForzarCambioContrasena::class);
    }
}
