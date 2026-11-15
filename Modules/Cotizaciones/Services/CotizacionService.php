<?php

namespace Modules\Cotizaciones\Services;

use App\Exceptions\BusinessRuleException;
use App\Support\Descuento;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Catalogo\Models\VarianteProducto;
use Modules\Cotizaciones\Models\Cotizacion;
use Modules\Cotizaciones\Models\DetalleCotizacion;
use Modules\Precios\Services\TipoCambioService;

class CotizacionService
{
    public function __construct(private TipoCambioService $tipoCambio) {}

    public function crear(array $data, User $actor): Cotizacion
    {
        if (! $actor->id_sucursal) {
            throw new BusinessRuleException('Tu usuario no tiene sucursal asignada.');
        }

        $tc = $this->tipoCambio->actuales()['paralelo'];
        if (! $tc) {
            throw new BusinessRuleException(
                'No hay tipo de cambio paralelo vigente. Registra uno en /precios/tipo-cambio antes de cotizar.'
            );
        }

        return DB::transaction(function () use ($data, $actor, $tc) {
            $cot = Cotizacion::create([
                'id_cliente' => $data['id_cliente'],
                'id_usuario' => $actor->id_usuario,
                'id_sucursal' => $actor->id_sucursal,
                'id_tipo_cambio' => $tc->id_tipo_cambio,
                'fecha_emision' => now()->toDateString(),
                'vigencia_dias' => $data['vigencia_dias'],
                'estado' => 'EMITIDA',
                'observacion' => $data['observacion'] ?? null,
            ]);

            $this->syncLineas($cot, $data['lineas']);

            return $cot->refresh();
        });
    }

    public function actualizar(Cotizacion $cot, array $data): Cotizacion
    {
        if (! $this->puedeEditarse($cot)) {
            throw new BusinessRuleException('Solo se pueden editar cotizaciones EMITIDAS.');
        }

        return DB::transaction(function () use ($cot, $data) {
            $cot->update([
                'id_cliente' => $data['id_cliente'],
                'vigencia_dias' => $data['vigencia_dias'],
                'observacion' => $data['observacion'] ?? null,
            ]);
            $this->syncLineas($cot, $data['lineas']);

            return $cot->refresh();
        });
    }

    public function anular(Cotizacion $cot): void
    {
        if (! $this->puedeAnularse($cot)) {
            throw new BusinessRuleException('Solo se pueden anular cotizaciones EMITIDAS.');
        }
        $cot->update(['estado' => 'ANULADA']);
    }

    public function puedeEditarse(Cotizacion $c): bool
    {
        return $c->estado === 'EMITIDA';
    }

    public function puedeAnularse(Cotizacion $c): bool
    {
        return $c->estado === 'EMITIDA';
    }

    public function puedeConvertirse(Cotizacion $c): bool
    {
        return $c->estado === 'EMITIDA' && $c->condicionTemporal() === 'VIGENTE';
    }

    private function syncLineas(Cotizacion $cot, array $lineas): void
    {
        $variantes = VarianteProducto::whereIn('id_variante', array_column($lineas, 'id_variante'))
            ->get(['id_variante', 'sku', 'precio_usd'])
            ->keyBy('id_variante');

        // El precio es autoritativo: siempre el del catálogo, nunca el del formulario.
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

        // Sin trigger de "no update" en detalle_cotizacion: reemplazo total dentro de tx.
        $cot->detalles()->delete();
        foreach ($lineas as $l) {
            $v = $variantes[(int) $l['id_variante']];
            $precio = round((float) $v->precio_usd, 2);
            $cantidad = (int) $l['cantidad'];

            DetalleCotizacion::create([
                'id_cotizacion' => $cot->id_cotizacion,
                'id_variante' => (int) $l['id_variante'],
                'cantidad' => $cantidad,
                'precio_unitario_usd' => $precio,
                'descuento_pct' => Descuento::aPorcentaje(
                    $l['descuento_tipo'] ?? 'PCT',
                    $l['descuento_valor'] ?? ($l['descuento_pct'] ?? 0),
                    $precio,
                    $cantidad
                ),
            ]);
        }
        $cot->unsetRelation('detalles');
    }
}
