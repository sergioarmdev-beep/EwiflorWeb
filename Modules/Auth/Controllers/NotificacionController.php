<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\Notificacion;

/** Centro de notificaciones in-app (campana del encabezado). */
class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        $noLeidas = Notificacion::query()
            ->de($request->user())
            ->where('leida', false)
            ->count();

        $notificaciones = Notificacion::query()
            ->de($request->user())
            ->orderByDesc('fecha_creacion')
            ->orderByDesc('id_notificacion')
            ->paginate(20);

        return view('auth::notificaciones.index', [
            'notificaciones' => $notificaciones,
            'noLeidas' => $noLeidas,
        ]);
    }

    /** Marca una como leída y abre su acción. */
    public function leer(Request $request, Notificacion $notificacion)
    {
        if ((int) $notificacion->id_usuario !== (int) $request->user()->id_usuario) {
            abort(403);
        }

        $notificacion->marcarLeida();

        return $notificacion->url
            ? redirect($notificacion->url)
            : redirect()->route('notificaciones.index');
    }

    public function leerTodas(Request $request)
    {
        Notificacion::query()
            ->de($request->user())
            ->where('leida', false)
            ->update(['leida' => true, 'fecha_lectura' => DB::raw('now()')]);

        return back()->with('success', 'Notificaciones marcadas como leídas.');
    }
}
