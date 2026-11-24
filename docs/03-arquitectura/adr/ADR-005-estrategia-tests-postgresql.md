# ADR-005 — Estrategia de pruebas automatizadas sobre PostgreSQL

- **Estado:** Aceptado e implementado
- **Fecha:** 2026-11-24
- **Última revisión:** 2026-11-24
- **Decisores:** Equipo EwiflorWeb + usuario
- **Módulo afectado:** `tests/`, `config/database.php`, `phpunit.xml`, `phpunit.pg.xml`, `composer.json`, `.github/workflows/ci.yml`
- **Motor de base de datos:** PostgreSQL

## Contexto

`phpunit.xml` fuerza SQLite en memoria (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`), pero la lógica de negocio del proyecto no está en PHP/Eloquent: está en funciones PostgreSQL (`registrar_venta`, `registrar_movimiento_inventario`, `registrar_reserva`, `cancelar_venta`, `convertir_cotizacion_a_venta`, `fn_registrar_intento_acceso`, etc.), invocadas siempre vía `App\Support\DbFunction`.

Consecuencias observadas:

1. Ningún test existente ejerce esas reglas; solo hay `tests/Unit/ExampleTest.php` y `tests/Feature/ExampleTest.php` (ambos del scaffold).
2. El `Feature\ExampleTest` falla con `PDOException: could not find driver`: el PHP 8.5 del PATH (`C:\tools\php85\php.exe`) solo trae `pdo_pgsql`, no `pdo_sqlite`.
3. Los artefactos de prueba no pueden limpiarse con `DELETE`: la tabla `auditoria` es inmutable por trigger (`fn_auditoria_inmutable`), incluso para el rol migrador.

`AGENTS.md` establece: *«Para probar lógica de negocio falta definir estrategia con PG primero; no improvisar»*. Este ADR la define.

## Decisión

### 1. Dos suites separadas, un mismo destino PG

| Suite | Configuración | Base de datos | Qué cubre |
|-------|---------------|---------------|-----------|
| `Unit`, `Feature` | `phpunit.xml` | `pgsql_test` → `Ewiflor_DB_MVP_test` | lógica PHP pura, helpers, HTTP que no toca reglas PG |
| `Integration` | `phpunit.pg.xml` | `pgsql_test` → `Ewiflor_DB_MVP_test` | funciones PG, reglas transaccionales, integración módulo ↔ BD |

Se descarta SQLite: el PHP de referencia no trae `pdo_sqlite` y un driver distinto no puede ejercitar las funciones PostgreSQL. Las dos configuraciones comparten la conexión `pgsql_test`, definida en `config/database.php` con variables `DB_TEST_*` (con respaldo en `DB_*` para el entorno local). El nombre de la BD está fijado en los XML: ninguna suite puede apuntar accidentalmente a `Ewiflor_DB_MVP`. `<source>` incluye `app/` **y** `Modules/`, porque ahí vive la lógica de negocio.

### 2. Base de datos de prueba dedicada

- Nombre: **`Ewiflor_DB_MVP_test`** (nunca `Ewiflor_DB_MVP`).
- Se crea explícitamente con un rol con `CREATEDB` (en este cluster: `postgres`, `ADMIN` o `cloud`) y se carga desde la fuente de verdad: `database/sql/v5-MVP.sql` + `database/sql/patches/` en orden (`A-01`, `A-02`, `A-03`, `A-04`, ...). `ewiflor_migrator` **no tiene `CREATEDB`** y no puede crearla.
- Usa los mismos roles (`ewiflor_app`, `ewiflor_migrator`, `ewiflor_auth`, `ewiflor_backup`) y grants; la app conecta con `ewiflor_app`.
- **Crear y cargar esta BD requiere aprobación explícita**; este ADR no la crea.

### 3. Aislamiento por test

- Cada test `Integration` corre dentro de una transacción externa y hace `ROLLBACK` en `tearDown()` (`Tests\Integration\PostgresTestCase`).
- No se usa `RefreshDatabase` ni `Migration` por test: el esquema se carga una vez al preparar la BD de prueba.
- El rollback evita dejar filas en `auditoria` (que no se pueden borrar) y hace los tests repetibles.
- Los datos de cada caso se crean dentro de su propia transacción; ningún test depende de IDs de la BD de desarrollo (p. ej. la venta 46 real).

### 4. Configuración y ejecución

- `phpunit.xml` y `phpunit.pg.xml`: definen `DB_CONNECTION=pgsql_test` y `DB_TEST_DATABASE=Ewiflor_DB_MVP_test`; el segundo limita la suite a `tests/Integration`. Usuario/contraseña se toman de `.env`/`DB_TEST_*` (no se versionan). `composer test` y `composer test:pg` usan la misma BD.
- Script Composer `test:pg`: limpia caché de configuración y ejecuta `vendor/phpunit/phpunit/phpunit -c phpunit.pg.xml` con el PHP de `C:\tools\php85` (evita el problema del PATH con XAMPP 8.2, igual que Pint).
- Los tests PG se escriben en español y siguen el patrón `[OK]/[FAIL]` de los scripts temporales usados hasta ahora, pero con aserciones PHPUnit reales.

### 5. Alcance inicial de los tests

Una vez aprobada la BD, cubrir como mínimo:

- `convertir_cotizacion_a_venta`: propaga `garantia_meses` (regresión de la oleada 1) y rechaza almacén de otra sucursal.
- `registrar_venta`: descuenta stock, respeta alcance por sucursal y rechaza almacén ajeno.
- `fn_registrar_intento_acceso`: 3 fallos → bloqueo, 4.º no re-notifica, éxito limpia la ventana.
- `visiblePara()` del módulo Servicio Técnico (regla de la oleada 2).

## Consecuencias

- **Positivas:** las reglas de negocio dejan de estar sin cobertura automatizada; los fallos de regresión SQL (como `garantia_meses`) se detectan antes de desplegar; no se ensucia la BD de desarrollo.
- **Negativas:** requiere un paso manual de aprovisionamiento de BD; la suite es más lenta que SQLite; dos archivos de configuración hay que mantener sincronizados.
- **Riesgo mitigado:** `Ewiflor_DB_MVP_test` es independiente; un rollback fallido no afecta datos reales, pero debe verificarse el conteo de filas antes/después en cada corrida.

## Estado de implementación

- [x] Definición de la estrategia (este ADR).
- [x] `phpunit.pg.xml`, `Tests\Integration\PostgresTestCase` y script `test:pg` preparados.
- [x] Crear y cargar `Ewiflor_DB_MVP_test`. El usuario autorizó usar la credencial local de `postgres`; `ewiflor_migrator` sigue sin `CREATEDB`, pero eso ya no bloquea la creación. Se cargó `v5-MVP.sql` + configuración + parches `A-01`…`A-04` y las 19 migraciones Laravel. Verificación: 37 tablas, 8 vistas, `solicitud_servicio.id_sucursal`, `idx_solicitud_sucursal`, funciones críticas y grants de `ewiflor_app`. `Ewiflor_DB_MVP` quedó idéntica (16 solicitudes antes y después).
- [x] Escribir los tests de la sección 5: `IntentoAccesoTest`, `RegistrarVentaTest`, `ConvertirCotizacionTest` y `AlcanceSolicitudTest`. `composer test:pg` → **OK (9 tests, 45 assertions)**; rollback verificado (BD de prueba queda sin filas de negocio).
- [x] Oleada 4: conexión `pgsql_test`, ambas suites sobre `Ewiflor_DB_MVP_test`, `<source>` con `Modules/`, suite PG en **33 tests / 139 aserciones** y CI con aprovisionamiento desechable (`.github/workflows/ci.yml`, `tools/ci/prepare-test-db.sh`).

### Notas de aprovisionamiento

- El nombre de la BD debe ir entre comillas en `CREATE DATABASE`; sin ellas PostgreSQL lo crea en minúsculas (`ewiflor_db_mvp_test`). La tentativa fallida se eliminó antes de crear la BD definitiva; no se cargó SQL en ella.
- `2026_11_18_000002_id_comision_auto_incremental` no puede ejecutarse sobre un esquema fresco: la migración `...crear_tabla_comisiones` ya crea `id_comision` con `nextval(...)`. Se verificó el efecto y se marcó como aplicada en la BD de prueba (no se modificó la migración).
- Los tests crean sus propios datos dentro de la transacción; la BD de prueba solo tiene el usuario `admin` del SQL canónico.
- `composer test:pg` debe ejecutarse con PHP 8.5: `C:\tools\php85\php.exe C:\ProgramData\ComposerSetup\bin\composer.phar test:pg`. Con `composer.bat` del PATH se usaría XAMPP 8.2.
