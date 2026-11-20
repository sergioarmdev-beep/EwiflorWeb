<?php

namespace Modules\Auth\Support;

/**
 * Normalización y comparación de números de celular para el flujo
 * «Olvidé mi contraseña» (correo + celular registrados).
 */
class Celular
{
    /**
     * Versión para GUARDAR: dígitos, conservando un '+' inicial si venía.
     * Ej.: "+591 (7000) 0000" -> "+59170000000"; "7000 0000" -> "70000000".
     */
    public static function normalizar(?string $valor): string
    {
        $limpio = trim((string) $valor);
        $digitos = self::soloDigitos($limpio);

        if ($digitos === '') {
            return '';
        }

        return str_starts_with($limpio, '+') ? '+'.$digitos : $digitos;
    }

    /** Solo dígitos, sin '+', espacios, guiones ni paréntesis. */
    public static function soloDigitos(?string $valor): string
    {
        return preg_replace('/\D+/', '', (string) $valor) ?? '';
    }

    /**
     * ¿Coinciden el celular registrado y el ingresado?
     *
     * Se comparan los dígitos completos; además, si uno tiene exactamente
     * 8 dígitos (formato boliviano) se acepta contra los últimos 8 del otro,
     * para tolerar registros con y sin el prefijo 591.
     */
    public static function coincide(?string $registrado, ?string $ingresado): bool
    {
        $a = self::soloDigitos($registrado);
        $b = self::soloDigitos($ingresado);

        if ($a === '' || $b === '') {
            return false;
        }

        if ($a === $b) {
            return true;
        }

        if (strlen($b) === 8 && strlen($a) > 8 && str_ends_with($a, $b)) {
            return true;
        }

        if (strlen($a) === 8 && strlen($b) > 8 && str_ends_with($b, $a)) {
            return true;
        }

        return false;
    }
}
