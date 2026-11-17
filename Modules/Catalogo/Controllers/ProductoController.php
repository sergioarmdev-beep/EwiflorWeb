<?php

namespace Modules\Catalogo\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Catalogo\Models\Marca;
use Modules\Catalogo\Models\Producto;
use Modules\Catalogo\Requests\ProductoRequest;
use Modules\Catalogo\Services\ProductoService;
use Modules\Promociones\Services\PromocionService;
use Throwable;

class ProductoController extends Controller
{
    private const POR_PAGINA = 15;

    public function __construct(private ProductoService $productoService, private PromocionService $promociones) {}

    /* =========================================================
     |  LISTADO (panel)
     * ========================================================= */

    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => 'nullable|string|max:100',
            'marca' => 'nullable|integer',
            'categoria' => 'nullable|in:'.implode(',', Producto::CATEGORIAS),
            'tipo' => 'nullable|in:'.implode(',', Producto::TIPOS),
            'estado' => 'nullable|in:ACTIVO,INACTIVO',
            'stock_bajo' => 'nullable|boolean',
            'promocion' => 'nullable|boolean',
        ]);

        $query = Producto::query()
            ->select('producto.*')
            ->with(['marca', 'imagenPrincipal', 'variantes:id_variante,id_producto'])
            ->buscar($filtros['q'] ?? null)
            ->when($filtros['marca'] ?? null, fn ($q, $v) => $q->where('producto.id_marca', $v))
            ->when($filtros['categoria'] ?? null, fn ($q, $v) => $q->where('producto.categoria', $v))
            ->when($filtros['tipo'] ?? null, fn ($q, $v) => $q->where('producto.tipo_producto', $v))
            ->when($filtros['estado'] ?? null, fn ($q, $v) => $q->where('producto.estado', $v))
            ->orderBy('producto.nombre')
            ->selectRaw(ProductoService::SQL_STOCK_TOTAL.' as stock_total')
            ->selectRaw(ProductoService::SQL_STOCK_DISPONIBLE.' as stock_actual');

        if (! empty($filtros['stock_bajo'])) {
            $query->where('producto.modalidad', 'INVENTARIABLE')
                ->whereRaw(ProductoService::SQL_STOCK_DISPONIBLE.' <= producto.stock_minimo');
        }

        // Productos con alguna promoción vigente en alguna de sus variantes.
        if (! empty($filtros['promocion'])) {
            $query->whereHas('variantes', fn ($q) => $q
                ->whereHas('promociones', fn ($p) => $p->vigentes()));
        }

        $productos = $query->paginate(self::POR_PAGINA)->withQueryString();

        $resumen = [
            'total' => Producto::count(),
            'activos' => Producto::where('estado', 'ACTIVO')->count(),
            'stock_bajo' => Producto::where('estado', 'ACTIVO')
                ->where('modalidad', 'INVENTARIABLE')
                ->whereRaw(ProductoService::SQL_STOCK_DISPONIBLE.' <= producto.stock_minimo')
                ->count(),
            'con_promocion' => Producto::where('estado', 'ACTIVO')
                ->whereHas('variantes', fn ($q) => $q
                    ->whereHas('promociones', fn ($p) => $p->vigentes()))
                ->count(),
        ];

        return view('catalogo::productos.index', [
            'productos' => $productos,
            'marcas' => Marca::orderBy('nombre')->get(),
            'categorias' => Producto::CATEGORIAS,
            'tipos' => Producto::TIPOS,
            'filtros' => $filtros,
            'resumen' => $resumen,
            // id_variante => promo vigente, para pintar los badges del listado
            'promocionesVigentes' => $this->promociones->mapaVigente(
                $productos->getCollection()->flatMap(fn ($p) => $p->variantes)
            ),
        ]);
    }

    /* =========================================================
     |  CREAR / EDITAR
     * ========================================================= */

    public function create()
    {
        return view('catalogo::productos.create', [
            'marcas' => Marca::orderBy('nombre')->get(),
            'categorias' => Producto::CATEGORIAS,
            // Los combos se crean desde el módulo de Promociones.
            'tipos' => Producto::tiposCatalogo(),
        ]);
    }

    public function store(ProductoRequest $request)
    {
        $datos = $request->safe()->only([
            'codigo', 'nombre', 'descripcion', 'categoria', 'id_marca', 'modelo',
            'tipo_producto', 'modalidad', 'stock_minimo', 'garantia_meses',
        ]);
        $datos['es_publico'] = $request->boolean('es_publico');
        $datos['estado'] = 'ACTIVO';

        $rutasNuevas = [];

        try {
            DB::transaction(function () use ($request, $datos, &$rutasNuevas) {
                $producto = Producto::create($datos);

                $rutasNuevas = $this->productoService->guardarImagenes($producto, $request->file('imagenes', []));

                // Guardar documentos
                $documentosNuevos = $this->productoService->guardarDocumentos($producto, $request->file('documentos', []));
                $rutasNuevas = array_merge($rutasNuevas, $documentosNuevos);

                $this->productoService->sincronizarVariantes(
                    $producto,
                    $request->input('variantes', []),
                    $this->puedeEditarPrecio()
                );

                // Sincronizar repuestos (si es maquinaria)
                if ($producto->tipo_producto === 'MAQUINARIA') {
                    $producto->repuestos()->sync($request->input('repuestos', []));
                }
            });
        } catch (Throwable $e) {
            // Si algo falla no dejamos archivos huérfanos en el disco.
            $this->productoService->borrarArchivos($rutasNuevas);
            throw $e;
        }

        return redirect()->route('catalogo.productos.index')->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Producto $producto)
    {
        $producto->load(['imagenes', 'variantes', 'documentos', 'repuestos']);

        return view('catalogo::productos.edit', [
            'producto' => $producto,
            'marcas' => Marca::orderBy('nombre')->get(),
            'categorias' => Producto::CATEGORIAS,
            // Los combos se gestionan desde el módulo de Promociones.
            'tipos' => $producto->tipo_producto === 'COMBO'
                ? ['COMBO']
                : Producto::tiposCatalogo(),
            'promocionesVigentes' => $this->promociones->mapaVigente($producto->variantes),
        ]);
    }

    public function update(ProductoRequest $request, Producto $producto)
    {
        $datos = $request->safe()->only([
            'codigo', 'nombre', 'descripcion', 'categoria', 'id_marca', 'modelo',
            'tipo_producto', 'modalidad', 'stock_minimo', 'garantia_meses',
        ]);
        $datos['es_publico'] = $request->boolean('es_publico');

        $rutasNuevas = [];
        $rutasABorrar = [];

        try {
            DB::transaction(function () use ($request, $producto, $datos, &$rutasNuevas, &$rutasABorrar) {
                $producto->update($datos);

                // 1) Imágenes marcadas para eliminar (solo las de este producto)
                $idsEliminar = array_map('intval', $request->input('eliminar_imagenes', []));
                if ($idsEliminar) {
                    $aEliminar = $producto->imagenes()->whereIn('id_imagen', $idsEliminar)->get();
                    foreach ($aEliminar as $img) {
                        $rutasABorrar[] = $img->ruta_imagen;
                        $img->delete();
                    }
                }

                // 2) Imágenes nuevas
                $rutasNuevas = $this->productoService->guardarImagenes($producto, $request->file('imagenes', []));

                // 3) Imagen principal elegida (y garantía de que siempre haya una)
                $this->productoService->asegurarPrincipal($producto, $request->integer('imagen_principal') ?: null);

                // 4) Variantes
                $this->productoService->sincronizarVariantes(
                    $producto,
                    $request->input('variantes', []),
                    $this->puedeEditarPrecio()
                );

                // 5) Documentos marcados para eliminar
                $documentosEliminar = array_map('intval', $request->input('eliminar_documentos', []));
                if ($documentosEliminar) {
                    $docsAEliminar = $producto->documentos()->whereIn('id_documento', $documentosEliminar)->get();
                    foreach ($docsAEliminar as $doc) {
                        $rutasABorrar[] = $doc->ruta_archivo;
                        $doc->delete();
                    }
                }

                // 6) Documentos nuevos
                $documentosRutas = $this->productoService->guardarDocumentos($producto, $request->file('documentos', []));
                $rutasNuevas = array_merge($rutasNuevas, $documentosRutas);

                // 7) Repuestos (si es maquinaria)
                if ($producto->tipo_producto === 'MAQUINARIA') {
                    $producto->repuestos()->sync($request->input('repuestos', []));
                } else {
                    // Si ya no es maquinaria, limpiar repuestos asociados
                    $producto->repuestos()->detach();
                }
            });
        } catch (Throwable $e) {
            $this->productoService->borrarArchivos($rutasNuevas);
            throw $e;
        }

        // Solo se borran del disco cuando la transacción ya se confirmó.
        $this->productoService->borrarArchivos($rutasABorrar);

        return redirect()->route('catalogo.productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    /* =========================================================
     |  ESTADO / ELIMINAR
     * ========================================================= */

    public function toggleEstado(Producto $producto)
    {
        $nuevoEstado = $producto->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $producto->update(['estado' => $nuevoEstado]);

        $mensaje = $nuevoEstado === 'ACTIVO' ? 'Producto activado.' : 'Producto desactivado (ya no se venderá).';

        return back()->with('success', $mensaje);
    }

    public function destroy(Producto $producto)
    {
        $rutas = $producto->imagenes()->pluck('ruta_imagen')->all();
        $rutasDocumentos = $producto->documentos()->pluck('ruta_archivo')->all();

        try {
            // Primero la base de datos: si hay ventas o movimientos asociados la
            // transacción se revierte y NO se tocan los archivos.
            DB::transaction(function () use ($producto) {
                // Los repuestos se desvinculan (tabla pivote)
                $producto->repuestos()->detach();
                // Los documentos se eliminan (CASCADE en FK)
                $producto->documentos()->delete();
                $producto->variantes()->delete();
                $producto->imagenes()->delete();
                $producto->delete();
            });
        } catch (QueryException $e) {
            return back()->with('error', 'No se puede eliminar el producto porque tiene ventas o movimientos de inventario asociados. Intente desactivarlo en su lugar.');
        }

        $this->productoService->borrarArchivos($rutas);
        $this->productoService->borrarArchivos($rutasDocumentos);

        return back()->with('success', 'Producto, variantes, imágenes, documentos y repuestos eliminados correctamente.');
    }

    /* =========================================================
     |  HELPERS PRIVADOS
     * ========================================================= */

    /** El precio de lista solo lo pueden modificar Gerente y Administrador (RN-004). */
    private function puedeEditarPrecio(): bool
    {
        return in_array(
            auth()->user()?->nombre_rol,
            ProductoService::ROLES_EDITAN_PRECIO,
            true
        );
    }
}
