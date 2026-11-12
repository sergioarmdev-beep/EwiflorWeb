<?php

namespace Modules\Catalogo\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Catalogo\Models\Marca;
use Modules\Catalogo\Models\Producto;
use Modules\Catalogo\Models\ProductoImagen;
use Modules\Catalogo\Requests\ProductoRequest;
use Throwable;

class ProductoController extends Controller
{
    /**
     * Configuración opcional para la ALERTA DE STOCK BAJO.
     *
     * El módulo de catálogo no conoce dónde guarda el sistema las existencias,
     * así que se configura aquí. Mientras 'tabla' sea null la alerta queda
     * desactivada (no se muestra nada y no se hace ninguna consulta extra).
     *
     * Ejemplo si tuviera una tabla `inventario(id_producto, cantidad)`:
     *   'tabla' => 'inventario', 'columna_producto' => 'id_producto', 'columna_cantidad' => 'cantidad'
     */
    private const STOCK = [
        'tabla' => null,
        'columna_producto' => 'id_producto',
        'columna_cantidad' => 'cantidad',
    ];

    private const POR_PAGINA = 15;

    /* =========================================================
     |  LISTADO (panel)
     * ========================================================= */

    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => 'nullable|string|max:100',
            'marca' => 'nullable|integer',
            'categoria' => 'nullable|in:' . implode(',', Producto::CATEGORIAS),
            'tipo' => 'nullable|in:' . implode(',', Producto::TIPOS),
            'estado' => 'nullable|in:ACTIVO,INACTIVO',
            'stock_bajo' => 'nullable|boolean',
        ]);

        $stockExpr = $this->stockExpr();

        $query = Producto::query()
            ->select('producto.*')
            ->with(['marca', 'imagenPrincipal'])
            ->buscar($filtros['q'] ?? null)
            ->when($filtros['marca'] ?? null, fn ($q, $v) => $q->where('producto.id_marca', $v))
            ->when($filtros['categoria'] ?? null, fn ($q, $v) => $q->where('producto.categoria', $v))
            ->when($filtros['tipo'] ?? null, fn ($q, $v) => $q->where('producto.tipo_producto', $v))
            ->when($filtros['estado'] ?? null, fn ($q, $v) => $q->where('producto.estado', $v))
            ->orderBy('producto.nombre');

        if ($stockExpr) {
            $query->selectRaw("$stockExpr as stock_actual");

            if (!empty($filtros['stock_bajo'])) {
                $query->where('producto.modalidad', 'INVENTARIABLE')
                    ->whereRaw("$stockExpr <= producto.stock_minimo");
            }
        }

        $productos = $query->paginate(self::POR_PAGINA)->withQueryString();

        $resumen = [
            'total' => Producto::count(),
            'activos' => Producto::where('estado', 'ACTIVO')->count(),
            'stock_bajo' => $stockExpr
                ? Producto::where('estado', 'ACTIVO')
                    ->where('modalidad', 'INVENTARIABLE')
                    ->whereRaw("$stockExpr <= producto.stock_minimo")
                    ->count()
                : null,
        ];

        return view('catalogo::productos.index', [
            'productos' => $productos,
            'marcas' => Marca::orderBy('nombre')->get(),
            'categorias' => Producto::CATEGORIAS,
            'tipos' => Producto::TIPOS,
            'filtros' => $filtros,
            'resumen' => $resumen,
            'stockHabilitado' => (bool) $stockExpr,
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
            'tipos' => Producto::TIPOS,
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

                $rutasNuevas = $this->guardarImagenes($producto, $request->file('imagenes', []));
                $this->sincronizarVariantes($producto, $request->input('variantes', []));
            });
        } catch (Throwable $e) {
            // Si algo falla no dejamos archivos huérfanos en el disco.
            $this->borrarArchivos($rutasNuevas);
            throw $e;
        }

        return redirect()->route('catalogo.productos.index')->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Producto $producto)
    {
        $producto->load(['imagenes', 'variantes']);

        return view('catalogo::productos.edit', [
            'producto' => $producto,
            'marcas' => Marca::orderBy('nombre')->get(),
            'categorias' => Producto::CATEGORIAS,
            'tipos' => Producto::TIPOS,
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
                $rutasNuevas = $this->guardarImagenes($producto, $request->file('imagenes', []));

                // 3) Imagen principal elegida (y garantía de que siempre haya una)
                $this->asegurarPrincipal($producto, $request->integer('imagen_principal') ?: null);

                // 4) Variantes
                $this->sincronizarVariantes($producto, $request->input('variantes', []));
            });
        } catch (Throwable $e) {
            $this->borrarArchivos($rutasNuevas);
            throw $e;
        }

        // Solo se borran del disco cuando la transacción ya se confirmó.
        $this->borrarArchivos($rutasABorrar);

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

        try {
            // Primero la base de datos: si hay ventas o movimientos asociados la
            // transacción se revierte y NO se tocan los archivos.
            DB::transaction(function () use ($producto) {
                $producto->variantes()->delete();
                $producto->imagenes()->delete();
                $producto->delete();
            });
        } catch (QueryException $e) {
            return back()->with('error', 'No se puede eliminar el producto porque tiene ventas o movimientos de inventario asociados. Intente desactivarlo en su lugar.');
        }

        $this->borrarArchivos($rutas);

        return back()->with('success', 'Producto, variantes e imágenes eliminados correctamente.');
    }

    /* =========================================================
     |  CATÁLOGO PÚBLICO
     * ========================================================= */

    public function catalogoPublico()
    {
        $productos = Producto::publicos()
            ->with([
                'marca',
                'imagenes',
                'variantes' => fn ($q) => $q->where('estado', 'ACTIVO'),
            ])
            ->orderBy('nombre')
            ->get();

        // Datos que consume el modal de detalle (se serializan como JSON en la vista).
        $detalle = $productos->mapWithKeys(function (Producto $p) {
            return [$p->id_producto => [
                'id' => $p->id_producto,
                'nombre' => $p->nombre,
                'marca' => $p->marca?->nombre,
                'pais' => $p->marca?->pais_origen,
                'modelo' => $p->modelo,
                'categoria' => $p->categoria,
                'tipo' => $p->tipo_producto,
                'garantia' => (int) $p->garantia_meses,
                'descripcion' => $p->descripcion,
                'imagenes' => $p->imagenes->map(fn ($i) => [
                    'url' => $i->url,
                    'alt' => $i->texto_alternativo ?: $p->nombre,
                ])->values(),
                'variantes' => $p->variantes->map(fn ($v) => [
                    'sku' => $v->sku,
                    'color' => $v->color,
                    'caracteristicas' => $v->caracteristicas,
                ])->values(),
            ]];
        });

        return view('catalogo::productos.publico', compact('productos', 'detalle'));
    }

    /* =========================================================
     |  HELPERS PRIVADOS
     * ========================================================= */

    /** Subconsulta SQL con el stock actual, o null si no está configurado. */
    private function stockExpr(): ?string
    {
        $tabla = self::STOCK['tabla'];
        if (!$tabla) {
            return null;
        }

        $fk = self::STOCK['columna_producto'];
        $cant = self::STOCK['columna_cantidad'];

        // Los identificadores vienen de la constante de arriba, nunca del usuario.
        return "(select COALESCE(SUM({$tabla}.{$cant}), 0) from {$tabla} where {$tabla}.{$fk} = producto.id_producto)";
    }

    /**
     * Guarda las imágenes subidas. La primera pasa a ser la principal solo si
     * el producto todavía no tiene una. Devuelve las rutas guardadas.
     *
     * @param  array<int, \Illuminate\Http\UploadedFile>  $archivos
     * @return string[]
     */
    private function guardarImagenes(Producto $producto, array $archivos): array
    {
        if (!$archivos) {
            return [];
        }

        // Se consulta el modelo directo (sin el orderBy de la relación) porque
        // PostgreSQL no permite ORDER BY junto a una función de agregado.
        $existentes = ProductoImagen::where('id_producto', $producto->id_producto);
        $tienePrincipal = (clone $existentes)->where('es_principal', true)->exists();
        $maxOrden = (clone $existentes)->max('orden');
        $orden = $maxOrden === null ? 0 : ((int) $maxOrden + 1);
        $rutas = [];

        foreach (array_values($archivos) as $i => $archivo) {
            $ruta = $archivo->store('productos', 'public');
            $rutas[] = $ruta;

            $producto->imagenes()->create([
                'ruta_imagen' => $ruta,
                'es_principal' => !$tienePrincipal && $i === 0,
                'orden' => $orden + $i,
            ]);
        }

        return $rutas;
    }

    /** Deja exactamente una imagen principal (la elegida, o la primera si no hay). */
    private function asegurarPrincipal(Producto $producto, ?int $idElegida): void
    {
        $imagenes = $producto->imagenes()->get();
        if ($imagenes->isEmpty()) {
            return;
        }

        $principal = $idElegida ? $imagenes->firstWhere('id_imagen', $idElegida) : null;
        $principal ??= $imagenes->firstWhere('es_principal', true) ?? $imagenes->first();

        ProductoImagen::where('id_producto', $producto->id_producto)
            ->where('id_imagen', '!=', $principal->id_imagen)
            ->where('es_principal', true)
            ->update(['es_principal' => false]);

        if (!$principal->es_principal) {
            $principal->update(['es_principal' => true]);
        }
    }

    /**
     * Sincroniza las variantes con lo enviado en el formulario:
     * actualiza las existentes, crea las nuevas y retira las que se quitaron.
     * Si una variante quitada tiene movimientos asociados (FK), se desactiva en vez de borrarse.
     */
    private function sincronizarVariantes(Producto $producto, array $variantes): void
    {
        $conservadas = [];

        foreach ($variantes as $v) {
            $datos = [
                'sku' => trim($v['sku']),
                'color' => ($v['color'] ?? null) ?: null,
                'caracteristicas' => ($v['caracteristicas'] ?? null) ?: null,
                'estado' => $v['estado'] ?? 'ACTIVO',
            ];

            $existente = !empty($v['id_variante'])
                ? $producto->variantes()->where('id_variante', $v['id_variante'])->first()
                : null;

            if ($existente) {
                $existente->update($datos);
                $conservadas[] = $existente->id_variante;
            } else {
                $conservadas[] = $producto->variantes()->create($datos)->id_variante;
            }
        }

        $sobrantes = $producto->variantes()->whereNotIn('id_variante', $conservadas)->get();

        foreach ($sobrantes as $variante) {
            try {
                // Transacción anidada = savepoint: un fallo de FK no aborta la transacción externa.
                DB::transaction(fn () => $variante->delete());
            } catch (QueryException $e) {
                $variante->update(['estado' => 'INACTIVO']);
            }
        }
    }

    private function borrarArchivos(array $rutas): void
    {
        foreach ($rutas as $ruta) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
