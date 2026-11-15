# AGENTS.md — EwiflorWeb

Sistema de gestión de inventario. Monolito modular: Laravel 13 + Blade + PostgreSQL 18, PHP ≥ 8.3 (verificado con 8.5), frontend Vite + Tailwind 4, paquetes JS con **pnpm**. Base de datos: `Ewiflor_DB_MVP` (no `ewiflor_db`). Docs del equipo en `docs/` (español).

## Comandos

- Dev completo (serve + queue + pail + vite): `composer dev`
- Setup inicial: `composer setup`
- Frontend: `pnpm install` / `pnpm dev` / `pnpm build` (los scripts de composer.json dicen `npm`, pero el lockfile es de pnpm)
- Tests: `composer test` — ejecuta `config:clear` antes. Uno solo: `php artisan test --filter=NombreTest`
- Lint PHP: `vendor/bin/pint`

> **Entorno local (Windows):** el `php` del PATH es XAMPP 8.2 y **no sirve** (Composer exige ≥ 8.4.1). Usar `C:\tools\php85\php.exe` (8.5.12) para `artisan`, `composer` y Pint. `vendor/bin/pint.bat` falla directo: `C:\tools\php85\php.exe vendor\laravel\pint\builds\pint`.

## Arquitectura modular (reglas duras)

- Toda lógica de negocio vive en `Modules/<Modulo>/` con namespace `Modules\<Modulo>\...`. **Prohibido** crear modelos/controladores de negocio en `app/` (solo utilidades globales: middleware, excepciones, `app/Support/`).
- Estructura por módulo: `Controllers/`, `Models/`, `Requests/`, `Routes/web.php`, `Views/`, `Providers/`, `Services/` cuando aplique.
- Módulo nuevo: ServiceProvider que carga rutas con `Route::middleware('web')->group(__DIR__.'/../Routes/web.php')` y vistas con `$this->loadViewsFrom(..., 'prefijo')`; registrar el provider en `bootstrap/providers.php`. Vistas: `modulo::vista`.
- No tocar `routes/web.php` central (solo redirige `/` a `catalogo.publico`).
- Cada tabla tiene **un único módulo dueño** de su modelo; los demás solo importan. Mapa: `docs/05-desarrollo/ubicacion-modelos.md` — verificar contra código, está algo desactualizado.

## Base de datos — el patrón central

- Lógica transaccional (stock, dinero, reservas, cancelación) en **funciones PostgreSQL**: `registrar_venta`, `registrar_movimiento_inventario`, `registrar_reserva`, `cancelar_venta`, `convertir_cotizacion_a_venta`. Fuente de verdad: `database/sql/v5-MVP.sql` + `database/sql/patches/` (el script base no se modifica in-place; los cambios van en parches numerados, ej. `A-01`).
- Llamarlas **siempre** vía `App\Support\DbFunction::call()/scalar()/query()`. Nunca `DB::select()` crudo en controllers/modelos para reglas de negocio. `RAISE EXCEPTION` (SQLSTATE `P0001`) → `BusinessRuleException` → 422 JSON / flash `error` + `back()->withInput()`.
- Los Services validan precondiciones, llaman la función PG y hacen post-procesamiento; no reimplementan reglas en PHP/Eloquent.
- Migraciones Laravel marginales (sobra la tabla `migrations`). DDL corre con el rol migrador: `... --database=pgsql_migrator`. Roles mínimos: `ewiflor_app`, `ewiflor_migrator` (`IN ROLE ewiflor_owner`), `ewiflor_auth`, `ewiflor_backup`.
- Auditoría 100% por triggers PG (`fn_auditar_cambio`). **No** crear Observers ni insertar en `auditoria` desde Laravel. El actor se fija con `set_config('app.id_usuario', ...)` vía middleware `SetDatabaseUser` (grupo `web`); sin usuario autenticado queda `NULL`.

## Tests — trampa conocida

- `phpunit.xml` fuerza **SQLite en memoria**, pero la lógica de negocio vive en PostgreSQL → ningún test ejerce `registrar_venta` ni funciones similares con esta config. Solo existen `tests/ExampleTest`.
- Para probar lógica de negocio falta definir estrategia con PG primero; no improvisar.

## Gotchas

- Ventas/movimientos exigen que el actor tenga `$actor->id_sucursal` (los services lo validan; sin él, `BusinessRuleException`).
- `existencia` usa **PK compuesta** `(id_almacen, id_variante)` sin `id` surrogate (`$incrementing = false`).
- Rutas estáticas (`/crear`) **antes** de dinámicas `/{id}` con `whereNumber(...)` (ya causó colisión de binding en Clientes).
- Flash: el layout global renderiza `session('success')` y `session('error')`; Ventas/Clientes/Cotizaciones usan `session('ok')` con banner propio en sus vistas.
- Layout global único: `resources/views/layouts/app.blade.php` (tema oscuro por defecto). `Garantias` solo tiene carpetas vacías (el trigger SQL `fn_crear_garantias_venta` crea las garantías).
- `FORCE_HTTPS=true` (o producción) fuerza HTTPS desde `AppServiceProvider`.
- Usuario `admin` de `v5-MVP.sql`: su hash pgcrypto `crypt()` no pasa `Auth::attempt()` en PHP 8.5 (`RuntimeException: This password does not use the Bcrypt algorithm`). Regenerar con `Hash::make('admin123')` tras cargar el SQL si no puede entrar.
- Toda sesión con cambios significativos se registra en `memory.md` (raíz): qué se hizo, archivos, decisiones y gotchas. Entradas nuevas al final.

## Referencias

- `docs/05-desarrollo/funciones-postgresql.md`, `docs/05-desarrollo/auditoria.md`, `docs/05-desarrollo/instalacion-local.md` (v1.2.1, aprobado), `docs/03-arquitectura/adr/`.
