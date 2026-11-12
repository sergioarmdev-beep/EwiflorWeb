<?php

namespace Modules\Auth\Support;

/**
 * Reglas de contraseña en un único lugar (las usan crear/editar usuario y "Mi perfil").
 * Deben coincidir con los requisitos mínimos del medidor de fortaleza de la vista:
 * mínimo 8 caracteres, al menos una letra y al menos un número.
 */
class ReglasContrasena
{
    public static function reglas(bool $requerida = true): array
    {
        return [
            $requerida ? 'required' : 'nullable',
            'string',
            'min:8',
            'max:72', // bcrypt ignora/rechaza lo que pase de 72 bytes
            function (string $atributo, mixed $valor, \Closure $fallar) {
                if (!is_string($valor)) {
                    return;
                }
                if (!preg_match('/\p{L}/u', $valor)) {
                    $fallar('La contraseña debe incluir al menos una letra.');
                }
                if (!preg_match('/\d/', $valor)) {
                    $fallar('La contraseña debe incluir al menos un número.');
                }
            },
        ];
    }

    public static function mensajes(): array
    {
        return [
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max'       => 'La contraseña no puede superar los 72 caracteres.',
        ];
    }
}
