<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::select('SELECT c.id_cotizacion, c.estado, count(d.id_cotizacion) AS lineas FROM cotizacion c LEFT JOIN detalle_cotizacion d ON d.id_cotizacion = c.id_cotizacion GROUP BY c.id_cotizacion, c.estado ORDER BY c.id_cotizacion');

foreach ($rows as $r) {
    echo "cot {$r->id_cotizacion} | {$r->estado} | lineas={$r->lineas}\n";
}
