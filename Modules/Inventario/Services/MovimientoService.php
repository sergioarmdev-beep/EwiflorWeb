<?php

namespace Modules\Inventario\Services;

use App\Support\DbFunction;
use Modules\Auth\Models\User;

class MovimientoService
{
    /**
     * Registra un movimiento de inventario vía la función PostgreSQL.
     *
     * @param  array{tipo: string, motivo: string, id_almacen_origen?: ?int,
     *               id_almacen_destino?: ?int, id_variante: int,
     *               cantidad: int, sentido?: ?string}  $data
     * @return int  id_movimiento
     */
    public function registrar(array $data, User $actor): int
    {
        $detalle = [
            'id_variante' => (int) $data['id_variante'],
            'cantidad'    => (int) $data['cantidad'],
        ];

        if ($data['tipo'] === 'AJUSTE') {
            $detalle['sentido'] = $data['sentido'];
        }

        return (int) DbFunction::scalar('registrar_movimiento_inventario', [
            $data['tipo'],
            $data['motivo'],
            $actor->id_usuario,
            $data['id_almacen_origen'] ?? null,
            $data['id_almacen_destino'] ?? null,
            null, // id_venta
            json_encode([$detalle]),
        ]);
    }
}
