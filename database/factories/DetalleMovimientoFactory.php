<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Catalogo\Models\VarianteProducto;
use Modules\Inventario\Models\DetalleMovimiento;
use Modules\Inventario\Models\Movimiento;

class DetalleMovimientoFactory extends Factory
{
    protected $model = DetalleMovimiento::class;

    public function definition(): array
    {
        return [
            'id_movimiento' => Movimiento::inRandomOrder()->value('id_movimiento') ?? 1,
            'id_variante' => VarianteProducto::inRandomOrder()->value('id_variante') ?? 1,
            'cantidad' => fake()->numberBetween(1, 20),
            'sentido' => fake()->randomElement(['INCREMENTO', 'DECREMENTO']),
            'observacion' => fake()->sentence(),
        ];
    }
}
