<?php

namespace Modules\Ventas\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pago';
    protected $primaryKey = 'id_pago';
    public $timestamps = false;

    protected $fillable = ['id_venta', 'forma_pago', 'monto_bs', 'referencia'];

    protected $casts = [
        'monto_bs'   => 'decimal:2',
        'fecha_hora' => 'datetime',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }

    public const FORMAS = ['EFECTIVO', 'QR', 'TRANSFERENCIA'];
}
