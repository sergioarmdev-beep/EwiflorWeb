<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Services\IntentoAccesoService;

class LoginController extends Controller
{
    public function __construct(private IntentoAccesoService $intentos) {}

    public function showLoginForm()
    {
        return view('auth::login');
    }

    /**
     * Se puede entrar con el nombre de usuario O con el correo registrado.
     * 3 intentos fallidos en menos de 10 minutos bloquean la cuenta 10 minutos
     * y avisan a gerencia (y a la administración de la misma sucursal).
     */
    public function login(Request $request)
    {
        // Ambos identificadores se guardan en minúsculas (ver User::nombreUsuario()
        // y User::correo()), así "Admin " y "admin@x.com" entran igual.
        $request->merge([
            'identificador' => strtolower(trim((string) $request->input('identificador'))),
        ]);

        $credentials = $request->validate([
            'identificador' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
        ]);

        $identificador = $credentials['identificador'];
        $contrasena = $credentials['password'];

        $usuario = $this->intentos->resolver($identificador);

        if (! $usuario) {
            return $this->rechazar($request, $identificador, IntentoAccesoService::MENSAJE_CREDENCIALES);
        }

        // Bloqueo por intentos fallidos: se responde antes de validar la clave.
        if ($usuario->estaBloqueado()) {
            return $this->rechazar($request, $identificador, $this->mensajeDeBloqueo($usuario));
        }

        $claveCorrecta = Hash::check($contrasena, $usuario->contrasena_hash);

        // Solo las cuentas ACTIVAS pueden iniciar sesión.
        if (! $usuario->estaActivo()) {
            $mensaje = $claveCorrecta
                ? IntentoAccesoService::MENSAJE_INACTIVA
                : IntentoAccesoService::MENSAJE_CREDENCIALES;

            return $this->rechazar($request, $identificador, $mensaje);
        }

        if (! $claveCorrecta) {
            $resultado = $this->intentos->registrarFallido($usuario, $identificador, $request->ip());

            if ($resultado !== null && ! empty($resultado->bloqueo_nuevo)) {
                $usuario->refresh();

                return $this->rechazar($request, $identificador, $this->mensajeDeBloqueo($usuario));
            }

            return $this->rechazar($request, $identificador, IntentoAccesoService::MENSAJE_CREDENCIALES);
        }

        if (! Auth::attempt(['nombre_usuario' => $usuario->nombre_usuario, 'password' => $contrasena])) {
            return $this->rechazar($request, $identificador, IntentoAccesoService::MENSAJE_CREDENCIALES);
        }

        // Acceso correcto: limpia bloqueo y ventana de fallos.
        $this->intentos->registrarExitoso($usuario, $identificador, $request->ip());

        $request->session()->regenerate();

        if ($usuario->debe_cambiar_contrasena) {
            return redirect()->route('perfil.show');
        }

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function rechazar(Request $request, string $identificador, string $mensaje)
    {
        return back()
            ->withErrors(['identificador' => $mensaje])
            ->onlyInput('identificador');
    }

    private function mensajeDeBloqueo($usuario): string
    {
        $minutos = $usuario->minutosDeBloqueo();

        return 'Tu cuenta quedó bloqueada tras 3 intentos fallidos en menos de 10 minutos. '
            ."Vuelve a intentarlo en {$minutos} min y avísale a tu gerente.";
    }
}
