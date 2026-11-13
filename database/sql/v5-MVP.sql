-- =====================================================================
-- Ewiflor Importaciones — Script MVP académico (v5-MVP)
-- PostgreSQL 14+ (probado en 18)
--
-- Alcance:
--   Nivel 1 completo + Nivel 2 simplificado.
--   NO incluye: promociones, comisiones, unidad_serie, intento_login,
--   auditoria_detalle, notificaciones, contacto web, documentos,
--   compatibilidad repuesto, especificaciones, reembolsos,
--   aplicacion_anticipo, reserva_asignacion, reserva_unidad.
--
-- Orden de ejecución:
--   1) PASO 0 (extensiones) como superusuario.
--   2) PASO 1 (BEGIN...COMMIT) como propietario.
--   3) PASO 2 (roles técnicos) como superusuario.
--   4) PASO 3 (límites por rol).
-- =====================================================================

-- =====================================================================
-- PASO 0 — Extensiones
-- =====================================================================
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- =====================================================================
-- PASO 1 — Esquema
-- =====================================================================
BEGIN;

-- ---------------------------------------------------------------------
-- 1.0 Tipos y dominios
-- ---------------------------------------------------------------------
CREATE DOMAIN monto_usd          AS NUMERIC(12,2) CHECK (VALUE >= 0);
CREATE DOMAIN monto_usd_positivo AS NUMERIC(12,2) CHECK (VALUE > 0);

CREATE TYPE accion_permiso  AS ENUM ('LEER','CREAR','EDITAR','APROBAR');
CREATE TYPE sentido_ajuste  AS ENUM ('INCREMENTO','DECREMENTO');
CREATE TYPE tipo_movimiento AS ENUM ('ENTRADA','SALIDA','AJUSTE','TRANSFERENCIA');
CREATE TYPE tipo_dato_param AS ENUM ('INTEGER','NUMERIC','TEXT','BOOLEAN');
CREATE TYPE nombre_rol      AS ENUM ('GERENTE','ADMINISTRADOR','VENDEDOR','SERVICIO_TECNICO');

-- ---------------------------------------------------------------------
-- 1.1 Permisos y roles
-- ---------------------------------------------------------------------
CREATE TABLE permiso (
    id_permiso INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    modulo     VARCHAR(40) NOT NULL,
    accion     accion_permiso NOT NULL,
    UNIQUE (modulo, accion)
);

CREATE TABLE rol_permiso (
    nombre_rol nombre_rol NOT NULL,
    id_permiso INTEGER REFERENCES permiso (id_permiso) ON DELETE CASCADE,
    PRIMARY KEY (nombre_rol, id_permiso)
);

-- ---------------------------------------------------------------------
-- 1.2 Sucursales, almacenes y usuarios
-- ---------------------------------------------------------------------
CREATE TABLE sucursal (
    id_sucursal  INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre       VARCHAR(80) NOT NULL UNIQUE,
    departamento VARCHAR(30) CHECK (departamento IN (
                    'LA_PAZ','SANTA_CRUZ','COCHABAMBA','ORURO','POTOSI',
                    'TARIJA','CHUQUISACA','BENI','PANDO')),
    direccion    VARCHAR(200),
    telefono     VARCHAR(20),
    estado       VARCHAR(10) NOT NULL DEFAULT 'ACTIVO'
                 CHECK (estado IN ('ACTIVO','INACTIVO'))
);

CREATE TABLE almacen (
    id_almacen  INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre      VARCHAR(80) NOT NULL UNIQUE,
    tamano      VARCHAR(8) CHECK (tamano IN ('GRANDE','PEQUENO')),
    id_sucursal INTEGER NOT NULL REFERENCES sucursal (id_sucursal) ON DELETE RESTRICT,
    estado      VARCHAR(10) NOT NULL DEFAULT 'ACTIVO'
                CHECK (estado IN ('ACTIVO','INACTIVO'))
);

CREATE TABLE usuario (
    id_usuario              INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre_completo         VARCHAR(100) NOT NULL,
    nombre_usuario          VARCHAR(30)  NOT NULL UNIQUE
                            CHECK (nombre_usuario = lower(nombre_usuario)),
    correo                  VARCHAR(100) UNIQUE
                            CHECK (correo IS NULL OR correo = lower(correo)),
    contrasena_hash         VARCHAR(255) NOT NULL,
    nombre_rol              nombre_rol NOT NULL,
    id_sucursal             INTEGER REFERENCES sucursal (id_sucursal) ON DELETE RESTRICT,
    porcentaje_comision_pct NUMERIC(5,2) NOT NULL DEFAULT 0
                            CHECK (porcentaje_comision_pct BETWEEN 0 AND 100),
    estado                  VARCHAR(10) NOT NULL DEFAULT 'ACTIVO'
                            CHECK (estado IN ('ACTIVO','INACTIVO')),
    fecha_creacion          TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT ck_usuario_hash_formato CHECK (
        contrasena_hash ~ '^\$2[aby]\$\d{2}\$[./A-Za-z0-9]{53}$'
    )
);

-- ---------------------------------------------------------------------
-- 1.3 Catálogos de producto
-- ---------------------------------------------------------------------
CREATE TABLE marca (
    id_marca    INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre      VARCHAR(60) NOT NULL UNIQUE,
    pais_origen VARCHAR(50)
);

CREATE TABLE producto (
    id_producto     INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    codigo          VARCHAR(30)  NOT NULL UNIQUE,
    nombre          VARCHAR(150) NOT NULL,
    descripcion     TEXT,
    categoria       VARCHAR(50) NOT NULL
                    CHECK (categoria IN ('ALIMENTARIA','PANADERIA','REPOSTERIA','CARNICA')),
    id_marca        INTEGER NOT NULL REFERENCES marca (id_marca) ON DELETE RESTRICT,
    modelo          VARCHAR(60),
    tipo_producto   VARCHAR(10) NOT NULL
                    CHECK (tipo_producto IN ('MAQUINARIA','REPUESTO','ACCESORIO')),
    modalidad       VARCHAR(14) NOT NULL DEFAULT 'INVENTARIABLE'
                    CHECK (modalidad IN ('INVENTARIABLE','BAJO_PEDIDO')),
    stock_minimo    INTEGER NOT NULL DEFAULT 0 CHECK (stock_minimo >= 0),
    garantia_meses  INTEGER NOT NULL DEFAULT 0 CHECK (garantia_meses >= 0),
    es_publico      BOOLEAN NOT NULL DEFAULT true,
    estado          VARCHAR(10) NOT NULL DEFAULT 'ACTIVO'
                    CHECK (estado IN ('ACTIVO','INACTIVO'))
);

