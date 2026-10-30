-- =====================================================================
-- Parche A-01: reserva.id_almacen (para bases ya creadas con el script v5-MVP)
-- Ejecutar como propietario (ewiflor_owner o superusuario).
-- =====================================================================
BEGIN;

ALTER TABLE reserva
    ADD COLUMN id_almacen INTEGER REFERENCES almacen (id_almacen) ON DELETE RESTRICT;

-- Reservas RESERVA existentes: asignar manualmente su almacén antes de agregar
-- la restricción. Ejemplo (ajustar al caso real):
--   UPDATE reserva SET id_almacen = 1 WHERE tipo = 'RESERVA' AND id_almacen IS NULL;

ALTER TABLE reserva
    ADD CONSTRAINT ck_reserva_almacen
    CHECK ((tipo = 'RESERVA') = (id_almacen IS NOT NULL));

CREATE INDEX idx_reserva_almacen ON reserva (id_almacen, id_variante)
    WHERE id_almacen IS NOT NULL;

CREATE OR REPLACE FUNCTION registrar_reserva(
    p_tipo           VARCHAR,
    p_id_cliente     INTEGER,
    p_id_usuario     INTEGER,
    p_id_variante    INTEGER,
    p_cantidad       INTEGER,
    p_precio_usd     NUMERIC,
    p_id_tipo_cambio INTEGER,
    p_anticipo_bs    NUMERIC,
    p_forma_pago     VARCHAR,
    p_id_almacen     INTEGER DEFAULT NULL,
    p_vigencia_dias  INTEGER DEFAULT NULL
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_id_reserva INTEGER;
    v_disponible INTEGER;
    v_tc         NUMERIC;
    v_min_bs     NUMERIC;
    v_max_dias   INTEGER;
    v_vigencia   INTEGER;
    v_vence      DATE := NULL;
BEGIN
    IF p_tipo NOT IN ('RESERVA','BAJO_PEDIDO') THEN
        RAISE EXCEPTION 'Tipo de reserva inválido: %', p_tipo;
    END IF;

    SELECT valor::NUMERIC INTO v_tc FROM tipo_cambio WHERE id_tipo_cambio = p_id_tipo_cambio;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Tipo de cambio % inexistente', p_id_tipo_cambio;
    END IF;

    -- Anticipo mínimo: 10% del total en Bs (parámetro simplificado).
    v_min_bs := ROUND(p_precio_usd * p_cantidad * v_tc * 0.10, 2);
    IF p_anticipo_bs < v_min_bs THEN
        RAISE EXCEPTION 'Anticipo insuficiente (mínimo % Bs, recibido % Bs)',
            v_min_bs, p_anticipo_bs;
    END IF;

    IF p_tipo = 'RESERVA' THEN
        IF p_id_almacen IS NULL THEN
            RAISE EXCEPTION 'La reserva requiere almacén';
        END IF;
        SELECT valor::INTEGER INTO v_max_dias
          FROM parametro_sistema WHERE clave = 'RESERVA_VIGENCIA_DIAS';
        v_vigencia := COALESCE(p_vigencia_dias, v_max_dias);
        IF v_vigencia <= 0 OR v_vigencia > v_max_dias THEN
            RAISE EXCEPTION 'La vigencia debe estar entre 1 y % días', v_max_dias;
        END IF;
        v_vence := current_date + v_vigencia;

        SELECT cantidad_total - cantidad_reservada INTO v_disponible
          FROM existencia
         WHERE id_almacen = p_id_almacen AND id_variante = p_id_variante
         FOR UPDATE;

        IF v_disponible IS NULL OR v_disponible < p_cantidad THEN
            RAISE EXCEPTION 'Disponibilidad insuficiente (disponible=%, solicitado=%)',
                COALESCE(v_disponible,0), p_cantidad;
        END IF;
    END IF;

    INSERT INTO reserva
        (tipo, id_cliente, id_usuario, id_variante, id_almacen, cantidad,
         precio_unitario_usd, id_tipo_cambio, monto_anticipo_bs,
         forma_pago, fecha_vencimiento)
    VALUES
        (p_tipo, p_id_cliente, p_id_usuario, p_id_variante,
         CASE WHEN p_tipo = 'RESERVA' THEN p_id_almacen END, p_cantidad,
         p_precio_usd, p_id_tipo_cambio, p_anticipo_bs,
         p_forma_pago, v_vence)
    RETURNING id_reserva INTO v_id_reserva;

    IF p_tipo = 'RESERVA' THEN
        UPDATE existencia
           SET cantidad_reservada = cantidad_reservada + p_cantidad
         WHERE id_almacen = p_id_almacen AND id_variante = p_id_variante;
    END IF;

    RETURN v_id_reserva;
END;
$$;

-- CREATE OR REPLACE conserva el propietario original de la función.
COMMIT;
