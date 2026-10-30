-- =====================================================================
-- Ewiflor Importaciones — Parche de configuración v5-MVP
-- Ejecutar como superusuario sobre ewiflor_db
-- Aplica: D1 (rol migrador), D3 (migrations), D6 (auditoría lista),
--         D8 (script como fuente de verdad)
-- =====================================================================

BEGIN;

-- 1) Rol migrador (D1a) ----------------------------------------------
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'ewiflor_migrator') THEN
        CREATE ROLE ewiflor_migrator
            LOGIN
            NOSUPERUSER NOCREATEDB NOCREATEROLE NOREPLICATION;
    END IF;
END $$;

GRANT ewiflor_owner TO ewiflor_migrator;

-- El migrador debe poder SET ROLE a ewiflor_owner
ALTER ROLE ewiflor_migrator SET search_path = public;

-- 2) Permisos faltantes sobre enums ----------------------------------
GRANT USAGE ON TYPE
    tipo_movimiento,
    sentido_ajuste,
    accion_permiso,
    tipo_dato_param
TO ewiflor_app;

-- 3) Permisos EXECUTE explícitos sobre funciones ---------------------
GRANT EXECUTE ON FUNCTION public.fn_autenticar_usuario(VARCHAR, TEXT) TO ewiflor_app;
GRANT EXECUTE ON FUNCTION public.registrar_venta(INTEGER, INTEGER, INTEGER, INTEGER, JSONB, INTEGER) TO ewiflor_app;
GRANT EXECUTE ON FUNCTION public.registrar_movimiento_inventario(tipo_movimiento, VARCHAR, INTEGER, INTEGER, INTEGER, INTEGER, JSONB) TO ewiflor_app;
GRANT EXECUTE ON FUNCTION public.registrar_reserva(VARCHAR, INTEGER, INTEGER, INTEGER, INTEGER, NUMERIC, INTEGER, NUMERIC, VARCHAR, INTEGER, INTEGER) TO ewiflor_app;
GRANT EXECUTE ON FUNCTION public.cancelar_venta(INTEGER, INTEGER, VARCHAR) TO ewiflor_app;
GRANT EXECUTE ON FUNCTION public.convertir_cotizacion_a_venta(INTEGER, INTEGER, INTEGER) TO ewiflor_app;

-- 4) Tabla migrations (necesaria para Laravel) ----------------------
CREATE TABLE IF NOT EXISTS public.migrations (
    id          INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    migration   VARCHAR(255) NOT NULL,
    batch       INTEGER NOT NULL
);

ALTER TABLE public.migrations OWNER TO ewiflor_owner;
GRANT SELECT, INSERT, UPDATE, DELETE ON public.migrations TO ewiflor_app;
GRANT SELECT ON public.migrations TO ewiflor_backup;

-- 5) Red de seguridad de default privileges -------------------------
-- Si el migrador crea tablas SIN SET ROLE, queremos que igual
-- se apliquen los grants por defecto.
ALTER DEFAULT PRIVILEGES FOR ROLE ewiflor_migrator IN SCHEMA public
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO ewiflor_app;
ALTER DEFAULT PRIVILEGES FOR ROLE ewiflor_migrator IN SCHEMA public
    GRANT USAGE, SELECT ON SEQUENCES TO ewiflor_app;
ALTER DEFAULT PRIVILEGES FOR ROLE ewiflor_migrator IN SCHEMA public
    GRANT SELECT ON TABLES TO ewiflor_backup;

COMMIT;

-- =====================================================================
-- Asignar contraseña al migrador (hacer fuera del script, en sesión
-- interactiva, para no dejar la contraseña en un archivo versionado):
--
--   ALTER ROLE ewiflor_migrator PASSWORD '<contraseña-segura>';
--
-- Guardar la contraseña en el gestor de secretos del equipo, NUNCA
-- en el repositorio.
-- =====================================================================