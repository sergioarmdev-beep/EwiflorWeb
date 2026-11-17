<?php

namespace Modules\Promociones\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Modules\Auth\Models\User;
use Modules\Catalogo\Models\VarianteProducto;

class Promocion extends Model
{
    public const ESTADOS = ['ACTIVA', 'INACTIVA'];

    public const TIPOS = ['PCT', 'USD'];

    protected $table = 'promocion';

    protected $primaryKey = 'id_promocion';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo_descuento',
        'valor_descuento',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'id_usuario_crea',
    ];

    protected $casts = [
        'valor_descuento' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_creacion' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'id_promocion';
    }

    /* ---------- Relaciones ---------- */

    public function variantes()
    {
        return $this->belongsToMany(
            VarianteProducto::class,
            'promocion_variante',
            'id_promocion',
            'id_variante'
        )->withPivot('id_promocion', 'id_variante');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario_crea', 'id_usuario');
    }

    /* ---------- Scopes ---------- */

    /** Búsqueda sin distinguir mayúsculas por nombre o descripción. */
    public function scopeBuscar(Builder $query, ?string $termino): Builder
    {
        $termino = trim((string) $termino);
        if ($termino === '') {
            return $query;
        }

        $like = '%'.mb_strtolower(addcslashes($termino, '%_\\')).'%';

        return $query->where(fn (Builder $q) => $q
            ->whereRaw('LOWER(promocion.nombre) LIKE ?', [$like])
            ->orWhereRaw("LOWER(COALESCE(promocion.descripcion, '')) LIKE ?", [$like]));
    }

    /** Promociones activas y dentro de su rango de vigencia. */
    public function scopeVigentes(Builder $query): Builder
    {
        return $query
            ->where('estado', 'ACTIVA')
            ->where('fecha_inicio', '<=', Date::today())
            ->where(fn (Builder $q) => $q
                ->whereNull('fecha_fin')
                ->orWhere('fecha_fin', '>=', Date::today()));
    }

    /* ---------- Accesores ---------- */

    public function getEstaVigenteAttribute(): bool
    {
        return $this->estado === 'ACTIVA'
            && $this->fecha_inicio->lte(Date::today())
            && ($this->fecha_fin === null || $this->fecha_fin->gte(Date::today()));
    }
}
