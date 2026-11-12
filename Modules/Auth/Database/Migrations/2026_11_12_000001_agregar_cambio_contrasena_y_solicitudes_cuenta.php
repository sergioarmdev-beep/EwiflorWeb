<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - usuario.debe_cambiar_contrasena : obliga a cambiar la contraseña en el siguiente ingreso.
 * - solicitud_cuenta                : solicitudes enviadas desde el login, que un admin revisa.
 *
 * Es idempotente: si ya aplicaste el SQL a mano, no falla.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('usuario', 'debe_cambiar_contrasena')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->boolean('debe_cambiar_contrasena')->default(false);
            });
        }

        if (!Schema::hasTable('solicitud_cuenta')) {
            Schema::create('solicitud_cuenta', function (Blueprint $table) {
                $table->increments('id_solicitud');
                $table->string('nombre_completo', 100);
                $table->string('correo', 100);
                $table->string('celular', 20);
                $table->text('motivo');
                $table->string('estado', 12)->default('PENDIENTE'); // PENDIENTE | APROBADA | RECHAZADA
                $table->timestamp('fecha_creacion')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->integer('id_usuario_revisor')->nullable();
                $table->timestamp('fecha_revision')->nullable();
                $table->string('nota_revision', 255)->nullable();
                $table->integer('id_usuario_creado')->nullable();

                $table->index('estado');
                $table->index('correo');

                $table->foreign('id_usuario_revisor')->references('id_usuario')->on('usuario')->nullOnDelete();
                $table->foreign('id_usuario_creado')->references('id_usuario')->on('usuario')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_cuenta');

        if (Schema::hasColumn('usuario', 'debe_cambiar_contrasena')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->dropColumn('debe_cambiar_contrasena');
            });
        }
    }
};
