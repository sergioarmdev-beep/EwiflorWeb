<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::select('SELECT id_usuario, nombre_usuario, nombre_rol, estado, LEFT(contrasena_hash, 7) AS hash_prefijo FROM usuario ORDER BY id_usuario');

foreach ($rows as $r) {
    echo "{$r->id_usuario} | {$r->nombre_usuario} | {$r->nombre_rol} | {$r->estado} | {$r->hash_prefijo}\n";
}
