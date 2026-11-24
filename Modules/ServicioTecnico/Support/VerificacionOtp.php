<?php

namespace Modules\ServicioTecnico\Support;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

/**
 * Estado pendiente del OTP de la identificación pública.
 *
 * El código se guarda hasheado en sesión y solo se canjea por una sesión de
 * cliente cuando pasa la verificación. Nunca se devuelve al navegador.
 */
final class VerificacionOtp
{
    public const KEY_PENDIENTE = 'serviciotecnico.otp_pendiente';

    public const VIGENCIA_MINUTOS = 10;

    public const MAX_INTENTOS = 5;

    public const ESTADO_OK = 'ok';

    public const ESTADO_INCORRECTO = 'incorrecto';

    public const ESTADO_EXPIRADO = 'expirado';

    public const ESTADO_AGOTADO = 'agotado';

    public const ESTADO_SIN_PENDIENTE = 'sin_pendiente';

    /** Código numérico de 6 dígitos, incluyendo ceros a la izquierda. */
    public static function generarCodigo(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function iniciar(
        int $idCliente,
        string $nombre,
        string $telefono,
        string $correo,
        string $codigo
    ): void {
        Session::put(self::KEY_PENDIENTE, [
            'id_cliente' => $idCliente,
            'nombre' => $nombre,
            'telefono' => $telefono,
            'correo' => $correo,
            'codigo_hash' => Hash::make($codigo),
            'expira_en' => now()->addMinutes(self::VIGENCIA_MINUTOS)->getTimestamp(),
            'intentos' => 0,
        ]);
    }

    public function obtener(): ?array
    {
        $pendiente = Session::get(self::KEY_PENDIENTE);

        if (! is_array($pendiente)) {
            return null;
        }

        foreach (['id_cliente', 'nombre', 'telefono', 'correo', 'codigo_hash', 'expira_en', 'intentos'] as $clave) {
            if (! array_key_exists($clave, $pendiente)) {
                $this->limpiar();

                return null;
            }
        }

        return $pendiente;
    }

    /**
     * @return array{estado: string, intentos_restantes: int}
     */
    public function verificar(string $codigo): array
    {
        $pendiente = $this->obtener();

        if ($pendiente === null) {
            return ['estado' => self::ESTADO_SIN_PENDIENTE, 'intentos_restantes' => 0];
        }

        if (now()->getTimestamp() >= (int) $pendiente['expira_en']) {
            $this->limpiar();

            return ['estado' => self::ESTADO_EXPIRADO, 'intentos_restantes' => 0];
        }

        $intentos = (int) $pendiente['intentos'];

        if ($intentos >= self::MAX_INTENTOS) {
            $this->limpiar();

            return ['estado' => self::ESTADO_AGOTADO, 'intentos_restantes' => 0];
        }

        if (! Hash::check($codigo, $pendiente['codigo_hash'])) {
            $intentos++;
            $pendiente['intentos'] = $intentos;
            Session::put(self::KEY_PENDIENTE, $pendiente);

            if ($intentos >= self::MAX_INTENTOS) {
                $this->limpiar();

                return ['estado' => self::ESTADO_AGOTADO, 'intentos_restantes' => 0];
            }

            return [
                'estado' => self::ESTADO_INCORRECTO,
                'intentos_restantes' => self::MAX_INTENTOS - $intentos,
            ];
        }

        return [
            'estado' => self::ESTADO_OK,
            'intentos_restantes' => self::MAX_INTENTOS - $intentos,
        ];
    }

    public function limpiar(): void
    {
        Session::forget(self::KEY_PENDIENTE);
    }
}
