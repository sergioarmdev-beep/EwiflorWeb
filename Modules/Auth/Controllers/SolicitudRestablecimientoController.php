<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\SolicitudRestablecimiento;
use Modules\Auth\Services\RestablecerContrasenaService;

/**
 * Bandeja de «solicitudes de restablecimiento de contraseña».
 *
 * Un GERENTE ve todas las solicitudes; un ADMINISTRADOR solo las de
 * VENDEDOR/SERVICIO_TECNICO de su misma sucursal. El usuario que pidió
 * la recuperación nunca cambia su contraseña por sí mismo.
 */
class SolicitudRestablecimientoController extends Controller
{
    public function __construct(private RestablecerContrasenaService $restablecer) {}

    public function index(Request $request)
    {
        $actor = $request->user();

        $estado = strtoupper((string) $request->input('estado', SolicitudRestablecimiento::PENDIENTE));
        $filtros = [
            SolicitudRestablecimiento::PENDIENTE,
            SolicitudRestablecimiento::ATENDIDA,
            SolicitudRestablecimiento::RECHAZADA,
        ];

        $query = SolicitudRestablecimiento::query()
            ->visiblesPara($actor)
            ->with(['usuario.sucursal', 'atendio']);

        if (in_array($estado, $filtros, true)) {
            $query->where('estado', $estado);
        } else {
            $estado = 'TODAS';
        }

        $solicitudes = $query->orderByDesc('fecha_creacion')->paginate(15)->withQueryString();

        $conteos = SolicitudRestablecimiento::query()
            ->visiblesPara($actor)
            ->selectRaw('estado, COUNT(*) AS total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return view('auth::solicitudes-contrasena.index', [
            'solicitudes' => $solicitudes,
            'estado' => $estado,
            'conteos' => $conteos,
            'actor' => $actor,
        ]);
    }

    /**
     * Genera la clave temporal: la solicitud queda ATENDIDA, se limpia cualquier
     * bloqueo de la cuenta y se marcan leídas sus notificaciones.
     */
    public function atender(Request $request, SolicitudRestablecimiento $solicitud)
    {
        $this->autorizar($request, $solicitud);

        if (! $solicitud->estaPendiente()) {
            return back()->with('error', 'Esa solicitud ya fue procesada.');
        }

        $usuario = $solicitud->usuario;

        if (! $usuario) {
            return back()->with('error', 'La cuenta relacionada ya no existe.');
        }

        $temporal = $this->restablecer->generarTemporal($usuario, $request->user());

        return back()->with('password_temporal', [
            'nombre' => $usuario->nombre_completo,
            'nombre_usuario' => $usuario->nombre_usuario,
            'password' => $temporal,
        ]);
    }

    /** La descarta sin restablecer nada (queda el motivo para auditoría). */
    public function descartar(Request $request, SolicitudRestablecimiento $solicitud)
    {
        $this->autorizar($request, $solicitud);

        if (! $solicitud->estaPendiente()) {
            return back()->with('error', 'Esa solicitud ya fue procesada.');
        }

        $data = $request->validate([
            'nota' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $solicitud, $data) {
            $solicitud->update([
                'estado' => SolicitudRestablecimiento::RECHAZADA,
                'id_usuario_atendio' => $request->user()->id_usuario,
                'fecha_atencion' => DB::raw('now()'),
                'nota' => $data['nota'] ?? null,
            ]);

            $request->user()
                ->notificaciones()
                ->where('id_solicitud_restablecimiento', $solicitud->id_solicitud_restablecimiento)
                ->where('leida', false)
                ->update(['leida' => true, 'fecha_lectura' => DB::raw('now()')]);
        });

        return back()->with('success', 'Solicitud descartada.');
    }

    /** 403 si la solicitud está fuera del alcance del actor. */
    private function autorizar(Request $request, SolicitudRestablecimiento $solicitud): void
    {
        $permitida = SolicitudRestablecimiento::query()
            ->visiblesPara($request->user())
            ->whereKey($solicitud->getKey())
            ->exists();

        if (! $permitida) {
            abort(403, 'No tienes permisos para atender esa solicitud.');
        }
    }
}
