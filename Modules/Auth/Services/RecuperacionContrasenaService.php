<?php

namespace Modules\Auth\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Models\SolicitudRestablecimiento;
use Modules\Auth\Models\User;
use Modules\Auth\Support\Celular;

/**
 * «Olvidé mi contraseña»: verifica correo + celular registrados y, si
 * coinciden, deja una solicitud pendiente y avisa a quienes pueden
 * restablecer la clave. El usuario NUNCA cambia su contraseña aquí.
 *
 * La respuesta al usuario es idéntica exista o no la cuenta (evita
 * descubrir correos/números registrados).
 */
class RecuperacionContrasenaService
{
    public function __construct(private NotificacionService $notificaciones) {}

    /**
     * @return SolicitudRestablecimiento|null null si los datos no coinciden
     *                                        o si falla la base de datos
     */
    public function solicitar(string $correo, string $celular, ?string $ip = null): ?SolicitudRestablecimiento
    {
        $correo = strtolower(trim($correo));

        if ($correo === '') {
            return null;
        }

        $usuario = User::query()->where('correo', $correo)->first();

        if (! $usuario || ! Celular::coincide($usuario->celular, $celular)) {
            return null;
        }

        try {
            return DB::transaction(function () use ($usuario, $correo, $celular, $ip) {
                // Bloquea la fila de la cuenta: serializa pedidos concurrentes
                // de la misma cuenta y evita duplicar la solicitud pendiente.
                User::query()->whereKey($usuario->id_usuario)->lockForUpdate()->first();

                $pendiente = SolicitudRestablecimiento::query()
                    ->where('id_usuario', $usuario->id_usuario)
                    ->where('estado', SolicitudRestablecimiento::PENDIENTE)
                    ->first();

                if ($pendiente) {
                    // Ya había una solicitud abierta: no se duplica ni se re-avisa.
                    return $pendiente;
                }

                $solicitud = SolicitudRestablecimiento::create([
                    'id_usuario' => $usuario->id_usuario,
                    'correo_solicitado' => $correo,
                    'celular_solicitado' => Celular::normalizar($celular),
                    'estado' => SolicitudRestablecimiento::PENDIENTE,
                    'ip' => $ip,
                ]);

                $this->notificaciones->notificarSolicitud($solicitud, $usuario);

                return $solicitud;
            });
        } catch (QueryException $e) {
            Log::error('No se pudo crear la solicitud de restablecimiento.', [
                'id_usuario' => $usuario->id_usuario,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
