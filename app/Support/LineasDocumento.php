<?php

namespace App\Support;

use App\Exceptions\BusinessRuleException;
use Modules\Auth\Models\User;
use Modules\Catalogo\Models\VarianteProducto;
use Modules\Promociones\Services\PromocionService;

/**
 * Normalización compartida de las líneas de ventas y cotizaciones.
 *
 * El precio siempre es el del catálogo (nunca el enviado por el formulario),
 * el descuento se convierte a porcentaje con Descuento y se autoriza contra
 * las promociones vigentes según el rol del actor. Ventas añade id_almacen;
 * Cotizaciones no, porque su detalle no tiene almacén.
 */
final class LineasDocumento
{
    public function __construct(private PromocionService $promociones) {}

    /**
     * @param  array<int, array<string, mixed>>  $lineas
     * @return array<int, array<string, mixed>>
     *
     * @throws BusinessRuleException
     */
    public function normalizar(array $lineas, ?int $idAlmacen, User $actor): array
    {
        $variantes = VarianteProducto::with('producto:id_producto,modalidad')
            ->whereIn('id_variante', array_column($lineas, 'id_variante'))
            ->get()
            ->keyBy('id_variante');

        $faltantes = [];
        foreach ($lineas as $l) {
            $v = $variantes[(int) $l['id_variante']] ?? null;

            if (! $v) {
                throw new BusinessRuleException("La variante #{$l['id_variante']} no existe.");
            }
            if ((float) ($v->precio_usd ?? 0) <= 0) {
                $faltantes[] = $v->sku;
            }
        }

        if (! empty($faltantes)) {
            throw new BusinessRuleException(sprintf(
                'Estas variantes no tienen precio de lista: %s. Un Gerente o Administrador debe asignarlo en Catálogo.',
                implode(', ', array_unique($faltantes))
            ));
        }

        $promociones = $this->promociones->mapaVigente($variantes->collect());

        return collect($lineas)->map(function ($l) use ($idAlmacen, $variantes, $promociones, $actor) {
            $v = $variantes[(int) $l['id_variante']];
            $precio = round((float) $v->precio_usd, 2);
            $cantidad = (int) $l['cantidad'];

            $descuentoPct = Descuento::aPorcentaje(
                $l['descuento_tipo'] ?? 'PCT',
                $l['descuento_valor'] ?? ($l['descuento_pct'] ?? 0),
                $precio,
                $cantidad
            );

            $this->promociones->validarDescuento(
                $actor,
                $descuentoPct,
                $promociones[(int) $l['id_variante']] ?? null,
                sprintf('Línea %s', $v->sku)
            );

            $linea = [
                'id_variante' => (int) $l['id_variante'],
                'cantidad' => $cantidad,
                'precio_unitario_usd' => $precio,
                'descuento_pct' => $descuentoPct,
                'garantia_meses' => (int) ($l['garantia_meses'] ?? 0),
            ];

            if ($idAlmacen !== null) {
                // BAJO_PEDIDO no admite almacén: registrar_venta lo exige así.
                $linea['id_almacen'] = $v->producto?->modalidad === 'BAJO_PEDIDO'
                    ? null
                    : $idAlmacen;
            }

            return $linea;
        })->values()->all();
    }
}
