<?php

namespace Modules\Inventario\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalogo\Models\VarianteProducto;

class DetalleMovimiento extends Model
{
    protected $table = 'detalle_movimiento';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_movimiento',
        'id_variante',
        'cantidad',
        'sentido',
        'observacion',
    ];

    protected $casts = [
        'cantidad' => 'integer',
    ];

    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(Movimiento::class, 'id_movimiento', 'id_movimiento');
    }

    public function variante(): BelongsTo
    {
        return $this->belongsTo(VarianteProducto::class, 'id_variante', 'id_variante');
    }
}
