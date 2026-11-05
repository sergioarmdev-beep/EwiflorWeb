<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {

        $existe = DB::table('usuario')->where('nombre_usuario', 'admin')->exists();

        if (!$existe) {
            DB::table('usuario')->insert([
                'nombre_usuario' => 'admin',
                'correo' => 'admin@ewiflor.com',
                'contrasena_hash' => Hash::make('admin123'),
                'nombre' => 'Administrador',
                'apellido' => 'Sistema',
                'nombre_rol' => 'GERENTE',
                'estado' => 'ACTIVO',
                'fecha_creacion' => now(),
            ]);

            $this->command->info('Usuario administrador creado con éxito (admin / admin123).');
        } else {
            $this->command->info('El usuario administrador ya existe. No se modificó.');
        }
    }
}
