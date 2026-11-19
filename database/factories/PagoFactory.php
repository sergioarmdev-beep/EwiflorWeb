<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Ventas\Models\Pago;
use Modules\Ventas\Models\Venta;

class PagoFactory extends Factory
{
    protected $model = Pago::class;

    public function definition(): array
    {
        return [
            'id_venta' => Venta::inRandomOrder()->value('id_venta') ?? 1,
            'forma_pago' => fake()->randomElement(Pago::FORMAS),
            'monto_bs' => fake()->randomFloat(2, 20, 1000),
            'fecha_hora' => fake()->dateTimeBetween('-90 days', 'today'),
            'referencia' => fake()->optional()->bothify('REF-####'),
        ];
    }
}
