<?php

namespace Modules\Garantias\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Auth\Models\User;
use Modules\Clientes\Models\Cliente;
use Modules\Inventario\Models\Sucursal;

class SolicitudServicio extends Model
{
    public const ESTADOS = ['PENDIENTE', 'PROGRAMADA', 'CERRADA', 'CANCELADA'];

    public const RESULTADOS = ['REPARACION', 'SERVICIO_PAGADO', 'REEMPLAZO'];

    protected $table = 'solicitud_servicio';

    protected $primaryKey = 'id_solicitud';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_sucursal',
        'id_garantia',
        'id_usuario_registra',
        'id_tecnico',
        'descripcion_falla',
        'fecha_solicitud',
        'fecha_visita',
        'estado',
        'diagnostico',
        'resultado',
        'costo_servicio_bs',
        'fecha_cierre',
        'origen',
        'motivo_cancelacion',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_visita' => 'datetime',
        'fecha_cierre' => 'datetime',
        'costo_servicio_bs' => 'decimal:2',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_solicitud';
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function garantia()
    {
        return $this->belongsTo(Garantia::class, 'id_garantia', 'id_garantia');
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'id_tecnico', 'id_usuario');
    }

    public function registra()
    {
        return $this->belongsTo(User::class, 'id_usuario_registra', 'id_usuario');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }

    /** Fue solicitada dentro de una garantía registrada. */
    public function estaEnGarantia(): bool
    {
        return $this->id_garantia !== null;
    }

    public function estaPendiente(): bool
    {
        return $this->estado === 'PENDIENTE';
    }

    public function estaAceptada(): bool
    {
        return $this->estado === 'PROGRAMADA';
    }

    public function estaCerrada(): bool
    {
        return $this->estado === 'CERRADA';
    }

    public function estaCancelada(): bool
    {
        return $this->estado === 'CANCELADA';
    }

    public function scopeDisponibles(Builder $q): Builder
    {
        return $q->where('estado', 'PENDIENTE')->whereNull('id_tecnico');
    }
}
