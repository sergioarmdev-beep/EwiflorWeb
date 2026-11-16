<?php

namespace Modules\ServicioTecnico\Support;

use Illuminate\Support\Facades\Session;

final class SesionCliente
{
    public const KEY_CLIENTE_ID = 'serviciotecnico.cliente_id';

    public const KEY_CLIENTE_NOMBRE = 'serviciotecnico.cliente_nombre';

    public const KEY_CLIENTE_TELEFONO = 'serviciotecnico.cliente_telefono';

    public static function establecer(int $idCliente, string $nombre, string $telefono): void
    {
        Session::put(self::KEY_CLIENTE_ID, $idCliente);
        Session::put(self::KEY_CLIENTE_NOMBRE, $nombre);
        Session::put(self::KEY_CLIENTE_TELEFONO, $telefono);
    }

    public static function obtener(): ?array
    {
        $id = Session::get(self::KEY_CLIENTE_ID);

        if ($id === null) {
            return null;
        }

        return [
            'id_cliente' => (int) $id,
            'nombre' => Session::get(self::KEY_CLIENTE_NOMBRE, ''),
            'telefono' => Session::get(self::KEY_CLIENTE_TELEFONO, ''),
        ];
    }

    public static function limpiar(): void
    {
        Session::forget(self::KEY_CLIENTE_ID);
        Session::forget(self::KEY_CLIENTE_NOMBRE);
        Session::forget(self::KEY_CLIENTE_TELEFONO);
    }

    public static function idCliente(): ?int
    {
        $id = Session::get(self::KEY_CLIENTE_ID);

        return $id !== null ? (int) $id : null;
    }
}
