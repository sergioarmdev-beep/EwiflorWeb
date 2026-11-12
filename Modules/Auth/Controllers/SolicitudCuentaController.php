<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\Models\SolicitudCuenta;
use Modules\Auth\Models\User;

class SolicitudCuentaController extends Controller
{
    private const MENSAJE_OK = 'Tu solicitud fue enviada. Un administrador la revisará y se pondrá en contacto contigo.';

    /**
     * Público (desde el login). Los errores van al "bag" 'solicitud' para no mezclarse
     * con los errores de autenticación del formulario principal.
     */
    public function store(Request $request)
    {
        // Honeypot: un humano nunca rellena este campo oculto. Respondemos "OK" para no dar pistas al bot.
        if ($request->filled('sitio_web')) {
            return redirect()->route('login')->with('solicitud_ok', self::MENSAJE_OK);
        }

        $request->merge([
            'correo' => strtolower(trim((string) $request->input('correo'))),
        ]);

        $data = $request->validateWithBag('solicitud', [
            'nombre_completo' => ['required', 'string', 'max:100'],
            'correo'          => ['required', 'email', 'max:100'],
            'celular'         => ['required', 'string', 'max:20', 'regex:/^[0-9+()\-\s]{7,20}$/'],
            'motivo'          => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'celular.regex' => 'Ingresa un número de celular válido (solo dígitos, +, espacios o guiones).',
            'motivo.min'    => 'Cuéntanos un poco más sobre el motivo (mínimo 10 caracteres).',
        ]);

        $yaExiste = User::where('correo', $data['correo'])->exists()
            || SolicitudCuenta::where('correo', $data['correo'])->where('estado', SolicitudCuenta::PENDIENTE)->exists();

        if ($yaExiste) {
            return back()
                ->withErrors(
                    ['correo' => 'Ya existe una cuenta o una solicitud pendiente con ese correo. Si olvidaste tu acceso, contacta a un administrador.'],
                    'solicitud'
                )
                ->withInput();
        }

        SolicitudCuenta::create($data + ['estado' => SolicitudCuenta::PENDIENTE]);

        return redirect()->route('login')->with('solicitud_ok', self::MENSAJE_OK);
    }

    public function index(Request $request)
    {
        $estado = strtoupper((string) $request->input('estado', SolicitudCuenta::PENDIENTE));
        $filtros = [SolicitudCuenta::PENDIENTE, SolicitudCuenta::APROBADA, SolicitudCuenta::RECHAZADA];

        $query = SolicitudCuenta::query()->with(['revisor:id_usuario,nombre_completo', 'usuarioCreado:id_usuario,nombre_completo']);

        if (in_array($estado, $filtros, true)) {
            $query->where('estado', $estado);
        } else {
            $estado = 'TODAS';
        }

        $solicitudes = $query->orderByDesc('fecha_creacion')->paginate(15)->withQueryString();

        $conteos = SolicitudCuenta::query()
            ->selectRaw('estado, COUNT(*) AS total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return view('auth::solicitudes.index', [
            'solicitudes' => $solicitudes,
            'estado'      => $estado,
            'conteos'     => $conteos,
        ]);
    }

    public function rechazar(Request $request, SolicitudCuenta $solicitud)
    {
        if (!$solicitud->estaPendiente()) {
            return back()->with('error', 'Esa solicitud ya fue procesada.');
        }

        $data = $request->validate([
            'nota_revision' => ['nullable', 'string', 'max:255'],
        ]);

        $solicitud->update([
            'estado'             => SolicitudCuenta::RECHAZADA,
            'id_usuario_revisor' => $request->user()->id_usuario,
            'fecha_revision'     => now(),
            'nota_revision'      => $data['nota_revision'] ?? null,
        ]);

        return back()->with('success', "Solicitud de {$solicitud->nombre_completo} rechazada.");
    }
}
