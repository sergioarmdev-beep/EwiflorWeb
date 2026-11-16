<?php

namespace Modules\Garantias\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Ventas\Models\DetalleVenta;

class Garantia extends Model
{
    /**
     * La tabla garantia se llena automáticamente mediante el trigger
     * PostgreSQL fn_crear_garantias_venta(). Laravel solo la lee.
     */
    protected $table = 'garantia';

    protected $primaryKey = 'id_garantia';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = ['*'];

    public function detalleVenta()
    {
        return $this->belongsTo(DetalleVenta::class, 'id_detalle_venta', 'id_detalle_venta');
    }
}
