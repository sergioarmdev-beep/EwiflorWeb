<?php

namespace Modules\Ventas\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Catalogo\Models\VarianteProducto;
use Modules\Garantias\Models\Garantia;

class DetalleVenta extends Model
{
    protected $table = 'detalle_venta';

    protected $primaryKey = 'id_detalle_venta';

    public $timestamps = false;

    protected $fillable = [
        'id_venta',
        'id_variante',
        'cantidad',
        'precio_unitario_usd',
        'descuento_pct',
        'garantia_meses',
    ];

    protected $casts = [
        'cantidad' => 'int',
        'precio_unitario_usd' => 'decimal:2',
        'descuento_pct' => 'decimal:2',
        'garantia_meses' => 'int',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }

    public function variante()
    {
        return $this->belongsTo(VarianteProducto::class, 'id_variante');
    }

    public function garantia()
    {
        return $this->hasOne(Garantia::class, 'id_detalle_venta', 'id_detalle_venta');
    }

    public function subtotalUsd(): float
    {
        return round(
            $this->cantidad * (float) $this->precio_unitario_usd
                * (1 - (float) $this->descuento_pct / 100),
            2
        );
    }
}
