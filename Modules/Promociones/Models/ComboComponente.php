<?php

namespace Modules\Promociones\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Catalogo\Models\Producto;

/**
 * Componentes de un combo. La PK es compuesta (id_producto, id_componente)
 * y no tiene id surrogate, así que este modelo se usa para leer y para
 * gestionar filas por query builder, nunca con find()/update() por id.
 */
class ComboComponente extends Model
{
    protected $table = 'combo_componente';

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = null;

    protected $fillable = ['id_producto', 'id_componente', 'cantidad'];

    protected $casts = [
        'cantidad' => 'integer',
    ];

    /** El combo al que pertenece la fila. */
    public function combo()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    /** El producto incluido en el combo. */
    public function componente()
    {
        return $this->belongsTo(Producto::class, 'id_componente', 'id_producto');
    }
}
