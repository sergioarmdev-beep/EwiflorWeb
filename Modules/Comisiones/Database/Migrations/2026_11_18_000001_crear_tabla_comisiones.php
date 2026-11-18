<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comision', function (Blueprint $table) {
            $table->increments('id_comision');
            $table->integer('id_usuario')->index();
            $table->integer('id_sucursal')->index();
            $table->integer('anio');
            $table->integer('mes');
            $table->integer('cantidad_ventas')->default(0);
            $table->decimal('total_ventas_bs', 12, 2)->default(0);
            $table->decimal('porcentaje_comision_pct', 5, 2)->default(0);
            $table->decimal('monto_comision_bs', 12, 2)->default(0);
            $table->string('estado', 20)->default('PENDIENTE');
            $table->timestamp('fecha_pago')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();

            $table->unique(
                ['id_usuario', 'id_sucursal', 'anio', 'mes'],
                'comision_usuario_sucursal_anio_mes_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comision');
    }
};
