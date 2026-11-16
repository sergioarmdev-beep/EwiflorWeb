<?php

namespace Modules\Catalogo\Support;

use Illuminate\Support\Collection;
use Modules\Catalogo\Models\VarianteProducto;

/**
 * Prepara el payload que alimenta el buscador/autocompletado de variantes
 * usado en Ventas y Cotizaciones.
 *
 * Vive en `Catalogo` porque las variantes y los productos son datos de ese
 * módulo; los demás módulos solo importan este helper.
 */
class BuscadorVariantes
{
    /**
     * @param  Collection<int, VarianteProducto>  $variantes
     * @return array<int, array{id:int, sku:string, codigo:string, nombre:string, marca:?string, color:?string, precio:?float}>
     */
    public static function payload($variantes): array
    {
        return $variantes
            ->map(fn (VarianteProducto $v) => [
                'id' => (int) $v->id_variante,
                'sku' => (string) $v->sku,
                'codigo' => (string) ($v->producto?->codigo ?? ''),
                'nombre' => (string) ($v->producto?->nombre ?? ''),
                'marca' => $v->producto?->marca?->nombre,
                'color' => $v->color,
                'precio' => $v->precio_usd !== null ? (float) $v->precio_usd : null,
            ])
            ->values()
            ->all();
    }
}
