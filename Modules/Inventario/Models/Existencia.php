<?php

namespace Modules\Inventario\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalogo\Models\VarianteProducto;

class Existencia extends Model
{
    protected $table = 'existencia';

    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_almacen',
        'id_variante',
        'cantidad_total',
        'cantidad_reservada',
        'ubicacion_interna',
    ];

    protected $casts = [
        'cantidad_total'     => 'integer',
        'cantidad_reservada' => 'integer',
    ];

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'id_almacen', 'id_almacen');
    }

    public function variante(): BelongsTo
    {
        return $this->belongsTo(VarianteProducto::class, 'id_variante', 'id_variante');
    }

    public function disponible(): int
    {
        return $this->cantidad_total - $this->cantidad_reservada;
    }
}
