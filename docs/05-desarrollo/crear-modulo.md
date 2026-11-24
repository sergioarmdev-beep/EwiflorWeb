# Cómo crear un módulo

**Propósito:** reproducir la convención modular nativa de EwiflorWeb sin
duplicar modelos ni sacar reglas de negocio de PostgreSQL a PHP.

Reglas duras: ver `AGENTS.md`. Toda la lógica de negocio vive en
`Modules/<Modulo>/` con namespace `Modules\<Modulo>\...`. No se crean
modelos ni controladores de negocio en `app/` (solo utilidades globales).

---

## 1. Estructura

```
Modules/<Modulo>/
├── Controllers/
├── Models/
├── Requests/
├── Routes/web.php
├── Views/
├── Providers/<Modulo>ServiceProvider.php
├── Services/            # cuando aplique
└── Database/Migrations/ # cuando aplique
```

Ejemplo mínimo de provider (copiar la forma usada por `Comisiones`):

```php
<?php

namespace Modules\Ejemplo\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class EjemploServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Views', 'ejemplo');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        Route::middleware('web')->group(__DIR__.'/../Routes/web.php');
    }
}
```

Registrar el provider en `bootstrap/providers.php`. **No** tocar
`routes/web.php`: solo redirige `/` a `catalogo.publico`.

Las vistas se invocan con el prefijo del provider:
`view('ejemplo::listado')`. El layout global es
`resources/views/layouts/app.blade.php`.

---

## 2. Rutas

Declarar siempre las rutas estáticas antes que las dinámicas; las dinámicas
llevan `whereNumber('id')`. Esta colisión ya ocurrió en Clientes.

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\Ejemplo\Controllers\EjemploController;

Route::get('/ejemplo/crear', [EjemploController::class, 'crear'])
    ->name('ejemplo.crear');

Route::get('/ejemplo/{id}', [EjemploController::class, 'ver'])
    ->whereNumber('id')
    ->name('ejemplo.ver');
```

El middleware `web` ya aplica `SetDatabaseUser`, que fija
`set_config('app.id_usuario', ...)` para la auditoría por triggers. Sin
usuario autenticado el actor queda `NULL`.

---

## 3. Modelos

Cada tabla tiene **un único módulo dueño**; los demás importan ese modelo.
Consultar `docs/05-desarrollo/ubicacion-modelos.md` antes de crear uno.
Ejemplos: `Sucursal` pertenece a `Inventario`, `TipoCambio` a `Precios`,
`Cotizacion` a `Cotizaciones`.

`existencia` tiene PK compuesta `(id_almacen, id_variante)` y no usa `id`
surrogate; en el modelo, `$incrementing = false` y `$primaryKey` es un array.

---

## 4. Reglas de negocio

Las operaciones transaccionales (stock, dinero, reservas, cancelación) viven
en funciones PostgreSQL: `registrar_venta`,
`registrar_movimiento_inventario`, `registrar_reserva`, `cancelar_venta`,
`convertir_cotizacion_a_venta`.

- Llamarlas **siempre** con `App\Support\DbFunction::call()`,
  `::scalar()` o `::query()`. Nunca con `DB::select()` crudo.
- El Service valida precondiciones, invoca la función PG y hace el
  post-procesamiento; no reimplementa la regla en PHP/Eloquent.
- Un `RAISE EXCEPTION` (SQLSTATE `P0001`) se convierte en
  `BusinessRuleException` → 422 JSON o flash `error` + `back()->withInput()`.
- La auditoría es 100% por triggers (`fn_auditar_cambio`). **No** crear
  Observers ni insertar en `auditoria` desde Laravel.

Los cambios de esquema no se hacen a mano en la BD de desarrollo: se añaden
a `database/sql/patches/` (fuente de verdad) o a una migración del módulo,
según corresponda.

---

## 5. Tests

- Lógica PHP pura y HTTP sin reglas PG: `tests/Unit` / `tests/Feature`
  (`composer test`).
- Reglas PG e integración módulo ↔ BD: `tests/Integration`, extendiendo
  `Tests\Integration\PostgresTestCase` (`composer test:pg`).
- `PostgresTestCase` abre una transacción en la conexión por defecto
  (`pgsql_test`) y la revierte al final: la auditoría es inmutable, así que
  borrar filas después no es una opción.
- Ninguna suite apunta a `Ewiflor_DB_MVP`. Ver ADR-005 y
  `docs/05-desarrollo/instalacion-local.md` (sección 6).

---

## 6. Checklist antes de abrir un PR

- [ ] Provider registrado en `bootstrap/providers.php`.
- [ ] Rutas estáticas antes de dinámicas; parámetros con `whereNumber`.
- [ ] Modelo importado del módulo dueño, sin duplicados.
- [ ] Reglas transaccionales llamadas vía `DbFunction`.
- [ ] Sin `DB::select()` para reglas de negocio y sin escritura en `auditoria`.
- [ ] Tests PG sobre `Ewiflor_DB_MVP_test` con rollback.
- [ ] `pint --test`, `composer test`, `composer test:pg` y `pnpm build` en verde.
- [ ] Documentación y ADR actualizados si cambia una decisión arquitectónica.
