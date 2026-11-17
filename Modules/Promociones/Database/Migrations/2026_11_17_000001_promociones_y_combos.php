<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Promociones (descuentos por variante) y combos (tipo de producto COMBO).
 *
 * - `promocion` + `promocion_variante`: descuento PCT o USD asociado a
 *   variantes concretas, con vigencia por fechas y estado ACTIVA/INACTIVA.
 * - `combo_componente`: productos incluidos en un combo. El combo es un
 *   `producto` con `tipo_producto = 'COMBO'` (se amplía el CHECK) que se
 *   vende como producto normal con precio y stock propios.
 * - Trigger de negocio: una variante no puede tener dos promociones ACTIVAS
 *   con rangos de fechas solapados.
 * - Redefine `registrar_venta()` para que un VENDEDOR no pueda aplicar un
 *   descuento mayor al de la promoción vigente de la variante (solo en
 *   ventas directas; las conversiones de cotización conservan el descuento
 *   ya autorizado al emitir el documento).
 * - Idempotente: tablas con IF NOT EXISTS, funciones y triggers se crean
 *   o reemplazan siempre.
 */
return new class extends Migration
{
    protected $connection = 'pgsql_migrator';

    public function up(): void
    {
        // 1) Tablas ----------------------------------------------------------
        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS promocion (
                id_promocion    INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                nombre          VARCHAR(120) NOT NULL,
                descripcion     VARCHAR(250),
                tipo_descuento  VARCHAR(3) NOT NULL CHECK (tipo_descuento IN ('PCT','USD')),
                valor_descuento NUMERIC(12,2) NOT NULL CHECK (valor_descuento > 0),
                fecha_inicio    DATE NOT NULL DEFAULT current_date,
                fecha_fin       DATE,
                estado          VARCHAR(10) NOT NULL DEFAULT 'ACTIVA'
                                CHECK (estado IN ('ACTIVA','INACTIVA')),
                id_usuario_crea INTEGER NOT NULL REFERENCES usuario (id_usuario) ON DELETE RESTRICT,
                fecha_creacion  TIMESTAMPTZ NOT NULL DEFAULT now(),
                CONSTRAINT ck_promocion_fechas
                    CHECK (fecha_fin IS NULL OR fecha_fin >= fecha_inicio),
                CONSTRAINT ck_promocion_pct
                    CHECK (tipo_descuento <> 'PCT' OR valor_descuento <= 100)
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS promocion_variante (
                id_promocion INTEGER NOT NULL REFERENCES promocion (id_promocion) ON DELETE CASCADE,
                id_variante  INTEGER NOT NULL REFERENCES variante_producto (id_variante) ON DELETE CASCADE,
                PRIMARY KEY (id_promocion, id_variante)
            )
        SQL);

        DB::statement('CREATE INDEX IF NOT EXISTS idx_promocion_variante_variante
            ON promocion_variante (id_variante)');

        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS combo_componente (
                id_producto   INTEGER NOT NULL REFERENCES producto (id_producto) ON DELETE CASCADE,
                id_componente INTEGER NOT NULL REFERENCES producto (id_producto) ON DELETE RESTRICT,
                cantidad      INTEGER NOT NULL DEFAULT 1 CHECK (cantidad > 0),
                PRIMARY KEY (id_producto, id_componente),
                CONSTRAINT ck_combo_no_auto CHECK (id_producto <> id_componente)
            )
        SQL);

        DB::statement('CREATE INDEX IF NOT EXISTS idx_combo_componente_componente
            ON combo_componente (id_componente)');

        // 2) producto.tipo_producto admite COMBO ------------------------------
        // El CHECK del script base es anónimo, por lo que su nombre generado
        // es producto_tipo_producto_check; se localiza por definición para
        // sobrevivir a cualquier renombrado previo.
        DB::statement(<<<'SQL'
            DO $$
            DECLARE
                v_constraint TEXT;
            BEGIN
                SELECT conname INTO v_constraint
                  FROM pg_constraint
                 WHERE conrelid = 'producto'::regclass
                   AND contype = 'c'
                   AND pg_get_constraintdef(oid) LIKE '%tipo_producto%';
                IF v_constraint IS NOT NULL THEN
                    EXECUTE format('ALTER TABLE producto DROP CONSTRAINT %I', v_constraint);
                END IF;

                ALTER TABLE producto
                    ADD CONSTRAINT producto_tipo_producto_check
                    CHECK (tipo_producto IN ('MAQUINARIA','REPUESTO','ACCESORIO','COMBO'));
            END
            $$;
        SQL);

        // 3) Reglas de negocio en PostgreSQL ----------------------------------
        // Cada función se envía por separado: unprepared() ejecuta un solo
        // bloque por llamada.
        DB::unprepared($this->fnValidarSolapePromocion());
        DB::unprepared($this->fnCheckVariante());
        DB::unprepared($this->fnCheckPromocion());
        DB::unprepared($this->fnModuloAuditoria());
        DB::unprepared($this->registrarVentaSql());

        DB::statement(<<<'SQL'
            DROP TRIGGER IF EXISTS trg_promocion_variante_solape ON promocion_variante
        SQL);
        DB::statement(<<<'SQL'
            CREATE TRIGGER trg_promocion_variante_solape
            AFTER INSERT OR UPDATE OF id_variante ON promocion_variante
            FOR EACH ROW EXECUTE FUNCTION fn_check_promo_variante()
        SQL);

        DB::statement('DROP TRIGGER IF EXISTS trg_promocion_solape ON promocion');
        DB::statement(<<<'SQL'
            CREATE TRIGGER trg_promocion_solape
            AFTER INSERT OR UPDATE OF estado, fecha_inicio, fecha_fin ON promocion
            FOR EACH ROW EXECUTE FUNCTION fn_check_promo_actualizada()
        SQL);

        // 4) Auditoría por triggers -------------------------------------------
        foreach (['promocion', 'promocion_variante', 'combo_componente'] as $tabla) {
            DB::statement("DROP TRIGGER IF EXISTS trg_aud_{$tabla} ON {$tabla}");
        }
        DB::statement(<<<'SQL'
            CREATE TRIGGER trg_aud_promocion
            AFTER INSERT OR UPDATE OR DELETE ON promocion
            FOR EACH ROW EXECUTE FUNCTION fn_auditar_cambio('id_promocion')
        SQL);
        DB::statement(<<<'SQL'
            CREATE TRIGGER trg_aud_promocion_variante
            AFTER INSERT OR UPDATE OR DELETE ON promocion_variante
            FOR EACH ROW EXECUTE FUNCTION fn_auditar_cambio('id_promocion', 'id_variante')
        SQL);
        DB::statement(<<<'SQL'
            CREATE TRIGGER trg_aud_combo_componente
            AFTER INSERT OR UPDATE OR DELETE ON combo_componente
            FOR EACH ROW EXECUTE FUNCTION fn_auditar_cambio('id_producto', 'id_componente')
        SQL);
    }

    public function down(): void
    {
        foreach (['promocion', 'promocion_variante', 'combo_componente'] as $tabla) {
            DB::statement("DROP TRIGGER IF EXISTS trg_aud_{$tabla} ON {$tabla}");
        }
        DB::statement('DROP TRIGGER IF EXISTS trg_promocion_solape ON promocion');
        DB::statement('DROP TRIGGER IF EXISTS trg_promocion_variante_solape ON promocion_variante');
        DB::unprepared($this->fnValidarSolapePromocion());
        DB::unprepared($this->fnCheckVariante());
        DB::unprepared($this->fnCheckPromocion());
        DB::unprepared($this->registrarVentaSqlOriginal());

        if (Schema::hasTable('promocion_variante')) {
            Schema::drop('promocion_variante');
        }
        if (Schema::hasTable('promocion')) {
            Schema::drop('promocion');
        }
        if (Schema::hasTable('combo_componente')) {
            Schema::drop('combo_componente');
        }

        // Restaurar el CHECK original de tipo_producto.
        DB::statement(<<<'SQL'
            DO $$
            DECLARE
                v_constraint TEXT;
            BEGIN
                SELECT conname INTO v_constraint
                  FROM pg_constraint
                 WHERE conrelid = 'producto'::regclass
                   AND contype = 'c'
                   AND pg_get_constraintdef(oid) LIKE '%tipo_producto%';
                IF v_constraint IS NOT NULL THEN
                    EXECUTE format('ALTER TABLE producto DROP CONSTRAINT %I', v_constraint);
                END IF;

                ALTER TABLE producto
                    ADD CONSTRAINT producto_tipo_producto_check
                    CHECK (tipo_producto IN ('MAQUINARIA','REPUESTO','ACCESORIO'));
            END
            $$;
        SQL);
    }

    /** Validación central: detecta dos promociones ACTIVAS solapadas para la misma variante. */
    private function fnValidarSolapePromocion(): string
    {
        return <<<'SQL'
            CREATE OR REPLACE FUNCTION fn_validar_solape_promocion(
                p_id_promocion INTEGER,
                p_id_variante  INTEGER
            ) RETURNS VOID
            LANGUAGE plpgsql
            AS $$
            DECLARE
                v_inicio  DATE;
                v_fin     DATE;
                v_solapa  INTEGER;
                v_nombre  VARCHAR;
            BEGIN
                SELECT fecha_inicio, fecha_fin INTO v_inicio, v_fin
                  FROM promocion
                 WHERE id_promocion = p_id_promocion;

                IF NOT FOUND THEN
                    RETURN;
                END IF;

                SELECT pv.id_promocion, p.nombre INTO v_solapa, v_nombre
                  FROM promocion_variante pv
                  JOIN promocion p ON p.id_promocion = pv.id_promocion
                 WHERE pv.id_variante = p_id_variante
                   AND pv.id_promocion <> p_id_promocion
                   AND p.estado = 'ACTIVA'
                   AND p.fecha_inicio <= COALESCE(v_fin, 'infinity'::date)
                   AND COALESCE(p.fecha_fin, 'infinity'::date) >= v_inicio
                 LIMIT 1;

                IF v_solapa IS NOT NULL THEN
                    RAISE EXCEPTION 'La variante % ya tiene la promoción activa «%» con fechas solapadas. Ajusta las fechas o desactiva la otra promoción.',
                        p_id_variante, v_nombre;
                END IF;
            END;
            $$;
        SQL;
    }

    /** Trigger sobre promocion_variante: valida cada variante al agregarse. */
    private function fnCheckVariante(): string
    {
        return <<<'SQL'
            CREATE OR REPLACE FUNCTION fn_check_promo_variante() RETURNS TRIGGER
            LANGUAGE plpgsql
            AS $$
            BEGIN
                PERFORM fn_validar_solape_promocion(NEW.id_promocion, NEW.id_variante);
                RETURN NEW;
            END;
            $$;
        SQL;
    }

    /** Trigger sobre promocion: revalida todas sus variantes al activar o cambiar fechas. */
    private function fnCheckPromocion(): string
    {
        return <<<'SQL'
            CREATE OR REPLACE FUNCTION fn_check_promo_actualizada() RETURNS TRIGGER
            LANGUAGE plpgsql
            AS $$
            BEGIN
                IF NEW.estado = 'ACTIVA' THEN
                    PERFORM fn_validar_solape_promocion(NEW.id_promocion, pv.id_variante)
                      FROM promocion_variante pv
                     WHERE pv.id_promocion = NEW.id_promocion;
                END IF;
                RETURN NEW;
            END;
            $$;
        SQL;
    }

    /** Mapeo tabla -> módulo para la auditoría. */
    private function fnModuloAuditoria(): string
    {
        return <<<'SQL'
            CREATE OR REPLACE FUNCTION fn_modulo_auditoria(p_tabla TEXT)
            RETURNS VARCHAR
            LANGUAGE sql
            IMMUTABLE
            AS $$
                SELECT CASE p_tabla
                    WHEN 'usuario'               THEN 'USUARIOS'
                    WHEN 'permiso'               THEN 'USUARIOS'
                    WHEN 'rol_permiso'           THEN 'USUARIOS'
                    WHEN 'producto'              THEN 'PRODUCTOS'
                    WHEN 'variante_producto'     THEN 'PRODUCTOS'
                    WHEN 'producto_imagen'       THEN 'PRODUCTOS'
                    WHEN 'producto_documento'    THEN 'PRODUCTOS'
                    WHEN 'producto_repuesto'     THEN 'PRODUCTOS'
                    WHEN 'combo_componente'      THEN 'PRODUCTOS'
                    WHEN 'marca'                 THEN 'PRODUCTOS'
                    WHEN 'promocion'             THEN 'PROMOCIONES'
                    WHEN 'promocion_variante'    THEN 'PROMOCIONES'
                    WHEN 'tipo_cambio'           THEN 'TIPO_CAMBIO'
                    WHEN 'existencia'            THEN 'INVENTARIO'
                    WHEN 'movimiento_inventario' THEN 'INVENTARIO'
                    WHEN 'cliente'               THEN 'CLIENTES'
                    WHEN 'cotizacion'            THEN 'COTIZACIONES'
                    WHEN 'venta'                 THEN 'VENTAS'
                    WHEN 'pago'                  THEN 'VENTAS'
                    WHEN 'garantia'              THEN 'GARANTIAS'
                    WHEN 'solicitud_servicio'    THEN 'SERVICIO_TECNICO'
                    WHEN 'reserva'               THEN 'RESERVAS'
                    ELSE 'GENERAL'
                END::VARCHAR;
            $$;
        SQL;
    }

    /**
     * `registrar_venta` con guarda de rol: un VENDEDOR en venta directa solo
     * puede aplicar el descuento de la promoción vigente de la variante (o
     * ninguno). En conversiones de cotización no se revalida porque el
     * descuento ya fue autorizado cuando se emitió el documento.
     */
    private function registrarVentaSql(): string
    {
        return <<<'SQL'
            CREATE OR REPLACE FUNCTION registrar_venta(
                p_id_cliente     INTEGER,
                p_id_usuario     INTEGER,
                p_id_sucursal    INTEGER,
                p_id_tipo_cambio INTEGER,
                p_lineas         JSONB,
                p_id_cotizacion  INTEGER DEFAULT NULL
            ) RETURNS INTEGER
            LANGUAGE plpgsql
            AS $$
            DECLARE
                v_id_venta  INTEGER;
                v_lin       JSONB;
                v_id_var    INTEGER;
                v_cant      INTEGER;
                v_precio    NUMERIC;
                v_desc      NUMERIC;
                v_id_alm    INTEGER;
                v_modalidad VARCHAR;
                v_estado    VARCHAR;
                v_vence     DATE;
                v_garantia  INTEGER;
                v_rol       VARCHAR;
                v_max_desc  NUMERIC;
            BEGIN
                IF jsonb_typeof(p_lineas) <> 'array' OR jsonb_array_length(p_lineas) = 0 THEN
                    RAISE EXCEPTION 'La venta debe tener al menos una línea';
                END IF;

                SELECT nombre_rol INTO v_rol
                  FROM usuario
                 WHERE id_usuario = p_id_usuario;
                IF NOT FOUND THEN
                    RAISE EXCEPTION 'El usuario % no existe', p_id_usuario;
                END IF;

                IF p_id_cotizacion IS NOT NULL THEN
                    SELECT estado, fecha_emision + vigencia_dias
                      INTO v_estado, v_vence
                      FROM cotizacion
                     WHERE id_cotizacion = p_id_cotizacion AND id_cliente = p_id_cliente
                     FOR UPDATE;
                    IF NOT FOUND THEN
                        RAISE EXCEPTION 'La cotización no pertenece al cliente indicado';
                    END IF;
                    IF v_estado <> 'EMITIDA' THEN
                        RAISE EXCEPTION 'La cotización % no está EMITIDA', p_id_cotizacion;
                    END IF;
                    IF current_date > v_vence THEN
                        RAISE EXCEPTION 'La cotización % está vencida', p_id_cotizacion;
                    END IF;
                END IF;

                INSERT INTO venta (id_cliente, id_usuario, id_sucursal,
                                   id_cotizacion, id_tipo_cambio, estado)
                VALUES (p_id_cliente, p_id_usuario, p_id_sucursal,
                        p_id_cotizacion, p_id_tipo_cambio, 'EN_PROCESO')
                RETURNING id_venta INTO v_id_venta;

                FOR v_lin IN SELECT * FROM jsonb_array_elements(p_lineas) LOOP
                    v_id_var    := (v_lin->>'id_variante')::INTEGER;
                    v_cant      := (v_lin->>'cantidad')::INTEGER;
                    v_precio    := (v_lin->>'precio_unitario_usd')::NUMERIC;
                    v_desc      := COALESCE((v_lin->>'descuento_pct')::NUMERIC, 0);
                    v_id_alm    := NULLIF(v_lin->>'id_almacen','')::INTEGER;
                    v_garantia  := NULLIF(v_lin->>'garantia_meses','')::INTEGER;

                    SELECT p.modalidad INTO v_modalidad
                      FROM variante_producto vp
                      JOIN producto p ON p.id_producto = vp.id_producto
                     WHERE vp.id_variante = v_id_var;
                    IF NOT FOUND THEN
                        RAISE EXCEPTION 'Variante % inexistente', v_id_var;
                    END IF;
                    IF v_id_alm IS NULL AND v_modalidad <> 'BAJO_PEDIDO' THEN
                        RAISE EXCEPTION 'La línea de la variante % debe indicar el almacén', v_id_var;
                    END IF;

                    -- Guarda de descuentos para VENDEDOR en ventas directas.
                    IF p_id_cotizacion IS NULL AND v_rol = 'VENDEDOR' THEN
                        v_max_desc := COALESCE((
                            SELECT CASE WHEN p.tipo_descuento = 'PCT'
                                        THEN p.valor_descuento
                                        ELSE LEAST(100, ROUND(
                                                 p.valor_descuento / NULLIF(v_precio, 0) * 100, 2))
                                    END
                              FROM promocion_variante pv
                              JOIN promocion p ON p.id_promocion = pv.id_promocion
                             WHERE pv.id_variante = v_id_var
                               AND p.estado = 'ACTIVA'
                               AND p.fecha_inicio <= current_date
                               AND (p.fecha_fin IS NULL OR p.fecha_fin >= current_date)
                             LIMIT 1), 0);

                        IF v_desc > v_max_desc + 0.005 THEN
                            RAISE EXCEPTION 'Un Vendedor solo puede aplicar el descuento de la promoción vigente (% %) o ninguno. Solicita la autorización de un Gerente o Administrador.',
                                v_max_desc, '%';
                        END IF;
                    END IF;

                    INSERT INTO detalle_venta
                        (id_venta, id_variante, cantidad, precio_unitario_usd, descuento_pct, garantia_meses)
                    VALUES (v_id_venta, v_id_var, v_cant, v_precio, v_desc, v_garantia);
                END LOOP;

                FOR v_id_alm IN
                    SELECT DISTINCT NULLIF(l.v->>'id_almacen','')::INTEGER
                      FROM jsonb_array_elements(p_lineas) AS l(v)
                     WHERE NULLIF(l.v->>'id_almacen','') IS NOT NULL
                LOOP
                    PERFORM registrar_movimiento_inventario(
                        'SALIDA'::tipo_movimiento,
                        'Venta ' || v_id_venta,
                        p_id_usuario, v_id_alm, NULL, v_id_venta,
                        (SELECT jsonb_agg(jsonb_build_object(
                                    'id_variante', (l.v->>'id_variante')::INTEGER,
                                    'cantidad',    (l.v->>'cantidad')::INTEGER))
                           FROM jsonb_array_elements(p_lineas) AS l(v)
                          WHERE NULLIF(l.v->>'id_almacen','')::INTEGER = v_id_alm)
                    );
                END LOOP;

                UPDATE venta SET estado = 'REGISTRADA' WHERE id_venta = v_id_venta;

                IF p_id_cotizacion IS NOT NULL THEN
                    UPDATE cotizacion SET estado = 'CONVERTIDA' WHERE id_cotizacion = p_id_cotizacion;
                END IF;

                RETURN v_id_venta;
            END;
            $$;
        SQL;
    }

    /** Versión previa (parche garantia_meses) para restaurar en down(). */
    private function registrarVentaSqlOriginal(): string
    {
        return <<<'SQL'
            CREATE OR REPLACE FUNCTION registrar_venta(
                p_id_cliente     INTEGER,
                p_id_usuario     INTEGER,
                p_id_sucursal    INTEGER,
                p_id_tipo_cambio INTEGER,
                p_lineas         JSONB,
                p_id_cotizacion  INTEGER DEFAULT NULL
            ) RETURNS INTEGER
            LANGUAGE plpgsql
            AS $$
            DECLARE
                v_id_venta  INTEGER;
                v_lin       JSONB;
                v_id_var    INTEGER;
                v_cant      INTEGER;
                v_precio    NUMERIC;
                v_desc      NUMERIC;
                v_id_alm    INTEGER;
                v_modalidad VARCHAR;
                v_estado    VARCHAR;
                v_vence     DATE;
                v_garantia  INTEGER;
            BEGIN
                IF jsonb_typeof(p_lineas) <> 'array' OR jsonb_array_length(p_lineas) = 0 THEN
                    RAISE EXCEPTION 'La venta debe tener al menos una línea';
                END IF;

                IF p_id_cotizacion IS NOT NULL THEN
                    SELECT estado, fecha_emision + vigencia_dias
                      INTO v_estado, v_vence
                      FROM cotizacion
                     WHERE id_cotizacion = p_id_cotizacion AND id_cliente = p_id_cliente
                     FOR UPDATE;
                    IF NOT FOUND THEN
                        RAISE EXCEPTION 'La cotización no pertenece al cliente indicado';
                    END IF;
                    IF v_estado <> 'EMITIDA' THEN
                        RAISE EXCEPTION 'La cotización % no está EMITIDA', p_id_cotizacion;
                    END IF;
                    IF current_date > v_vence THEN
                        RAISE EXCEPTION 'La cotización % está vencida', p_id_cotizacion;
                    END IF;
                END IF;

                INSERT INTO venta (id_cliente, id_usuario, id_sucursal,
                                   id_cotizacion, id_tipo_cambio, estado)
                VALUES (p_id_cliente, p_id_usuario, p_id_sucursal,
                        p_id_cotizacion, p_id_tipo_cambio, 'EN_PROCESO')
                RETURNING id_venta INTO v_id_venta;

                FOR v_lin IN SELECT * FROM jsonb_array_elements(p_lineas) LOOP
                    v_id_var    := (v_lin->>'id_variante')::INTEGER;
                    v_cant      := (v_lin->>'cantidad')::INTEGER;
                    v_precio    := (v_lin->>'precio_unitario_usd')::NUMERIC;
                    v_desc      := COALESCE((v_lin->>'descuento_pct')::NUMERIC, 0);
                    v_id_alm    := NULLIF(v_lin->>'id_almacen','')::INTEGER;
                    v_garantia  := NULLIF(v_lin->>'garantia_meses','')::INTEGER;

                    SELECT p.modalidad INTO v_modalidad
                      FROM variante_producto vp
                      JOIN producto p ON p.id_producto = vp.id_producto
                     WHERE vp.id_variante = v_id_var;
                    IF NOT FOUND THEN
                        RAISE EXCEPTION 'Variante % inexistente', v_id_var;
                    END IF;
                    IF v_id_alm IS NULL AND v_modalidad <> 'BAJO_PEDIDO' THEN
                        RAISE EXCEPTION 'La línea de la variante % debe indicar el almacén', v_id_var;
                    END IF;

                    INSERT INTO detalle_venta
                        (id_venta, id_variante, cantidad, precio_unitario_usd, descuento_pct, garantia_meses)
                    VALUES (v_id_venta, v_id_var, v_cant, v_precio, v_desc, v_garantia);
                END LOOP;

                FOR v_id_alm IN
                    SELECT DISTINCT NULLIF(l.v->>'id_almacen','')::INTEGER
                      FROM jsonb_array_elements(p_lineas) AS l(v)
                     WHERE NULLIF(l.v->>'id_almacen','') IS NOT NULL
                LOOP
                    PERFORM registrar_movimiento_inventario(
                        'SALIDA'::tipo_movimiento,
                        'Venta ' || v_id_venta,
                        p_id_usuario, v_id_alm, NULL, v_id_venta,
                        (SELECT jsonb_agg(jsonb_build_object(
                                    'id_variante', (l.v->>'id_variante')::INTEGER,
                                    'cantidad',    (l.v->>'cantidad')::INTEGER))
                           FROM jsonb_array_elements(p_lineas) AS l(v)
                          WHERE NULLIF(l.v->>'id_almacen','')::INTEGER = v_id_alm)
                    );
                END LOOP;

                UPDATE venta SET estado = 'REGISTRADA' WHERE id_venta = v_id_venta;

                IF p_id_cotizacion IS NOT NULL THEN
                    UPDATE cotizacion SET estado = 'CONVERTIDA' WHERE id_cotizacion = p_id_cotizacion;
                END IF;

                RETURN v_id_venta;
            END;
            $$;
        SQL;
    }
};
