<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Collection;
use Modules\Auth\Models\Notificacion;
use Modules\Auth\Models\SolicitudRestablecimiento;
use Modules\Auth\Models\User;

/**
 * Crea los avisos in-app del centro de notificaciones.
 *
 * Reglas (aprobadas con el usuario):
 * - Restablecimiento de clave:
 *     · ADMINISTRADOR / VENDEDOR / SERVICIO_TECNICO -> todos los GERENTE activos.
 *     · VENDEDOR / SERVICIO_TECNICO                 -> además el ADMINISTRADOR de su sucursal.
 *     · GERENTE                                     -> los demás GERENTE activos.
 * - Bloqueo por intentos fallidos:
 *     · todos los GERENTE activos + los ADMINISTRADOR de la sucursal de la cuenta bloqueada.
 * - Nunca se avisa al propio usuario origen.
 */
class NotificacionService
{
    /**
     * Aviso para quienes deben restablecer la contraseña (la petición ya
     * quedó guardada y verificada contra correo + celular).
     */
    public function notificarSolicitud(SolicitudRestablecimiento $solicitud, User $usuario): void
    {
        $url = route('usuarios.solicitudes-contrasena.index', ['estado' => 'PENDIENTE'], false);
        $etiqueta = $this->etiquetaDe($usuario);

        foreach ($this->destinatariosParaSolicitud($usuario) as $destino) {
            Notificacion::create([
                'id_usuario' => $destino->id_usuario,
                'tipo' => Notificacion::TIPO_SOLICITUD_CONTRASENA,
                'titulo' => 'Olvidó su contraseña',
                'mensaje' => "El usuario {$etiqueta} reportó que olvidó su contraseña (correo y celular verificados). "
                    .'Revisa su identidad y genera una clave temporal.',
                'url' => $url,
                'id_usuario_origen' => $usuario->id_usuario,
                'id_solicitud_restablecimiento' => $solicitud->id_solicitud_restablecimiento,
            ]);
        }
    }

    /**
     * Avisa UNA sola vez por bloqueo (solo cuando la función PG responde
     * bloqueo_nuevo = true).
     *
     * @param  \DateTimeInterface|null  $hasta  vigencia del bloqueo
     */
    public function notificarBloqueo(User $usuario, $hasta = null): void
    {
        $url = route('usuarios.index', ['bloqueados' => 1], false);
        $etiqueta = $this->etiquetaDe($usuario);
        $hora = $hasta instanceof \DateTimeInterface
            ? $hasta->format('H:i')
            : null;

        foreach ($this->destinatariosParaBloqueo($usuario) as $destino) {
            Notificacion::create([
                'id_usuario' => $destino->id_usuario,
                'tipo' => Notificacion::TIPO_BLOQUEO_CUENTA,
                'titulo' => 'Intentos fallidos de acceso',
                'mensaje' => "La cuenta {$etiqueta} intentó iniciar sesión 3 veces sin éxito en menos de 10 minutos."
                    .($hora ? " Quedó bloqueada hasta las {$hora}." : ' Quedó bloqueada temporalmente.')
                    .' Si es necesario, restablece su contraseña.',
                'url' => $url,
                'id_usuario_origen' => $usuario->id_usuario,
            ]);
        }
    }

    /** Destinatarios para una solicitud de restablecimiento. */
    private function destinatariosParaSolicitud(User $origen): Collection
    {
        $gerentes = User::query()
            ->with('sucursal')
            ->where('estado', 'ACTIVO')
            ->where('nombre_rol', 'GERENTE')
            ->where('id_usuario', '!=', $origen->id_usuario)
            ->get();

        $esDeSoporte = in_array($origen->nombre_rol, ['VENDEDOR', 'SERVICIO_TECNICO'], true);
        if (! $esDeSoporte || $origen->id_sucursal === null) {
            return $gerentes;
        }

        $administradores = User::query()
            ->with('sucursal')
            ->where('estado', 'ACTIVO')
            ->where('nombre_rol', 'ADMINISTRADOR')
            ->where('id_sucursal', $origen->id_sucursal)
            ->where('id_usuario', '!=', $origen->id_usuario)
            ->get();

        return $gerentes->merge($administradores)->unique('id_usuario')->values();
    }

    /** Destinatarios del aviso de bloqueo. */
    private function destinatariosParaBloqueo(User $bloqueado): Collection
    {
        $gerentes = User::query()
            ->with('sucursal')
            ->where('estado', 'ACTIVO')
            ->where('nombre_rol', 'GERENTE')
            ->where('id_usuario', '!=', $bloqueado->id_usuario)
            ->get();

        if ($bloqueado->id_sucursal === null) {
            return $gerentes;
        }

        $administradores = User::query()
            ->with('sucursal')
            ->where('estado', 'ACTIVO')
            ->where('nombre_rol', 'ADMINISTRADOR')
            ->where('id_sucursal', $bloqueado->id_sucursal)
            ->where('id_usuario', '!=', $bloqueado->id_usuario)
            ->get();

        return $gerentes->merge($administradores)->unique('id_usuario')->values();
    }

    /** "nombre (ROL · Sucursal)" para los mensajes. */
    private function etiquetaDe(User $usuario): string
    {
        $sucursal = $usuario->sucursal?->nombre;

        return "{$usuario->nombre_completo} ({$usuario->nombre_rol}"
            .($sucursal ? " · {$sucursal}" : '').')';
    }
}
