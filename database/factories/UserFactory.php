<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'nombre_completo' => fake()->name(),
            'nombre_usuario' => fake()->unique()->slug(),
            'correo' => fake()->unique()->safeEmail(),
            'contrasena_hash' => static::$password ??= Hash::make('password123'),
            'nombre_rol' => fake()->randomElement([
                'GERENTE', 'ADMINISTRADOR', 'VENDEDOR', 'SERVICIO_TECNICO',
            ]),
            'id_sucursal' => null,
            'porcentaje_comision_pct' => 0,
            'estado' => 'ACTIVO',
        ];
    }
}
