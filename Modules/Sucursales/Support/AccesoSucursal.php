<?php

namespace Modules\Sucursales\Support;

use Illuminate\Support\Collection;
use Modules\Auth\Models\User;
use Modules\Inventario\Models\Sucursal;

/**
 * Helper GLOBAL de alcance usuario ↔ sucursal.
 *
 * Importable desde cualquier módulo (igual que App\Support\DbFunction):
 *   use Modules\Sucursales\Support\AccesoSucursal;
 *
 * Regla de negocio: GERENTE y ADMINISTRADOR controlan TODAS las sucursales.
 * Los demás roles solo ven/operan la sucursal a la que están asignados.
 */
final class AccesoSucursal
{
    /**
     * ¿El usuario gestiona todas las sucursales?
     */
    public static function esGestor(?User $user): bool
    {
        return $user !== null && in_array($user->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true);
    }

    /**
     * Sucursales visibles para el usuario:
     * - GERENTE/ADMINISTRADOR → todas (activas e inactivas).
     * - Otros roles → únicamente su sucursal asignada (en cualquier estado,
     *   para que pueda consultarla aunque la desactiven).
     *
     * @return Collection<int, Sucursal>
     */
    public static function sucursalesVisibles(?User $user): Collection
    {
        if ($user === null) {
            return collect();
        }

        if (self::esGestor($user)) {
            return Sucursal::query()
                ->with('almacenes')
                ->orderBy('nombre')
                ->get();
        }

        if ($user->id_sucursal) {
            return Sucursal::query()
                ->with('almacenes')
                ->where('id_sucursal', $user->id_sucursal)
                ->get();
        }

        return collect();
    }

    /**
     * Igual que sucursalesVisibles() pero solo ACTIVAS — para dropdowns
     * de formularios (ventas, movimientos, asignación de almacenes).
     *
     * @return Collection<int, Sucursal>
     */
    public static function sucursalesActivas(?User $user): Collection
    {
        return self::sucursalesVisibles($user)->where('estado', 'ACTIVO')->values();
    }

    /**
     * ¿El usuario puede operar sobre esta sucursal?
     * (gestores → siempre; el resto → solo la suya)
     */
    public static function puedeOperarEn(?User $user, int $idSucursal): bool
    {
        if ($user === null) {
            return false;
        }

        return self::esGestor($user) || (int) $user->id_sucursal === $idSucursal;
    }

    /**
     * Usuarios asignados a una sucursal (cualquier estado).
     *
     * @return Collection<int, User>
     */
    public static function usuariosDe(int $idSucursal): Collection
    {
        return User::query()
            ->where('id_sucursal', $idSucursal)
            ->orderBy('nombre_completo')
            ->get();
    }

    /**
     * La sucursal asignada al usuario, o null si no tiene.
     */
    public static function sucursalDelUsuario(?User $user): ?Sucursal
    {
        if ($user === null || ! $user->id_sucursal) {
            return null;
        }

        return Sucursal::find($user->id_sucursal);
    }
}
