<?php

namespace Modules\Comisiones\Services;

use App\Exceptions\BusinessRuleException;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Comisiones\Models\Comision;
use Modules\Sucursales\Support\AccesoSucursal;

class ComisionService
{
    /**
     * Calcula y guarda (inserta o actualiza) las comisiones de un año/mes
     * para los vendedores dentro del alcance del actor.
     *
     * Solo se consideran ventas que tengan al menos un pago registrado
     * y que no estén canceladas. El monto se toma en Bs (total_bs de la venta).
     *
     * @return Comision[] comisiones generadas o actualizadas
     */
    public function calcularYGuardar(int $anio, int $mes, ?int $idSucursal, User $actor): array
    {
        $idSucursalEfectivo = $this->resolverSucursal($actor, $idSucursal);

        $desde = Carbon::create($anio, $mes, 1, 0, 0, 0);
        $hasta = clone $desde;
        $hasta->endOfMonth()->endOfDay();

        // Ventas con pago registrado, no canceladas, del mes y dentro del alcance.
        // Se usa whereExists en lugar de JOIN pago para no duplicar filas cuando
        // una venta tiene varios pagos (eso inflaba el total_bs).
        $ventas = DB::table('venta')
            ->join('v_reporte_ventas as rv', 'rv.id_venta', '=', 'venta.id_venta')
            ->whereExists(fn ($q) => $q->selectRaw('1')
                ->from('pago')
                ->whereColumn('pago.id_venta', 'venta.id_venta'))
            ->where('venta.estado', '!=', 'CANCELADA')
            ->whereBetween('venta.fecha_hora', [$desde, $hasta])
            ->when($idSucursalEfectivo, fn ($q) => $q->where('venta.id_sucursal', $idSucursalEfectivo))
            ->select(
                'venta.id_usuario',
                'venta.id_sucursal',
                DB::raw('count(distinct venta.id_venta) as cantidad_ventas'),
                DB::raw('coalesce(sum(coalesce(rv.total_bs, 0)), 0) as total_ventas_bs')
            )
            ->groupBy('venta.id_usuario', 'venta.id_sucursal')
            ->get()
            // Clave compuesta: un vendedor puede operar en más de una sucursal.
            ->keyBy(fn ($r) => (int) $r->id_usuario . ':' . (int) $r->id_sucursal);

        $vendedores = $this->vendedoresAlcance($actor, $idSucursalEfectivo);

        $guardadas = [];
        foreach ($vendedores as $vendedor) {
            $clave = (int) $vendedor->id_usuario . ':' . (int) $vendedor->id_sucursal;
            $venta = $ventas[$clave] ?? null;
            $cantidad = $venta ? (int) $venta->cantidad_ventas : 0;
            $totalVentas = $venta ? (float) $venta->total_ventas_bs : 0.0;
            $porcentaje = (float) ($vendedor->porcentaje_comision_pct ?? 0);
            $monto = round($totalVentas * $porcentaje / 100, 2);

            $comision = Comision::updateOrCreate(
                [
                    'id_usuario' => (int) $vendedor->id_usuario,
                    'id_sucursal' => (int) $vendedor->id_sucursal,
                    'anio' => $anio,
                    'mes' => $mes,
                ],
                [
                    'cantidad_ventas' => $cantidad,
                    'total_ventas_bs' => $totalVentas,
                    'porcentaje_comision_pct' => $porcentaje,
                    'monto_comision_bs' => $monto,
                ]
            );

            // Si la fila es NUEVA, la estado a PENDIENTE. Si ya existía, NO toques el estado.
            if ($comision->wasRecentlyCreated) {
                $comision->estado = Comision::ESTADO_PENDIENTE;
                $comision->save();
            }

            $guardadas[] = $comision;
        }

        return $guardadas;
    }

    /**
     * Lista comisiones con filtros, respetando el alcance de sucursal del actor.
     */
    public function listar(?int $anio, ?int $mes, ?int $idSucursal, ?string $estado, User $actor): Collection
    {
        $idSucursalEfectivo = $this->resolverSucursal($actor, $idSucursal);

        $q = Comision::query()
            ->with(['vendedor', 'sucursal'])
            ->when($anio, fn ($query) => $query->where('anio', $anio))
            ->when($mes, fn ($query) => $query->where('mes', $mes))
            ->when($estado, fn ($query) => $query->where('estado', $estado))
            ->when($idSucursalEfectivo, fn ($query) => $query->where('id_sucursal', $idSucursalEfectivo));

        return $q->orderByDesc('anio')
            ->orderByDesc('mes')
            ->orderBy('id_usuario')
            ->get();
    }

    /**
     * Marca una comisión como pagada si el actor puede operar en su sucursal.
     */
    public function marcarPagado(int $idComision, User $actor): Comision
    {
        $comision = Comision::find($idComision);
        if (! $comision) {
            throw new BusinessRuleException('La comisión no existe.');
        }

        if (! AccesoSucursal::puedeOperarEn($actor, (int) $comision->id_sucursal)) {
            throw new BusinessRuleException('No puedes operar comisiones de esa sucursal.');
        }

        if ($comision->esPagado()) {
            throw new BusinessRuleException('La comisión ya está pagada.');
        }

        $comision->estado = Comision::ESTADO_PAGADO;
        $comision->fecha_pago = now();
        $comision->save();

        return $comision->fresh();
    }

    public function sucursalesParaFiltro(User $actor): Collection
    {
        return AccesoSucursal::sucursalesVisibles($actor);
    }

    public function vendedoresParaFiltro(?int $idSucursal, User $actor): Collection
    {
        $idSucursalEfectivo = $this->resolverSucursal($actor, $idSucursal);

        $q = User::query()
            ->where('nombre_rol', 'VENDEDOR')
            ->where('estado', 'ACTIVO');

        if ($idSucursalEfectivo) {
            $q->where('id_sucursal', $idSucursalEfectivo);
        }

        return $q->orderBy('nombre_completo')->get();
    }

    /**
     * Resuelve la sucursal efectiva según el rol del actor:
     * - ADMINISTRADOR → solo su sucursal asignada.
     * - GERENTE → todas (null) o la indicada si pertenece a su alcance.
     */
    private function resolverSucursal(User $actor, ?int $idSucursal): ?int
    {
        if ($actor->esAdministrador()) {
            return (int) $actor->id_sucursal;
        }

        // Gerente
        if ($idSucursal) {
            if (! AccesoSucursal::puedeOperarEn($actor, (int) $idSucursal)) {
                throw new BusinessRuleException('No puedes operar en esa sucursal.');
            }
            return (int) $idSucursal;
        }

        return null;
    }

    private function vendedoresAlcance(User $actor, ?int $idSucursal): Collection
    {
        return $this->vendedoresParaFiltro($idSucursal, $actor);
    }
}
