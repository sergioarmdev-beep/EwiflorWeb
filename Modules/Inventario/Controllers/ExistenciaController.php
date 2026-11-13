<?php

namespace Modules\Inventario\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Inventario\Models\Almacen;

class ExistenciaController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('v_reporte_existencias');

        if ($almacen = $request->input('almacen')) {
            $query->where('almacen', $almacen);
        }

        if ($buscar = trim((string) $request->input('buscar'))) {
            $query->where(function ($q) use ($buscar) {
                $q->where('producto', 'ILIKE', "%{$buscar}%")
                    ->orWhere('sku', 'ILIKE', "%{$buscar}%")
                    ->orWhere('codigo', 'ILIKE', "%{$buscar}%");
            });
        }

        if ($request->input('solo_alerta') === '1') {
            $query->where('alerta', 'REPONER');
        }

        $existencias = $query
            ->orderBy('producto')
            ->orderBy('almacen')
            ->paginate(25)
            ->withQueryString();

        return view('inventario::existencias.index', [
            'existencias' => $existencias,
            'almacenes'   => Almacen::orderBy('nombre')->get(['id_almacen', 'nombre']),
            'filtros'     => $request->only(['almacen', 'buscar', 'solo_alerta']),
        ]);
    }
}
