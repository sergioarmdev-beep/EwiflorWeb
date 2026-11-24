<?php

namespace Modules\Dashboard\Services;

/**
 * Alcance de sucursal de las métricas del dashboard (ADR-006).
 *
 * - global(): GERENTE, sin filtro.
 * - sucursal(): el resto de roles con sucursal asignada.
 * - sinSucursal(): fail-closed; cualquier consulta con este alcance devuelve 0 filas.
 */
final class DashboardAlcance
{
    private function __construct(
        public readonly bool $global,
        public readonly ?int $idSucursal,
    ) {}

    public static function global(): self
    {
        return new self(true, null);
    }

    public static function sucursal(int $idSucursal): self
    {
        return new self(false, $idSucursal);
    }

    public static function sinSucursal(): self
    {
        return new self(false, null);
    }

    /** Construye el alcance a partir del rol y la sucursal del usuario. */
    public static function desdeUsuario(string $rol, ?int $idSucursal): self
    {
        if ($rol === 'GERENTE') {
            return self::global();
        }

        return $idSucursal === null ? self::sinSucursal() : self::sucursal($idSucursal);
    }

    /** Aplica el filtro a un query builder. Llamar con la columna ya calificada. */
    public function aplicar($query, string $columna): void
    {
        if ($this->global) {
            return;
        }

        if ($this->idSucursal === null) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where($columna, $this->idSucursal);
    }
}
