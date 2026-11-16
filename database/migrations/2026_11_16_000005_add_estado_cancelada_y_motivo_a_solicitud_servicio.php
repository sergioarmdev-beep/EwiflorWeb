<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega el estado CANCELADA y la columna motivo_cancelacion a solicitud_servicio,
 * y crea la función PostgreSQL cancelar_solicitud_servicio().
 *
 * - Estado CANCELADA: solo aplica a solicitudes PENDIENTE o PROGRAMADA.
 * - motivo_cancelacion: texto obligatorio al cancelar.
 * - fecha_cierre se establece automáticamente al cancelar.
 * - La auditoría queda cubierta por el trigger fn_auditar_cambio (no Observers).
 *
 * Idempotente: si la columna ya existe, no falla.
 */
return new class extends Migration
{
    protected $connection = 'pgsql_migrator';

    public function up(): void
    {
        // 1) Columna motivo_cancelacion (si no existe).
        if (! Schema::hasColumn('solicitud_servicio', 'motivo_cancelacion')) {
            DB::statement('ALTER TABLE solicitud_servicio
                ADD COLUMN motivo_cancelacion TEXT NULL');
        }

        // 2) Reemplazar el CHECK constraint de estado para incluir CANCELADA.
        // Buscamos el nombre del constraint existente sobre la columna estado
        // y lo dropeamos antes de agregar el nuevo.
        DB::statement(<<<'SQL'
DO $$
DECLARE
    v_constraint_name text;
BEGIN
    SELECT conname INTO v_constraint_name
    FROM pg_constraint
    WHERE conrelid = 'solicitud_servicio'::regclass
      AND contype = 'c'
      AND pg_get_constraintdef(oid) LIKE '%estado%';

    IF v_constraint_name IS NOT NULL THEN
        EXECUTE format('ALTER TABLE solicitud_servicio DROP CONSTRAINT %I', v_constraint_name);
    END IF;

    ALTER TABLE solicitud_servicio
    ADD CONSTRAINT ck_solicitud_servicio_estado
    CHECK (estado IN ('PENDIENTE', 'PROGRAMADA', 'CERRADA', 'CANCELADA'));
END $$;
SQL
        );

        // 3) Función PostgreSQL para cancelar una solicitud de servicio.
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION cancelar_solicitud_servicio(
    p_id_solicitud      INTEGER,
    p_id_usuario_cancela INTEGER,
    p_motivo            TEXT
) RETURNS VOID
LANGUAGE plpgsql
AS $$
DECLARE
    v_estado TEXT;
BEGIN
    SELECT estado INTO v_estado
    FROM solicitud_servicio
    WHERE id_solicitud = p_id_solicitud;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'La solicitud % no existe', p_id_solicitud;
    END IF;

    IF v_estado NOT IN ('PENDIENTE', 'PROGRAMADA') THEN
        RAISE EXCEPTION 'Solo se pueden cancelar solicitudes en estado PENDIENTE o PROGRAMADA (actual: %)', v_estado;
    END IF;

    UPDATE solicitud_servicio
    SET estado = 'CANCELADA',
        motivo_cancelacion = p_motivo,
        fecha_cierre = now()
    WHERE id_solicitud = p_id_solicitud;
END;
$$;
SQL
        );
    }

    public function down(): void
    {
        DB::statement('DROP FUNCTION IF EXISTS cancelar_solicitud_servicio(INTEGER, INTEGER, TEXT)');

        DB::statement(<<<'SQL'
DO $$
DECLARE
    v_constraint_name text;
BEGIN
    SELECT conname INTO v_constraint_name
    FROM pg_constraint
    WHERE conrelid = 'solicitud_servicio'::regclass
      AND contype = 'c'
      AND pg_get_constraintdef(oid) LIKE '%estado%';

    IF v_constraint_name IS NOT NULL THEN
        EXECUTE format('ALTER TABLE solicitud_servicio DROP CONSTRAINT %I', v_constraint_name);
    END IF;

    ALTER TABLE solicitud_servicio
    ADD CONSTRAINT ck_solicitud_servicio_estado
    CHECK (estado IN ('PENDIENTE', 'PROGRAMADA', 'CERRADA'));
END $$;
SQL
        );

        if (Schema::hasColumn('solicitud_servicio', 'motivo_cancelacion')) {
            DB::statement('ALTER TABLE solicitud_servicio DROP COLUMN motivo_cancelacion');
        }
    }
};
