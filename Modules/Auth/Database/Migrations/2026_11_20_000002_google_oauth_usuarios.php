<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Integración OAuth de Google (solo login, sin creación de cuentas).
 *
 * - usuario.google_id   : identificador de Google (sub), único, nullable.
 * - usuario.proveedor   : 'tradicional' | 'google' | 'ambos'.
 * - usuario.avatar_url  : foto de perfil de Google.
 *
 * Las cuentas solo se VINCULAN si el correo ya existe en la tabla usuario.
 * Nunca se crea una cuenta nueva desde Google.
 *
 * Idempotente: se puede ejecutar más de una vez. DDL con rol migrador.
 */
return new class extends Migration
{
    protected $connection = 'pgsql_migrator';

    public function up(): void
    {
        // ── 1) Columnas nuevas en usuario ─────────────────────────────
        if (! Schema::hasColumn('usuario', 'google_id')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->string('google_id', 255)->nullable()->unique('ux_usuario_google_id');
            });
        }

        if (! Schema::hasColumn('usuario', 'proveedor')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->string('proveedor', 30)->nullable()->default('tradicional')
                    ->comment("'tradicional' | 'google' | 'ambos'");
            });
        }

        if (! Schema::hasColumn('usuario', 'avatar_url')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->string('avatar_url', 500)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('usuario', 'google_id')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->dropUnique('ux_usuario_google_id');
                $table->dropColumn('google_id');
            });
        }

        if (Schema::hasColumn('usuario', 'proveedor')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->dropColumn('proveedor');
            });
        }

        if (Schema::hasColumn('usuario', 'avatar_url')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->dropColumn('avatar_url');
            });
        }
    }
};
