<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EwiflorDemoSeeder::class, // sucursales, usuarios, catálogo, tipo de cambio, existencia, clientes, ventas y movimientos
        ]);
    }
}
