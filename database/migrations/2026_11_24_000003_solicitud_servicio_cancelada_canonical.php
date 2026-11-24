<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Traslada a la fuente de verdad SQL (parche A-05) el estado CANCELADA,
 * la función cancelar_solicitud_servicio() y la transición terminal.
 *
 * El contenido se lee del parche para que no existan dos definiciones divergentes.
 */
return new class extends Migration
{
    protected $connection = 'pgsql_migrator';

    public function up(): void
    {
        $parche = database_path('sql/patches/A-05-solicitud-servicio-cancelada.sql');

        DB::unprepared(file_get_contents($parche));
    }

    public function down(): void
    {
        DB::statement('DROP FUNCTION IF EXISTS public.cancelar_solicitud_servicio(INTEGER, INTEGER, TEXT)');

        DB::statement(<<<'SQL'
            DO $$
            DECLARE
                v_constraint_name text;
            BEGIN
                FOR v_constraint_name IN
                    SELECT conname
                    FROM pg_constraint
                    WHERE conrelid = 'public.solicitud_servicio'::regclass
                      AND contype = 'c'
                      AND pg_get_constraintdef(oid) LIKE '%(estado)%ARRAY%'
                LOOP
                    EXECUTE format('ALTER TABLE public.solicitud_servicio DROP CONSTRAINT %I', v_constraint_name);
                END LOOP;

                ALTER TABLE public.solicitud_servicio
                    ADD CONSTRAINT solicitud_servicio_estado_check
                    CHECK (estado IN ('PENDIENTE', 'PROGRAMADA', 'CERRADA'));
            END $$;
        SQL
        );

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION public.fn_solicitud_transicion() RETURNS TRIGGER AS $$
            BEGIN
                IF OLD.estado = 'CERRADA' AND NEW.estado <> 'CERRADA' THEN
                    RAISE EXCEPTION 'No se puede reabrir una solicitud cerrada';
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        SQL
        );

        if (Schema::hasColumn('solicitud_servicio', 'motivo_cancelacion')) {
            Schema::table('solicitud_servicio', function (Blueprint $table) {
                $table->dropColumn('motivo_cancelacion');
            });
        }
    }
};
