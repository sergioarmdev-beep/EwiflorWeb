<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Ventas\Models\Venta;
use Modules\Auth\Models\User;
use Modules\Clientes\Models\Cliente;
use Modules\Precios\Models\TipoCambio;
use Modules\Inventario\Models\Sucursal;

class VentaFactory extends Factory
{
    protected $model = Venta::class;

    public function definition(): array
    {
        $fecha = fake()->dateTimeBetween('-90 days', 'today');

        return [
            'id_cliente' => Cliente::inRandomOrder()->value('id_cliente') ?? 1,
            'id_usuario' => User::where('nombre_rol', 'VENDEDOR')
                ->inRandomOrder()
                ->value('id_usuario') ?? User::inRandomOrder()->value('id_usuario') ?? 1,
            'id_sucursal' => Sucursal::where('estado', 'ACTIVO')
                ->inRandomOrder()
                ->value('id_sucursal') ?? 1,
            'id_cotizacion' => null,
            'id_tipo_cambio' => TipoCambio::whereDate('fecha', '<=', $fecha)
                ->orderByDesc('fecha')
                ->value('id_tipo_cambio') ?? 1,
            'fecha_hora' => $fecha,
            'estado' => 'REGISTRADA',
            'numero_factura' => null,
            'fecha_entrega' => null,
        ];
    }
}
