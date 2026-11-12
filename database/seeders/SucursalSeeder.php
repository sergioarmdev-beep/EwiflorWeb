<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventario\Models\Sucursal;
use Modules\Inventario\Models\Almacen;

class SucursalSeeder extends Seeder
{
    /**
     * Crea las 2 sucursales y 3 almacenes descritos en el SRS.
     * Idempotente: re-ejecutarlo no duplica ni falla por UNIQUE.
     */
    public function run(): void
    {
        $sucursales = [
            [
                'nombre'       => 'La Paz',
                'departamento' => 'LA_PAZ',
                'direccion'    => 'Av. Arce, Zona Central',
                'telefono'     => '2-2441234',
                'estado'       => 'ACTIVO',
                'almacenes'    => [
                    ['nombre' => 'Almacén La Paz Central', 'tamano' => 'GRANDE'],
                    ['nombre' => 'Almacén La Paz Secundario', 'tamano' => 'PEQUENO'],
                ],
            ],
            [
                'nombre'       => 'Santa Cruz',
                'departamento' => 'SANTA_CRUZ',
                'direccion'    => 'Av. Cristo Redentor, Km 5',
                'telefono'     => '3-3456789',
                'estado'       => 'ACTIVO',
                'almacenes'    => [
                    ['nombre' => 'Almacén Santa Cruz Central', 'tamano' => 'GRANDE'],
                ],
            ],
        ];

        foreach ($sucursales as $data) {
            $almacenes = $data['almacenes'];
            unset($data['almacenes']);

            $sucursal = Sucursal::updateOrCreate(
                ['nombre' => $data['nombre']],
                $data
            );

            foreach ($almacenes as $alm) {
                Almacen::updateOrCreate(
                    ['nombre' => $alm['nombre']],
                    [
                        'tamano'      => $alm['tamano'],
                        'id_sucursal' => $sucursal->id_sucursal,
                        'estado'      => 'ACTIVO',
                    ]
                );
            }
        }

        $this->command->info('Sucursales y almacenes creados:');
        $this->command->table(
            ['Sucursal', 'Almacén', 'Tamaño'],
            Almacen::with('sucursal')
                ->orderBy('id_almacen')
                ->get()
                ->map(fn($a) => [
                    $a->sucursal?->nombre ?? '—',
                    $a->nombre,
                    $a->tamano,
                ])
                ->toArray()
        );
    }
}
