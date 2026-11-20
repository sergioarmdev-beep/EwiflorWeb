<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * Aviso in-app del centro de notificaciones (campana / página /notificaciones).
 * Solo se usa desde el módulo Auth: nunca se envían correos.
 */
class Notificacion extends Model
{
    public const TIPO_SOLICITUD_CONTRASENA = 'SOLICITUD_CONTRASENA';

    public const TIPO_BLOQUEO_CUENTA = 'BLOQUEO_CUENTA';

    protected $table = 'notificacion';

    protected $primaryKey = 'id_notificacion';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'tipo',
        'titulo',
        'mensaje',
        'url',
        'id_usuario_origen',
        'id_solicitud_restablecimiento',
        'leida',
        'fecha_lectura',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'fecha_creacion' => 'datetime',
        'fecha_lectura' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_notificacion';
    }

    public function marcadaLeida(): bool
    {
        return (bool) $this->leida;
    }

    /**
     * Marca la notificación como leída. La fecha se guarda con now() de la BD:
     * escribe el mismo instante que los defaults de creación (evita el
     * desfase de zona horaria entre PHP y la sesión de PostgreSQL).
     */
    public function marcarLeida(): void
    {
        if ($this->leida) {
            return;
        }

        static::whereKey($this->getKey())
            ->update(['leida' => true, 'fecha_lectura' => DB::raw('now()')]);

        $this->refresh();
    }

    public function destino(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function origen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_origen', 'id_usuario');
    }

    /** Solo ve las propias. */
    public function scopeDe(Builder $query, User $usuario): Builder
    {
        return $query->where('id_usuario', $usuario->id_usuario);
    }
}
