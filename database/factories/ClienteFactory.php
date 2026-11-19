<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clientes\Models\Cliente;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->company(),
            'telefono' => fake()->phoneNumber(),
            'correo' => fake()->optional()->safeEmail(),
            'tipo_cliente' => fake()->randomElement(Cliente::TIPOS),
            'es_mayorista' => fake()->boolean(20),
            'estado' => 'ACTIVO',
        ];
    }
}