CREATE TABLE variante_producto (
    id_variante     INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_producto     INTEGER NOT NULL REFERENCES producto (id_producto) ON DELETE RESTRICT,
    sku             VARCHAR(40) NOT NULL UNIQUE,
    color           VARCHAR(30),
    caracteristicas VARCHAR(200),
    estado          VARCHAR(10) NOT NULL DEFAULT 'ACTIVO'
                    CHECK (estado IN ('ACTIVO','INACTIVO'))
);

-- ---------------------------------------------------------------------
-- 1.4 Tipo de cambio (manual)
-- ---------------------------------------------------------------------
CREATE TABLE tipo_cambio (
    id_tipo_cambio INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    fecha          DATE NOT NULL,
    tipo           VARCHAR(8) NOT NULL CHECK (tipo IN ('PARALELO','OFICIAL')),
    valor          NUMERIC(10,4) NOT NULL CHECK (valor > 0),
    origen         VARCHAR(6) NOT NULL DEFAULT 'API' CHECK (origen IN ('API','MANUAL')),
    id_usuario     INTEGER REFERENCES usuario (id_usuario) ON DELETE RESTRICT,
    fecha_registro TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (fecha, tipo, fecha_registro)
);

-- ---------------------------------------------------------------------
-- 1.5 Existencia
-- ---------------------------------------------------------------------
CREATE TABLE existencia (
    id_almacen         INTEGER REFERENCES almacen (id_almacen) ON DELETE RESTRICT,
    id_variante        INTEGER REFERENCES variante_producto (id_variante) ON DELETE RESTRICT,
    cantidad_total     INTEGER NOT NULL DEFAULT 0 CHECK (cantidad_total >= 0),
    cantidad_reservada INTEGER NOT NULL DEFAULT 0 CHECK (cantidad_reservada >= 0),
    ubicacion_interna  VARCHAR(60),
    PRIMARY KEY (id_almacen, id_variante),
    CHECK (cantidad_reservada <= cantidad_total)
);

-- ---------------------------------------------------------------------
-- 1.6 Clientes
-- ---------------------------------------------------------------------
CREATE TABLE cliente (
    id_cliente     INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre         VARCHAR(120) NOT NULL,
    telefono       VARCHAR(20)  NOT NULL,
    correo         VARCHAR(100),
    tipo_cliente   VARCHAR(40) CHECK (tipo_cliente IN (
                       'DISTRIBUIDOR','PANADERIA','PASTELERIA',
                       'COMIDA_RAPIDA','RESTAURANTE','PERSONA_NATURAL')),
    es_mayorista   BOOLEAN NOT NULL DEFAULT false,
    fecha_registro TIMESTAMPTZ NOT NULL DEFAULT now(),
    estado VARCHAR(10) NOT NULL DEFAULT 'ACTIVO'
       CHECK (estado IN ('ACTIVO','INACTIVO'))
);

-- ---------------------------------------------------------------------
-- 1.7 Cotizaciones
-- ---------------------------------------------------------------------
CREATE TABLE cotizacion (
    id_cotizacion  INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_cliente     INTEGER NOT NULL REFERENCES cliente (id_cliente) ON DELETE RESTRICT,
    id_usuario     INTEGER NOT NULL REFERENCES usuario (id_usuario) ON DELETE RESTRICT,
    id_sucursal    INTEGER NOT NULL REFERENCES sucursal (id_sucursal) ON DELETE RESTRICT,
    id_tipo_cambio INTEGER NOT NULL REFERENCES tipo_cambio (id_tipo_cambio) ON DELETE RESTRICT,
    fecha_emision  DATE NOT NULL DEFAULT current_date,
    vigencia_dias  INTEGER NOT NULL DEFAULT 14 CHECK (vigencia_dias > 0),
    estado         VARCHAR(10) NOT NULL DEFAULT 'EMITIDA'
                   CHECK (estado IN ('EMITIDA','CONVERTIDA','ANULADA')),
    observacion    VARCHAR(200)
);

CREATE TABLE detalle_cotizacion (
    id_cotizacion       INTEGER REFERENCES cotizacion (id_cotizacion) ON DELETE CASCADE,
    id_variante         INTEGER REFERENCES variante_producto (id_variante) ON DELETE RESTRICT,
    cantidad            INTEGER NOT NULL CHECK (cantidad > 0),
    precio_unitario_usd monto_usd NOT NULL,
    descuento_pct       NUMERIC(5,2) NOT NULL DEFAULT 0
                        CHECK (descuento_pct BETWEEN 0 AND 100),
    PRIMARY KEY (id_cotizacion, id_variante)
);

-- ---------------------------------------------------------------------
-- 1.8 Ventas y pagos
-- ---------------------------------------------------------------------
CREATE TABLE venta (
    id_venta           INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_cliente         INTEGER NOT NULL REFERENCES cliente (id_cliente) ON DELETE RESTRICT,
    id_usuario         INTEGER NOT NULL REFERENCES usuario (id_usuario) ON DELETE RESTRICT,
    id_sucursal        INTEGER NOT NULL REFERENCES sucursal (id_sucursal) ON DELETE RESTRICT,
    id_cotizacion      INTEGER REFERENCES cotizacion (id_cotizacion) ON DELETE RESTRICT,
    id_tipo_cambio     INTEGER NOT NULL REFERENCES tipo_cambio (id_tipo_cambio) ON DELETE RESTRICT,
    fecha_hora         TIMESTAMPTZ NOT NULL DEFAULT now(),
    estado             VARCHAR(10) NOT NULL DEFAULT 'EN_PROCESO'
                       CHECK (estado IN ('EN_PROCESO','REGISTRADA','CANCELADA')),
    numero_factura     VARCHAR(30),
    fecha_entrega      DATE,
    id_usuario_cancela INTEGER REFERENCES usuario (id_usuario) ON DELETE RESTRICT,
    fecha_cancelacion  TIMESTAMPTZ,
    motivo_cancelacion VARCHAR(200),
    CONSTRAINT ck_venta_cancelacion CHECK (
        estado <> 'CANCELADA'
        OR (id_usuario_cancela IS NOT NULL
            AND fecha_cancelacion IS NOT NULL
            AND motivo_cancelacion IS NOT NULL
            AND btrim(motivo_cancelacion) <> '')
    ),
    CONSTRAINT ck_venta_factura_unica UNIQUE (id_sucursal, numero_factura)
);

