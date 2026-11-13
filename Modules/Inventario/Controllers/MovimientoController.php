<?php

namespace Modules\Inventario\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalogo\Models\VarianteProducto;
use Modules\Inventario\Models\Almacen;
use Modules\Inventario\Models\Movimiento;
use Modules\Inventario\Requests\StoreMovimientoRequest;
use Modules\Inventario\Services\MovimientoService;

class MovimientoController extends Controller
{
    public function __construct(private MovimientoService $service) {}

    public function index(Request $request)
    {
        $query = Movimiento::query()->with([
            'usuario',
            'almacenOrigen',
            'almacenDestino',
            'detalles.variante.producto',
        ]);

        if ($tipo = $request->input('tipo')) {
            $query->where('tipo', $tipo);
        }

        $movimientos = $query
            ->orderByDesc('fecha_hora')
            ->orderByDesc('id_movimiento')
            ->paginate(20)
            ->withQueryString();

        return view('inventario::movimientos.index', [
            'movimientos' => $movimientos,
            'filtros'     => $request->only(['tipo']),
        ]);
    }

    public function create()
    {
        return view('inventario::movimientos.create', [
            'almacenes'  => Almacen::where('estado', 'ACTIVO')->orderBy('nombre')->get(),
            'variantes'  => VarianteProducto::with('producto')
                ->where('estado', 'ACTIVO')
                ->orderBy('sku')
                ->get(),
        ]);
    }

    public function store(StoreMovimientoRequest $request)
    {
        try {
            $id = $this->service->registrar($request->validated(), $request->user());

            return redirect()
                ->route('inventario.movimientos.index')
                ->with('success', "Movimiento #{$id} registrado correctamente.");
        } catch (BusinessRuleException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
