<?php

namespace Modules\Catalogo\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'producto';
    protected $primaryKey = 'id_producto';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'categoria',
        'id_marca',
        'modelo',
        'tipo_producto',
        'modalidad',
        'stock_minimo',
        'garantia_meses',
        'es_publico',
        'estado'
    ];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    public function variantes()
    {
        return $this->hasMany(VarianteProducto::class, 'id_producto', 'id_producto');
    }
}
