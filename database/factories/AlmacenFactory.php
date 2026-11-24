<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Inventario\Models\Almacen;
use Modules\Inventario\Models\Sucursal;

class AlmacenFactory extends Factory
{
    protected $model = Almacen::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->company().' Almacén',
            'tamano' => fake()->randomElement(['GRANDE', 'PEQUENO']),
            'id_sucursal' => Sucursal::inRandomOrder()->value('id_sucursal') ?? 1,
            'estado' => 'ACTIVO',
        ];
    }
}
