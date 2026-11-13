<?php

namespace Modules\Clientes\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Cotizaciones\Models\Cotizacion;
use Modules\Ventas\Models\Venta;

class Cliente extends Model
{
    protected $table = 'cliente';
    protected $primaryKey = 'id_cliente';
    public $timestamps = false; // fecha_registro la pone el DEFAULT now() de la BD

    protected $fillable = [
        'nombre',
        'telefono',
        'correo',
        'tipo_cliente',
        'es_mayorista',
        'estado',
    ];

    protected $casts = [
        'es_mayorista'   => 'boolean',
        'fecha_registro' => 'datetime',
    ];

    /** Valores aceptados por el CHECK de la tabla. */
    public const TIPOS = [
        'DISTRIBUIDOR',
        'PANADERIA',
        'PASTELERIA',
        'COMIDA_RAPIDA',
        'RESTAURANTE',
        'PERSONA_NATURAL',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_cliente';
    }

    public function scopeActivos(Builder $q): Builder
    {
        return $q->where('estado', 'ACTIVO');
    }

    public function scopeMayoristas(Builder $q): Builder
    {
        return $q->where('es_mayorista', true);
    }

    public function setCorreoAttribute(?string $value): void
    {
        $this->attributes['correo'] = ($value !== null && trim($value) !== '')
            ? strtolower(trim($value))
            : null;
    }
    /**
     * Cotizaciones registradas para este cliente.
     */
    public function cotizaciones(): HasMany
    {
        return $this->hasMany(
            Cotizacion::class,
            'id_cliente',
            'id_cliente'
        );
    }

    /**
     * Ventas registradas para este cliente.
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(
            Venta::class,
            'id_cliente',
            'id_cliente'
        );
    }
}
