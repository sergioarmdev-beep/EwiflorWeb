<?php

namespace Modules\Clientes\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
}
