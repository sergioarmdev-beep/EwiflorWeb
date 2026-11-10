<?php

namespace Modules\Catalogo\Controllers;

use App\Http\Controllers\Controller;
use Modules\Catalogo\Models\Producto;
use Modules\Catalogo\Models\Marca;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    private $categorias = ['ALIMENTARIA', 'PANADERIA', 'REPOSTERIA', 'CARNICA'];
    private $tipos = ['MAQUINARIA', 'REPUESTO', 'ACCESORIO'];

    public function index()
    {
        $productos = Producto::with(['marca'])->get();
        return view('catalogo::productos.index', compact('productos'));
    }

    public function create()
    {
        $marcas = Marca::all();
        $categorias = $this->categorias;
        $tipos = $this->tipos;
        return view('catalogo::productos.create', compact('marcas', 'categorias', 'tipos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:30|unique:producto,codigo',
            'nombre' => 'required|string|max:150',
            'categoria' => 'required|in:' . implode(',', $this->categorias),
            'id_marca' => 'required|exists:marca,id_marca',
            'tipo_producto' => 'required|in:' . implode(',', $this->tipos),
            'modalidad' => 'required|in:INVENTARIABLE,BAJO_PEDIDO',
            'modelo' => 'nullable|string|max:60',
            'garantia_meses' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'descripcion' => 'nullable|string',
        ]);

        // Manejo manual del checkbox (si no está marcado, no llega en el request)
        $validated['es_publico'] = $request->has('es_publico');
        $validated['estado'] = 'ACTIVO'; // Por defecto al crear

        Producto::create($validated);

        return redirect()->route('catalogo.productos.index')
            ->with('success', 'Producto creado exitosamente.');
    }
    public function catalogoPublico()
    {
        // Solo trae productos activos y marcados como públicos
        $productos = Producto::with(['marca'])
            ->where('es_publico', true)
            ->where('estado', 'ACTIVO')
            ->get();

        return view('catalogo::productos.publico', compact('productos'));
    }
}