CREATE TABLE detalle_venta (
    id_detalle_venta    INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_venta            INTEGER NOT NULL REFERENCES venta (id_venta) ON DELETE CASCADE,
    id_variante         INTEGER NOT NULL REFERENCES variante_producto (id_variante) ON DELETE RESTRICT,
    cantidad            INTEGER NOT NULL CHECK (cantidad > 0),
    precio_unitario_usd monto_usd NOT NULL,
    descuento_pct       NUMERIC(5,2) NOT NULL DEFAULT 0
                        CHECK (descuento_pct BETWEEN 0 AND 100),
    UNIQUE (id_venta, id_variante)
);

CREATE TABLE pago (
    id_pago    INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_venta   INTEGER NOT NULL REFERENCES venta (id_venta) ON DELETE RESTRICT,
    forma_pago VARCHAR(13) NOT NULL CHECK (forma_pago IN ('EFECTIVO','QR','TRANSFERENCIA')),
    monto_bs   NUMERIC(12,2) NOT NULL CHECK (monto_bs > 0),
    fecha_hora TIMESTAMPTZ NOT NULL DEFAULT now(),
    referencia VARCHAR(60)
);

-- ---------------------------------------------------------------------
-- 1.9 Movimientos de inventario
-- ---------------------------------------------------------------------
CREATE TABLE movimiento_inventario (
    id_movimiento      INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    tipo               tipo_movimiento NOT NULL,
    fecha_hora         TIMESTAMPTZ NOT NULL DEFAULT now(),
    motivo             VARCHAR(200) NOT NULL,
    id_usuario         INTEGER NOT NULL REFERENCES usuario (id_usuario) ON DELETE RESTRICT,
    id_almacen_origen  INTEGER REFERENCES almacen (id_almacen) ON DELETE RESTRICT,
    id_almacen_destino INTEGER REFERENCES almacen (id_almacen) ON DELETE RESTRICT,
    id_venta           INTEGER REFERENCES venta (id_venta) ON DELETE RESTRICT,
    CONSTRAINT ck_mov_almacenes CHECK (
        (tipo = 'ENTRADA'       AND id_almacen_destino IS NOT NULL)
        OR (tipo = 'SALIDA'       AND id_almacen_origen  IS NOT NULL)
        OR (tipo = 'TRANSFERENCIA' AND id_almacen_origen IS NOT NULL
                                   AND id_almacen_destino IS NOT NULL
                                   AND id_almacen_origen <> id_almacen_destino)
        OR (tipo = 'AJUSTE'       AND id_almacen_origen  IS NOT NULL
                                   AND id_almacen_destino IS NULL)
    )
);

CREATE TABLE detalle_movimiento (
    id_movimiento INTEGER REFERENCES movimiento_inventario (id_movimiento) ON DELETE CASCADE,
    id_variante   INTEGER REFERENCES variante_producto (id_variante) ON DELETE RESTRICT,
    cantidad      INTEGER NOT NULL CHECK (cantidad > 0),
    sentido       sentido_ajuste,
    observacion   VARCHAR(200),
    PRIMARY KEY (id_movimiento, id_variante)
);

-- ---------------------------------------------------------------------
-- 1.10 Auditoría simple (cabecera)
-- ---------------------------------------------------------------------
CREATE TABLE auditoria (
    id_auditoria   BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_usuario     INTEGER REFERENCES usuario (id_usuario) ON DELETE RESTRICT,
    fecha_hora     TIMESTAMPTZ NOT NULL DEFAULT now(),
    modulo         VARCHAR(40) NOT NULL,
    operacion      VARCHAR(13) NOT NULL
                   CHECK (operacion IN ('CREAR','MODIFICAR','ELIMINAR','CANCELAR','INICIO_SESION')),
    tabla_afectada VARCHAR(60) NOT NULL,
    id_registro    VARCHAR(40)
);

-- ---------------------------------------------------------------------
-- 1.11 Parámetros del sistema
-- ---------------------------------------------------------------------
CREATE TABLE parametro_sistema (
    clave       VARCHAR(50) PRIMARY KEY,
    valor       VARCHAR(100) NOT NULL,
    tipo_dato   tipo_dato_param NOT NULL DEFAULT 'TEXT',
    descripcion VARCHAR(200),
    CHECK (
        (tipo_dato = 'INTEGER' AND valor ~ '^-?\d+$')
        OR (tipo_dato = 'NUMERIC' AND valor ~ '^-?\d+(\.\d+)?$')
        OR (tipo_dato = 'BOOLEAN' AND valor IN ('true','false'))
        OR tipo_dato = 'TEXT'
    )
);

-- ---------------------------------------------------------------------
-- 1.12 Reservas (Nivel 2, simplificado)
-- ---------------------------------------------------------------------
CREATE TABLE reserva (
    id_reserva          INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    tipo                VARCHAR(12) NOT NULL CHECK (tipo IN ('RESERVA','BAJO_PEDIDO')),
    id_cliente          INTEGER NOT NULL REFERENCES cliente (id_cliente) ON DELETE RESTRICT,
    id_usuario          INTEGER NOT NULL REFERENCES usuario (id_usuario) ON DELETE RESTRICT,
    id_variante         INTEGER NOT NULL REFERENCES variante_producto (id_variante) ON DELETE RESTRICT,
    cantidad            INTEGER NOT NULL CHECK (cantidad > 0),
    precio_unitario_usd monto_usd NOT NULL,
    id_tipo_cambio      INTEGER NOT NULL REFERENCES tipo_cambio (id_tipo_cambio) ON DELETE RESTRICT,
    monto_anticipo_bs   NUMERIC(12,2) NOT NULL CHECK (monto_anticipo_bs > 0),
    forma_pago          VARCHAR(13) NOT NULL CHECK (forma_pago IN ('EFECTIVO','QR','TRANSFERENCIA')),
    fecha_registro      TIMESTAMPTZ NOT NULL DEFAULT now(),
    fecha_vencimiento   DATE,
    estado              VARCHAR(10) NOT NULL DEFAULT 'ACTIVA'
                        CHECK (estado IN ('ACTIVA','VENCIDA','CONVERTIDA','CANCELADA')),
    id_venta            INTEGER REFERENCES venta (id_venta) ON DELETE RESTRICT,
    CHECK (tipo = 'BAJO_PEDIDO' OR fecha_vencimiento IS NOT NULL)
);

-- ---------------------------------------------------------------------
-- 1.13 Garantías (Nivel 2, simplificado — solo por línea de venta)
-- ---------------------------------------------------------------------
CREATE TABLE garantia (
    id_garantia      INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_detalle_venta INTEGER NOT NULL REFERENCES detalle_venta (id_detalle_venta) ON DELETE RESTRICT,
    fecha_inicio     DATE NOT NULL,
    duracion_meses   INTEGER NOT NULL CHECK (duracion_meses > 0),
    cobertura        VARCHAR(30) NOT NULL DEFAULT 'DEFECTO_DE_FABRICA'
);

