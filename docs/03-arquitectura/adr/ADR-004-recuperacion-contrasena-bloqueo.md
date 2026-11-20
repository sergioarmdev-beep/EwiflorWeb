# ADR-004 — Recuperación de contraseña asistida, login por usuario o correo, y bloqueo por intentos fallidos

- **Estado:** Aceptado
- **Fecha:** 2026-11-20
- **Última revisión:** 2026-11-20
- **Decisores:** Equipo EwiflorWeb + usuario
- **Módulo afectado:** `Modules/Auth/`
- **Motor de base de datos:** PostgreSQL

## Contexto

El módulo de autenticación original solo aceptaba login por `nombre_usuario` y no ofrecía ningún flujo de «olvidé mi contraseña»: no existía tabla de reseteo (ADR-001, D5), no se enviaban correos (el proyecto no envía mail) y no había protección contra fuerza bruta más allá de la sesión.

Se requirió implementar tres cosas aprobadas por el usuario:

1. **«Olvidé mi contraseña»**: formulario público que pide **correo + celular** registrados. Si coinciden, se notifica a un supervisor (nunca se cambia la clave directamente por el usuario).
2. **Login por identificador**: aceptar tanto `nombre_usuario` como `correo`.
3. **Rate limit / bloqueo de cuenta**: 3 intentos fallidos en menos de 10 minutos bloquean la cuenta durante 10 minutos (auto-liberación) y notifican a gerencia.

## Decisión

### 1. «Olvidé mi contraseña» — recuperación asistida, sin cambio directo

- Ruta pública: `GET/POST /olvide-mi-contrasena` (nombres `password.olvide` y `password.olvide.store`).
- Pide **correo** y **celular**. Si la combinación coincide con una cuenta registrada, se crea una `solicitud_restablecimiento` en estado `PENDIENTE` y se notifica a quienes pueden restablecer la clave.
- El usuario **nunca** cambia su contraseña aquí. Solo un gerente o administrador (según alcance) genera una clave temporal.
- La respuesta al usuario es idéntica exista o no la cuenta (evita enumerar correos/números registrados).
- Honeypot `sitio_web` y throttle `5,1` por minuto para proteger contra bots.

**Destinatarios de la notificación de solicitud:**

| Rol del solicitante | Notificar a |
|---------------------|-------------|
| GERENTE | todos los demás GERENTE activos |
| ADMINISTRADOR | todos los GERENTE activos |
| VENDEDOR / SERVICIO_TECNICO | todos los GERENTE activos + el ADMINISTRADOR de su misma sucursal |

Nunca se notifica al propio usuario origen.

### 2. Login por usuario o correo

- El campo de login se llama `identificador` (acepta `nombre_usuario` o `correo`, ambos normalizados a minúsculas).
- Antes de validar la contraseña se comprueba `usuario.estaBloqueado()` (bloqueo temporal por intentos fallidos).
- Errores genéricos: nunca se revela si el identificador existe.
- Throttle `10,1` por minuto en `POST /login`.

### 3. Bloqueo por intentos fallidos (3 en <10 min → 10 min)

- Nueva columna `usuario.bloqueado_hasta TIMESTAMPTZ`.
- Las reglas viven en la función PostgreSQL `fn_registrar_intento_acceso()` (atómica, segura bajo concurrencia), invocada vía `App\Support\DbFunction::call()`:
  - 3 fallos en ventana de 10 min → `bloqueado_hasta = now() + INTERVAL '10 minutes'`, devuelve `bloqueo_nuevo = TRUE`.
  - 4.º intento dentro del bloqueo → no re-notifica (`bloqueo_nuevo = FALSE`).
  - Acceso exitoso → limpia `bloqueado_hasta` y la ventana de fallos.
- `fn_desbloquear_usuario()` permite desbloqueo manual anticipado (a petición de un gerente/administrador).
- Tiempo de seguridad: las marcas de tiempo se escriben solo con `DB::raw('now()')` o defaults de BD; nunca `Carbon` hacia las columnas PG (desfase de zona horaria PHP/BD).
- **Destinatarios del aviso de bloqueo:** todos los GERENTE activos + los ADMINISTRADOR de la sucursal de la cuenta bloqueada. Nunca el origen.
- El bloqueo se libera solo a los 10 minutos (auto-release, no permanente ni manual).

### 4. Esquema y migración

- Nueva migración dentro del módulo Auth (no parche `database/sql/patches/`):
  `Modules/Auth/Database/Migrations/2026_11_20_000001_recuperacion_contrasena_y_bloqueo.php`
  con `$connection = 'pgsql_migrator'`.
