# memory.md — Registro de cambios y decisiones

Bitácora acumulativa de cambios significativos realizados en el proyecto.
Cada entrada documenta: qué se hizo, archivos creados/modificados, decisiones de diseño y hallazgos operativos que no se ven en el código.

> Convención: entradas nuevas al final. Una entrada por sesión/feature. Incluir siempre los gotchas descubiertos.

---

## 2026-11-15 — Creación de `AGENTS.md`

- Se creó `AGENTS.md` en la raíz con instrucciones para agentes (OpenCode):
  - Comandos exactos (`composer dev`, `composer setup`, pnpm, tests, Pint).
  - Reglas de arquitectura modular (Modules/, un módulo dueño por tabla,ServiceProvider + `bootstrap/providers.php`).
  - Patrón central: lógica transaccional en funciones PostgreSQL vía `App\Support\DbFunction`, auditoría por triggers.
  - Trampa de tests: `phpunit.xml` usa SQLite en memoria → no puede ejercitar funciones PG.

---

## 2026-11-15 — Feature: módulo `Sucursales` (CRUD sucursales + almacenes + helper global de alcance)

### Qué se hizo
Nuevo módulo `Modules/Sucursales/` para gestionar sucursales y almacenes (crear, editar, activar/desactivar — **sin DELETE**), más un helper global para consultar la relación usuario ↔ sucursal.

### Archivos creados
```
Modules/Sucursales/
├── Providers/SucursalesServiceProvider.php
├── Support/AccesoSucursal.php    ← helper global de alcance
├── Support/Catalogos.php         ← catálogos (departamentos, tamaños)
├── Controllers/SucursalController.php
├── Controllers/AlmacenController.php
├── Services/SucursalService.php  ← reglas de desactivación
├── Requests/{Store,Update}{Sucursal,Almacen}Request.php  (4 archivos)
├── Routes/web.php                ← 12 rutas
└── Views/ (index, show, create, edit, _form, almacenes/{create,edit,_form_almacen})
```

### Archivos modificados
- `bootstrap/providers.php` → registrado `Modules\Sucursales\Providers\SucursalesServiceProvider`.
- `resources/views/layouts/app.blade.php` → ítem "Sucursales" en la sección Administración, visible solo para GERENTE/ADMINISTRADOR.

### Decisiones de diseño
- **Módulo nuevo, no dentro de Inventario:** `Sucursal`/`Almacen` siguen siendo propiedad de `Modules\Inventario\Models`; el módulo nuevo solo los importa (regla "un módulo dueño, los demás importan" de `docs/05-desarrollo/ubicacion-modelos.md`).
- **Permisos:** escritura con `role:GERENTE,ADMINISTRADOR`; lectura para cualquier autenticado pero con alcance filtrado.
- **Helper global de alcance** (`Modules\Sucursales\Support\AccesoSucursal`): GERENTE/ADMINISTRADOR ven TODAS las sucursales; los demás roles solo su `id_sucursal`. Importable desde cualquier módulo, igual que `App\Support\DbFunction`.
- **Lógica en PHP, no en PG:** esto es dato organizacional (no stock/dinero), por eso la lógica de desactivación vive en `SucursalService` con `DB::transaction()` — excepción documentada a la regla de funciones PostgreSQL.
- **Sin DELETE:** el esquema solo define `ACTIVO/INACTIVO` y hay FKs RESTRICT.
- **Sin migraciones ni funciones PG nuevas:** las tablas ya existen en `database/sql/v5-MVP.sql` con sus CHECK/UNIQUE.
- **Rutas con `whereNumber('sucursal'|'almacen')`** y `/crear` registrado ANTES de `/{sucursal}` para evitar colisión de binding.

### Reglas de negocio implementadas
- Desactivar **sucursal**: bloquea si tiene usuarios con `estado=ACTIVO` asignados (deben reasignarse/desactivarse en la sección Usuarios); si pasa, cascada `INACTIVO` a sus almacenes en transacción.
- Desactivar **almacén**: bloquea si tiene existencias con `cantidad_total > 0`.
- Reactivar: solo el elemento individual (los almacenes se reactivan uno a uno).
- Sin Observers: la auditoría la registra el trigger PG `fn_auditar_cambio` vía `SetDatabaseUser`.

### Gotchas descubiertos
- **El PHP del PATH (`where.exe php` → 8.2.12/XAMPP) NO sirve** para este proyecto (Composer exige ≥ 8.4.1). El intérprete correcto es **`C:\tools\php85\php.exe`** (PHP 8.5.12): usarlo para `artisan` y todo lo PHP.
- **Pint:** `vendor\bin\pint.bat` usa el PHP del PATH y falla; ejecutar con `C:\tools\php85\php.exe vendor\laravel\pint\builds\pint`.
- Los flash messages que el layout global renderiza son `session('success')` y `session('error')` (algunos módulos antiguos usan `'ok'` con banner propio en la vista).
- Las tablas `existencia` usan PK compuesta `(id_almacen, id_variante)`; bloquear desactivación de almacén consultando `cantidad_total > 0`.

### Verificación
- `php artisan route:list --path=sucursales` → 12 rutas OK.
- `php artisan view:cache` → las 8 vistas compilan sin errores.
- Pint aplicado sobre el módulo.