-- ---------------------------------------------------------------------
-- 1.14 Solicitudes de servicio técnico (Nivel 2)
-- ---------------------------------------------------------------------
CREATE TABLE solicitud_servicio (
    id_solicitud        INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_cliente          INTEGER NOT NULL REFERENCES cliente (id_cliente) ON DELETE RESTRICT,
    id_garantia         INTEGER REFERENCES garantia (id_garantia) ON DELETE RESTRICT,
    id_usuario_registra INTEGER NOT NULL REFERENCES usuario (id_usuario) ON DELETE RESTRICT,
    id_tecnico          INTEGER REFERENCES usuario (id_usuario) ON DELETE RESTRICT,
    descripcion_falla   TEXT NOT NULL,
    fecha_solicitud     TIMESTAMPTZ NOT NULL DEFAULT now(),
    fecha_visita        TIMESTAMPTZ,
    estado              VARCHAR(10) NOT NULL DEFAULT 'PENDIENTE'
                        CHECK (estado IN ('PENDIENTE','PROGRAMADA','CERRADA')),
    diagnostico         TEXT,
    resultado           VARCHAR(15)
                        CHECK (resultado IN ('REPARACION','SERVICIO_PAGADO','REEMPLAZO')),
    costo_servicio_bs   NUMERIC(12,2) CHECK (costo_servicio_bs >= 0),
    fecha_cierre        TIMESTAMPTZ,
    CHECK (estado <> 'CERRADA'
           OR (fecha_cierre IS NOT NULL AND resultado IS NOT NULL))
);

-- ---------------------------------------------------------------------
-- 1.15 Comprobantes (Nivel 2, simplificado)
-- ---------------------------------------------------------------------
CREATE TABLE comprobante (
    id_comprobante INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_venta       INTEGER NOT NULL REFERENCES venta (id_venta) ON DELETE RESTRICT,
    tipo           VARCHAR(13) NOT NULL
                   CHECK (tipo IN ('RECIBO','NOTA_VENTA','NOTA_GARANTIA')),
    numero         VARCHAR(30) NOT NULL,
    fecha_emision  TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (tipo, numero)
);

-- ---------------------------------------------------------------------
-- 1.16 Índices
-- ---------------------------------------------------------------------
CREATE INDEX idx_variante_producto      ON variante_producto (id_producto);
CREATE INDEX idx_tipo_cambio_fecha       ON tipo_cambio (fecha, tipo, fecha_registro DESC);
CREATE INDEX idx_existencia_variante     ON existencia (id_variante);
CREATE INDEX idx_movimiento_fecha        ON movimiento_inventario (fecha_hora);
CREATE INDEX idx_movimiento_venta        ON movimiento_inventario (id_venta)
                                        WHERE id_venta IS NOT NULL;
CREATE INDEX idx_cotizacion_cliente      ON cotizacion (id_cliente);
CREATE INDEX idx_venta_fecha             ON venta (fecha_hora);
CREATE INDEX idx_venta_cliente           ON venta (id_cliente);
CREATE INDEX idx_venta_vendedor          ON venta (id_usuario);
CREATE INDEX idx_detalle_venta_variante  ON detalle_venta (id_variante);
CREATE INDEX idx_pago_venta              ON pago (id_venta);
CREATE INDEX idx_reserva_estado          ON reserva (estado, fecha_vencimiento);
CREATE INDEX idx_solicitud_estado        ON solicitud_servicio (estado);
CREATE INDEX idx_garantia_detalle        ON garantia (id_detalle_venta);
CREATE INDEX idx_auditoria_fecha         ON auditoria (fecha_hora DESC);
CREATE INDEX idx_auditoria_usuario       ON auditoria (id_usuario);
CREATE INDEX idx_auditoria_tabla         ON auditoria (tabla_afectada, id_registro);

-- ---------------------------------------------------------------------
-- 1.17 Vistas
-- ---------------------------------------------------------------------
CREATE VIEW v_existencia_disponible AS
SELECT id_almacen, id_variante, cantidad_total, cantidad_reservada,
       cantidad_total - cantidad_reservada AS cantidad_disponible
FROM existencia;

CREATE VIEW v_venta_total AS
SELECT v.id_venta,
       COALESCE(SUM(d.cantidad * d.precio_unitario_usd * (1 - d.descuento_pct / 100)), 0) AS total_usd,
       ROUND(COALESCE(SUM(d.cantidad * d.precio_unitario_usd * (1 - d.descuento_pct / 100)), 0)
             * tc.valor, 2) AS total_bs
FROM venta v
LEFT JOIN detalle_venta d ON d.id_venta = v.id_venta
JOIN tipo_cambio tc ON tc.id_tipo_cambio = v.id_tipo_cambio
GROUP BY v.id_venta, tc.valor;

CREATE VIEW v_cotizacion_estado AS
SELECT c.id_cotizacion, c.fecha_emision, c.vigencia_dias,
       c.fecha_emision + c.vigencia_dias AS fecha_vencimiento,
       c.estado AS estado_proceso,
       CASE
           WHEN c.estado <> 'EMITIDA' THEN 'CERRADA'
           WHEN current_date > c.fecha_emision + c.vigencia_dias THEN 'VENCIDA'
           ELSE 'VIGENTE'
       END AS condicion_temporal
FROM cotizacion c;

-- Catálogo público (Nivel 2)
CREATE VIEW v_catalogo_publico AS
SELECT p.id_producto, p.codigo, p.nombre, p.descripcion,
       p.categoria, m.nombre AS marca, p.modelo, p.tipo_producto,
       p.garantia_meses,
       v.id_variante, v.sku, v.color, v.caracteristicas
FROM producto p
JOIN marca m ON m.id_marca = p.id_marca
JOIN variante_producto v ON v.id_producto = p.id_producto
WHERE p.es_publico = true
  AND p.estado = 'ACTIVO'
  AND v.estado = 'ACTIVO';

-- Vista de autenticación
CREATE VIEW v_auth_usuario AS
SELECT u.id_usuario, u.nombre_usuario, u.contrasena_hash, u.estado,
       u.nombre_rol::VARCHAR AS nombre_rol
FROM usuario u;

-- Garantía con fecha de vencimiento
CREATE VIEW v_garantia_vigente AS
SELECT g.id_garantia, g.id_detalle_venta,
       g.fecha_inicio, g.duracion_meses,
       (g.fecha_inicio + (g.duracion_meses || ' months')::interval)::date AS fecha_vencimiento,
       (current_date >= g.fecha_inicio
        AND current_date <= (g.fecha_inicio + (g.duracion_meses || ' months')::interval)::date) AS vigente
