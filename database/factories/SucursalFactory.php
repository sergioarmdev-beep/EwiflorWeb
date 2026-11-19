<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Inventario\Models\Sucursal;

class SucursalFactory extends Factory
{
    protected $model = Sucursal::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->city() . ' Sucursal',
            'departamento' => fake()->randomElement([
                'LA_PAZ', 'SANTA_CRUZ', 'COCHABAMBA', 'ORURO', 'POTOSI',
                'TARIJA', 'CHUQUISACA', 'BENI', 'PANDO',
            ]),
            'direccion' => fake()->address(),
            'telefono' => fake()->phoneNumber(),
            'estado' => 'ACTIVO',
        ];
    }
}
