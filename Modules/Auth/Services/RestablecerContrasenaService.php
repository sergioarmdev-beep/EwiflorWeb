<?php

namespace Modules\Auth\Services;

use App\Support\DbFunction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\Notificacion;
use Modules\Auth\Models\SolicitudRestablecimiento;
use Modules\Auth\Models\User;
use Modules\Auth\Support\GeneradorContrasena;

/**
 * Genera una clave temporal para un usuario («olvidó su contraseña»):
 * - hashea la nueva contraseña,
 * - obliga a cambiarla en el próximo ingreso (debe_cambiar_contrasena),
 * - limpia cualquier bloqueo por intentos fallidos,
 * - cierra las solicitudes de restablecimiento pendientes de esa cuenta,
 * - marca como leídas las notificaciones del actor sobre ese usuario.
 */
class RestablecerContrasenaService
{
    public function generarTemporal(User $usuario, User $actor): string
    {
        $temporal = GeneradorContrasena::generar(10, false);

        DB::transaction(function () use ($usuario, $actor, $temporal) {
            $usuario->forceFill([
                'contrasena_hash' => Hash::make($temporal),
                'debe_cambiar_contrasena' => true,
            ])->save();

            DbFunction::call('fn_desbloquear_usuario', [$usuario->id_usuario]);

            SolicitudRestablecimiento::query()
                ->where('id_usuario', $usuario->id_usuario)
                ->where('estado', SolicitudRestablecimiento::PENDIENTE)
                ->update([
                    'estado' => SolicitudRestablecimiento::ATENDIDA,
                    'id_usuario_atendio' => $actor->id_usuario,
                    'fecha_atencion' => DB::raw('now()'),
                ]);

            Notificacion::query()
                ->where('id_usuario', $actor->id_usuario)
                ->where('id_usuario_origen', $usuario->id_usuario)
                ->where('leida', false)
                ->update([
                    'leida' => true,
                    'fecha_lectura' => DB::raw('now()'),
                ]);
        });

        return $temporal;
    }
}
