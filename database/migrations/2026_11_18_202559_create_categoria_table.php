<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria', function (Blueprint $table) {
            $table->string('slug', 50)->primary();      // 'ALIMENTARIA', etc.
            $table->string('titulo', 80);
            $table->string('descripcion', 200)->nullable();
            $table->string('imagen', 255)->nullable();
            $table->smallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
        });

        // Defaults actuales (lo que hoy está hardcodeado en $meta)
        DB::table('categoria')->insert([
            [
                'slug'        => 'ALIMENTARIA',
                'titulo'      => 'Línea alimentaria',
                'descripcion' => 'Equipamiento para preparación, proceso y conservación de alimentos.',
                'imagen'      => '/images/catalog/categoria-alimentaria.jpg',
                'orden'       => 1,
                'activo'      => true,
            ],
            [
                'slug'        => 'PANADERIA',
                'titulo'      => 'Panadería',
                'descripcion' => 'Maquinaria para procesos de producción de panadería y masas.',
                'imagen'      => '/images/catalog/categoria-panaderia.jpg',
                'orden'       => 2,
                'activo'      => true,
            ],
            [
                'slug'        => 'REPOSTERIA',
                'titulo'      => 'Repostería',
                'descripcion' => 'Equipos para elaboración de repostería, dulces y postres.',
                'imagen'      => '/images/catalog/categoria-reposteria.jpg',
                'orden'       => 3,
                'activo'      => true,
            ],
            [
                'slug'        => 'CARNICA',
                'titulo'      => 'Línea cárnica',
                'descripcion' => 'Soluciones para procesamiento de carnes y embutidos.',
                'imagen'      => '/images/catalog/categoria-carnica.jpg',
                'orden'       => 4,
                'activo'      => true,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria');
    }
};
