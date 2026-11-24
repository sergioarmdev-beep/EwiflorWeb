<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Catalogo\Models\Producto;
use Modules\Catalogo\Models\VarianteProducto;

class VarianteProductoFactory extends Factory
{
    protected $model = VarianteProducto::class;

    public function definition(): array
    {
        return [
            'id_producto' => Producto::inRandomOrder()->value('id_producto') ?? 1,
            'sku' => fake()->unique()->bothify('SKU-####'),
            'color' => fake()->randomElement(['Negro', 'Blanco', 'Rojo', 'Azul', 'Gris', 'Plateado']),
            'caracteristicas' => fake()->sentence(6),
            'precio_usd' => fake()->randomFloat(2, 10, 500),
            'estado' => 'ACTIVO',
        ];
    }
}