FROM garantia g;

-- Reportes básicos
CREATE VIEW v_reporte_ventas AS
SELECT v.id_venta,
       v.fecha_hora::date AS fecha,
       v.estado,
       c.nombre AS cliente,
       u.nombre_completo AS vendedor,
       s.nombre AS sucursal,
       vt.total_usd,
       vt.total_bs,
       COALESCE((SELECT SUM(monto_bs) FROM pago WHERE id_venta = v.id_venta), 0) AS pagado_bs
FROM venta v
JOIN cliente c    ON c.id_cliente = v.id_cliente
JOIN usuario u    ON u.id_usuario = v.id_usuario
JOIN sucursal s   ON s.id_sucursal = v.id_sucursal
LEFT JOIN v_venta_total vt ON vt.id_venta = v.id_venta;

CREATE VIEW v_reporte_existencias AS
SELECT a.nombre AS almacen,
       s.nombre AS sucursal,
       p.codigo,
       p.nombre AS producto,
       vp.sku,
       e.cantidad_total,
       e.cantidad_reservada,
       e.cantidad_total - e.cantidad_reservada AS disponible,
       p.stock_minimo,
       CASE WHEN e.cantidad_total - e.cantidad_reservada <= p.stock_minimo
            THEN 'REPONER' ELSE 'OK' END AS alerta
FROM existencia e
JOIN almacen a           ON a.id_almacen = e.id_almacen
JOIN sucursal s          ON s.id_sucursal = a.id_sucursal
JOIN variante_producto vp ON vp.id_variante = e.id_variante
JOIN producto p          ON p.id_producto = vp.id_producto;

