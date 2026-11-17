<?php

namespace Modules\Promociones\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Catalogo\Models\VarianteProducto;

/**
 * Pivote promocion <-> variante con PK compuesta (id_promocion, id_variante).
 * No tiene id surrogate, por eso $incrementing es false y no se usa find().
 */
class PromocionVariante extends Model
{
    protected $table = 'promocion_variante';

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = null;

    protected $fillable = ['id_promocion', 'id_variante'];

    public function promocion()
    {
        return $this->belongsTo(Promocion::class, 'id_promocion', 'id_promocion');
    }

    public function variante()
    {
        return $this->belongsTo(VarianteProducto::class, 'id_variante', 'id_variante');
    }
}
