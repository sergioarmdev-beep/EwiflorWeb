<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega garantia_meses a detalle_venta y detalle_cotizacion para poder
 * editar la duracion de garantia por linea al realizar venta o cotizacion.
 *
 * - La columna es nulable: NULL => usar el default de producto.garantia_meses.
 * - Reemplaza las funciones PostgreSQL registrar_venta(),
 *   convertir_cotizacion_a_venta() y el trigger fn_crear_garantias_venta()
 *   para que lean/escriban el nuevo campo.
 * - Enriquece v_garantia_vigente con datos de venta, cliente y producto.
 * - Idempotente: si la columna ya existe, no falla.
 */
return new class extends Migration
{
    protected $connection = 'pgsql_migrator';

    public function up(): void
    {
        if (! Schema::hasColumn('detalle_venta', 'garantia_meses')) {
            DB::statement('ALTER TABLE detalle_venta
                ADD COLUMN garantia_meses INTEGER
                CHECK (garantia_meses IS NULL OR garantia_meses >= 0)');
        }

        if (! Schema::hasColumn('detalle_cotizacion', 'garantia_meses')) {
            DB::statement('ALTER TABLE detalle_cotizacion
                ADD COLUMN garantia_meses INTEGER
                CHECK (garantia_meses IS NULL OR garantia_meses >= 0)');
        }

        // Reemplazar funciones PostgreSQL para que manejen garantia_meses.
        DB::unprepared($this->registrarVentaSql());
        DB::unprepared($this->convertirCotizacionSql());
        DB::unprepared($this->crearGarantiasSql());

        // La vista no puede usar CREATE OR REPLACE porque cambia columnas;
        // se dropa y se recrea con el nuevo orden de columnas.
        DB::statement('DROP VIEW IF EXISTS v_garantia_vigente');
        DB::unprepared($this->vistaGarantiaSql());
    }

    public function down(): void
    {
        // Restaurar funciones originales de v5-MVP.sql antes de quitar columnas.
        DB::unprepared($this->registrarVentaSqlOriginal());
        DB::unprepared($this->convertirCotizacionSqlOriginal());
        DB::unprepared($this->crearGarantiasSqlOriginal());

        // Restaurar la vista original.
        DB::statement('DROP VIEW IF EXISTS v_garantia_vigente');
        DB::unprepared($this->vistaGarantiaSqlOriginal());

        if (Schema::hasColumn('detalle_venta', 'garantia_meses')) {
            Schema::table('detalle_venta', function ($table) {
                $table->dropColumn('garantia_meses');
            });
        }

        if (Schema::hasColumn('detalle_cotizacion', 'garantia_meses')) {
            Schema::table('detalle_cotizacion', function ($table) {
                $table->dropColumn('garantia_meses');
            });
        }
    }

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
        v_id_var := (v_lin->>'id_variante')::INTEGER;
        v_cant   := (v_lin->>'cantidad')::INTEGER;
        v_precio := (v_lin->>'precio_unitario_usd')::NUMERIC;
        v_desc   := COALESCE((v_lin->>'descuento_pct')::NUMERIC, 0);
        v_id_alm := NULLIF(v_lin->>'id_almacen','')::INTEGER;

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
            (id_venta, id_variante, cantidad, precio_unitario_usd, descuento_pct)
        VALUES (v_id_venta, v_id_var, v_cant, v_precio, v_desc);
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

    private function convertirCotizacionSql(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION convertir_cotizacion_a_venta(
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
                'garantia_meses',      dc.garantia_meses,
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

    private function convertirCotizacionSqlOriginal(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION convertir_cotizacion_a_venta(
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

    private function crearGarantiasSql(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION fn_crear_garantias_venta() RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    IF NEW.estado = 'REGISTRADA' AND OLD.estado = 'EN_PROCESO' THEN
        INSERT INTO garantia (id_detalle_venta, fecha_inicio, duracion_meses)
        SELECT dv.id_detalle_venta, NEW.fecha_hora::date,
               COALESCE(dv.garantia_meses, p.garantia_meses)
          FROM detalle_venta dv
          JOIN variante_producto vp ON vp.id_variante = dv.id_variante
          JOIN producto p           ON p.id_producto = vp.id_producto
         WHERE dv.id_venta = NEW.id_venta
           AND COALESCE(dv.garantia_meses, p.garantia_meses) > 0;
    END IF;
    RETURN NEW;
END;
$$;
SQL;
    }

    private function crearGarantiasSqlOriginal(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION fn_crear_garantias_venta() RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    IF NEW.estado = 'REGISTRADA' AND OLD.estado = 'EN_PROCESO' THEN
        INSERT INTO garantia (id_detalle_venta, fecha_inicio, duracion_meses)
        SELECT dv.id_detalle_venta, NEW.fecha_hora::date, p.garantia_meses
          FROM detalle_venta dv
          JOIN variante_producto vp ON vp.id_variante = dv.id_variante
          JOIN producto p           ON p.id_producto = vp.id_producto
         WHERE dv.id_venta = NEW.id_venta
           AND p.garantia_meses > 0;
    END IF;
    RETURN NEW;
END;
$$;
SQL;
    }

    private function vistaGarantiaSql(): string
    {
        return <<<'SQL'
CREATE OR REPLACE VIEW v_garantia_vigente AS
SELECT g.id_garantia,
       g.id_detalle_venta,
       dv.id_venta,
       v.id_cliente,
       vp.id_variante,
       vp.sku,
       p.nombre AS producto,
       g.fecha_inicio,
       g.duracion_meses,
       (g.fecha_inicio + (g.duracion_meses || ' months')::interval)::date AS fecha_vencimiento,
       (current_date >= g.fecha_inicio
        AND current_date <= (g.fecha_inicio + (g.duracion_meses || ' months')::interval)::date) AS vigente
FROM garantia g
JOIN detalle_venta dv ON dv.id_detalle_venta = g.id_detalle_venta
JOIN venta v ON v.id_venta = dv.id_venta
JOIN variante_producto vp ON vp.id_variante = dv.id_variante
JOIN producto p ON p.id_producto = vp.id_producto;
SQL;
    }

    private function vistaGarantiaSqlOriginal(): string
    {
        return <<<'SQL'
CREATE OR REPLACE VIEW v_garantia_vigente AS
SELECT g.id_garantia, g.id_detalle_venta,
       g.fecha_inicio, g.duracion_meses,
       (g.fecha_inicio + (g.duracion_meses || ' months')::interval)::date AS fecha_vencimiento,
       (current_date >= g.fecha_inicio
        AND current_date <= (g.fecha_inicio + (g.duracion_meses || ' months')::interval)::date) AS vigente
FROM garantia g;
SQL;
    }
};
