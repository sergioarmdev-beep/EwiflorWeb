<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega la ubicación geográfica a sucursal (latitud/longitud separadas,
 * sin PostGIS). Columnas nulables: la ubicación es opcional.
 *
 * Es idempotente: si ya aplicaste el SQL a mano, no falla.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('sucursal', 'latitud')) {
            return;
        }

        DB::statement('
            ALTER TABLE sucursal
            ADD COLUMN latitud  DOUBLE PRECISION
                CHECK (latitud  IS NULL OR (latitud  BETWEEN -90  AND 90)),
            ADD COLUMN longitud DOUBLE PRECISION
                CHECK (longitud IS NULL OR (longitud BETWEEN -180 AND 180))
        ');
    }

    public function down(): void
    {
        if (Schema::hasColumn('sucursal', 'longitud')) {
            Schema::table('sucursal', function ($table) {
                $table->dropColumn(['longitud', 'latitud']);
            });
        }
    }
};
