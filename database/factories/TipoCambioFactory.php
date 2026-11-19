<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Precios\Models\TipoCambio;
use Modules\Auth\Models\User;

class TipoCambioFactory extends Factory
{
    protected $model = TipoCambio::class;

    public function definition(): array
    {
        return [
            'fecha' => fake()->dateTimeBetween('-90 days', 'today')->format('Y-m-d'),
            'tipo' => fake()->randomElement(['PARALELO', 'OFICIAL']),
            'valor' => fake()->randomFloat(4, 6.80, 7.40),
            'origen' => fake()->randomElement(['API', 'MANUAL']),
            'id_usuario' => User::inRandomOrder()->value('id_usuario') ?? 1,
        ];
    }
}
