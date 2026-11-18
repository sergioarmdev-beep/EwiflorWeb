<?php

namespace Modules\Publico\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Catalogo\Models\Producto;
use Modules\Promociones\Services\PromocionService;
use Modules\ServicioTecnico\Services\SolicitudService;

class PublicoController extends Controller
{
    public function __construct(
        private SolicitudService $solicitudes,
        private PromocionService $promociones,
    ) {}

    public function catalogoPublico()
    {
        $productos = Producto::publicos()
            ->with([
                'marca',
                'imagenes',
                'variantes' => fn($q) => $q->where('estado', 'ACTIVO'),
            ])
            ->orderBy('nombre')
            ->get();

        // 🔽 NUEVO: categorías con conteo + imagen de ejemplo
        $categorias = $this->construirCategorias($productos);

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

        return view('publico::publico', compact('productos', 'detalle', 'promociones', 'categorias'));
    }

    /**
     * Devuelve una colección con TODAS las categorías de Producto::CATEGORIAS,
     * aunque no tengan productos todavía. Así la sección nunca queda vacía.
     */
    private function construirCategorias(Collection $productos): Collection
    {
        $meta = [
            'ALIMENTARIA' => [
                'titulo'      => 'Línea alimentaria',
                'descripcion' => 'Equipamiento para preparación, proceso y conservación de alimentos.',
                'imagen'      => '/images/catalog/categoria-alimentaria.jpg',
            ],
            'PANADERIA' => [
                'titulo'      => 'Panadería',
                'descripcion' => 'Maquinaria para procesos de producción de panadería y masas.',
                'imagen'      => '/images/catalog/categoria-panaderia.jpg',
            ],
            'REPOSTERIA' => [
                'titulo'      => 'Repostería',
                'descripcion' => 'Equipos para elaboración de repostería, dulces y postres.',
                'imagen'      => '/images/catalog/categoria-reposteria.jpg',
            ],
            'CARNICA' => [
                'titulo'      => 'Línea cárnica',
                'descripcion' => 'Soluciones para procesamiento de carnes y embutidos.',
                'imagen'      => '/images/catalog/categoria-carnica.jpg',
            ],
        ];

        $agrupados = $productos->groupBy('categoria');

        return collect(Producto::CATEGORIAS)->map(function (string $slug) use ($agrupados, $meta) {
            $grupo  = $agrupados->get($slug, collect());
            $config = $meta[$slug] ?? [
                'titulo'      => ucfirst(mb_strtolower($slug)),
                'descripcion' => '',
                'imagen'      => null,
            ];

            // Imagen representativa: primer producto de la categoría con imagen.
            $conImagen = $grupo->first(fn($p) => $p->imagenes->isNotEmpty());
            $imagen    = $conImagen?->imagenes->first()?->url ?: $config['imagen'];

            return [
                'slug'        => $slug,
                'titulo'      => $config['titulo'],
                'descripcion' => $config['descripcion'],
                'imagen'      => $imagen,
                'total'       => $grupo->count(),
            ];
        })->values();
    }

    // ... contactarServicioTecnico() queda igual
}
