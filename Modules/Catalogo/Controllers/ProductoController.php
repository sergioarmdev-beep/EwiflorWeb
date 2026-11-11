<?php

namespace Modules\Catalogo\Controllers;

use App\Http\Controllers\Controller;
use Modules\Catalogo\Models\Producto;
use Modules\Catalogo\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // 2MB Max
        ]);

        $validated['es_publico'] = $request->has('es_publico');
        $validated['estado'] = 'ACTIVO';

        // 1. Crear el producto
        $producto = Producto::create($validated);

        // 2. Guardar la imagen si se subió
        if ($request->hasFile('imagen')) {
            $ruta = $request->file('imagen')->store('productos', 'public');
            $producto->imagenes()->create([
                'ruta_imagen' => $ruta,
                'es_principal' => true,
                'orden' => 0
            ]);
        }

        return redirect()->route('catalogo.productos.index')->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Producto $producto)
    {
        $marcas = Marca::all();
        $categorias = $this->categorias;
        $tipos = $this->tipos;
        $imagenPrincipal = $producto->imagenPrincipal;
        return view('catalogo::productos.edit', compact('producto', 'marcas', 'categorias', 'tipos', 'imagenPrincipal'));
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:30|unique:producto,codigo,' . $producto->id_producto . ',id_producto',
            'nombre' => 'required|string|max:150',
            'categoria' => 'required|in:' . implode(',', $this->categorias),
            'id_marca' => 'required|exists:marca,id_marca',
            'tipo_producto' => 'required|in:' . implode(',', $this->tipos),
            'modalidad' => 'required|in:INVENTARIABLE,BAJO_PEDIDO',
            'modelo' => 'nullable|string|max:60',
            'garantia_meses' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $validated['es_publico'] = $request->has('es_publico');
        $producto->update($validated);

        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior
            $imagenAnterior = $producto->imagenPrincipal;
            if ($imagenAnterior) {
                Storage::disk('public')->delete($imagenAnterior->ruta_imagen);
                $imagenAnterior->delete();
            }

            // Guardar nueva
            $ruta = $request->file('imagen')->store('productos', 'public');
            $producto->imagenes()->create([
                'ruta_imagen' => $ruta,
                'es_principal' => true,
                'orden' => 0
            ]);
        }

        return redirect()->route('catalogo.productos.index')->with('success', 'Producto actualizado correctamente.');
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
    public function toggleEstado(Producto $producto)
    {
        $nuevoEstado = $producto->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $producto->update(['estado' => $nuevoEstado]);

        $mensaje = $nuevoEstado === 'ACTIVO' ? 'Producto activado.' : 'Producto desactivado (ya no se venderá).';
        return back()->with('success', $mensaje);
    }

    public function destroy(Producto $producto)
    {
        try {
            foreach ($producto->imagenes as $imagen) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($imagen->ruta_imagen);
            }

            $producto->delete();
            return back()->with('success', 'Producto e imágenes eliminados físicamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'No se puede eliminar el producto porque tiene ventas o movimientos de inventario asociados. Intente desactivarlo en su lugar.');
        }
    }
}
