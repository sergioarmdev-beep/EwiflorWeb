<?php

namespace Modules\Catalogo\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table      = 'categoria';
    protected $primaryKey = 'slug';
    public    $incrementing = false;
    protected $keyType    = 'string';
    public    $timestamps = false;

    protected $fillable = ['slug', 'titulo', 'descripcion', 'imagen', 'orden', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function scopeActivas($q)
    {
        return $q->where('activo', true)->orderBy('orden');
    }
}
