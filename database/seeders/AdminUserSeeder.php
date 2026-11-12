<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Inventario\Models\Sucursal;
use Modules\Auth\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $idSucursal = Sucursal::query()->value('id_sucursal');

        User::updateOrCreate(
            ['nombre_usuario' => 'admin'],
            [
                'nombre_completo'         => 'Administrador Demo',
                'correo'                  => 'admin@ewiflor.local',
                'contrasena_hash'         => Hash::make('admin123'),
                'nombre_rol'              => 'GERENTE',
                'id_sucursal'             => $idSucursal,
                'porcentaje_comision_pct' => 0,
                'estado'                  => 'ACTIVO',
            ]
        );
    }
}
