# ADR-006 — Alcance por sucursal en Servicio Técnico

- **Estado:** Aceptado (aprobado por el usuario el 2026-11-24)
- **Fecha:** 2026-11-24
- **Última revisión:** 2026-11-24
- **Decisores:** Equipo EwiflorWeb + usuario
- **Módulo afectado:** `Modules/ServicioTecnico/`, `Modules/Garantias/`, `Modules/Clientes/`
- **Motor de base de datos:** PostgreSQL

## Contexto

La oleada 1 cerró el IDOR del detalle con `SolicitudService::visiblePara()`, pero dejó explícita una deuda: GERENTE y ADMINISTRADOR ven todo y los técnicos mezclan solicitudes de todas las sucursales. El mapeo de la oleada 2 confirmó que el alcance no puede aplicarse solo en PHP:

- `solicitud_servicio` **no tiene `id_sucursal`** (`database/sql/v5-MVP.sql:356-374`).
- `cliente` tampoco tiene `id_sucursal` y un cliente puede comprar en varias sucursales.
- `id_garantia` es nullable y las solicitudes pagadas no lo traen.
- `id_usuario_registra` es NULL en solicitudes públicas; `id_tecnico` es NULL hasta aceptarlas.
- No hay FK de `solicitud_servicio` ni de `garantia` a `almacen`.

Hoy no hay forma fiable de responder «¿de qué sucursal es esta solicitud?» para una solicitud pública sin garantía. Los puntos sin filtro de sucursal son: `pendientesParaTecnico()`, `aceptar()`, `cerrar()`, `cancelar()`, `todas()`, `tecnicos()`, `garantiasPorVencer()`, `ClienteController::show()` y varios dashboards.

La regla de documentos comerciales (`AlcanceDocumentos`: GERENTE todo, ADMIN su sucursal, VENDEDOR sus documentos dentro de su sucursal) es la referencia a seguir; `User::puedeVerTodosLosDocumentos()` es más laxo y **no** debe mandar en este módulo.

## Decisión propuesta

### 1. Columna de sucursal en la solicitud

Añadir `solicitud_servicio.id_sucursal INTEGER NULL REFERENCES sucursal(id_sucursal)`:

- Parche canónico `database/sql/patches/A-04-solicitud-servicio-id-sucursal.sql` y migración equivalente con `$connection = 'pgsql_migrator'`.
- **Backfill** de filas existentes, en este orden:
  1. `id_garantia → garantia.id_detalle_venta → detalle_venta.id_venta → venta.id_sucursal`.
  2. `id_usuario_registra → usuario.id_sucursal`.
  3. `id_tecnico → usuario.id_sucursal`.
  4. Lo que quede sin resolver se deja NULL (solo GERENTE lo ve).

Nullable a propósito: no se inventa una sucursal para datos históricos ambiguos.

### 2. Asignación al crear

| Origen | Regla |
|--------|-------|
| Interna (`crearInterna`) | `id_sucursal = id_sucursal` del usuario que registra; sin sucursal → `BusinessRuleException` (fail-closed). |
| Pública con garantía | Sucursal de la venta a la que pertenece la garantía. |
| Pública sin garantía | Sucursal de la **última venta** del cliente; si el cliente no tiene ventas, NULL (solo GERENTE). |

La asignación se hace en el Service (validación de precondiciones), no reimplementando reglas transaccionalmente en PHP.

### 3. Regla de visibilidad y acción

| Rol | Alcance |
|-----|---------|
| GERENTE | Todas las solicitudes, cualquier sucursal. |
| ADMINISTRADOR | Solo solicitudes de `user.id_sucursal`. Sin sucursal → sin acceso. Puede ver/cancelar/aceptar/cerrar dentro de ella. |
| VENDEDOR | Solo las que él registró **y** de su sucursal. |
| SERVICIO_TECNICO | Solo las de su sucursal: `PENDIENTE` sin asignar (para aceptar) y las asignadas a él. No ve ni acepta pendientes de otra sucursal. |
| Público (cliente) | Sin cambios: por `id_cliente` de la sesión. |

La misma regla se aplica en **todos** los puntos, no solo en `show()`:

- `visiblePara()` (detalle, con 404).
- `pendientesParaTecnico()` (filtrar por sucursal del técnico).
- `todas()` y `tecnicos()` (alcance del listado de gestión).
- `aceptar()`, `cerrar()`, `cancelar()` (defensa en profundidad en el Service, además del middleware).
- `ConsultaPublicaService::garantiasPorVencer()` (evita filtrar nombre/teléfono de otras sucursales).
- `ClienteController::show()` (el listado de solicitudes ya revela descripción y estado).
- Las rutas de `cerrar` se alinean con el Service y la vista: hoy GERENTE/ADMIN reciben 403 aunque `CerrarSolicitudRequest`, `SolicitudService` y `show.blade.php` los consideran aptos.

### 4. Alternativa descartada

Derivar la sucursal en cada consulta sin columna nueva (join a garantía/venta/usuario): no cubre solicitudes públicas sin garantía, encarece los listados y deja reglas divergentes por consulta. Se descarta.

## Consecuencias

- Cambio de esquema con backfill no destructivo (UPDATE, sin borrados; la auditoría es inmutable).
- Requiere aprobación antes de ejecutar la migración en la BD de desarrollo.
- Las solicitudes huérfanas (sin sucursal derivable) dejan de ser visibles para técnicos y administradores; GERENTE conserva acceso para auditarlas.
- Los dashboards se incorporaron en la oleada 3: `DashboardAlcance` decide entre visión global (GERENTE), sucursal concreta y fail-closed; `DashboardStatsTrait` reutiliza `SolicitudService::aplicarAlcanceVisible()` para no duplicar la regla.

### 5. Consolidación de `CANCELADA` (parche A-05)

La migración que añadió `CANCELADA` no estaba en la fuente canónica. El parche
`A-05-solicitud-servicio-cancelada.sql` y su migración `2026_11_24_000003` la
incorporan: CHECK de estados, `motivo_cancelacion`, `cancelar_solicitud_servicio()`
y la transición terminal que impide reactivar canceladas. También elimina CHECKs
de enumeración duplicados y restaura el CHECK que exige cierre/resultado.

## Estado de implementación

- [x] Diagnóstico de lagunas de datos y puntos sin filtro.
- [x] Regla propuesta por rol y criterio de asignación.
- [x] Aprobación del usuario.
- [x] Parche A-04 + migración y backfill (BD de desarrollo: 16 filas, 9 con sucursal, 7 NULL; columna, índice y FK verificados).
- [x] Aplicación de la regla en servicios, controladores y vistas (creación, detalle, bandeja, gestión, técnicos, cierre, cancelación, garantías por vencer y `ClienteController::show`).
- [x] Verificación con BD de prueba: `AlcanceSolicitudTest` incluido en la suite PG (ADR-005), 9/9 tests OK.
- [x] Oleada 3: dashboards por rol/sucursal (`DashboardAlcance` + `DashboardStatsTrait`), saneamiento de Servicio Técnico (validación centralizada, garantías del cliente, `fecha_visita`) y parche A-05.
- [x] Suite PG ampliada con `DashboardAlcanceTest`, `CancelarSolicitudTest` y `GarantiaPertenenciaTest`: **25 tests, 97 aserciones OK**.
