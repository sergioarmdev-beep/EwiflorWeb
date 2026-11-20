<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Recuperación de contraseña asistida + bloqueo por intentos fallidos de login.
 *
 * - usuario.celular             : número con el que se verifica «Olvidé mi contraseña».
 * - usuario.bloqueado_hasta     : bloqueo temporal (10 min) tras 3 fallos en menos de 10 min.
 * - intento_acceso              : bitácora de intentos de acceso; cuenta la ventana de fallos.
 * - solicitud_restablecimiento  : pedido de clave olvidada que un gerente/responsable atiende.
 * - notificacion                : centro de avisos in-app (solicitud de clave y bloqueos).
 * - fn_registrar_intento_acceso : INSERT del intento + conteo + bloqueo (atómico en PG).
 * - fn_desbloquear_usuario      : limpia el bloqueo y la ventana de fallos.
 *
 * Idempotente: se puede ejecutar más de una vez. DDL con rol migrador.
 */
return new class extends Migration
{
    protected $connection = 'pgsql_migrator';

    public function up(): void
    {
        // ── 1) Columnas nuevas en usuario ─────────────────────────────
        if (! Schema::hasColumn('usuario', 'celular')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->string('celular', 20)->nullable();
            });
        }

        if (! Schema::hasColumn('usuario', 'bloqueado_hasta')) {
            Schema::table('usuario', function (Blueprint $table) {
                // TIMESTAMPTZ: se compara con now() en PG y con Carbon en PHP,
                // sin depender del timezone de la BD ni de la aplicación.
                $table->timestampTz('bloqueado_hasta')->nullable();
            });
        }

        // ── 2) Bitácora de intentos ───────────────────────────────────
        if (! Schema::hasTable('intento_acceso')) {
            Schema::create('intento_acceso', function (Blueprint $table) {
                $table->bigIncrements('id_intento');
                $table->integer('id_usuario');
                $table->string('identificador', 100);
                $table->string('ip', 45)->nullable();
                $table->boolean('exitoso')->default(false);
                $table->timestampTz('fecha')->default(DB::raw('CURRENT_TIMESTAMP'));

                $table->index(['id_usuario', 'fecha'], 'ix_intento_acceso_usuario_fecha');
                $table->foreign('id_usuario', 'fk_intento_acceso_usuario')
                    ->references('id_usuario')->on('usuario')->cascadeOnDelete();
            });
        }

        // ── 3) Solicitudes de restablecimiento ────────────────────────
        if (! Schema::hasTable('solicitud_restablecimiento')) {
            Schema::create('solicitud_restablecimiento', function (Blueprint $table) {
                $table->bigIncrements('id_solicitud_restablecimiento');
                $table->integer('id_usuario');
                $table->string('correo_solicitado', 100);
                $table->string('celular_solicitado', 20);
                $table->string('estado', 12)->default('PENDIENTE'); // PENDIENTE | ATENDIDA | RECHAZADA
                $table->string('ip', 45)->nullable();
                $table->timestampTz('fecha_creacion')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->integer('id_usuario_atendio')->nullable();
                $table->timestampTz('fecha_atencion')->nullable();
                $table->string('nota', 255)->nullable();

                $table->index('estado', 'ix_solicitud_restablecimiento_estado');
                $table->index('id_usuario', 'ix_solicitud_restablecimiento_usuario');
                $table->foreign('id_usuario', 'fk_sol_rest_usuario')
                    ->references('id_usuario')->on('usuario')->cascadeOnDelete();
                $table->foreign('id_usuario_atendio', 'fk_sol_rest_atendio')
                    ->references('id_usuario')->on('usuario')->nullOnDelete();
            });

            // Una sola solicitud pendiente por cuenta: evita duplicar avisos.
            DB::statement(<<<'SQL'
                CREATE UNIQUE INDEX IF NOT EXISTS ux_solicitud_restablecimiento_pendiente
                    ON solicitud_restablecimiento (id_usuario)
                    WHERE estado = 'PENDIENTE'
            SQL);
        }

        // ── 4) Centro de notificaciones ───────────────────────────────
        if (! Schema::hasTable('notificacion')) {
            Schema::create('notificacion', function (Blueprint $table) {
                $table->bigIncrements('id_notificacion');
                $table->integer('id_usuario'); // destinatario
                $table->string('tipo', 30);    // SOLICITUD_CONTRASENA | BLOQUEO_CUENTA
                $table->string('titulo', 120);
                $table->string('mensaje', 500)->nullable();
                $table->string('url', 255)->nullable();
                $table->integer('id_usuario_origen')->nullable();
                $table->integer('id_solicitud_restablecimiento')->nullable();
                $table->boolean('leida')->default(false);
                $table->timestampTz('fecha_creacion')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->timestampTz('fecha_lectura')->nullable();

                $table->index(['id_usuario', 'leida', 'fecha_creacion'], 'ix_notificacion_destino');
                $table->foreign('id_usuario', 'fk_notificacion_usuario')
                    ->references('id_usuario')->on('usuario')->cascadeOnDelete();
                $table->foreign('id_usuario_origen', 'fk_notificacion_origen')
                    ->references('id_usuario')->on('usuario')->nullOnDelete();
                $table->foreign('id_solicitud_restablecimiento', 'fk_notificacion_solicitud')
                    ->references('id_solicitud_restablecimiento')->on('solicitud_restablecimiento')->cascadeOnDelete();
            });
        }

        // ── 5) Funciones de negocio ───────────────────────────────────
        // unprepared() por función: los cuerpos $$ ... $$ van en su propio statement.
        DB::unprepared($this->fnRegistrarIntento());
        DB::unprepared($this->fnDesbloquearUsuario());
        DB::unprepared($this->grantEjecucion());
    }

    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS public.fn_registrar_intento_acceso(INTEGER, VARCHAR, VARCHAR, BOOLEAN)');
        DB::unprepared('DROP FUNCTION IF EXISTS public.fn_desbloquear_usuario(INTEGER)');

        Schema::dropIfExists('notificacion');
        Schema::dropIfExists('solicitud_restablecimiento');
        Schema::dropIfExists('intento_acceso');

        if (Schema::hasColumn('usuario', 'bloqueado_hasta')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->dropColumn('bloqueado_hasta');
            });
        }

        if (Schema::hasColumn('usuario', 'celular')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->dropColumn('celular');
            });
        }
    }

    /**
     * Registra el intento de acceso y, si son 3 fallos en menos de 10 minutos,
     * bloquea la cuenta durante 10 minutos (una sola vez por bloqueo: bloqueo_nuevo).
     *
     * Devuelve:
     *   intentos        -> fallos en la ventana de 10 min
     *   bloqueado_hasta -> vigencia del bloqueo (null si no está bloqueada)
     *   bloqueo_nuevo   -> TRUE solo cuando acaba de bloquearse (para avisar una vez)
     */
    private function fnRegistrarIntento(): string
    {
        return <<<'SQL'
            CREATE OR REPLACE FUNCTION public.fn_registrar_intento_acceso(
                p_id_usuario    INTEGER,
                p_identificador VARCHAR,
                p_ip            VARCHAR,
                p_exitoso       BOOLEAN
            )
            RETURNS TABLE (
                intentos        INTEGER,
                bloqueado_hasta TIMESTAMPTZ,
                bloqueo_nuevo   BOOLEAN
            )
            LANGUAGE plpgsql
            AS $$
            DECLARE
                v_fallos INTEGER;
                v_hasta  TIMESTAMPTZ;
                v_nuevo  BOOLEAN := FALSE;
            BEGIN
                INSERT INTO intento_acceso (id_usuario, identificador, ip, exitoso)
                VALUES (p_id_usuario, p_identificador, p_ip, p_exitoso);

                IF p_exitoso THEN
                    -- Una entrada correcta reinicia la ventana de fallos.
                    UPDATE usuario
                       SET bloqueado_hasta = NULL
                     WHERE id_usuario = p_id_usuario;

                    DELETE FROM intento_acceso
                     WHERE id_usuario = p_id_usuario
                       AND NOT exitoso
                       AND fecha > now() - INTERVAL '10 minutes';

                    RETURN QUERY SELECT 0, NULL::TIMESTAMPTZ, FALSE;
                    RETURN;
                END IF;

                SELECT COUNT(*)::INTEGER
                  INTO v_fallos
                  FROM intento_acceso e
                 WHERE e.id_usuario = p_id_usuario
                   AND NOT e.exitoso
                   AND e.fecha > now() - INTERVAL '10 minutes';

                -- Fila bloqueada mientras se evalúa (concurrencia segura).
                SELECT u.bloqueado_hasta
                  INTO v_hasta
                  FROM usuario u
                 WHERE u.id_usuario = p_id_usuario
                   FOR UPDATE;

                IF v_fallos >= 3 AND (v_hasta IS NULL OR v_hasta <= now()) THEN
                    v_hasta := now() + INTERVAL '10 minutes';

                    UPDATE usuario
                       SET bloqueado_hasta = v_hasta
                     WHERE id_usuario = p_id_usuario;

                    v_nuevo := TRUE;
                END IF;

                RETURN QUERY SELECT v_fallos, v_hasta, v_nuevo;
            END;
            $$;
        SQL;
    }

    /** Quitar el bloqueo y la ventana de fallos recientes (desbloqueo manual). */
    private function fnDesbloquearUsuario(): string
    {
        return <<<'SQL'
            CREATE OR REPLACE FUNCTION public.fn_desbloquear_usuario(
                p_id_usuario INTEGER
            )
            RETURNS VOID
            LANGUAGE plpgsql
            AS $$
            BEGIN
                UPDATE usuario
                   SET bloqueado_hasta = NULL
                 WHERE id_usuario = p_id_usuario;

                DELETE FROM intento_acceso
                 WHERE id_usuario = p_id_usuario
                   AND NOT exitoso
                   AND fecha > now() - INTERVAL '10 minutes';
            END;
            $$;
        SQL;
    }

    private function grantEjecucion(): string
    {
        return <<<'SQL'
            DO $$
            BEGIN
                IF EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'ewiflor_app') THEN
                    EXECUTE 'GRANT EXECUTE ON FUNCTION public.fn_registrar_intento_acceso(INTEGER, VARCHAR, VARCHAR, BOOLEAN) TO ewiflor_app';
                    EXECUTE 'GRANT EXECUTE ON FUNCTION public.fn_desbloquear_usuario(INTEGER) TO ewiflor_app';
                END IF;
            END;
            $$;
        SQL;
    }
};
