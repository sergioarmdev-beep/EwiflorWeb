<?php

namespace Modules\Publico\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Catalogo\Models\Producto;
use Modules\Catalogo\Models\Categoria;
use Modules\Promociones\Services\PromocionService;
use Modules\ServicioTecnico\Services\SolicitudService;
use Modules\Ventas\Models\DetalleVenta;

class PublicoController extends Controller
{
    /** Cuántos productos se muestran como vista previa en el inicio. */
    private const PRODUCTOS_EN_INICIO = 6;

    /** Cuántos productos por página en el catálogo completo. */
    private const PRODUCTOS_POR_PAGINA = 12;

    public function __construct(
        private SolicitudService $solicitudes,
        private PromocionService $promociones,
    ) {}

    /**
     * Inicio público: hero, equipos destacados (los más vendidos), categorías
     * y el resto de secciones institucionales de la portada.
     */
    public function inicio()
    {
        $datos = $this->datosCatalogo();
        $productos = $datos['productos'];

        // Ranking de productos por unidades vendidas (detalle_venta).
        $destacados = collect($this->idsMasVendidos(self::PRODUCTOS_EN_INICIO))
            ->map(fn (int $id) => $productos->firstWhere('id_producto', $id))
            ->filter()
            ->values();

        // Si aún no hay ventas suficientes, se completa con el catálogo público.
        if ($destacados->count() < self::PRODUCTOS_EN_INICIO) {
            $extra = $productos
                ->reject(fn (Producto $p) => $destacados->contains('id_producto', $p->id_producto))
                ->take(self::PRODUCTOS_EN_INICIO - $destacados->count());

            $destacados = $destacados->concat($extra)->values();
        }

        return view('publico::publico', array_merge($datos, [
            'destacados' => $destacados,
        ]));
    }

    /** Página completa del catálogo, con filtro por categoría (?categoria=slug) y paginación. */
    public function catalogo(Request $request)
    {
        $datos = $this->datosCatalogo();

        $slug = strtoupper((string) $request->query('categoria', ''));
        $cat  = $datos['categorias']->firstWhere('slug', $slug);

        // Solo se preselecciona si existe y tiene productos.
        $categoriaActiva = ($cat && $cat['total'] > 0) ? $slug : null;

        // Filtrar productos por categoría si hay una activa.
        $productosFiltrados = $datos['productos'];
        if ($categoriaActiva) {
            $productosFiltrados = $productosFiltrados->where('categoria', $categoriaActiva);
        }

        // Búsqueda desde el navbar: filtra sobre todo el catálogo público.
        $termino = trim((string) $request->query('search', ''));
        if ($termino !== '') {
            $aguja = $this->normalizar($termino);

            $productosFiltrados = $productosFiltrados
                ->filter(function (Producto $p) use ($aguja) {
                    $texto = $this->normalizar(implode(' ', array_filter([
                        $p->codigo,
                        $p->nombre,
                        $p->modelo,
                        $p->categoria,
                        $p->descripcion,
                        $p->marca?->nombre,
                    ])));

                    return str_contains($texto, $aguja);
                })
                ->values();
        }

        // Paginación (12 por página).
        $pagina = (int) $request->query('pagina', 1);
        $porPagina = self::PRODUCTOS_POR_PAGINA;
        $offset = ($pagina - 1) * $porPagina;
        $productosPaginados = $productosFiltrados->slice($offset, $porPagina)->values();

        $paginator = new LengthAwarePaginator(
            $productosPaginados,
            $productosFiltrados->count(),
            $porPagina,
            $pagina,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Recalcular promociones solo para los productos de esta página.
        $promociones = $this->promociones->mapaVigente(
            $productosPaginados->flatMap(fn(Producto $p) => $p->variantes)
        );

        $detalle = $productosPaginados->mapWithKeys(function (Producto $p) use ($promociones) {
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
                'es_combo' => $p->esCombo(),
                'componentes' => $p->esCombo()
                    ? $p->componentesCombo->map(fn($c) => ($c->componente?->nombre ?? 'Producto #' . $c->id_componente) . ' ×' . $c->cantidad)->implode(', ')
                    : null,
                'imagenes' => $p->imagenes->map(fn($i) => [
                    'url' => $i->url,
                    'alt' => $i->texto_alternativo ?: $p->nombre,
                ])->values(),
                'variantes' => $p->variantes->map(fn($v) => [
                    'sku' => $v->sku,
                    'color' => $v->color,
                    'caracteristicas' => $v->caracteristicas,
                    'precio' => $v->precio_usd !== null ? (float) $v->precio_usd : null,
                    'promo' => $promociones[$v->id_variante] ?? null,
                ])->values(),
            ]];
        });

