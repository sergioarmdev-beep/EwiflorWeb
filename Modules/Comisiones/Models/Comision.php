<?php

namespace Modules\Comisiones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;
use Modules\Inventario\Models\Sucursal;

class Comision extends Model
{
    protected $table = 'comision';

    protected $primaryKey = 'id_comision';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_sucursal',
        'anio',
        'mes',
        'cantidad_ventas',
        'total_ventas_bs',
        'porcentaje_comision_pct',
        'monto_comision_bs',
        'estado',
        'fecha_pago',
        'fecha_creacion',
    ];

    protected $casts = [
        'total_ventas_bs' => 'decimal:2',
        'porcentaje_comision_pct' => 'decimal:2',
        'monto_comision_bs' => 'decimal:2',
        'fecha_pago' => 'datetime',
        'fecha_creacion' => 'datetime',
    ];

    public const ESTADO_PENDIENTE = 'PENDIENTE';
    public const ESTADO_PAGADO = 'PAGADO';

    public function esPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function esPagado(): bool
    {
        return $this->estado === self::ESTADO_PAGADO;
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }
}
