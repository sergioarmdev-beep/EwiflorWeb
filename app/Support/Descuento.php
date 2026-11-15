<?php

namespace App\Support;

use App\Exceptions\BusinessRuleException;

/**
 * Normaliza el descuento de una línea de venta/cotización.
 *
 * El formulario permite capturarlo como porcentaje (PCT) o como monto en USD
 * sobre el total de la línea. La base de datos solo almacena el porcentaje
 * (detalle_venta.descuento_pct / detalle_cotizacion.descuento_pct), así que
 * aquí se hace la conversión de forma centralizada y validada.
 */
class Descuento
{
    /**
     * @param  string|null  $tipo  PCT|USD
     * @param  mixed  $valor  porcentaje o monto en USD según el tipo
     * @param  float  $precioUnitario  precio de lista en USD (autoritativo del catálogo)
     * @param  int  $cantidad  unidades de la línea
     *
     * @throws BusinessRuleException
     */
    public static function aPorcentaje(?string $tipo, mixed $valor, float $precioUnitario, int $cantidad): float
    {
        $tipo = strtoupper(trim((string) ($tipo ?: 'PCT')));
        $valor = round((float) $valor, 2);

        if ($valor < 0) {
            throw new BusinessRuleException('El descuento no puede ser negativo.');
        }

        if ($tipo === 'USD') {
            $bruto = round($precioUnitario * $cantidad, 2);

            if ($valor > 0 && $bruto <= 0) {
                throw new BusinessRuleException('No se puede aplicar un descuento en USD a una línea sin importe.');
            }

            if ($valor > $bruto + 0.005) {
                throw new BusinessRuleException(sprintf(
                    'El descuento (USD %s) no puede superar el subtotal de la línea (USD %s).',
                    number_format($valor, 2),
                    number_format($bruto, 2)
                ));
            }

            $porcentaje = $bruto > 0 ? round($valor / $bruto * 100, 2) : 0.0;
        } else {
            $porcentaje = $valor;
        }

        if ($porcentaje > 100) {
            throw new BusinessRuleException('El descuento no puede superar el 100% de la línea.');
        }

        return $porcentaje;
    }
}
