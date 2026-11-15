# AGENTS.md — EwiflorWeb

> **Importante:** toda sesión que realice cambios significativos debe registrarlos en `memory.md` (raíz del repo): qué se hizo, archivos creados/modificados, decisiones de diseño y gotchas descubiertos. Entradas nuevas al final.

Sistema de gestión de inventario. Monolito modular: Laravel 13 + Blade + PostgreSQL 18, PHP ≥ 8.3 (verificado con 8.5), frontend Vite + Tailwind 4, paquetes JS con **pnpm**. Toda la documentación del equipo está en `docs/` (en español): SRS, ADRs y guías en `docs/05-desarrollo/`.

## Comandos

- Dev completo (serve + queue + pail + vite): `composer dev`
- Setup inicial: `composer setup`
- Frontend: `pnpm install` / `pnpm dev` / `pnpm build` (los scripts de composer.json dicen `npm`, pero el lockfile es de pnpm)
- Tests: `composer test` — un solo test: `php artisan test --filter=NombreTest`
- Lint PHP: `vendor/bin/pint`

## Arquitectura modular (reglas duras)

- Toda lógica de negocio vive en `Modules/<Modulo>/` con namespace `Modules\<Modulo>\...`. **Prohibido** crear modelos/controladores de negocio en `app/` (solo utilidades globales: middleware, excepciones, `app/Support/`).
- Estructura por módulo: `Controllers/`, `Models/`, `Requests/`, `Routes/web.php`, `Views/`, `Providers/`, y `Services/` cuando aplique.
- Un módulo nuevo requiere: ServiceProvider que carga rutas con `Route::middleware('web')->group(__DIR__.'/../Routes/web.php')` y vistas con `$this->loadViewsFrom(..., 'prefijo')`; registrar el provider en `bootstrap/providers.php`. Las vistas se referencian como `modulo::vista`.
- No tocar `routes/web.php` central (solo redirige `/` a `catalogo.publico`).
- Cada tabla tiene **un único módulo dueño** de su modelo; los demás módulos solo importan. Mapa en `docs/05-desarrollo/ubicacion-modelos.md` — verificar contra código, está algo desactualizado (ej. `Sucursal` ya vive en `Modules/Inventario`, cotización es de `Modules/Cotizaciones`).

## Base de datos — el patrón central

- La lógica transaccional (stock, dinero, garantías) vive en **funciones PostgreSQL**, no en Laravel: `registrar_venta`, `registrar_movimiento_inventario`, `registrar_reserva`, `cancelar_venta`, `convertir_cotizacion_a_venta`. Fuente de verdad del esquema: `database/sql/v5-MVP.sql` (+ `database/sql/patches/`).
- Llamarlas **siempre** vía `App\Support\DbFunction::call()/scalar()`. Nunca `DB::select()` crudo en controllers/modelos. `RAISE EXCEPTION` (SQLSTATE `P0001`) se convierte en `BusinessRuleException`, que `bootstrap/app.php` renderiza como 422 / flash de error.
- Los Services de módulo validan precondiciones, llaman la función PG y hacen post-procesamiento; no reimplementan las reglas en PHP/Eloquent.
- Auditoría 100% por triggers PG (`fn_auditar_cambio`). **No** crear Observers ni insertar en `auditoria` desde Laravel. El actor se identifica con `set_config('app.id_usuario', ...)` fijado por el middleware `SetDatabaseUser`.
- Las migraciones Laravel son marginales (mayoría del esquema viene del SQL). Los roles de DB son de mínimo privilegio: `ewiflor_app`, `ewiflor_migrator`, `ewiflor_auth`, `ewiflor_backup` (migrar usa la conexión `pgsql_migrator`).

## Tests — trampa conocida

- `phpunit.xml` fuerza **SQLite en memoria**, pero la lógica de negocio vive en funciones PostgreSQL → ningún test puede ejercitar `registrar_venta` ni funciones similares con la config actual. Solo existen los `ExampleTest` de Laravel.
- Para testear lógica de negocio hay que definir una estrategia de testing con PG primero; no lo improvises.

## Gotchas

- Crear ventas/movimientos exige que el usuario tenga `$actor->id_sucursal` (validación que repiten los services).
- Layout global único: `resources/views/layouts/app.blade.php`. El módulo `Garantias` es solo carpetas vacías (las garantías las crea el trigger SQL `fn_crear_garantias_venta`).
- En producción o con `FORCE_HTTPS=true` se fuerza HTTPS desde `AppServiceProvider`.
- Referencias clave: `docs/05-desarrollo/funciones-postgresql.md`, `docs/05-desarrollo/auditoria.md`, `docs/03-arquitectura/adr/`.
