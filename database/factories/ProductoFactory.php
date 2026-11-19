<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Catalogo\Models\Producto;
use Modules\Catalogo\Models\Marca;

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->bothify('PROD-####'),
            'nombre' => fake()->unique()->words(3, true) . ' ' . fake()->word(),
            'descripcion' => fake()->sentence(),
            'categoria' => fake()->randomElement(Producto::CATEGORIAS),
            'id_marca' => Marca::inRandomOrder()->value('id_marca') ?? 1,
            'modelo' => fake()->bothify('MOD-??'),
            'tipo_producto' => fake()->randomElement(array_diff(Producto::TIPOS, ['COMBO'])),
            'modalidad' => fake()->randomElement(['INVENTARIABLE', 'BAJO_PEDIDO']),
            'stock_minimo' => fake()->numberBetween(0, 20),
            'garantia_meses' => fake()->randomElement([0, 3, 6, 12]),
            'es_publico' => true,
            'estado' => 'ACTIVO',
        ];
    }
}
