-- A-03 — Restaura garantia_meses en convertir_cotizacion_a_venta
--
-- Contexto: la migración 2026_11_15_000002 agregó detalle_cotizacion.garantia_meses
-- y lo propagaba al JSON entregado a registrar_venta(). La migración posterior
-- 2026_11_18_000001 (alcance por sucursal) redefinió la función y omitió la clave,
-- haciendo que las cotizaciones convertidas usaran el garantia_meses por defecto
-- del producto. Este parche conserva las guardas de sucursal y reincorpora el valor.
--
-- Aplicar con el rol migrador:
--   psql ... -f database/sql/patches/A-03-convertir-cotizacion-garantia-meses.sql
-- La migración Modules/Ventas/Database/Migrations/2026_11_24_000001_* aplica
-- esta misma definición y su down() restaura la versión sin el campo.

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
