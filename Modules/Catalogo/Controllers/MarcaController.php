<?php

namespace Modules\Catalogo\Controllers;

use App\Http\Controllers\Controller;
use Modules\Catalogo\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas = Marca::orderBy('nombre')->get();
        return view('catalogo::marcas.index', compact('marcas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:60|unique:marca,nombre',
            'pais_origen' => 'nullable|string|max:50',
        ]);

        Marca::create($validated);
        return back()->with('success', 'Marca agregada correctamente.');
    }

    public function update(Request $request, Marca $marca)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:60|unique:marca,nombre,' . $marca->id_marca . ',id_marca',
            'pais_origen' => 'nullable|string|max:50',
        ]);

        $marca->update($validated);
        return back()->with('success', 'Marca actualizada.');
    }
    public function destroy(Marca $marca)
    {
        try {
            $marca->delete();
            return back()->with('success', 'Marca eliminada correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Error 23503 en PostgreSQL significa violación de llave foránea
            return back()->with('error', 'No se puede eliminar la marca porque tiene productos asociados.');
        }
    }
}
