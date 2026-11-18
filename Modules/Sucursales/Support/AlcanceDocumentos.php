<?php

namespace Modules\Sucursales\Support;

use Illuminate\Support\Collection;
use Modules\Auth\Models\User;
use Modules\Inventario\Models\Almacen;
use Modules\Inventario\Models\Sucursal;

/**
 * Alcance de los documentos comerciales (ventas y cotizaciones) por rol y sucursal.
 *
 * Regla de negocio:
 *  - GERENTE        → todas las sucursales.
 *  - ADMINISTRADOR  → solo la sucursal a la que está asignado.
 *  - VENDEDOR       → solo los documentos que él emitió/registró, dentro de su sucursal.
 *  - Sin sucursal (cualquier rol distinto de GERENTE) → sin acceso (fail-closed).
 *
 * No confundir con AccesoSucursal::esGestor(), donde el ADMINISTRADOR gestiona
 * todas las sucursales: ahí es gestión de sucursales/almacenes, acá es alcance
 * de lectura/escritura de los documentos comerciales, que es más restrictivo.
 *
 * Importable desde cualquier módulo (igual que App\Support\DbFunction):
 *   use Modules\Sucursales\Support\AlcanceDocumentos;
 */
final class AlcanceDocumentos
{
    /** ¿Ve/ops en todas las sucursales? (solo GERENTE) */
    public static function esGerente(?User $user): bool
    {
        return $user !== null && $user->nombre_rol === 'GERENTE';
    }

    /** ¿Solo consulta sus propios documentos (dentro de su sucursal)? (VENDEDOR) */
    public static function soloSusDocumentos(?User $user): bool
    {
        return $user !== null && $user->nombre_rol === 'VENDEDOR';
    }

    /** Sucursal asignada al usuario, o null si no tiene (o si es Gerente, que no se limita). */
    public static function sucursalDelUsuario(?User $user): ?int
    {
        return $user !== null && $user->id_sucursal ? (int) $user->id_sucursal : null;
    }

    /** ¿Puede operar (vender, cancelar, cobrar) sobre documentos/almacenes de esa sucursal? */
    public static function puedeOperarEnSucursal(?User $user, int $idSucursal): bool
    {
        if ($user === null) {
            return false;
        }

        if (self::esGerente($user)) {
            return true;
        }

        $sucursal = self::sucursalDelUsuario($user);

        return $sucursal !== null && $sucursal === $idSucursal;
    }

    /**
     * ¿Puede consultar este documento concreto?
     *
     * @param  int|null  $idUsuario  autor del documento (null = sin autor conocido)
     */
    public static function puedeVerDocumento(?User $user, int $idSucursal, ?int $idUsuario): bool
    {
        if ($user === null) {
            return false;
        }

        if (self::esGerente($user)) {
            return true;
        }

        if (! self::puedeOperarEnSucursal($user, $idSucursal)) {
            return false;
        }

        if (! self::soloSusDocumentos($user)) {
            return true;
        }

        return $idUsuario !== null && $idUsuario === (int) $user->id_usuario;
    }

    /** Almacenes ACTIVOS que el usuario puede usar para vender. */
    public static function almacenesVisibles(?User $user): Collection
    {
        if ($user === null) {
            return collect();
        }

        $query = Almacen::with('sucursal')
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre');

        if (! self::esGerente($user)) {
            $sucursal = self::sucursalDelUsuario($user);
            if ($sucursal === null) {
                return collect();
            }
            $query->where('id_sucursal', $sucursal);
        }

        return $query->get();
    }

    /** Sucursales para el selector de filtro del listado (solo Gerente cambia de sucursal). */
    public static function sucursalesParaFiltro(?User $user): Collection
    {
        if (! self::esGerente($user)) {
            return collect();
        }

        return Sucursal::where('estado', 'ACTIVO')->orderBy('nombre')->get();
    }

    /** Usuarios para el filtro "vendedor" (vacío para VENDEDOR, que solo ve lo suyo). */
    public static function usuariosVisibles(?User $user): Collection
    {
        if ($user === null || self::soloSusDocumentos($user)) {
            return collect();
        }

        $query = User::where('estado', 'ACTIVO')->orderBy('nombre_completo');

        if (! self::esGerente($user)) {
            $sucursal = self::sucursalDelUsuario($user);
            if ($sucursal === null) {
                return collect();
            }
            $query->where('id_sucursal', $sucursal);
        }

        return $query->get(['id_usuario', 'nombre_completo', 'nombre_rol']);
    }

    /**
     * Texto del aviso de alcance para el listado, o null si no aplica (Gerente).
     *
     * @param  string  $entidad  plural del documento ("ventas", "cotizaciones")
     * @param  string  $verbo  qué hizo el usuario ("registraste", "emitiste")
     */
    public static function aviso(?User $user, string $entidad, string $verbo = 'registraste'): ?string
    {
        if ($user === null || self::esGerente($user)) {
            return null;
        }

        $sucursal = self::sucursalDelUsuario($user);
        if ($sucursal === null) {
            return "Tu usuario no tiene sucursal asignada: no hay {$entidad} visibles.";
        }

        $nombre = Sucursal::find($sucursal)?->nombre ?? "sucursal #{$sucursal}";

        return self::soloSusDocumentos($user)
            ? "Solo se muestran las {$entidad} que tú {$verbo} en la sucursal {$nombre}."
            : "Solo se muestran las {$entidad} de la sucursal {$nombre}.";
    }
}
