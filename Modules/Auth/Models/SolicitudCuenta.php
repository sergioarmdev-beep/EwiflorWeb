<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudCuenta extends Model
{
    public const PENDIENTE = 'PENDIENTE';
    public const APROBADA  = 'APROBADA';
    public const RECHAZADA = 'RECHAZADA';

    protected $table = 'solicitud_cuenta';
    protected $primaryKey = 'id_solicitud';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombre_completo',
        'correo',
        'celular',
        'motivo',
        'estado',
        'id_usuario_revisor',
        'fecha_revision',
        'nota_revision',
        'id_usuario_creado',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_revision' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_solicitud';
    }

    public function estaPendiente(): bool
    {
        return $this->estado === self::PENDIENTE;
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_revisor', 'id_usuario');
    }

    public function usuarioCreado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_creado', 'id_usuario');
    }
}
