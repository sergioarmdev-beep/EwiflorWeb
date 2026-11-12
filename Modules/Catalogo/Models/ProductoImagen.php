<?php

namespace Modules\Catalogo\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoImagen extends Model
{
    protected $table = 'producto_imagen';
    protected $primaryKey = 'id_imagen';
    public $timestamps = false;
    protected $fillable = ['id_producto', 'ruta_imagen', 'texto_alternativo', 'orden', 'es_principal'];

    protected $casts = [
        'es_principal' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    /** URL pública del archivo (requiere `php artisan storage:link`). */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->ruta_imagen);
    }
}
