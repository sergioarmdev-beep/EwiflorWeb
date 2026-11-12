<?php

namespace Modules\Catalogo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    public const CATEGORIAS = ['ALIMENTARIA', 'PANADERIA', 'REPOSTERIA', 'CARNICA'];
    public const TIPOS = ['MAQUINARIA', 'REPUESTO', 'ACCESORIO'];

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

    protected $casts = [
        'es_publico' => 'boolean',
    ];

    public function getRouteKeyName()
    {
        return 'id_producto';
    }

    /* ---------- Relaciones ---------- */

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    public function variantes()
    {
        return $this->hasMany(VarianteProducto::class, 'id_producto', 'id_producto');
    }

    public function imagenes()
    {
        return $this->hasMany(ProductoImagen::class, 'id_producto', 'id_producto')->orderBy('orden');
    }

    public function imagenPrincipal()
    {
        return $this->hasOne(ProductoImagen::class, 'id_producto', 'id_producto')->where('es_principal', true);
    }

    /* ---------- Scopes ---------- */

    public function scopePublicos(Builder $query): Builder
    {
        return $query->where('es_publico', true)->where('estado', 'ACTIVO');
    }

    /**
     * Búsqueda sin distinguir mayúsculas por código, nombre, modelo o marca.
     */
    public function scopeBuscar(Builder $query, ?string $termino): Builder
    {
        $termino = trim((string) $termino);
        if ($termino === '') {
            return $query;
        }

        $like = '%' . mb_strtolower(addcslashes($termino, '%_\\')) . '%';

        return $query->where(function (Builder $q) use ($like) {
            $q->whereRaw('LOWER(producto.codigo) LIKE ?', [$like])
                ->orWhereRaw('LOWER(producto.nombre) LIKE ?', [$like])
                ->orWhereRaw('LOWER(producto.modelo) LIKE ?', [$like])
                ->orWhereHas('marca', fn (Builder $m) => $m->whereRaw('LOWER(nombre) LIKE ?', [$like]));
        });
    }

    /* ---------- Accesores ---------- */

    /**
     * Stock bajo: solo aplica a productos INVENTARIABLES y únicamente cuando
     * la consulta trajo la columna calculada `stock_actual`
     * (ver constante STOCK en ProductoController).
     */
    public function getStockBajoAttribute(): bool
    {
        if ($this->modalidad !== 'INVENTARIABLE' || !array_key_exists('stock_actual', $this->attributes)) {
            return false;
        }

        return (int) $this->attributes['stock_actual'] <= (int) $this->stock_minimo;
    }
}
