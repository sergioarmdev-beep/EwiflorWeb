<?php

namespace Modules\Catalogo\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Promociones\Models\Promocion;

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

    /** Promociones (dueño: Promociones) que cubren esta variante. */
    public function promociones()
    {
        return $this->belongsToMany(
            Promocion::class,
            'promocion_variante',
            'id_variante',
            'id_promocion'
        );
    }
}
