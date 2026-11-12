<?php

namespace Modules\Inventario\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Almacen extends Model
{
    protected $table = 'almacen';
    protected $primaryKey = 'id_almacen';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'tamano',
        'id_sucursal',
        'estado',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_almacen';
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }
}
