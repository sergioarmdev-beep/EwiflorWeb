
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::create('producto_imagen', function (Blueprint $table) {
            $table->increments('id_imagen');

            // Debe coincidir con producto.id_producto (INTEGER).
            $table->integer('id_producto');

            $table->string('ruta_imagen', 500);
            $table->string('texto_alternativo', 255)->nullable();
            $table->smallInteger('orden')->default(0);
            $table->boolean('es_principal')->default(false);
            $table->timestampTz('fecha_creacion')->useCurrent();

            $table->foreign(
                'id_producto',
                'fk_producto_imagen_producto'
            )
                ->references('id_producto')
                ->on('producto')
                ->cascadeOnDelete();

            $table->index(
                ['id_producto', 'orden'],
                'idx_producto_imagen_orden'
            );
        });

        // Impide registrar órdenes negativas.
        DB::statement(
            'ALTER TABLE producto_imagen
             ADD CONSTRAINT chk_producto_imagen_orden
             CHECK (orden >= 0)'
        );

        // Permite como máximo una imagen principal por producto.
        DB::statement(
            'CREATE UNIQUE INDEX uq_producto_imagen_principal
             ON producto_imagen (id_producto)
             WHERE es_principal = TRUE'
        );
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_imagen');
    }
};