        return view('publico::catalogo', array_merge($datos, [
            'productos'       => $productosPaginados,
            'paginator'       => $paginator,
            'totalProductos'  => $productosFiltrados->count(),
            'detalle'         => $detalle,
            'promociones'     => $promociones,
            'categorias'      => $datos['categorias'],
            'categoriaActiva' => $categoriaActiva,
        ]));
    }

    /** Página "Nosotros" (solo vista). */
    public function nosotros()
    {
        return view('publico::nosotros');
    }

    /** Página independiente de servicio técnico (solo vista; el POST ya existe). */
    public function servicioTecnico()
    {
        return view('publico::servicio-tecnico');
    }

    /**
     * IDs de los productos públicos con más unidades vendidas.
     * Suma detalle_venta → variante → producto; nunca precios.
     */
    private function idsMasVendidos(int $limite): array
    {
        return DetalleVenta::query()
            ->join('variante_producto as vp', 'vp.id_variante', '=', 'detalle_venta.id_variante')
            ->join('producto as p', 'p.id_producto', '=', 'vp.id_producto')
            ->where('p.es_publico', true)
            ->where('p.estado', 'ACTIVO')
            ->selectRaw('p.id_producto, SUM(detalle_venta.cantidad) AS vendidos')
            ->groupBy('p.id_producto')
            ->orderByDesc('vendidos')
            ->limit($limite)
            ->pluck('p.id_producto')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** Texto en minúsculas y sin tildes, para comparar búsquedas. */
    private function normalizar(string $texto): string
    {
        return Str::ascii(mb_strtolower(trim($texto)));
    }

    /**
     * Datos compartidos por inicio y catálogo.
     * $limite: si se indica, solo se muestran/serializan esos productos,
     * pero las categorías siguen contando TODOS los productos públicos.
     */
    private function datosCatalogo(?int $limite = null): array
    {
        $todos = Producto::publicos()
            ->with([
                'marca',
                'imagenes',
                'componentesCombo.componente',
                'variantes' => fn($q) => $q->where('estado', 'ACTIVO'),
            ])
            ->orderBy('nombre')
            ->get();

        $categorias = $this->construirCategorias($todos);

        $productos = $limite ? $todos->take($limite)->values() : $todos;

        $promociones = $this->promociones->mapaVigente(
            $productos->flatMap(fn(Producto $p) => $p->variantes)
        );

        $detalle = $productos->mapWithKeys(function (Producto $p) use ($promociones) {
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
                'es_combo' => $p->esCombo(),
                'componentes' => $p->esCombo()
                    ? $p->componentesCombo->map(fn($c) => ($c->componente?->nombre ?? 'Producto #' . $c->id_componente) . ' ×' . $c->cantidad)->implode(', ')
                    : null,
                'imagenes' => $p->imagenes->map(fn($i) => [
                    'url' => $i->url,
                    'alt' => $i->texto_alternativo ?: $p->nombre,
                ])->values(),
                'variantes' => $p->variantes->map(fn($v) => [
                    'sku' => $v->sku,
                    'color' => $v->color,
                    'caracteristicas' => $v->caracteristicas,
                    'precio' => $v->precio_usd !== null ? (float) $v->precio_usd : null,
                    'promo' => $promociones[$v->id_variante] ?? null,
                ])->values(),
            ]];
        });

        return [
            'productos'       => $productos,
            'totalProductos'  => $todos->count(),
            'detalle'         => $detalle,
            'promociones'     => $promociones,
            'categorias'      => $categorias,
            'categoriaActiva' => null,
        ];
    }

    /**
     * Devuelve una colección con TODAS las categorías de Producto::CATEGORIAS,
     * aunque no tengan productos todavía. Así la sección nunca queda vacía.
     */
    private function construirCategorias(Collection $productos): Collection
    {
        $agrupados = $productos->groupBy('categoria');

        // Lee metadata de DB; si la tabla está vacía, cae a defaults seguros.
        $registros = Categoria::activas()->get()->keyBy('slug');

        $slugs = $registros->isNotEmpty()
            ? $registros->keys()->all()
            : Producto::CATEGORIAS; // fallback si no corrió el seeder

        return collect($slugs)->map(function (string $slug) use ($agrupados, $registros) {
            $grupo = $agrupados->get($slug, collect());
            $cat   = $registros->get($slug);

            // Imagen de respaldo: primer producto de la categoría con imagen.
            $conImagen = $grupo->first(fn($p) => $p->imagenes->isNotEmpty());

            return [
                'slug'        => $slug,
                'titulo'      => $cat?->titulo      ?? ucfirst(mb_strtolower($slug)),
                'descripcion' => $cat?->descripcion ?? '',
                'imagen'      => $cat?->imagen ?: $conImagen?->imagenes->first()?->url,
                'total'       => $grupo->count(),
            ];
        })->values();
    }


    public function contactarServicioTecnico(Request $request)
    {
        $validado = $request->validate([
            'nombre' => ['required', 'string', 'min:2', 'max:100'],
            'telefono' => ['required', 'string', 'min:7', 'max:20'],
            'mensaje' => ['nullable', 'string', 'min:5', 'max:1000'],
        ]);

        $cliente = $this->solicitudes->buscarCliente($validado['nombre'], $validado['telefono']);

        if ($cliente === null) {
            return back()
                ->withInput()
                ->with('error', 'No se encontró un cliente con ese nombre y número de celular. Asegúrate de estar registrado con compras previas.');
        }

        $solicitud = $this->solicitudes->crearPublica($cliente->id_cliente, [
            'descripcion_falla' => $validado['mensaje'] ?? 'Solicitud de contacto desde la web pública.',
            'id_garantia' => null,
        ]);

        return redirect()
            ->route('serviciotecnico.publico.panel')
            ->with('success', 'Tu solicitud de contacto ha sido registrada. Un técnico la revisará pronto.');
    }

    /** Página de detalle de producto público (URL única para SEO y compartición). */
    public function productoDetalle($id)
    {
        $producto = Producto::publicos()
            ->with([
                'marca',
                'imagenes',
                'variantes' => fn($q) => $q->where('estado', 'ACTIVO'),
            ])
            ->findOrFail($id);

        return view('publico::producto-detalle', [
            'producto' => $producto,
        ]);
    }
}
