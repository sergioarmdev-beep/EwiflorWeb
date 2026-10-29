# ADR-001 — Configuración del proyecto tras la simplificación v5-MVP del esquema

- **Estado:** Aceptado
- **Fecha:** 2026-10-29
- **Última revisión:** 2026-10-29 (incorporados parche A-01 y lecciones de puesta en marcha)
- **Decisores:** Equipo EwiflorWeb
- **Contexto:** El esquema PostgreSQL se simplificó al script `v5-MVP`,
  eliminando tablas, reemplazando la tabla `rol` por un enum `nombre_rol`,
  reduciendo los roles de PostgreSQL a `ewiflor_owner` (NOLOGIN),
  `ewiflor_app` y `ewiflor_backup`, y dejando fuera la tabla `migrations`
  y las tablas de infraestructura de Laravel. La estructura de tablas se
  conservará y, como mucho, aumentará.

  La base de datos resultante se llama **`Ewiflor_DB_MVP`** (no `ewiflor_db`,
  que era el nombre en el informe original). Cualquier documento, script o
  `.env` debe usar ese nombre. Se asume que `ewiflor_db` no existe o está
  descartada.

  Durante la puesta en marcha aparecieron tres ajustes que se incorporan como
  parte de esta decisión:
  1. Un **parche de configuración** (`v5-MVP-config.sql`) con permisos
     faltantes, rol migrador y tabla `migrations`.
  2. Un **parche estructural** (`A-01-reserva-id-almacen.sql`) que añade
     `reserva.id_almacen` para poder auditar qué almacén respalda cada
     reserva.
  3. Una **corrección operativa documentada** sobre el hash de `admin`
     (el `crypt()` de pgcrypto no es compatible con `Auth::attempt()` de
     Laravel en este stack).

## Decisión

Se adoptan las siguientes decisiones de configuración:

| #   | Decisión                               | Implementación                                                                                                                                                                               |
| --- | -------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| D1  | Rol migrador dedicado                  | `ewiflor_migrator` LOGIN `IN ROLE ewiflor_owner`. Laravel usa la conexión `pgsql_migrator` con `SET ROLE ewiflor_owner` para DDL.                                                            |
| D2  | Autenticación Eloquent sobre `usuario` | `App\Models\User` apunta a `usuario`, usa `nombre_usuario` como username, `contrasena_hash` como password, `Hash::check` de Laravel. `fn_autenticar_usuario` queda como respaldo, no se usa. |
| D3  | Drivers `file`/`sync` para arrancar    | `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`. No se crean `sessions`, `cache`, `jobs`. Se migrará a drivers database solo cuando el migrador esté validado.            |
| D4  | Sin "remember me"                      | No se añade `remember_token`; `Auth::attempt(..., true)` está prohibido.                                                                                                                     |
| D5  | Sin password reset                     | No se añade tabla de reseteo; se documenta en el manual de usuario.                                                                                                                          |
| D6  | Auditoría con contexto de sesión       | Middleware `SetDatabaseUser` publica `app.id_usuario` vía `set_config(...)`. El trigger `fn_auditar_cambio()` ya es `SECURITY DEFINER` con `search_path` fijo.                               |
| D7  | PK compuestas en modelos Eloquent      | `existencia`, `detalle_cotizacion`, `detalle_movimiento` no reciben `id` surrogate. Los modelos usarán `$primaryKey` compuesto y `$incrementing=false` cuando se implementen.                |
| D8  | Script SQL como fuente de verdad       | El esquema se versiona en `database/sql/v5-MVP*.sql`. Laravel solo administra la tabla `migrations`. Las migraciones de negocio se posponen.                                                 |

### D9 Parches estructurales versionados

Cualquier cambio al esquema `v5-MVP` se aplica como **parche numerado** en
`database/sql/patches/` (`A-01-...sql`, `A-02-...sql`, …) y se refleja aquí
en la tabla de "Parches aplicados". El script `v5-MVP.sql` **no se modifica
in-place** una vez que un entorno lo ha ejecutado: los cambios van por parche.
Cuando todos los entornos estén al día, se podrá consolidar un `v6-MVP.sql`
que integre los parches, y se anotará en este ADR.

## Parches aplicados sobre `v5-MVP`

| Parche | Fecha      | Contenido                                                                                                                                                                                                                                    | Estado   |
| ------ | ---------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------- |
| Config | 2026-10-29 | `v5-MVP-config.sql`: rol `ewiflor_migrator`, `GRANT USAGE` de 4 enums a `ewiflor_app`, `GRANT EXECUTE` de las 6 funciones de negocio, creación de tabla `migrations`, `ALTER DEFAULT PRIVILEGES` para `ewiflor_migrator`.                    | Aplicado |
| A-01   | 2026-10-29 | `A-01-reserva-id-almacen.sql`: añade `reserva.id_almacen` (FK a `almacen`), `ck_reserva_almacen` (`RESERVA` ⟺ `id_almacen IS NOT NULL`), índice parcial `idx_reserva_almacen`, y reemplaza `registrar_reserva()` para insertar `id_almacen`. | Aplicado |

## Correcciones operativas documentadas

Estas no son decisiones nuevas, sino incidencias encontradas al ejecutar el
plan y su resolución definitiva. Se documentan aquí para que no se repitan.

### C1 — Hash de `admin`

El `INSERT` de `admin` en `v5-MVP.sql` usa `crypt('admin123', gen_salt('bf', 12))`.
Aunque el hash cumple el `CHECK` de la tabla (`$2a$12$...`), `password_get_info()`
de PHP 8.5 no lo reconoce como bcrypt en este stack y `Auth::attempt()` lanza:

    RuntimeException: This password does not use the Bcrypt algorithm.

**Resolución (aplicada):** regenerar el hash con `Hash::make()` en cada entorno
tras ejecutar `v5-MVP.sql`:

```php
DB::statement("SELECT set_config('app.id_usuario', '1', false)");
$user = App\Models\User::where('nombre_usuario', 'admin')->first();
$user->contrasena_hash = Hash::make('admin123');
$user->save();
```