-- ---------------------------------------------------------------------
-- 1.18 Funciones de negocio
-- ---------------------------------------------------------------------
CREATE OR REPLACE FUNCTION registrar_movimiento_inventario(
    p_tipo               tipo_movimiento,
    p_motivo             VARCHAR,
    p_id_usuario         INTEGER,
    p_id_almacen_origen  INTEGER,
    p_id_almacen_destino INTEGER,
    p_id_venta           INTEGER,
    p_detalles           JSONB
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_id_mov  INTEGER;
    v_det     JSONB;
    v_id_var  INTEGER;
    v_cant    INTEGER;
    v_sentido sentido_ajuste;
BEGIN
    INSERT INTO movimiento_inventario
        (tipo, motivo, id_usuario, id_almacen_origen, id_almacen_destino, id_venta)
    VALUES
        (p_tipo, p_motivo, p_id_usuario, p_id_almacen_origen,
         p_id_almacen_destino, p_id_venta)
    RETURNING id_movimiento INTO v_id_mov;

    FOR v_det IN SELECT * FROM jsonb_array_elements(p_detalles) LOOP
        v_id_var  := (v_det->>'id_variante')::INTEGER;
        v_cant    := (v_det->>'cantidad')::INTEGER;
        v_sentido := NULLIF(v_det->>'sentido','')::sentido_ajuste;

        INSERT INTO detalle_movimiento
            (id_movimiento, id_variante, cantidad, sentido)
        VALUES (v_id_mov, v_id_var, v_cant, v_sentido);

        IF p_tipo = 'ENTRADA' THEN
            INSERT INTO existencia (id_almacen, id_variante, cantidad_total)
            VALUES (p_id_almacen_destino, v_id_var, v_cant)
            ON CONFLICT (id_almacen, id_variante)
            DO UPDATE SET cantidad_total = existencia.cantidad_total + v_cant;

        ELSIF p_tipo = 'SALIDA' THEN
            UPDATE existencia
               SET cantidad_total = cantidad_total - v_cant
             WHERE id_almacen = p_id_almacen_origen
               AND id_variante = v_id_var
               AND cantidad_total - cantidad_reservada >= v_cant;
            IF NOT FOUND THEN
                RAISE EXCEPTION 'Disponibilidad insuficiente para variante %', v_id_var;
            END IF;

        ELSIF p_tipo = 'TRANSFERENCIA' THEN
            UPDATE existencia
               SET cantidad_total = cantidad_total - v_cant
             WHERE id_almacen = p_id_almacen_origen
               AND id_variante = v_id_var
               AND cantidad_total - cantidad_reservada >= v_cant;
            IF NOT FOUND THEN
                RAISE EXCEPTION 'Disponibilidad insuficiente para transferir variante %', v_id_var;
            END IF;
            INSERT INTO existencia (id_almacen, id_variante, cantidad_total)
            VALUES (p_id_almacen_destino, v_id_var, v_cant)
            ON CONFLICT (id_almacen, id_variante)
            DO UPDATE SET cantidad_total = existencia.cantidad_total + v_cant;

        ELSIF p_tipo = 'AJUSTE' THEN
            IF v_sentido IS NULL THEN
                RAISE EXCEPTION 'Los ajustes requieren sentido (INCREMENTO/DECREMENTO)';
            END IF;
            IF v_sentido = 'INCREMENTO' THEN
                INSERT INTO existencia (id_almacen, id_variante, cantidad_total)
                VALUES (p_id_almacen_origen, v_id_var, v_cant)
                ON CONFLICT (id_almacen, id_variante)
                DO UPDATE SET cantidad_total = existencia.cantidad_total + v_cant;
            ELSE
                UPDATE existencia
                   SET cantidad_total = cantidad_total - v_cant
                 WHERE id_almacen = p_id_almacen_origen
                   AND id_variante = v_id_var
                   AND cantidad_total - cantidad_reservada >= v_cant;
                IF NOT FOUND THEN
                    RAISE EXCEPTION 'Ajuste negativo dejaría existencia negativa';
                END IF;
            END IF;
        END IF;
    END LOOP;

    RETURN v_id_mov;
END;
$$;

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

-- Helper para la demo: convierte una cotización entera en venta
-- (todas las líneas salen del mismo almacén).
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
        (tipo, id_cliente, id_usuario, id_variante, cantidad,
         precio_unitario_usd, id_tipo_cambio, monto_anticipo_bs,
         forma_pago, fecha_vencimiento)
    VALUES
        (p_tipo, p_id_cliente, p_id_usuario, p_id_variante, p_cantidad,
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

CREATE OR REPLACE FUNCTION cancelar_venta(
    p_id_venta   INTEGER,
    p_id_usuario INTEGER,
    p_motivo     VARCHAR
) RETURNS VOID
LANGUAGE plpgsql
AS $$
DECLARE
    v_mov RECORD;
BEGIN
    UPDATE venta
       SET estado = 'CANCELADA',
           id_usuario_cancela = p_id_usuario,
           fecha_cancelacion  = now(),
           motivo_cancelacion = p_motivo
     WHERE id_venta = p_id_venta AND estado <> 'CANCELADA';
    IF NOT FOUND THEN
        RAISE EXCEPTION 'La venta % no existe o ya está cancelada', p_id_venta;
    END IF;

    FOR v_mov IN
        SELECT m.id_movimiento, m.id_almacen_origen
          FROM movimiento_inventario m
         WHERE m.id_venta = p_id_venta AND m.tipo = 'SALIDA'
    LOOP
        PERFORM registrar_movimiento_inventario(
            'ENTRADA'::tipo_movimiento,
            'Reversión por cancelación de venta ' || p_id_venta,
            p_id_usuario, NULL, v_mov.id_almacen_origen, p_id_venta,
            (SELECT jsonb_agg(jsonb_build_object(
                        'id_variante', dm.id_variante,
                        'cantidad',    dm.cantidad))
               FROM detalle_movimiento dm
              WHERE dm.id_movimiento = v_mov.id_movimiento)
        );
    END LOOP;
END;
$$;

-- Autenticación (simplificada: sin intento_login ni bloqueos)
CREATE OR REPLACE FUNCTION fn_autenticar_usuario(
    p_nombre_usuario VARCHAR,
    p_contrasena     TEXT
) RETURNS TABLE (
    id_usuario INTEGER,
    nombre_rol VARCHAR,
    exito      BOOLEAN,
    motivo     VARCHAR
)
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public, pg_temp
AS $$
DECLARE
    v_hash       TEXT;
    v_estado     VARCHAR;
    v_ok         BOOLEAN := false;
    v_motivo     VARCHAR := NULL;
    v_id_usuario INTEGER;
    v_nombre_rol VARCHAR;
BEGIN
    SELECT u.contrasena_hash, u.estado, u.id_usuario, u.nombre_rol::VARCHAR
      INTO v_hash, v_estado, v_id_usuario, v_nombre_rol
      FROM public.usuario u
     WHERE u.nombre_usuario = lower(p_nombre_usuario);

    IF v_hash IS NULL THEN
        v_motivo := 'USUARIO_INEXISTENTE';
    ELSIF v_estado <> 'ACTIVO' THEN
        v_motivo := 'USUARIO_INACTIVO';
    ELSE
        v_ok := (v_hash = crypt(p_contrasena, v_hash));
        IF NOT v_ok THEN
            v_motivo := 'CONTRASENA_INCORRECTA';
        END IF;
    END IF;

    IF NOT v_ok THEN
        RETURN QUERY SELECT NULL::INTEGER, NULL::VARCHAR, false, v_motivo;
        RETURN;
    END IF;

    RETURN QUERY SELECT v_id_usuario, v_nombre_rol, true, NULL::VARCHAR;
END;
$$;

-- ---------------------------------------------------------------------
-- 1.19 Auditoría automática (simplificada)
-- ---------------------------------------------------------------------
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
        WHEN 'marca'                 THEN 'PRODUCTOS'
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

CREATE OR REPLACE FUNCTION fn_auditar_cambio()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public, pg_temp
AS $$
DECLARE
    v_id_usuario  INTEGER;
    v_operacion   VARCHAR(13);
    v_id_registro VARCHAR(40);
    v_columna_pk  TEXT;
    v_old_json    JSONB;
    v_new_json    JSONB;
    v_partes      TEXT[];
    i             INTEGER;
BEGIN
    IF TG_OP = 'INSERT' THEN
        v_operacion := 'CREAR';
        v_new_json  := to_jsonb(NEW);
    ELSIF TG_OP = 'UPDATE' THEN
        v_operacion := 'MODIFICAR';
        v_old_json  := to_jsonb(OLD);
        v_new_json  := to_jsonb(NEW);
    ELSIF TG_OP = 'DELETE' THEN
        v_operacion := 'ELIMINAR';
        v_old_json  := to_jsonb(OLD);
    END IF;

    BEGIN
        v_id_usuario := current_setting('app.id_usuario', true)::INTEGER;
    EXCEPTION WHEN OTHERS THEN
        v_id_usuario := NULL;
    END;

    IF v_id_usuario IS NOT NULL
       AND NOT EXISTS (SELECT 1 FROM public.usuario WHERE id_usuario = v_id_usuario) THEN
        v_id_usuario := NULL;
    END IF;

    IF TG_NARGS > 0 THEN
        v_partes := ARRAY[]::TEXT[];
        FOR i IN 0..TG_NARGS - 1 LOOP
            v_partes := v_partes || COALESCE(
                COALESCE(v_new_json, v_old_json) ->> TG_ARGV[i], '');
        END LOOP;
        v_id_registro := array_to_string(v_partes, ':');
    ELSE
        SELECT a.attname INTO v_columna_pk
          FROM pg_index ix
          JOIN pg_attribute a ON a.attrelid = ix.indrelid AND a.attnum = ANY(ix.indkey)
         WHERE ix.indrelid = TG_RELID AND ix.indisprimary
         LIMIT 1;

        IF v_columna_pk IS NOT NULL THEN
            v_id_registro := CASE TG_OP
                WHEN 'DELETE' THEN v_old_json ->> v_columna_pk
                ELSE                v_new_json ->> v_columna_pk
            END;
        END IF;
    END IF;

    INSERT INTO public.auditoria
        (id_usuario, modulo, operacion, tabla_afectada, id_registro)
    VALUES
        (v_id_usuario,
         public.fn_modulo_auditoria(TG_TABLE_NAME),
         v_operacion,
         TG_TABLE_NAME,
         v_id_registro);

    RETURN CASE TG_OP WHEN 'DELETE' THEN OLD ELSE NEW END;
END;
$$;

-- ---------------------------------------------------------------------
-- 1.20 Triggers de integridad y auditoría
-- ---------------------------------------------------------------------
CREATE OR REPLACE FUNCTION fn_venta_cliente_cotizacion() RETURNS TRIGGER AS $$
BEGIN
    IF NEW.id_cotizacion IS NOT NULL THEN
        PERFORM 1 FROM cotizacion
         WHERE id_cotizacion = NEW.id_cotizacion AND id_cliente = NEW.id_cliente;
        IF NOT FOUND THEN
            RAISE EXCEPTION 'El cliente de la venta no coincide con el de la cotización';
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_venta_cliente_cotizacion
BEFORE INSERT OR UPDATE ON venta
FOR EACH ROW EXECUTE FUNCTION fn_venta_cliente_cotizacion();

CREATE OR REPLACE FUNCTION fn_venta_transicion() RETURNS TRIGGER AS $$
BEGIN
    IF OLD.estado = 'CANCELADA' AND NEW.estado <> 'CANCELADA' THEN
        RAISE EXCEPTION 'No se puede reactivar una venta cancelada';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_venta_transicion
BEFORE UPDATE ON venta
FOR EACH ROW EXECUTE FUNCTION fn_venta_transicion();

CREATE OR REPLACE FUNCTION fn_solicitud_transicion() RETURNS TRIGGER AS $$
BEGIN
    IF OLD.estado = 'CERRADA' AND NEW.estado <> 'CERRADA' THEN
        RAISE EXCEPTION 'No se puede reabrir una solicitud cerrada';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_solicitud_transicion
BEFORE UPDATE ON solicitud_servicio
FOR EACH ROW EXECUTE FUNCTION fn_solicitud_transicion();

CREATE OR REPLACE FUNCTION fn_validar_sentido_ajuste() RETURNS TRIGGER AS $$
DECLARE v_tipo tipo_movimiento;
BEGIN
    SELECT tipo INTO v_tipo FROM movimiento_inventario WHERE id_movimiento = NEW.id_movimiento;
    IF v_tipo = 'AJUSTE' AND NEW.sentido IS NULL THEN
        RAISE EXCEPTION 'Los ajustes requieren sentido (INCREMENTO/DECREMENTO)';
    END IF;
    IF v_tipo <> 'AJUSTE' AND NEW.sentido IS NOT NULL THEN
        RAISE EXCEPTION 'El sentido sólo aplica a movimientos AJUSTE';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_detalle_movimiento_sentido
BEFORE INSERT OR UPDATE ON detalle_movimiento
FOR EACH ROW EXECUTE FUNCTION fn_validar_sentido_ajuste();

CREATE OR REPLACE FUNCTION fn_tipo_cambio_inmutable() RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (SELECT 1 FROM cotizacion WHERE id_tipo_cambio = OLD.id_tipo_cambio)
       OR EXISTS (SELECT 1 FROM venta      WHERE id_tipo_cambio = OLD.id_tipo_cambio)
       OR EXISTS (SELECT 1 FROM reserva    WHERE id_tipo_cambio = OLD.id_tipo_cambio) THEN
        RAISE EXCEPTION 'El tipo de cambio % está referenciado y no puede modificarse', OLD.id_tipo_cambio;
    END IF;
    RETURN CASE TG_OP WHEN 'DELETE' THEN OLD ELSE NEW END;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_tipo_cambio_inmutable
BEFORE UPDATE OR DELETE ON tipo_cambio
FOR EACH ROW EXECUTE FUNCTION fn_tipo_cambio_inmutable();

CREATE OR REPLACE FUNCTION fn_auditoria_inmutable() RETURNS TRIGGER AS $$
BEGIN
    RAISE EXCEPTION 'La tabla % es de sólo inserción', TG_TABLE_NAME;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_auditoria_no_update
BEFORE UPDATE OR DELETE ON auditoria
FOR EACH ROW EXECUTE FUNCTION fn_auditoria_inmutable();

-- Garantías auto-creadas al confirmar una venta
CREATE OR REPLACE FUNCTION fn_crear_garantias_venta() RETURNS TRIGGER AS $$
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
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_crear_garantias_venta
AFTER UPDATE ON venta
FOR EACH ROW EXECUTE FUNCTION fn_crear_garantias_venta();

-- Auditoría automática sobre operaciones importantes
CREATE TRIGGER trg_aud_venta AFTER INSERT OR UPDATE OR DELETE ON venta
    FOR EACH ROW EXECUTE FUNCTION fn_auditar_cambio('id_venta');
CREATE TRIGGER trg_aud_usuario AFTER INSERT OR UPDATE OR DELETE ON usuario
    FOR EACH ROW EXECUTE FUNCTION fn_auditar_cambio('id_usuario');
CREATE TRIGGER trg_aud_producto AFTER INSERT OR UPDATE OR DELETE ON producto
    FOR EACH ROW EXECUTE FUNCTION fn_auditar_cambio('id_producto');
CREATE TRIGGER trg_aud_variante_producto AFTER INSERT OR UPDATE OR DELETE ON variante_producto
    FOR EACH ROW EXECUTE FUNCTION fn_auditar_cambio('id_variante');
CREATE TRIGGER trg_aud_cliente AFTER INSERT OR UPDATE OR DELETE ON cliente
    FOR EACH ROW EXECUTE FUNCTION fn_auditar_cambio('id_cliente');
CREATE TRIGGER trg_aud_cotizacion AFTER INSERT OR UPDATE OR DELETE ON cotizacion
    FOR EACH ROW EXECUTE FUNCTION fn_auditar_cambio('id_cotizacion');
CREATE TRIGGER trg_aud_movimiento_inventario AFTER INSERT OR UPDATE OR DELETE ON movimiento_inventario
    FOR EACH ROW EXECUTE FUNCTION fn_auditar_cambio('id_movimiento');
CREATE TRIGGER trg_aud_tipo_cambio AFTER INSERT OR UPDATE OR DELETE ON tipo_cambio
    FOR EACH ROW EXECUTE FUNCTION fn_auditar_cambio('id_tipo_cambio');

-- ---------------------------------------------------------------------
-- 1.21 Datos iniciales
-- ---------------------------------------------------------------------
INSERT INTO permiso (modulo, accion)
SELECT m.modulo, a.accion
FROM unnest(ARRAY[
        'productos','inventario','clientes','cotizaciones',
        'ventas','garantias','tipo_cambio','reportes','servicio_tecnico'
     ]::text[]) AS m(modulo)
CROSS JOIN unnest(ARRAY['LEER','CREAR','EDITAR','APROBAR']::accion_permiso[]) AS a(accion);

INSERT INTO rol_permiso (nombre_rol, id_permiso)
SELECT 'GERENTE'::nombre_rol, id_permiso FROM permiso;

INSERT INTO rol_permiso (nombre_rol, id_permiso)
SELECT 'ADMINISTRADOR'::nombre_rol, id_permiso FROM permiso
WHERE (modulo IN ('productos','inventario','clientes','cotizaciones',
                  'ventas','garantias','tipo_cambio','servicio_tecnico')
       AND accion IN ('LEER','CREAR','EDITAR'))
   OR (modulo = 'reportes' AND accion IN ('LEER','CREAR'));

INSERT INTO rol_permiso (nombre_rol, id_permiso)
SELECT 'VENDEDOR'::nombre_rol, id_permiso FROM permiso
WHERE (modulo IN ('productos','inventario','garantias','tipo_cambio') AND accion = 'LEER')
   OR (modulo = 'clientes' AND accion IN ('LEER','CREAR','EDITAR'))
   OR (modulo IN ('cotizaciones','ventas') AND accion IN ('LEER','CREAR'));

INSERT INTO rol_permiso (nombre_rol, id_permiso)
SELECT 'SERVICIO_TECNICO'::nombre_rol, id_permiso FROM permiso
WHERE (modulo IN ('productos','inventario') AND accion = 'LEER')
   OR (modulo IN ('garantias','servicio_tecnico') AND accion IN ('LEER','EDITAR','CREAR'));

INSERT INTO parametro_sistema (clave, valor, tipo_dato, descripcion) VALUES
  ('COTIZACION_VIGENCIA_DIAS', '14',       'INTEGER', 'Vigencia por defecto de una cotización'),
  ('RESERVA_VIGENCIA_DIAS',    '30',       'INTEGER', 'Vigencia máxima de una reserva'),
  ('TIPO_CAMBIO_POR_DEFECTO',  'PARALELO', 'TEXT',    'Tipo de cambio por defecto');

-- Usuario admin de demo (contraseña: admin123)
INSERT INTO usuario (nombre_completo, nombre_usuario, contrasena_hash, nombre_rol,
                     porcentaje_comision_pct)
VALUES ('Administrador Demo', 'admin',
        crypt('admin123', gen_salt('bf', 12)),
        'GERENTE', 0);

COMMIT;

-- =====================================================================
-- PASO 2 — Roles técnicos de PostgreSQL
-- =====================================================================
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'ewiflor_owner') THEN
        CREATE ROLE ewiflor_owner NOLOGIN;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'ewiflor_app') THEN
        CREATE ROLE ewiflor_app LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE NOREPLICATION;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'ewiflor_backup') THEN
        CREATE ROLE ewiflor_backup LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE NOREPLICATION;
    END IF;
