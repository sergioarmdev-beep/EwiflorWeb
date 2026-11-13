<?php

namespace Modules\Cotizaciones\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Catalogo\Models\VarianteProducto;

class DetalleCotizacion extends Model
{
    protected $table = 'detalle_cotizacion';

    protected $primaryKey = 'id_cotizacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_cotizacion',
        'id_variante',
        'cantidad',
        'precio_unitario_usd',
        'descuento_pct',
    ];

    protected $casts = [
        'cantidad' => 'int',
        'precio_unitario_usd' => 'decimal:2',
        'descuento_pct' => 'decimal:2',
    ];

    public function variante()
    {
        return $this->belongsTo(VarianteProducto::class, 'id_variante', 'id_variante');
    }
}
