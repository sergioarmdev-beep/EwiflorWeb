<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('tipo_cambio', 'valor_compra')) {
            return;
        }

        Schema::table('tipo_cambio', function (Blueprint $table) {
            // `valor` sigue siendo el precio de venta; este es el de compra (solo viene de la API).
            $table->decimal('valor_compra', 10, 4)->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('tipo_cambio', 'valor_compra')) {
            Schema::table('tipo_cambio', function (Blueprint $table) {
                $table->dropColumn('valor_compra');
            });
        }
        // Deliberadamente no se elimina: `valor_compra` ya se usa en TipoCambioService.
        // Si en el futuro se requiere quitarla, hacerlo con una migración explícita
        // que primero migre los datos.
    }
};
