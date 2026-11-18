<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Alcance de sucursal en Ventas (Administrador/Vendedor) reforzado en la base.
 *
 * Regla de negocio:
 *  - GERENTE        → puede vender en cualquier sucursal.
 *  - ADMINISTRADOR  → solo en la sucursal a la que está asignado.
 *  - VENDEDOR       → solo en la sucursal a la que está asignado.
 *  - Sin sucursal   → no puede vender.
 *
 * Además, los almacenes de salida de una venta deben pertenecer a la sucursal
 * en la que se registra la venta (antes se podía descontar stock de otra
 * sucursal: la venta quedaba en la sucursal del usuario y el stock salía de
 * otra).
 *
 * Es la segunda capa: la primera está en VentaService/StoreVentaRequest.
 * Idempotente: solo crea o reemplaza funciones.
 */
return new class extends Migration
{
    protected $connection = 'pgsql_migrator';

    public function up(): void
    {
        DB::statement($this->registrarVentaConAlcance());
        DB::statement($this->convertirCotizacionConAlcance());
    }

    public function down(): void
    {
        // Versión previa: la definida por la migración de Promociones
        // (guarda de descuento del VENDEDOR, sin guarda de sucursal).
        DB::statement($this->registrarVentaAnterior());
        DB::statement($this->convertirCotizacionAnterior());
    }

    private function registrarVentaConAlcance(): string
    {
        return <<<'SQL'
            CREATE OR REPLACE FUNCTION public.registrar_venta(
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
                v_id_venta         INTEGER;
                v_lin              JSONB;
                v_id_var           INTEGER;
                v_cant             INTEGER;
                v_precio           NUMERIC;
                v_desc             NUMERIC;
                v_id_alm           INTEGER;
                v_modalidad        VARCHAR;
                v_estado           VARCHAR;
                v_vence            DATE;
                v_garantia         INTEGER;
                v_rol              VARCHAR;
                v_max_desc         NUMERIC;
                v_sucursal_usuario INTEGER;
            BEGIN
                IF jsonb_typeof(p_lineas) <> 'array' OR jsonb_array_length(p_lineas) = 0 THEN
                    RAISE EXCEPTION 'La venta debe tener al menos una línea';
                END IF;

                SELECT nombre_rol, id_sucursal
                  INTO v_rol, v_sucursal_usuario
                  FROM usuario
                 WHERE id_usuario = p_id_usuario;
                IF NOT FOUND THEN
                    RAISE EXCEPTION 'El usuario % no existe', p_id_usuario;
                END IF;

                -- ALCANCE DE SUCURSAL: solo GERENTE vende fuera de su sucursal.
                IF v_rol IN ('ADMINISTRADOR', 'VENDEDOR') THEN
                    IF v_sucursal_usuario IS NULL THEN
                        RAISE EXCEPTION 'El usuario % no tiene sucursal asignada', p_id_usuario;
                    END IF;
                    IF v_sucursal_usuario <> p_id_sucursal THEN
                        RAISE EXCEPTION 'Solo puedes registrar ventas de tu sucursal asignada (sucursal %)',
                            v_sucursal_usuario;
                    END IF;
                END IF;

                -- Los almacenes de salida deben ser de la sucursal de la venta.
                IF EXISTS (
                    SELECT 1
                      FROM jsonb_array_elements(p_lineas) AS l(v)
                      JOIN almacen a
                        ON a.id_almacen = NULLIF(l.v->>'id_almacen', '')::INTEGER
                     WHERE NULLIF(l.v->>'id_almacen', '') IS NOT NULL
                       AND a.id_sucursal <> p_id_sucursal
                ) THEN
                    RAISE EXCEPTION 'Los almacenes de la venta deben pertenecer a la sucursal %', p_id_sucursal;
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

    private function convertirCotizacionConAlcance(): string
    {
        return <<<'SQL'
            CREATE OR REPLACE FUNCTION public.convertir_cotizacion_a_venta(
                p_id_cotizacion INTEGER,
                p_id_usuario    INTEGER,
                p_id_almacen    INTEGER
            ) RETURNS INTEGER
            LANGUAGE plpgsql
            AS $$
            DECLARE
                v_lineas         JSONB;
                v_id_cliente     INTEGER;
                v_id_sucursal    INTEGER;
                v_id_tipo_cambio INTEGER;
            BEGIN
                SELECT c.id_cliente, c.id_sucursal, c.id_tipo_cambio
                  INTO v_id_cliente, v_id_sucursal, v_id_tipo_cambio
                  FROM cotizacion c
                 WHERE c.id_cotizacion = p_id_cotizacion;

                IF NOT FOUND THEN
                    RAISE EXCEPTION 'Cotización % inexistente', p_id_cotizacion;
                END IF;

                -- La venta nace en la sucursal de la cotización: el almacén debe ser de ahí.
                IF NOT EXISTS (
                    SELECT 1
                      FROM almacen a
                     WHERE a.id_almacen = p_id_almacen
                       AND a.id_sucursal = v_id_sucursal
                ) THEN
                    RAISE EXCEPTION 'El almacén % no pertenece a la sucursal de la cotización (%)',
                        p_id_almacen, v_id_sucursal;
                END IF;

                SELECT jsonb_agg(jsonb_build_object(
                            'id_variante',         dc.id_variante,
                            'cantidad',            dc.cantidad,
                            'precio_unitario_usd', dc.precio_unitario_usd,
                            'descuento_pct',       dc.descuento_pct,
                            'id_almacen',          p_id_almacen))
                  INTO v_lineas
                  FROM detalle_cotizacion dc
                 WHERE dc.id_cotizacion = p_id_cotizacion;

                RETURN registrar_venta(
                    v_id_cliente, p_id_usuario, v_id_sucursal,
                    v_id_tipo_cambio, v_lineas, p_id_cotizacion);
            END;
            $$;
            SQL;
    }

    /** Versión previa (migración de Promociones), sin guarda de sucursal. */
    private function registrarVentaAnterior(): string
    {
        return <<<'SQL'
            CREATE OR REPLACE FUNCTION public.registrar_venta(
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

    /** Versión previa (script base v5-MVP). */
    private function convertirCotizacionAnterior(): string
    {
        return <<<'SQL'
            CREATE OR REPLACE FUNCTION public.convertir_cotizacion_a_venta(
                p_id_cotizacion INTEGER,
                p_id_usuario    INTEGER,
                p_id_almacen    INTEGER
            ) RETURNS INTEGER
            LANGUAGE plpgsql
            AS $$
            DECLARE
                v_lineas         JSONB;
                v_id_cliente     INTEGER;
                v_id_sucursal    INTEGER;
                v_id_tipo_cambio INTEGER;
            BEGIN
                SELECT c.id_cliente, c.id_sucursal, c.id_tipo_cambio
                  INTO v_id_cliente, v_id_sucursal, v_id_tipo_cambio
                  FROM cotizacion c
                 WHERE c.id_cotizacion = p_id_cotizacion;

                IF NOT FOUND THEN
                    RAISE EXCEPTION 'Cotización % inexistente', p_id_cotizacion;
                END IF;

                SELECT jsonb_agg(jsonb_build_object(
                            'id_variante',         dc.id_variante,
                            'cantidad',            dc.cantidad,
                            'precio_unitario_usd', dc.precio_unitario_usd,
                            'descuento_pct',       dc.descuento_pct,
                            'id_almacen',          p_id_almacen))
                  INTO v_lineas
                  FROM detalle_cotizacion dc
                 WHERE dc.id_cotizacion = p_id_cotizacion;

                RETURN registrar_venta(
                    v_id_cliente, p_id_usuario, v_id_sucursal,
                    v_id_tipo_cambio, v_lineas, p_id_cotizacion);
            END;
            $$;
            SQL;
    }
};
