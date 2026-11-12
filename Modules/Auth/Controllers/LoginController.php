<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth::login');
    }

    public function login(Request $request)
    {
        // Los nombres de usuario se guardan en minúsculas (ver User::nombreUsuario()),
        // así "Admin " y "admin" entran igual.
        $request->merge([
            'nombre_usuario' => strtolower(trim((string) $request->input('nombre_usuario'))),
        ]);

        $credentials = $request->validate([
            'nombre_usuario' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Solo las cuentas ACTIVAS pueden iniciar sesión (antes una cuenta desactivada seguía entrando).
        if (Auth::attempt($credentials + ['estado' => 'ACTIVO'], $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (Auth::user()->debe_cambiar_contrasena) {
                return redirect()->route('perfil.show');
            }

            return redirect()->intended('/dashboard');
        }

        // Si la clave es correcta pero la cuenta está inactiva, lo decimos (solo lo ve quien conoce la clave).
        $usuario = User::where('nombre_usuario', $credentials['nombre_usuario'])->first();
        if ($usuario && !$usuario->estaActivo() && Hash::check($credentials['password'], $usuario->contrasena_hash)) {
            return back()->withErrors([
                'nombre_usuario' => 'Tu cuenta está desactivada. Contacta a un administrador.',
            ])->onlyInput('nombre_usuario');
        }

        return back()->withErrors([
            'nombre_usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('nombre_usuario');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
