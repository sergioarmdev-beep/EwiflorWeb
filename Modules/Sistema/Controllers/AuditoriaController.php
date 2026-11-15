<?php

namespace Modules\Sistema\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sistema\Models\Auditoria;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Auditoria::with('usuario')->orderByDesc('fecha_hora');

        if ($fecha = $request->input('fecha')) {
            $query->whereDate('fecha_hora', $fecha);
        }
        if ($desde = $request->input('desde')) {
            $query->whereDate('fecha_hora', '>=', $desde);
        }
        if ($hasta = $request->input('hasta')) {
            $query->whereDate('fecha_hora', '<=', $hasta);
        }
        if ($usuario = $request->input('usuario')) {
            $query->whereHas('usuario', fn($q) => $q->where('nombre_usuario', 'ilike', "%{$usuario}%"));
        }
        if ($modulo = $request->input('modulo')) {
            $query->where('modulo', $modulo);
        }
        if ($operacion = $request->input('operacion')) {
            $query->where('operacion', $operacion);
        }
        if ($tabla = $request->input('tabla')) {
            $query->where('tabla_afectada', 'ilike', "%{$tabla}%");
        }

        $auditorias = $query->paginate(25)->withQueryString();

        // Valores para los filtros del formulario
        $modulos = Auditoria::distinct()->orderBy('modulo')->pluck('modulo');
        $operaciones = ['CREAR', 'MODIFICAR', 'ELIMINAR', 'CANCELAR', 'INICIO_SESION'];

        return view('sistema::auditoria.index', [
            'auditorias' => $auditorias,
            'modulos' => $modulos,
            'operaciones' => $operaciones,
        ]);
    }
}
