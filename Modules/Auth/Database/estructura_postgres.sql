-- Equivalente en SQL (PostgreSQL) a la migración, por si prefieres ejecutarlo a mano.
-- Es seguro ejecutarlo más de una vez.

ALTER TABLE usuario
    ADD COLUMN IF NOT EXISTS debe_cambiar_contrasena BOOLEAN NOT NULL DEFAULT FALSE;

CREATE TABLE IF NOT EXISTS solicitud_cuenta (
    id_solicitud        SERIAL PRIMARY KEY,
    nombre_completo     VARCHAR(100) NOT NULL,
    correo              VARCHAR(100) NOT NULL,
    celular             VARCHAR(20)  NOT NULL,
    motivo              TEXT         NOT NULL,
    estado              VARCHAR(12)  NOT NULL DEFAULT 'PENDIENTE'
                        CHECK (estado IN ('PENDIENTE', 'APROBADA', 'RECHAZADA')),
    fecha_creacion      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_usuario_revisor  INTEGER REFERENCES usuario (id_usuario) ON DELETE SET NULL,
    fecha_revision      TIMESTAMP,
    nota_revision       VARCHAR(255),
    id_usuario_creado   INTEGER REFERENCES usuario (id_usuario) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS solicitud_cuenta_estado_idx ON solicitud_cuenta (estado);
CREATE INDEX IF NOT EXISTS solicitud_cuenta_correo_idx ON solicitud_cuenta (correo);
