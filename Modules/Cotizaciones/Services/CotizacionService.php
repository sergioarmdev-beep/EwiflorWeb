<?php

namespace Modules\Cotizaciones\Services;

use App\Exceptions\BusinessRuleException;
use App\Support\LineasDocumento;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Cotizaciones\Models\Cotizacion;
use Modules\Cotizaciones\Models\DetalleCotizacion;
use Modules\Precios\Services\TipoCambioService;

class CotizacionService
{
    public function __construct(
        private TipoCambioService $tipoCambio,
        private LineasDocumento $lineas,
    ) {}

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

            $this->syncLineas($cot, $data['lineas'], $actor);

            return $cot->refresh();
        });
    }

    public function actualizar(Cotizacion $cot, array $data, User $actor): Cotizacion
    {
        if (! $this->puedeEditarse($cot)) {
            throw new BusinessRuleException('Solo se pueden editar cotizaciones EMITIDAS.');
        }

        return DB::transaction(function () use ($cot, $data, $actor) {
            $cot->update([
                'id_cliente' => $data['id_cliente'],
                'vigencia_dias' => $data['vigencia_dias'],
                'observacion' => $data['observacion'] ?? null,
            ]);
            $this->syncLineas($cot, $data['lineas'], $actor);

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

    private function syncLineas(Cotizacion $cot, array $lineas, User $actor): void
    {
        $normalizadas = $this->lineas->normalizar($lineas, null, $actor);

        // Sin trigger de "no update" en detalle_cotizacion: reemplazo total dentro de tx.
        $cot->detalles()->delete();
        foreach ($normalizadas as $linea) {
            DetalleCotizacion::create([
                'id_cotizacion' => $cot->id_cotizacion,
                'id_variante' => $linea['id_variante'],
                'cantidad' => $linea['cantidad'],
                'precio_unitario_usd' => $linea['precio_unitario_usd'],
                'descuento_pct' => $linea['descuento_pct'],
                'garantia_meses' => $linea['garantia_meses'],
            ]);
        }
        $cot->unsetRelation('detalles');
    }
}
