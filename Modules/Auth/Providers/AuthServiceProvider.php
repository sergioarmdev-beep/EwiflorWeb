<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Auth\Middleware\ForzarCambioContrasena;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Views', 'auth');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Se aplica a TODO el grupo "web": así, un usuario con cambio de contraseña pendiente
        // queda bloqueado también en los demás módulos (Inventario, etc.) sin tocar sus rutas.
        $this->app['router']->pushMiddlewareToGroup('web', ForzarCambioContrasena::class);
    }
}
