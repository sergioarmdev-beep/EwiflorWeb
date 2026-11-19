<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Inventario\Models\Movimiento;
use Modules\Auth\Models\User;
use Modules\Inventario\Models\Almacen;

class MovimientoFactory extends Factory
{
    protected $model = Movimiento::class;

    public function definition(): array
    {
        $tipo = fake()->randomElement(['ENTRADA', 'SALIDA', 'AJUSTE', 'TRANSFERENCIA']);

        return [
            'tipo' => $tipo,
            'motivo' => fake()->sentence(),
            'id_usuario' => User::inRandomOrder()->value('id_usuario') ?? 1,
            'id_almacen_origen' => $tipo === 'ENTRADA'
                ? null
                : Almacen::inRandomOrder()->value('id_almacen') ?? 1,
            'id_almacen_destino' => in_array($tipo, ['ENTRADA', 'TRANSFERENCIA'])
                ? Almacen::inRandomOrder()->value('id_almacen') ?? 1
                : null,
            'id_venta' => null,
            'fecha_hora' => fake()->dateTimeBetween('-90 days', 'today'),
        ];
    }
}
