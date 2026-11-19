<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Ventas\Models\DetalleVenta;
use Modules\Ventas\Models\Venta;
use Modules\Catalogo\Models\VarianteProducto;

class DetalleVentaFactory extends Factory
{
    protected $model = DetalleVenta::class;

    public function definition(): array
    {
        return [
            'id_venta' => Venta::inRandomOrder()->value('id_venta') ?? 1,
            'id_variante' => VarianteProducto::inRandomOrder()->value('id_variante') ?? 1,
            'cantidad' => fake()->numberBetween(1, 5),
            'precio_unitario_usd' => fake()->randomFloat(2, 5, 300),
            'descuento_pct' => fake()->randomElement([0, 0, 0, 5, 10]),
            'garantia_meses' => fake()->randomElement([0, 0, 3, 6, 12]),
        ];
    }
}