- Columnas nuevas en `usuario`: `celular VARCHAR(20) NULL`, `bloqueado_hasta TIMESTAMPTZ NULL` (DB nullable para no romper filas existentes; obligatorio a nivel app).
- Tablas nuevas: `intento_acceso`, `solicitud_restablecimiento` (índice único parcial `estado='PENDIENTE'` por usuario), `notificacion`.
- Funciones PG: `fn_registrar_intento_acceso(INTEGER, VARCHAR, VARCHAR, BOOLEAN)` y `fn_desbloquear_usuario(INTEGER)`, con `GRANT EXECUTE` a `ewiflor_app`.
- Idempotente: se puede ejecutar más de una vez.

### 5. `usuario.celular` y `correo` requeridos a nivel app

- `StoreUserRequest` y `UpdateUserRequest`: `correo` y `celular` pasan de `nullable` a `required`, con regex celular `/^[0-9+()\-\s]{7,20}$/` y normalización en `prepareForValidation` (vía `Modules\Auth\Support\Celular`).
- Sin correo ni celular la cuenta no puede usar «Olvidé mi contraseña».

### 6. Centro de notificaciones in-app

- Tabla `notificacion` con tipos `SOLICITUD_CONTRASENA` y `BLOQUEO_CUENTA`.
- Campana en el encabezado del layout global (`layouts/app.blade.php`) con contador de no leídas (`$notificacionesNoLeidas` vía `View::composer` en `AuthServiceProvider`).
- Enlace en sidebar (Administración) y página `/notificaciones` (`NotificacionController@index`, `leer`, `leerTodas`).
- No hay envío de correo: todo es in-app.

### 7. Gestión de usuarios

- `UserController@resetPassword` ahora delega en `RestablecerContrasenaService::generarTemporal()` (hash + `debe_cambiar_contrasena=true` + limpia bloqueo + cierra solicitudes pendientes + marca notificaciones leídas).
- Nueva acción `UserController@desbloquear` (`PATCH /{usuario}/desbloquear`): guarda no self, solo si está bloqueada; llama `IntentoAccesoService::desbloquear()` y marca leídas las notificaciones `BLOQUEO_CUENTA` del actor.
- `UserController@resumen(User $actor)` agrega `bloqueados` (`whereRaw('bloqueado_hasta > now()')`) y `solicitudes_contrasena` (scoped `visiblesPara($actor)`).
- Vistas de usuarios: columna Celular, badge «Bloqueado / X min» + acción Desbloquear, filtros checkboxes «Bloqueadas» / «Sin recuperación», tarjeta «Claves olvidadas».

## Consecuencias

### Positivas

- Los usuarios pueden recuperar el acceso sin correos y sin cambiar su clave directamente (flujo asistido).
- Protección contra fuerza bruta con bloqueo temporal y notificación a gerencia.
- Centro de notificaciones reutilizable para otros módulos futuros.
- Reglas críticas (intentos, bloqueo) en PostgreSQL, invocadas vía `DbFunction`, sin duplicar lógica en PHP.

### Negativas / fricciones

- El bloqueo temporal (10 min) puede molestar si un usuario se equivoca repetidamente; se mitiga con el desbloqueo manual por gerencia.
- `usuario.celular` y `usuario.correo` son requeridos a nivel app pero nulables en DB: filas existentes sin estos datos no podrán usar «Olvidé mi contraseña» hasta que se completen.
- No hay tests automatizados para la lógica de negocio (phpunit fuerza SQLite; las funciones son PG-only). Verificación manual únicamente.

## Verificación

- Migración aplicada con `artisan migrate --database=pgsql_migrator` (325 ms).
- Esquema verificado contra `information_schema` con rol `ewiflor_app`.
- Probe funcional (transacción + rollback, 0 filas residuales): 3 fallos → bloqueo + `bloqueo_nuevo=true`; 4.º intento sin re-notificar; `fn_desbloquear_usuario` limpia; éxito reinicia ventana a 1.
- `pint --test Modules/Auth/` → PASSED.
- `artisan route:list` → todas las rutas registradas (estáticas antes de dinámicas).
- `artisan view:cache` → todas las vistas Blade compilan.
- 17 archivos PHP verificados con `php -l` → todos OK.

---

## Relación con ADR-001

La decisión D5 de ADR-001 («Sin password reset») queda **supersedida por esta decisión**: el proyecto ahora implementa un flujo de recuperación asistido (sin tabla de reseteo ni correo, pero con solicitud y notificación in-app).
