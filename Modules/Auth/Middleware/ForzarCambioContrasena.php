<?php

namespace Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Si el usuario autenticado tiene la marca debe_cambiar_contrasena (p. ej. tras un
 * restablecimiento hecho por un admin), lo encierra en "Mi perfil" hasta que cambie la clave.
 * Solo deja pasar las rutas de perfil y el cierre de sesión.
 */
class ForzarCambioContrasena
{
    public function handle(Request $request, Closure $next)
    {
        $usuario = $request->user();

        if ($usuario && $usuario->debe_cambiar_contrasena && !$request->routeIs('perfil.*', 'logout')) {
            if ($request->expectsJson()) {
                abort(423, 'Debes cambiar tu contraseña antes de continuar.');
            }

            return redirect()->route('perfil.show');
        }

        return $next($request);
    }
}