END $$;

ALTER SCHEMA public OWNER TO ewiflor_owner;
REVOKE CREATE ON SCHEMA public FROM PUBLIC;

DO $$
DECLARE r RECORD;
BEGIN
    -- Tablas y vistas
    FOR r IN
        SELECT c.relname, c.relkind FROM pg_class c
          JOIN pg_namespace n ON n.oid = c.relnamespace
         WHERE n.nspname = 'public' AND c.relkind IN ('r','v')
           AND NOT EXISTS (SELECT 1 FROM pg_depend d WHERE d.objid = c.oid AND d.deptype = 'e')
    LOOP
        IF r.relkind = 'v' THEN
            EXECUTE format('ALTER VIEW public.%I OWNER TO ewiflor_owner', r.relname);
        ELSE
            EXECUTE format('ALTER TABLE public.%I OWNER TO ewiflor_owner', r.relname);
        END IF;
    END LOOP;

    -- Tipos y dominios
    FOR r IN
        SELECT t.typname, t.typtype FROM pg_type t
          JOIN pg_namespace n ON n.oid = t.typnamespace
         WHERE n.nspname = 'public' AND t.typtype IN ('d','e')
           AND NOT EXISTS (SELECT 1 FROM pg_depend d WHERE d.objid = t.oid AND d.deptype = 'e')
    LOOP
        IF r.typtype = 'd' THEN
            EXECUTE format('ALTER DOMAIN public.%I OWNER TO ewiflor_owner', r.typname);
        ELSE
            EXECUTE format('ALTER TYPE public.%I OWNER TO ewiflor_owner', r.typname);
        END IF;
    END LOOP;

    -- Funciones
    FOR r IN
        SELECT p.proname, pg_get_function_identity_arguments(p.oid) AS args
          FROM pg_proc p JOIN pg_namespace n ON n.oid = p.pronamespace
         WHERE n.nspname = 'public' AND p.prokind = 'f'
           AND NOT EXISTS (SELECT 1 FROM pg_depend d WHERE d.objid = p.oid AND d.deptype = 'e')
    LOOP
        EXECUTE format('ALTER FUNCTION public.%I(%s) OWNER TO ewiflor_owner',
                       r.proname, r.args);
    END LOOP;
