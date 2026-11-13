<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('cliente', 'estado')) {
            return;
        }

        // VARCHAR(10) + DEFAULT + CHECK igual que el resto de tablas del MVP.
        DB::statement("
            ALTER TABLE cliente
            ADD COLUMN estado VARCHAR(10) NOT NULL DEFAULT 'ACTIVO'
            CHECK (estado IN ('ACTIVO','INACTIVO'))
        ");

        DB::statement('CREATE INDEX idx_cliente_estado ON cliente (estado)');
    }

    public function down(): void {}
};
