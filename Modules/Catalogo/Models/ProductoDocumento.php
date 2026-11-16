<?php

namespace Modules\Catalogo\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoDocumento extends Model
{
    protected $table = 'producto_documento';

    protected $primaryKey = 'id_documento';

    public $timestamps = false;

    protected $fillable = [
        'id_producto',
        'nombre_original',
        'ruta_archivo',
        'extension',
        'fecha_subida',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