END $$;

GRANT USAGE ON SCHEMA public TO ewiflor_app, ewiflor_backup;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO ewiflor_app;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO ewiflor_app;
GRANT USAGE ON TYPE nombre_rol TO ewiflor_app;

-- Restricciones: auditoría, tipo_cambio y permisos de sólo lectura para la app
REVOKE UPDATE, DELETE ON auditoria FROM ewiflor_app;
REVOKE UPDATE, DELETE ON tipo_cambio FROM ewiflor_app;
REVOKE INSERT, UPDATE, DELETE ON permiso, rol_permiso FROM ewiflor_app;
GRANT SELECT ON permiso, rol_permiso TO ewiflor_app;

-- Backup read-only
GRANT SELECT ON ALL TABLES IN SCHEMA public TO ewiflor_backup;
GRANT SELECT ON ALL SEQUENCES IN SCHEMA public TO ewiflor_backup;

-- Default privileges
ALTER DEFAULT PRIVILEGES FOR ROLE ewiflor_owner IN SCHEMA public
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO ewiflor_app;
ALTER DEFAULT PRIVILEGES FOR ROLE ewiflor_owner IN SCHEMA public
    GRANT USAGE, SELECT ON SEQUENCES TO ewiflor_app;
ALTER DEFAULT PRIVILEGES FOR ROLE ewiflor_owner IN SCHEMA public
    GRANT SELECT ON TABLES TO ewiflor_backup;
ALTER DEFAULT PRIVILEGES FOR ROLE ewiflor_owner IN SCHEMA public
    GRANT SELECT ON SEQUENCES TO ewiflor_backup;

-- =====================================================================
-- PASO 3 — Límites por rol
-- =====================================================================
ALTER ROLE ewiflor_app SET statement_timeout = '30s';
ALTER ROLE ewiflor_app SET lock_timeout = '5s';
ALTER ROLE ewiflor_app SET idle_in_transaction_session_timeout = '60s';
ALTER ROLE ewiflor_app SET search_path = public;
ALTER ROLE ewiflor_app CONNECTION LIMIT 50;

ALTER ROLE ewiflor_backup SET statement_timeout = 0;
ALTER ROLE ewiflor_backup SET lock_timeout = 0;
ALTER ROLE ewiflor_backup SET idle_in_transaction_session_timeout = 0;
ALTER ROLE ewiflor_backup SET search_path = public;
ALTER ROLE ewiflor_backup CONNECTION LIMIT 5;

