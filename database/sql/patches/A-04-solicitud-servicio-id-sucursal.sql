-- A-04 — Sucursal de la solicitud de servicio
--
-- Contexto: solicitud_servicio no tenía id_sucursal y no podía derivarse de
-- forma fiable (cliente no tiene sucursal; id_garantia e id_usuario_registra
-- son nullable). ADR-006 define la regla de alcance que usa esta columna.
--
-- La columna es nullable a propósito: las filas históricas cuya sucursal no
-- puede resolverse quedan NULL y solo GERENTE las ve (fail-closed).
--
-- Aplicar con el rol migrador:
--   psql ... -f database/sql/patches/A-04-solicitud-servicio-id-sucursal.sql

ALTER TABLE public.solicitud_servicio
    ADD COLUMN IF NOT EXISTS id_sucursal INTEGER
    REFERENCES public.sucursal (id_sucursal) ON DELETE RESTRICT;

-- 1) Solicitudes en garantía: la sucursal es la de la venta del detalle.
UPDATE public.solicitud_servicio s
   SET id_sucursal = v.id_sucursal
  FROM public.garantia g
  JOIN public.detalle_venta dv ON dv.id_detalle_venta = g.id_detalle_venta
  JOIN public.venta v          ON v.id_venta = dv.id_venta
 WHERE s.id_garantia = g.id_garantia
   AND s.id_sucursal IS NULL;

-- 2) Solicitudes internas: la sucursal de quien las registró.
UPDATE public.solicitud_servicio s
   SET id_sucursal = u.id_sucursal
  FROM public.usuario u
 WHERE s.id_usuario_registra = u.id_usuario
   AND u.id_sucursal IS NOT NULL
   AND s.id_sucursal IS NULL;

-- 3) Ya asignadas a un técnico: la sucursal de él.
UPDATE public.solicitud_servicio s
   SET id_sucursal = u.id_sucursal
  FROM public.usuario u
 WHERE s.id_tecnico = u.id_usuario
   AND u.id_sucursal IS NOT NULL
   AND s.id_sucursal IS NULL;

CREATE INDEX IF NOT EXISTS idx_solicitud_sucursal
    ON public.solicitud_servicio (id_sucursal);
