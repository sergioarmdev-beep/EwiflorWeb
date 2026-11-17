<?php

namespace Modules\Promociones\Services;

use App\Exceptions\BusinessRuleException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Catalogo\Models\Producto;
use Modules\Catalogo\Models\VarianteProducto;
use Modules\Catalogo\Services\ProductoService;
use Modules\Promociones\Models\ComboComponente;
use Throwable;

/**
 * Creación y edición de combos.
 *
 * Un combo es un `producto` con `tipo_producto = 'COMBO'` (dueño de la tabla:
 * Catalogo) que se vende como un producto normal: precio y stock propios. Los
 * productos incluidos viven en `combo_componente` (dueño: este módulo) y son
 * informativos, no se descuentan de su inventario.
 */
class ComboService
{
    public function __construct(private ProductoService $productos) {}

    public function crear(array $datos, array $imagenes): Producto
    {
        $rutasNuevas = [];

        try {
            $combo = DB::transaction(function () use ($datos, $imagenes, &$rutasNuevas) {
                $combo = Producto::create($this->datosProducto($datos, 'ACTIVO'));

                $rutasNuevas = $this->productos->guardarImagenes($combo, $imagenes);
                $this->productos->asegurarPrincipal($combo, null);

                $this->sincronizarVariante($combo, (float) $datos['precio_usd']);
                $this->sincronizarComponentes($combo, $datos['componentes']);

                return $combo;
            });
        } catch (Throwable $e) {
            // Si algo falla no dejamos archivos huérfanos en el disco.
            $this->productos->borrarArchivos($rutasNuevas);
            throw $e;
        }

        return $combo;
    }

    public function actualizar(Producto $combo, array $datos, array $imagenes, ?int $imagenPrincipal, array $idsEliminar): Producto
    {
        $rutasNuevas = [];
        $rutasABorrar = [];

        try {
            DB::transaction(function () use ($combo, $datos, $imagenes, $imagenPrincipal, $idsEliminar, &$rutasNuevas, &$rutasABorrar) {
                $combo->update($this->datosProducto($datos, $combo->estado));

                // 1) Imágenes marcadas para eliminar (solo las de este combo)
                if ($idsEliminar) {
                    $aEliminar = $combo->imagenes()->whereIn('id_imagen', $idsEliminar)->get();
                    foreach ($aEliminar as $img) {
                        $rutasABorrar[] = $img->ruta_imagen;
                        $img->delete();
                    }
                }

                // 2) Imágenes nuevas
                $rutasNuevas = $this->productos->guardarImagenes($combo, $imagenes);

                // 3) Imagen principal elegida (y garantía de que siempre haya una)
                $this->productos->asegurarPrincipal($combo, $imagenPrincipal);

                // 4) Precio promocional (variante única del combo)
                $this->sincronizarVariante($combo, (float) $datos['precio_usd']);

                // 5) Componentes
                $this->sincronizarComponentes($combo, $datos['componentes']);
            });
        } catch (Throwable $e) {
            $this->productos->borrarArchivos($rutasNuevas);
            throw $e;
        }

        // Solo se borran del disco cuando la transacción ya se confirmó.
        $this->productos->borrarArchivos($rutasABorrar);

        return $combo->refresh();
    }

    public function alternarEstado(Producto $combo): Producto
    {
        $nuevoEstado = $combo->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $combo->update(['estado' => $nuevoEstado]);

        return $combo;
    }

    /** Datos base del producto; el tipo siempre es COMBO. */
    private function datosProducto(array $datos, string $estado): array
    {
        return [
            'codigo' => $datos['codigo'],
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'categoria' => $datos['categoria'],
            'id_marca' => $datos['id_marca'],
            'modelo' => $datos['modelo'] ?? null,
            'tipo_producto' => 'COMBO',
            'modalidad' => $datos['modalidad'],
            'stock_minimo' => $datos['stock_minimo'],
            'garantia_meses' => $datos['garantia_meses'],
            'es_publico' => (bool) ($datos['es_publico'] ?? true),
            'estado' => $estado,
        ];
    }

