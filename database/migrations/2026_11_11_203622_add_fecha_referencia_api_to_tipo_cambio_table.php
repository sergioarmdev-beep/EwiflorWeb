<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_cambio', function (Blueprint $table) {
            $table->timestampTz('fecha_referencia_api')
                ->nullable()
                ->after('fecha_registro');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_cambio', function (Blueprint $table) {
            $table->dropColumn('fecha_referencia_api');
        });
    }
};
