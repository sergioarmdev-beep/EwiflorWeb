<?php

namespace Modules\Sucursales\Controllers;

use App\Http\Controllers\Controller;
use Modules\Inventario\Models\Almacen;
use Modules\Inventario\Models\Sucursal;
use Modules\Sucursales\Requests\StoreAlmacenRequest;
use Modules\Sucursales\Requests\UpdateAlmacenRequest;
use Modules\Sucursales\Services\SucursalService;
use Modules\Sucursales\Support\AccesoSucursal;


class AlmacenController extends Controller
{
    public function __construct(private SucursalService $service) {}

    public function create(Sucursal $sucursal)
    {
        return view('sucursales::almacenes.create', [
            'sucursal' => $sucursal,
            'almacen' => new Almacen,
        ]);
    }

    public function store(StoreAlmacenRequest $request, Sucursal $sucursal)
    {
        Almacen::create($request->validated() + ['estado' => 'ACTIVO']);

        return redirect()
            ->route('sucursales.show', $sucursal)
            ->with('success', 'Almacén creado correctamente.');
    }

    public function edit(Almacen $almacen)
    {
        $almacen->load('sucursal');

        return view('sucursales::almacenes.edit', [
            'almacen' => $almacen,
            'sucursales' => AccesoSucursal::sucursalesActivas(auth()->user()),
        ]);
    }

    public function update(UpdateAlmacenRequest $request, Almacen $almacen)
    {
        $almacen->update($request->validated());
        $almacen->load('sucursal');

        return redirect()
            ->route('sucursales.show', $almacen->sucursal)
            ->with('success', 'Almacén actualizado.');
    }

    public function estado(Almacen $almacen)
    {
        $this->service->cambiarEstadoAlmacen($almacen);

        return back()->with('success', "Almacén {$almacen->estado}.");
    }
}
