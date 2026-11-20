<?php

namespace Modules\Auth\Services;

use App\Support\DbFunction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Models\User;
use stdClass;

/**
 * Intentos de acceso y bloqueo temporal.
 *
 * Las reglas viven en las funciones PostgreSQL `fn_registrar_intento_acceso`
 * y `fn_desbloquear_usuario` (3 fallos en menos de 10 min -> bloqueo de 10 min);
 * este servicio las invoca y crea el aviso de seguridad cuando corresponde.
 */
class IntentoAccesoService
{
    public const MAX_INTENTOS = 3;

    public const VENTANA_MINUTOS = 10;

    public const BLOQUEO_MINUTOS = 10;

    public const MENSAJE_CREDENCIALES = 'Las credenciales no coinciden con nuestros registros.';

    public const MENSAJE_INACTIVA = 'Tu cuenta está desactivada. Contacta a un administrador.';

    public function __construct(private NotificacionService $notificaciones) {}

    /** Busca la cuenta por nombre de usuario y, si no, por correo. */
    public function resolver(string $identificador): ?User
    {
        if ($identificador === '') {
            return null;
        }

        return User::query()
            ->where('nombre_usuario', $identificador)
            ->first()
            ?? User::query()->where('correo', $identificador)->first();
    }

    /**
     * Registra un intento fallido. Si es el 3.º en la ventana, la función PG
     * bloquea la cuenta y aquí se avisa a gerencia/administración (una sola vez).
     *
     * Devuelve null si falla la BD (se loguea; no se rompe el login).
     */
    public function registrarFallido(User $usuario, string $identificador, ?string $ip): ?stdClass
    {
        try {
            return DB::transaction(function () use ($usuario, $identificador, $ip) {
                $resultado = DbFunction::call('fn_registrar_intento_acceso', [
                    $usuario->id_usuario,
                    $identificador,
                    $ip,
                    false,
                ]);

                if ($resultado !== null && ! empty($resultado->bloqueo_nuevo)) {
                    $this->notificaciones->notificarBloqueo($usuario, $resultado->bloqueado_hasta);
                }

                return $resultado;
            });
        } catch (\Throwable $e) {
            Log::error('No se pudo registrar el intento de acceso fallido.', [
                'id_usuario' => $usuario->id_usuario,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /** Registra un acceso correcto: limpia bloqueo y ventana de fallos. */
    public function registrarExitoso(User $usuario, string $identificador, ?string $ip): void
    {
        try {
            DbFunction::call('fn_registrar_intento_acceso', [
                $usuario->id_usuario,
                $identificador,
                $ip,
                true,
            ]);
        } catch (\Throwable $e) {
            Log::error('No se pudo registrar el acceso exitoso.', [
                'id_usuario' => $usuario->id_usuario,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** Quita el bloqueo y borra los fallos recientes de la ventana. */
    public function desbloquear(User $usuario): void
    {
        DbFunction::call('fn_desbloquear_usuario', [$usuario->id_usuario]);
    }
}
