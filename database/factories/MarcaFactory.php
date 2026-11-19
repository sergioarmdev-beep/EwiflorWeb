<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Catalogo\Models\Marca;

class MarcaFactory extends Factory
{
    protected $model = Marca::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->company(),
            'pais_origen' => fake()->randomElement([
                'Bolivia', 'Brasil', 'Argentina', 'Estados Unidos', 'China', 'Alemania', 'México',
            ]),
        ];
    }
}
