-- A-05 — Estado CANCELADA de solicitudes de servicio
--
-- Contexto: la migración 2026_11_16_000005 agregó el estado CANCELADA, el campo
-- motivo_cancelacion y la función cancelar_solicitud_servicio(), pero nada de eso
-- estaba en v5-MVP.sql ni en los parches. Una instalación que cargue solo el SQL
-- canónico se quedaría sin la función que ya invoca SolicitudService::cancelar()
-- y permitiría CANCELADA -> otros estados, porque fn_solicitud_transicion()
-- solo bloqueaba la reapertura de CERRADA.
--
-- Aplicar con el rol migrador:
--   psql ... -f database/sql/patches/A-05-solicitud-servicio-cancelada.sql

ALTER TABLE public.solicitud_servicio
    ADD COLUMN IF NOT EXISTS motivo_cancelacion TEXT NULL;

-- Reemplaza únicamente el CHECK de enumeración de estados. No toca el CHECK
-- que exige fecha_cierre/resultado cuando estado = 'CERRADA'.
DO $$
DECLARE
    v_constraint_name text;
BEGIN
    -- Puede haber más de un CHECK de enumeración: v5-MVP.sql crea uno y la
    -- migración 2026_11_16_000005 añadió otro sin quitar el primero.
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
        ADD CONSTRAINT ck_solicitud_servicio_estado
        CHECK (estado IN ('PENDIENTE', 'PROGRAMADA', 'CERRADA', 'CANCELADA'));

    -- La migración anterior también pudo eliminar el CHECK de cierre; se restaura.
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
        WHERE conrelid = 'public.solicitud_servicio'::regclass
          AND contype = 'c'
          AND pg_get_constraintdef(oid) LIKE '%<> ''CERRADA''%'
    ) THEN
        ALTER TABLE public.solicitud_servicio
            ADD CONSTRAINT solicitud_servicio_check
            CHECK (estado <> 'CERRADA'
                   OR (fecha_cierre IS NOT NULL AND resultado IS NOT NULL));
    END IF;
END $$;

CREATE OR REPLACE FUNCTION public.cancelar_solicitud_servicio(
    p_id_solicitud       INTEGER,
    p_id_usuario_cancela INTEGER,
    p_motivo             TEXT
) RETURNS VOID
LANGUAGE plpgsql
AS $$
DECLARE
    v_estado TEXT;
BEGIN
    SELECT estado INTO v_estado
    FROM public.solicitud_servicio
    WHERE id_solicitud = p_id_solicitud;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'La solicitud % no existe', p_id_solicitud;
    END IF;

    IF v_estado NOT IN ('PENDIENTE', 'PROGRAMADA') THEN
        RAISE EXCEPTION 'Solo se pueden cancelar solicitudes en estado PENDIENTE o PROGRAMADA (actual: %)', v_estado;
    END IF;

    UPDATE public.solicitud_servicio
       SET estado = 'CANCELADA',
           motivo_cancelacion = p_motivo,
           fecha_cierre = now()
     WHERE id_solicitud = p_id_solicitud;
END;
$$;

GRANT EXECUTE ON FUNCTION public.cancelar_solicitud_servicio(INTEGER, INTEGER, TEXT) TO ewiflor_app;

-- Una solicitud cancelada es terminal, igual que una cerrada.
CREATE OR REPLACE FUNCTION public.fn_solicitud_transicion() RETURNS TRIGGER AS $$
BEGIN
    IF OLD.estado = 'CERRADA' AND NEW.estado <> 'CERRADA' THEN
        RAISE EXCEPTION 'No se puede reabrir una solicitud cerrada';
    END IF;

    IF OLD.estado = 'CANCELADA' AND NEW.estado <> 'CANCELADA' THEN
        RAISE EXCEPTION 'No se puede reactivar una solicitud cancelada';
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
