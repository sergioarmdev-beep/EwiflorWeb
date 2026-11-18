<?php

namespace Modules\Publico\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Catalogo\Models\Producto;
use Modules\Promociones\Services\PromocionService;
use Modules\ServicioTecnico\Services\SolicitudService;
use Modules\Catalogo\Models\Categoria;

class PublicoController extends Controller
{
    /** Cuántos productos se muestran como vista previa en el inicio. */
    private const PRODUCTOS_EN_INICIO = 6;

    public function __construct(
        private SolicitudService $solicitudes,
        private PromocionService $promociones,
    ) {}

    /** Inicio: vista previa del catálogo (primeros N productos) + categorías. */
    public function catalogoPublico()
    {
        return view('publico::publico', $this->datosCatalogo(self::PRODUCTOS_EN_INICIO));
    }

    /** Página completa del catálogo, con filtro por categoría (?categoria=slug). */
    public function catalogo(Request $request)
    {
        $datos = $this->datosCatalogo();

        $slug = strtoupper((string) $request->query('categoria', ''));
        $cat  = $datos['categorias']->firstWhere('slug', $slug);

        // Solo se preselecciona si existe y tiene productos.
        $datos['categoriaActiva'] = ($cat && $cat['total'] > 0) ? $slug : null;

        return view('publico::catalogo', $datos);
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
}
