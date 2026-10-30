<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'nombre_completo',
        'nombre_usuario',
        'correo',
        'contrasena_hash',
        'nombre_rol',
        'id_sucursal',
        'porcentaje_comision_pct',
        'estado',
    ];

    protected $hidden = [
        'contrasena_hash',
    ];

    protected $casts = [
        'porcentaje_comision_pct' => 'decimal:2',
        'fecha_creacion'          => 'datetime',
    ];

    public function username(): string
    {
        return 'nombre_usuario';
    }


    public function getAuthPassword(): string
    {
        return $this->contrasena_hash;
    }


    protected function nombreUsuario(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => strtolower($value),
        );
    }

    protected function correo(): Attribute
    {
        return Attribute::make(
            set: fn(?string $value) => $value === null ? null : strtolower($value),
        );
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }

    public function esGerente(): bool
    {
        return $this->nombre_rol === 'GERENTE';
    }
}
