-- =====================================================================
-- Parche A-02: Vinculación de repuestos y documentación de productos
-- =====================================================================

BEGIN;

-- Tabla para vincular repuestos a maquinaria
CREATE TABLE producto_repuesto (
    id_maquinaria INTEGER NOT NULL REFERENCES producto(id_producto) ON DELETE CASCADE,
    id_repuesto   INTEGER NOT NULL REFERENCES producto(id_producto) ON DELETE CASCADE,
    PRIMARY KEY (id_maquinaria, id_repuesto),
    CONSTRAINT ck_no_auto_vinculo CHECK (id_maquinaria <> id_repuesto)
);

-- Tabla para documentación de productos (txt, word)
CREATE TABLE producto_documento (
    id_documento   INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_producto    INTEGER NOT NULL REFERENCES producto(id_producto) ON DELETE CASCADE,
    nombre_original VARCHAR(150) NOT NULL,
    ruta_archivo   VARCHAR(255) NOT NULL,
    extension      VARCHAR(10) NOT NULL,
    fecha_subida   TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Índices para mejorar rendimiento
CREATE INDEX idx_producto_repuesto_repuesto ON producto_repuesto(id_repuesto);
CREATE INDEX idx_producto_documento_producto ON producto_documento(id_producto);

-- Auditoría
CREATE TRIGGER trg_aud_producto_repuesto AFTER INSERT OR UPDATE OR DELETE ON producto_repuesto
    FOR EACH ROW EXECUTE FUNCTION fn_auditar_cambio('id_maquinaria', 'id_repuesto');

CREATE TRIGGER trg_aud_producto_documento AFTER INSERT OR UPDATE OR DELETE ON producto_documento
    FOR EACH ROW EXECUTE FUNCTION fn_auditar_cambio('id_documento');

COMMIT;
