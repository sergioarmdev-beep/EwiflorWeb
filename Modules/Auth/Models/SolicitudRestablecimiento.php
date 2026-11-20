<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Una petición de «olvidé mi contraseña»: el usuario nunca cambia la clave
 * por sí mismo; un gerente (o el administrador de su sucursal) atiende la
 * solicitud generando una clave temporal.
 */
class SolicitudRestablecimiento extends Model
{
    public const PENDIENTE = 'PENDIENTE';

    public const ATENDIDA = 'ATENDIDA';

    public const RECHAZADA = 'RECHAZADA';

    protected $table = 'solicitud_restablecimiento';

    protected $primaryKey = 'id_solicitud_restablecimiento';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'correo_solicitado',
        'celular_solicitado',
        'estado',
        'ip',
        'fecha_creacion',
        'id_usuario_atendio',
        'fecha_atencion',
        'nota',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_atencion' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_solicitud_restablecimiento';
    }

    public function estaPendiente(): bool
    {
        return $this->estado === self::PENDIENTE;
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function atendio(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_atendio', 'id_usuario');
    }

    /**
     * Alcance de visibilidad/atención según quién consulta:
     * - GERENTE         : todas las solicitudes.
     * - ADMINISTRADOR   : solo VENDEDOR y SERVICIO_TECNICO de su misma sucursal.
     * - Resto de roles  : ninguna.
     */
    public function scopeVisiblesPara(Builder $query, User $actor): Builder
    {
        if ($actor->esGerente()) {
            return $query;
        }

        if (! $actor->esAdministrador() || $actor->id_sucursal === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('usuario', function (Builder $q) use ($actor) {
            $q->where('id_sucursal', $actor->id_sucursal)
                ->whereIn('nombre_rol', ['VENDEDOR', 'SERVICIO_TECNICO']);
        });
    }
}
