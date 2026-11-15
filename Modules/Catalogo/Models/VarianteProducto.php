<?php

namespace Modules\Catalogo\Models;

use Illuminate\Database\Eloquent\Model;

class VarianteProducto extends Model
{
    protected $table = 'variante_producto';

    protected $primaryKey = 'id_variante';

    public $timestamps = false;

    protected $fillable = ['id_producto', 'sku', 'color', 'caracteristicas', 'precio_usd', 'estado'];

    protected $casts = [
        'precio_usd' => 'decimal:2',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
