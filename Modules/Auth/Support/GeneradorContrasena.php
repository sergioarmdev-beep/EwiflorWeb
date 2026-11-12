<?php

namespace Modules\Auth\Support;

class GeneradorContrasena
{
    /**
     * Genera una contraseña aleatoria con CSPRNG. Excluye caracteres ambiguos (0/O, 1/l/I)
     * para que sea fácil de dictar o leer. Garantiza minúscula, mayúscula y número.
     */
    public static function generar(int $largo = 12, bool $simbolos = true): string
    {
        $largo = max(8, $largo);

        $grupos = [
            'abcdefghjkmnpqrstuvwxyz',
            'ABCDEFGHJKMNPQRSTUVWXYZ',
            '23456789',
        ];
        if ($simbolos) {
            $grupos[] = '!@#$%*-_+?';
        }

        $todos = implode('', $grupos);
        $chars = [];

        foreach ($grupos as $grupo) {
            $chars[] = $grupo[random_int(0, strlen($grupo) - 1)];
        }
        while (count($chars) < $largo) {
            $chars[] = $todos[random_int(0, strlen($todos) - 1)];
        }

        // Fisher-Yates con random_int (shuffle() no es criptográfico)
        for ($i = count($chars) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$chars[$i], $chars[$j]] = [$chars[$j], $chars[$i]];
        }

        return implode('', $chars);
    }
}
