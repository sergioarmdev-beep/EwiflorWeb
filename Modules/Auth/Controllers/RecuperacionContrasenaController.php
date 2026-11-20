<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\Services\RecuperacionContrasenaService;

/**
 * «Olvidé mi contraseña» (público, desde el login).
 *
 * Pide correo + celular registrados; si coinciden, deja una solicitud y
 * notifica a quien corresponde restablecer la clave. Nunca se revela si la
 * cuenta existe y el usuario no puede cambiar su contraseña por sí mismo.
 */
class RecuperacionContrasenaController extends Controller
{
    private const MENSAJE_OK = 'Si el correo y el celular coinciden con una cuenta registrada, '
        .'avisamos a quien corresponde para que restablezca tu contraseña. Te contactaremos por el mismo correo.';

    public function __construct(private RecuperacionContrasenaService $recuperacion) {}

    public function show()
    {
        return view('auth::recuperacion.olvide');
    }

    public function store(Request $request)
    {
        // Honeypot anti-bots: se responde igual para no dar pistas al bot.
        if ($request->filled('sitio_web')) {
            return $this->listo();
        }

        $data = $request->validate([
            'correo' => ['required', 'email', 'max:100'],
            'celular' => ['required', 'string', 'max:20', 'regex:/^[0-9+()\-\s]{7,20}$/'],
        ], [
            'correo.required' => 'Ingresa el correo con el que te registraste.',
            'correo.email' => 'Ingresa un correo válido.',
            'celular.required' => 'Ingresa el celular con el que te registraste.',
            'celular.regex' => 'Ingresa un celular válido (solo dígitos, +, espacios o guiones).',
        ]);

        $this->recuperacion->solicitar($data['correo'], $data['celular'], $request->ip());

        return $this->listo();
    }

    /** Respuesta idéntica haya coincidencia o no. */
    private function listo()
    {
        return redirect()
            ->route('password.olvide')
            ->with('recuperacion_ok', self::MENSAJE_OK);
    }
}
