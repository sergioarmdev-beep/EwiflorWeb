<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Inventario\Models\Existencia;
use Modules\Inventario\Models\Almacen;
use Modules\Catalogo\Models\VarianteProducto;

class ExistenciaFactory extends Factory
{
    protected $model = Existencia::class;

    public function definition(): array
    {
        $total = fake()->numberBetween(0, 120);

        return [
            'id_almacen' => Almacen::inRandomOrder()->value('id_almacen') ?? 1,
            'id_variante' => VarianteProducto::inRandomOrder()->value('id_variante') ?? 1,
            'cantidad_total' => $total,
            'cantidad_reservada' => fake()->numberBetween(0, $total),
            'ubicacion_interna' => fake()->optional()->bothify('LOC-??'),
        ];
    }
}
