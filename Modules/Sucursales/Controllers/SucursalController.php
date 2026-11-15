<?php

namespace Modules\Sucursales\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventario\Models\Sucursal;
use Modules\Sucursales\Requests\StoreSucursalRequest;
use Modules\Sucursales\Requests\UpdateSucursalRequest;
use Modules\Sucursales\Services\SucursalService;
use Modules\Sucursales\Support\AccesoSucursal;

class SucursalController extends Controller
{
    public function __construct(private SucursalService $service) {}

    public function index(Request $request)
    {
        $usuario = $request->user();
        $esGestor = AccesoSucursal::esGestor($usuario);

        $query = Sucursal::query()
            ->with('almacenes')
            ->withCount([
                'usuarios as usuarios_activos' => fn ($q) => $q->where('estado', 'ACTIVO'),
            ]);

        if (! $esGestor) {
            // Alcance: los roles no gestores solo ven su sucursal asignada.
            $query->where('id_sucursal', $usuario->id_sucursal ?? 0);
        }

        if ($s = trim((string) $request->input('buscar'))) {
            $query->where(function ($w) use ($s) {
                $w->where('nombre', 'ilike', "%{$s}%")
                    ->orWhere('direccion', 'ilike', "%{$s}%");
            });
        }

        if ($e = $request->input('estado')) {
            $query->where('estado', $e);
        }

        $sucursales = $query->orderBy('nombre')->get();

        return view('sucursales::index', [
            'sucursales' => $sucursales,
            'esGestor' => $esGestor,
        ]);
    }

    public function show(Request $request, Sucursal $sucursal)
    {
        if (! AccesoSucursal::puedeOperarEn($request->user(), $sucursal->id_sucursal)) {
            abort(403, 'No tienes acceso a esta sucursal.');
        }

        return view('sucursales::show', [
            'sucursal' => $sucursal->load('almacenes'),
            'usuarios' => AccesoSucursal::usuariosDe($sucursal->id_sucursal),
            'esGestor' => AccesoSucursal::esGestor($request->user()),
        ]);
    }

    public function create()
    {
        return view('sucursales::create', ['sucursal' => new Sucursal]);
    }

    public function store(StoreSucursalRequest $request)
    {
        $sucursal = Sucursal::create($request->validated() + ['estado' => 'ACTIVO']);

        return redirect()
            ->route('sucursales.show', $sucursal)
            ->with('success', 'Sucursal creada correctamente.');
    }

    public function edit(Sucursal $sucursal)
    {
        return view('sucursales::edit', ['sucursal' => $sucursal]);
    }

    public function update(UpdateSucursalRequest $request, Sucursal $sucursal)
    {
        $sucursal->update($request->validated());

        return redirect()
            ->route('sucursales.show', $sucursal)
            ->with('success', 'Sucursal actualizada.');
    }

    public function estado(Sucursal $sucursal)
    {
        $this->service->cambiarEstado($sucursal);

        return back()->with('success', "Sucursal {$sucursal->estado}.");
    }
}
