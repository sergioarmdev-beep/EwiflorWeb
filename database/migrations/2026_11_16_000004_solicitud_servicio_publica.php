<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Habilita solicitudes de servicio técnico creadas desde la web pública:
 * - id_usuario_registra pasa a nullable (un visitante anónimo no tiene usuario).
 * - Se agrega la columna origen (PUBLICO | INTERNO) para distinguir el canal.
 * - Índice por cliente para las consultas del panel público.
 * - Trigger de auditoría (regla: auditoría 100% por triggers de PostgreSQL).
 *
 * Idempotente: si ya se aplicó el cambio, no falla.
 */
return new class extends Migration
{
    protected $connection = 'pgsql_migrator';

    public function up(): void
    {
        DB::statement('ALTER TABLE solicitud_servicio ALTER COLUMN id_usuario_registra DROP NOT NULL');

        if (! Schema::hasColumn('solicitud_servicio', 'origen')) {
            DB::statement("ALTER TABLE solicitud_servicio
                ADD COLUMN origen VARCHAR(10) NOT NULL DEFAULT 'PUBLICO'
                CHECK (origen IN ('PUBLICO','INTERNO'))");
        }

        DB::statement('CREATE INDEX IF NOT EXISTS idx_solicitud_cliente ON solicitud_servicio (id_cliente)');

        // Auditoría por trigger (no Observers). El actor se toma de app.id_usuario.
        DB::statement('DROP TRIGGER IF EXISTS trg_aud_solicitud_servicio ON solicitud_servicio');
        DB::statement('CREATE TRIGGER trg_aud_solicitud_servicio
            AFTER INSERT OR UPDATE OR DELETE ON solicitud_servicio
            FOR EACH ROW EXECUTE FUNCTION fn_auditar_cambio(\'id_solicitud\')');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_aud_solicitud_servicio ON solicitud_servicio');
        DB::statement('DROP INDEX IF EXISTS idx_solicitud_cliente');

        if (Schema::hasColumn('solicitud_servicio', 'origen')) {
            DB::statement('ALTER TABLE solicitud_servicio DROP COLUMN origen');
        }

        // Restaurar NOT NULL solo si no quedan filas anónimas.
        $anonimas = DB::table('solicitud_servicio')->whereNull('id_usuario_registra')->count();
        if ($anonimas === 0) {
            DB::statement('ALTER TABLE solicitud_servicio ALTER COLUMN id_usuario_registra SET NOT NULL');
        }
    }
};
