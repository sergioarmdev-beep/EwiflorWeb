<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Support\ReglasContrasena;

class PerfilController extends Controller
{
    public function show(Request $request)
    {
        $usuario = $request->user()->load('sucursal');

        return view('auth::perfil', [
            'usuario' => $usuario,
            'forzado' => (bool) $usuario->debe_cambiar_contrasena,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $usuario = $request->user();
        $eraForzado = (bool) $usuario->debe_cambiar_contrasena;

        $request->validate([
            'password_actual' => ['required', 'string', 'current_password'],
            'password'        => array_merge(
                ReglasContrasena::reglas(true),
                ['confirmed', 'different:password_actual']
            ),
        ], ReglasContrasena::mensajes() + [
            'password_actual.required'         => 'Ingresa tu contraseña actual.',
            'password_actual.current_password' => 'La contraseña actual no es correcta.',
            'password.different'               => 'La nueva contraseña debe ser distinta de la actual.',
        ]);

        $usuario->forceFill([
            'contrasena_hash'         => Hash::make($request->input('password')),
            'debe_cambiar_contrasena' => false,
        ])->save();

        // Cambió de credenciales: renovamos el id de sesión.
        $request->session()->regenerate();

        if ($eraForzado) {
            return redirect()->route('dashboard')->with('success', 'Contraseña actualizada. ¡Ya puedes usar el sistema!');
        }

        return redirect()->route('perfil.show')->with('success', 'Tu contraseña fue actualizada correctamente.');
    }
}
