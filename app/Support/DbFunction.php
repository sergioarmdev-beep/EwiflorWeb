<?php

namespace App\Support;

use App\Exceptions\BusinessRuleException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class DbFunction
{
    /**
     * Invoca una función PostgreSQL y devuelve la primera fila (o null).
     *
     * @throws BusinessRuleException si la función lanza RAISE EXCEPTION (SQLSTATE P0001)
     * @throws QueryException        si el error no es de negocio
     */
    public static function call(string $function, array $params = []): mixed
    {
        $placeholders = implode(',', array_fill(0, count($params), '?'));
        $sql = "SELECT * FROM {$function}({$placeholders})";

        try {
            $rows = DB::select($sql, array_values($params));
            return $rows[0] ?? null;
        } catch (QueryException $e) {
            if ($e->getCode() === 'P0001') {
                $message = $e->errorInfo[2]
                    ?? $e->getPrevious()?->getMessage()
                    ?? 'Error de negocio';
                throw new BusinessRuleException($message, previous: $e);
            }
            throw $e;
        }
    }

    /**
     * Igual que call(), pero devuelve solo el primer campo de la primera fila.
     * Útil para funciones que retornan un escalar (INTEGER, BOOLEAN, etc.).
     */
    public static function scalar(string $function, array $params = []): mixed
    {
        $row = self::call($function, $params);
        if ($row === null) {
            return null;
        }
        return array_values((array) $row)[0] ?? null;
    }
}
