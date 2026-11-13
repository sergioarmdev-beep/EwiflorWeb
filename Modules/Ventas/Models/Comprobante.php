<?php

namespace Modules\Ventas\Models;

use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    protected $table = 'comprobante';
    protected $primaryKey = 'id_comprobante';
    public $timestamps = false;

    protected $fillable = ['id_venta', 'tipo', 'numero'];

    protected $casts = ['fecha_emision' => 'datetime'];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }
}
