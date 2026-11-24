<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A-04 — Sucursal de la solicitud de servicio (ADR-006).
 *
 * Añade solicitud_servicio.id_sucursal y hace backfill desde la garantía,
 * el usuario que registró o el técnico asignado. Las filas que no pueden
 * resolverse quedan NULL (solo GERENTE las ve).
 *
 * Definición canónica: database/sql/patches/A-04-solicitud-servicio-id-sucursal.sql
 */
return new class extends Migration
{
    protected $connection = 'pgsql_migrator';

    public function up(): void
    {
        DB::statement($this->columna());
        DB::statement($this->backfillGarantia());
        DB::statement($this->backfillRegistra());
        DB::statement($this->backfillTecnico());
        DB::statement('CREATE INDEX IF NOT EXISTS idx_solicitud_sucursal ON public.solicitud_servicio (id_sucursal)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS public.idx_solicitud_sucursal');
        DB::statement('ALTER TABLE public.solicitud_servicio DROP COLUMN IF EXISTS id_sucursal');
    }

    private function columna(): string
    {
        return <<<'SQL'
            ALTER TABLE public.solicitud_servicio
                ADD COLUMN IF NOT EXISTS id_sucursal INTEGER
                REFERENCES public.sucursal (id_sucursal) ON DELETE RESTRICT
            SQL;
    }

    private function backfillGarantia(): string
    {
        return <<<'SQL'
            UPDATE public.solicitud_servicio s
               SET id_sucursal = v.id_sucursal
              FROM public.garantia g
              JOIN public.detalle_venta dv ON dv.id_detalle_venta = g.id_detalle_venta
              JOIN public.venta v          ON v.id_venta = dv.id_venta
             WHERE s.id_garantia = g.id_garantia
               AND s.id_sucursal IS NULL
            SQL;
    }

    private function backfillRegistra(): string
    {
        return <<<'SQL'
            UPDATE public.solicitud_servicio s
               SET id_sucursal = u.id_sucursal
              FROM public.usuario u
             WHERE s.id_usuario_registra = u.id_usuario
               AND u.id_sucursal IS NOT NULL
               AND s.id_sucursal IS NULL
            SQL;
    }

    private function backfillTecnico(): string
    {
        return <<<'SQL'
            UPDATE public.solicitud_servicio s
               SET id_sucursal = u.id_sucursal
              FROM public.usuario u
             WHERE s.id_tecnico = u.id_usuario
               AND u.id_sucursal IS NOT NULL
               AND s.id_sucursal IS NULL
            SQL;
    }
};