    /**
     * El combo se vende por su única variante; el SKU se deriva del código.
     * El precio de lista lo fija aquí (rutas solo para Gerente/Administrador).
     */
    private function sincronizarVariante(Producto $combo, float $precioUsd): void
    {
        $sku = $this->skuDe($combo);
        $variante = $combo->variantes()->first();

        if ($variante) {
            $variante->update(['precio_usd' => round($precioUsd, 2)]);
        } else {
            try {
                $combo->variantes()->create([
                    'sku' => $sku,
                    'caracteristicas' => 'Combo',
                    'estado' => 'ACTIVO',
                    'precio_usd' => round($precioUsd, 2),
                ]);
            } catch (QueryException $e) {
                throw new BusinessRuleException(
                    'No se pudo crear la variante del combo (SKU '.$sku.'): revisa que el código del combo no coincida con el SKU de otro producto.',
                    previous: $e
                );
            }
        }
    }

    private function skuDe(Producto $combo): string
    {
        $sku = strtoupper(substr($combo->codigo, 0, 40));

        // El SKU del combo no puede chocar con el de otra variante existente.
        $choca = VarianteProducto::where('sku', $sku)
            ->when($combo->id_producto, fn ($q) => $q->whereNotIn(
                'id_variante',
                $combo->variantes()->pluck('id_variante')
            ))
            ->exists();

        if ($choca) {
            throw new BusinessRuleException(
                "El SKU «{$sku}» ya lo usa otra variante. Cambia el código del combo."
            );
        }

        return $sku;
    }

    /**
     * Reemplaza los componentes del combo. Valida existencia, estado y que no
     * se aniden combos (los componentes son productos individuales).
     *
     * @param  array<int, array{id_componente:int|string, cantidad:int|string}>  $componentes
     */
    private function sincronizarComponentes(Producto $combo, array $componentes): void
    {
        $componentes = $this->validarComponentes($combo, $componentes);

        try {
            DB::table('combo_componente')
                ->where('id_producto', $combo->id_producto)
                ->delete();

            DB::table('combo_componente')->insert($componentes->values()->all());
        } catch (QueryException $e) {
            throw new BusinessRuleException('No se pudieron guardar los componentes del combo.', previous: $e);
        }

        $combo->unsetRelation('componentesCombo');
    }

    /**
     * @param  array<int, array{id_componente:int|string, cantidad:int|string}>  $componentes
     * @return Collection<int, array{id_producto:int, id_componente:int, cantidad:int}>
     */
    private function validarComponentes(Producto $combo, array $componentes): Collection
    {
        $ids = array_values(array_unique(array_map('intval', array_column($componentes, 'id_componente'))));

        if (count($ids) < 2) {
            throw new BusinessRuleException('Un combo necesita al menos dos productos distintos incluidos.');
        }

        $productos = Producto::whereIn('id_producto', $ids)->get()->keyBy('id_producto');
        $filas = collect();
        $vistos = [];

        foreach ($componentes as $componente) {
            $id = (int) ($componente['id_componente'] ?? 0);
            $cantidad = max(1, (int) ($componente['cantidad'] ?? 1));

            if (isset($vistos[$id])) {
                continue; // el request ya marca duplicados como error
            }
            $vistos[$id] = true;

            $producto = $productos[$id] ?? null;

            if (! $producto) {
                throw new BusinessRuleException("El producto #{$id} del combo no existe.");
            }
            if ($producto->tipo_producto === 'COMBO') {
                throw new BusinessRuleException("No se permiten combos anidados: «{$producto->nombre}» ya es un combo.");
            }
            if ($producto->estado !== 'ACTIVO') {
                throw new BusinessRuleException("«{$producto->nombre}» está inactivo; no puede integrar un combo.");
            }

            $filas->push([
                'id_producto' => $combo->id_producto,
                'id_componente' => $id,
                'cantidad' => $cantidad,
            ]);
        }

        if ($filas->count() < 2) {
            throw new BusinessRuleException('Un combo necesita al menos dos productos distintos incluidos.');
        }

        return $filas;
    }

    /** Texto legible «Producto ×2, Producto ×1» para etiquetas y vistas. */
    public static function textoComponentes(Producto $combo): string
    {
        return $combo->componentesCombo
            ->map(fn (ComboComponente $c) => $c->componente?->nombre ?? "Producto #{$c->id_componente}")
            ->implode(', ');
    }
}
