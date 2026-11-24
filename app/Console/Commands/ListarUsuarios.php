<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ListarUsuarios extends Command
{
    protected $signature = 'ewiflor:usuarios:listar';

    protected $description = 'Lista los usuarios (id, nombre, rol y estado) sin exponer contraseñas';

    public function handle(): int
    {
        $usuarios = DB::table('usuario')
            ->orderBy('id_usuario')
            ->get(['id_usuario', 'nombre_usuario', 'nombre_rol', 'estado']);

        $this->table(
            ['ID', 'Usuario', 'Rol', 'Estado'],
            $usuarios->map(fn ($u) => [
                $u->id_usuario,
                $u->nombre_usuario,
                $u->nombre_rol,
                $u->estado,
            ])->all()
        );

        return self::SUCCESS;
    }
}
