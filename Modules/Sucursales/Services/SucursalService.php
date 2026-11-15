<?php

namespace Modules\Sucursales\Services;

use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Inventario\Models\Almacen;
use Modules\Inventario\Models\Sucursal;

/**
 * Reglas de activación/desactivación de sucursales y almacenes.
 *
 * Nota: esto es dato organizacional (no stock/dinero), por eso vive en PHP.
 * La auditoría NO se toca aquí: el trigger PostgreSQL fn_auditar_cambio
 * la registra automáticamente vía SetDatabaseUser.
 */
class SucursalService
{
    /**
     * Alterna ACTIVO/INACTIVO de una sucursal.
     *
     * Desactivar: bloquea si hay usuarios ACTIVOS asignados (deben
     * reasignarse o desactivarse primero) y pone en INACTIVO todos
     * los almacenes de la sucursal.
     * Reactivar: solo la sucursal vuelve a ACTIVO; los almacenes se
     * reactivan uno a uno, de forma explícita.
     */
    public function cambiarEstado(Sucursal $sucursal): Sucursal
    {
        if ($sucursal->estado === 'ACTIVO') {
            $activos = User::query()
                ->where('id_sucursal', $sucursal->id_sucursal)
                ->where('estado', 'ACTIVO')
                ->count();

            if ($activos > 0) {
                throw new BusinessRuleException(
                    "No se puede desactivar la sucursal \"{$sucursal->nombre}\": ".
                    "tiene {$activos} usuario(s) activo(s) asignado(s). ".
                    'Reasígnalos o desactívalos primero en la sección Usuarios.'
                );
            }

            return DB::transaction(function () use ($sucursal) {
                $sucursal->update(['estado' => 'INACTIVO']);
                Almacen::query()
                    ->where('id_sucursal', $sucursal->id_sucursal)
                    ->update(['estado' => 'INACTIVO']);

                return $sucursal;
            });
        }

        $sucursal->update(['estado' => 'ACTIVO']);

        return $sucursal;
    }

    /**
     * Alterna ACTIVO/INACTIVO de un almacén.
     * Desactivar: bloquea si tiene existencias con cantidad_total > 0.
     */
    public function cambiarEstadoAlmacen(Almacen $almacen): Almacen
    {
        if ($almacen->estado === 'ACTIVO') {
            $conStock = DB::table('existencia')
                ->where('id_almacen', $almacen->id_almacen)
                ->where('cantidad_total', '>', 0)
                ->count();

            if ($conStock > 0) {
                throw new BusinessRuleException(
                    "No se puede desactivar el almacén \"{$almacen->nombre}\": ".
                    "tiene {$conStock} producto(s) con existencias. ".
                    'Transfiere o ajusta el stock primero.'
                );
            }

            $almacen->update(['estado' => 'INACTIVO']);

            return $almacen;
        }

        $almacen->update(['estado' => 'ACTIVO']);

        return $almacen;
    }
}
