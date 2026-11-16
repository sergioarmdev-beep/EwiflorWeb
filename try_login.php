<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;

$candidatas = ['admin123', '123456', 'password', 'admin', 'gerente123', 'vendedor123', 'tecnico123', '12345678', 'secret'];

foreach (User::all() as $u) {
    foreach ($candidatas as $p) {
        if (Hash::check($p, $u->contrasena_hash)) {
            echo "{$u->nombre_usuario} => {$p}\n";
            break;
        }
    }
}
