<?php

namespace Modules\Catalogo\Support;

use Illuminate\Support\Collection;
use Modules\Catalogo\Models\VarianteProducto;
use Modules\Promociones\Models\ComboComponente;

/**
 * Prepara el payload que alimenta el buscador/autocompletado de variantes
 * usado en Ventas y Cotizaciones.
 *
 * Vive en `Catalogo` porque las variantes y los productos son datos de ese
 * módulo; los demás módulos solo importan este helper. Las promociones
 * vigentes se pasan como argumento (las resuelve `PromocionService`), de
 * modo que este helper no depende del ciclo Catalogo <-> Promociones.
 */
class BuscadorVariantes
{
    /**
     * @param  Collection<int, VarianteProducto>  $variantes
     * @param  array<int, array{nombre:string, tipo:string, valor:float, pct:float}>  $promociones
     *                                                                                              mapa id_variante => promoción vigente (ver PromocionService::mapaVigente)
     * @return array<int, array{id:int, sku:string, codigo:string, nombre:string, marca:?string, color:?string, precio:?float, promo:?array, es_combo:bool, combo:?string}>
     */
    public static function payload($variantes, array $promociones = []): array
    {
        $idsProductos = $variantes->pluck('id_producto')->unique()->filter()->all();

        $combos = $idsProductos === []
            ? collect()
            : ComboComponente::with('componente:id_producto,codigo,nombre')
                ->whereIn('id_producto', $idsProductos)
                ->get()
                ->groupBy('id_producto');

        return $variantes
            ->map(function (VarianteProducto $v) use ($promociones, $combos) {
                $componentes = $combos->get($v->id_producto);

                return [
                    'id' => (int) $v->id_variante,
                    'sku' => (string) $v->sku,
                    'codigo' => (string) ($v->producto?->codigo ?? ''),
                    'nombre' => (string) ($v->producto?->nombre ?? ''),
                    'marca' => $v->producto?->marca?->nombre,
                    'color' => $v->color,
                    'precio' => $v->precio_usd !== null ? (float) $v->precio_usd : null,
                    'promo' => $promociones[$v->id_variante] ?? null,
                    'es_combo' => (bool) $v->producto?->esCombo(),
                    'combo' => $componentes?->map(
                        fn ($c) => $c->componente?->nombre.' ×'.$c->cantidad
                    )->implode(', ') ?: null,
                ];
            })
            ->values()
            ->all();
    }
}
