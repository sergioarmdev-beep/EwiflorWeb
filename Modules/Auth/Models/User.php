<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Auth\Support\Celular;
use Modules\Inventario\Models\Sucursal;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuario';

    protected $primaryKey = 'id_usuario';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'nombre_completo',
        'nombre_usuario',
        'correo',
        'celular',
        'contrasena_hash',
        'nombre_rol',
        'id_sucursal',
        'porcentaje_comision_pct',
        'estado',
        'debe_cambiar_contrasena',
        'google_id',
        'proveedor',
        'avatar_url',
    ];

    protected $hidden = [
        'contrasena_hash',
    ];

    protected $casts = [
        'porcentaje_comision_pct' => 'decimal:2',
        'fecha_creacion' => 'datetime',
        'bloqueado_hasta' => 'datetime',
        'debe_cambiar_contrasena' => 'boolean',
    ];

    public function username(): string
    {
        return 'nombre_usuario';
    }

    public function getAuthPassword(): string
    {
        return $this->contrasena_hash;
    }

    protected function nombreUsuario(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtolower($value),
        );
    }

    protected function correo(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value === null ? null : strtolower($value),
        );
    }

    protected function celular(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => trim((string) $value) === '' ? null : Celular::normalizar($value),
        );
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }

    /** Cuenta bloqueada por intentos fallidos (no es «INACTIVA»: se libera sola). */
    public function estaBloqueado(): bool
    {
        return $this->bloqueado_hasta !== null && $this->bloqueado_hasta->isFuture();
    }

    /** Minutos enteros que faltan para liberar la cuenta (mínimo 1). */
    public function minutosDeBloqueo(): int
    {
        if (! $this->bloqueado_hasta) {
            return 0;
        }

        $segundos = now()->diffInSeconds($this->bloqueado_hasta, false);

        return (int) max(1, ceil($segundos / 60));
    }

    /** ¿Tiene correo y celular? Sin ambos no puede usar «Olvidé mi contraseña». */
    public function tieneDatosDeRecuperacion(): bool
    {
        return ! empty($this->correo) && ! empty($this->celular);
    }

    public function esGerente(): bool
    {
        return $this->nombre_rol === 'GERENTE';
    }

    public function getRouteKeyName(): string
    {
        return 'id_usuario';
    }

    public function esAdministrador(): bool
    {
        return $this->nombre_rol === 'ADMINISTRADOR';
    }

    /**
     * Roles que este usuario puede asignar a otros: solo un Gerente puede crear/ascender Gerentes.
     */
    public function rolesAsignables(): array
    {
        $roles = ['ADMINISTRADOR', 'VENDEDOR', 'SERVICIO_TECNICO'];

        return $this->esGerente() ? array_merge(['GERENTE'], $roles) : $roles;
    }

    public function puedeGestionarUsuarios(): bool
    {
        return in_array($this->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true);
    }

    /**
     * ¿Puede consultar los documentos comerciales (ventas y cotizaciones)
     * de todos los usuarios? Los demás roles solo ven los suyos.
     */
    public function puedeVerTodosLosDocumentos(): bool
    {
        return in_array($this->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }

    /** Avisos del centro de notificaciones de este usuario. */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'id_usuario', 'id_usuario');
    }

    /** Peticiones de «olvidé mi contraseña» de esta cuenta. */
    public function solicitudesRestablecimiento(): HasMany
    {
        return $this->hasMany(SolicitudRestablecimiento::class, 'id_usuario', 'id_usuario');
    }

    /** ¿Es una cuenta vinculada a Google? */
    public function esCuentaGoogle(): bool
    {
        return ! empty($this->google_id);
    }

    /** ¿Tiene contraseña tradicional definida? */
    public function tieneContrasena(): bool
    {
        return ! empty($this->contrasena_hash);
    }

    /** Vincula (o actualiza) el identificador de Google y la foto de perfil. */
    public function vincularGoogle(string $googleId, ?string $avatarUrl = null): void
    {
        $this->google_id = $googleId;
        $this->proveedor = $this->tieneContrasena() ? 'ambos' : 'google';
        $this->avatar_url = $avatarUrl;
        $this->save();
    }
}
