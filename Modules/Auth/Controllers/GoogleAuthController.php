<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Modules\Auth\Models\User;
use Modules\Auth\Services\IntentoAccesoService;

class GoogleAuthController extends Controller
{
    public function __construct(private IntentoAccesoService $intentos) {}

    /**
     * Inicia el flujo de OAuth con Google.
     * Guarda la URL de retorno (si existe) en la sesión para redirigir tras el login.
     */
    public function redirectToGoogle(Request $request)
    {
        $returnTo = $this->returnToSeguro($request->input('return_to'));

        if ($returnTo !== null) {
            $request->session()->put('google_oauth_return_to', $returnTo);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Callback de Google tras la autenticación.
     *
     * Reglas estrictas:
     *  - Solo se permite login si el correo de Google YA existe en la tabla `usuario`.
     *  - Nunca se crea una cuenta nueva.
     *  - Si el correo existe y no tiene google_id vinculado, se vincula (proveedor = 'ambos').
     *  - Si el correo existe y ya tiene el mismo google_id, se hace login directo.
     *  - Si el correo existe pero el google_id pertenece a otra cuenta de Google, se rechaza.
     */
    public function handleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return back()
                ->withErrors(['google' => 'No se pudo obtener la información de Google. Inténtalo de nuevo.'])
                ->withInput();
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));

        if ($email === '') {
            return back()
                ->withErrors(['google' => 'La cuenta de Google no tiene un correo asociado.'])
                ->withInput();
        }

        // Buscar por correo (coincide con User::correo() que guarda minúsculas).
        $usuario = User::where('correo', $email)->first();

        if (! $usuario) {
            return back()
                ->withErrors([
                    'google' => "El correo {$email} no está registrado en el sistema. Solicita acceso a un administrador.",
                ])
                ->withInput();
        }

        // Solo cuentas activas pueden entrar.
        if (! $usuario->estaActivo()) {
            return back()
                ->withErrors(['google' => 'Tu cuenta está inactiva. Contacta a un administrador.'])
                ->withInput();
        }

        // El login por Google no debe saltarse el bloqueo por intentos fallidos
        // que sí respeta LoginController.
        if ($usuario->estaBloqueado()) {
            return back()
                ->withErrors(['google' => 'Tu cuenta está temporalmente bloqueada. Inténtalo más tarde.'])
                ->withInput();
        }

        $googleId = (string) $googleUser->getId();
        $avatarUrl = $googleUser->getAvatar() ?: null;

        // Caso A: ya está vinculado con este mismo google_id → login directo.
        if ($usuario->google_id === $googleId) {
            $usuario->proveedor = 'ambos';
            $usuario->avatar_url = $avatarUrl;
            $usuario->save();

            return $this->finalizarLogin($request, $usuario);
        }

        // Caso B: no tiene google_id vinculado → vincular ahora (proveedor = 'ambos').
        if ($usuario->google_id === null || $usuario->google_id === '') {
            $usuario->google_id = $googleId;
            $usuario->proveedor = 'ambos';
            $usuario->avatar_url = $avatarUrl;
            $usuario->save();

            return $this->finalizarLogin($request, $usuario);
        }

        // Caso C: tiene un google_id distinto → ya está vinculado a otra cuenta de Google.
        return back()
            ->withErrors([
                'google' => 'Esta cuenta de Google ya está vinculada a otro usuario del sistema.',
            ])
            ->withInput();
    }

    /**
     * Inicia sesión, regenera la sesión y redirige al destino.
     */
    private function finalizarLogin(Request $request, User $usuario)
    {
        Auth::login($usuario, true);

        // Un acceso correcto por OAuth también limpia la ventana de fallos.
        $this->intentos->registrarExitoso($usuario, $usuario->correo, $request->ip());

        $request->session()->regenerate();

        $returnTo = $this->returnToSeguro($request->session()->pull('google_oauth_return_to'));

        return $returnTo
            ? redirect($returnTo)
            : redirect()->intended('/dashboard');
    }

    /**
     * Solo se aceptan rutas internas relativas. Se rechazan URL absolutas,
     * protocol-relative (//evil.com), barras invertidas y caracteres de control.
     */
    private function returnToSeguro(mixed $valor): ?string
    {
        if (! is_string($valor) || $valor === '' || ! Str::startsWith($valor, '/')) {
            return null;
        }

        if (Str::startsWith($valor, ['//', '/\\']) || str_contains($valor, '\\')) {
            return null;
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $valor) || parse_url($valor, PHP_URL_HOST) !== null) {
            return null;
        }

        return $valor;
    }
}
