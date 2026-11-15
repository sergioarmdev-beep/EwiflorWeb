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

---

## 2026-11-15 — Feature: localización de sucursales (Leaflet + OpenStreetMap)

### Qué se hizo
Campo de ubicación geográfica (latitud/longitud) para sucursales, con picker de mapa en el formulario, mapa visual reutilizable y endpoint JSON para el frontend futuro.

### Archivos creados
- `Modules/Sucursales/Database/Migrations/2026_11_15_000001_agregar_latitud_longitud_a_sucursal_table.php` — `latitud`/`longitud` `DOUBLE PRECISION` nulables con CHECK (±90/±180), idempotente, **sin PostGIS** (columnas separadas).
- `Modules/Sucursales/Views/partials/leaflet-assets.blade.php` — CDN de Leaflet 1.9.4 (con `@once` para no duplicar).
- `Modules/Sucursales/Views/partials/mapa-picker.blade.php` — picker para el formulario: click/arrastrable, inputs ocultos `latitud`/`longitud`, botones "Mi ubicación" (geolocalización) y "Quitar ubicación", lectura en vivo de coordenadas, centrado en Bolivia por defecto.
- `Modules/Sucursales/Views/partials/mapa-sucursales.blade.php` — componente reutilizable: mapa con marker por sucursal (verde=ACTIVA, rojo=INACTIVA), popup con nombre/estado/dirección/enlace a detalle, `fitBounds` o zoom 15 si hay una sola. Incluible: `@include('sucursales::partials.mapa-sucursales', ['sucursales' => ...])`.

### Archivos modificados
- `Modules/Inventario/Models/Sucursal.php` — `latitud`/`longitud` en `$fillable` + cast `float` (único touch al modelo dueño).
- `Modules/Sucursales/Requests/{Store,Update}SucursalRequest.php` — reglas `nullable|numeric|between` + `prepareForValidation` (vacío → null).
- `Modules/Sucursales/Providers/SucursalesServiceProvider.php` — ahora llama `loadMigrationsFrom`.
- `Modules/Sucursales/Support/AccesoSucursal.php` — nuevo `sucursalesParaMapa(User)`: solo sucursales con coordenadas, respetando alcance.
- `Modules/Sucursales/Controllers/SucursalController.php` — nuevo `mapa()` → JSON `GET /sucursales/mapa` (`sucursales.mapa`), para `fetch` del frontend futuro.
- `Modules/Sucursales/Routes/web.php` — ruta `sucursales.mapa` (grupo auth, alcance filtrado).
- `Modules/Sucursales/Views/_form.blade.php` — sección "Ubicación geográfica" con el picker.
- `Modules/Sucursales/Views/index.blade.php` y `show.blade.php` — mapa general y mapa por sucursal.

### Gotchas descubiertos
- **`@json(...)` (y directivas Blade en general) falla con expresiones complejas** (closures `fn () => [...]`, ternarios con casts anidados): `ParseError: Unclosed '['`. `view:cache` NO lo detecta; hay que renderizar la vista. Patrón seguro: construir el array/escalar en un bloque `@php` previo y pasar solo una variable: `@json($datos)`. (Causó 500 en `/sucursales` el 2026-11-15; corregido en `mapa-sucursales.blade.php`, `mapa-picker.blade.php` usa `$latJson`/`$lngJson`.)
- **Las migraciones del proyecto deben correr con `--database=pgsql_migrator`** (el rol dueño de las tablas es `ewiflor_migrator`; `ewiflor_app`, la conexión por defecto, lanza `SQLSTATE[42501]` "debe ser dueño de la tabla"). Ej.: `C:\tools\php85\php.exe artisan migrate --path=Modules/Sucursales/Database/Migrations --database=pgsql_migrator`.
- El modelo `Sucursal` ya tenía la relación `almacenes()` (apareció entre sesiones); el mapa/index dependen de ella.
- Leaflet va por CDN (`unpkg`), no por npm/Vite — requiere internet en dev.

### Verificación
- Migración aplicada OK (columnas `latitud`/`longitud` visibles en `information_schema.columns`).
- `route:list` → 13 rutas (incluida `sucursales.mapa`); `view:cache` OK con los nuevos partials; Pint aplicado.

---

## 2026-11-15 — Ventas: pago único por venta (parcial permitido)

### Qué se hizo
Se limitó el registro de pagos a **un único pago por venta**. El pago puede ser parcial; una vez registrado, no se admite otro. Si el monto supera el total de la venta, se rechaza.

### Archivos modificados
- `Modules/Ventas/Services/VentaService.php` — `registrarPago()` ahora usa `DB::transaction()` + `lockForUpdate()` sobre la venta, bloquea ventas canceladas, bloquea si ya existe un pago, calcula el total desde `v_venta_total` y valida que `0 < monto_bs <= total_bs` (comparación en centavos).
- `Modules/Ventas/Controllers/VentaController.php` — `show()` calcula `puedePagar` y `totalBs`; `registrarPago()` conserva el input al fallar (`withInput()`).
- `Modules/Ventas/Views/show.blade.php` — el formulario solo aparece si `puedePagar`; muestra aviso de pago único/parcial, prellena el monto con el total, limita el máximo y muestra el estado posterior (pagada o parcial). También se agregó el bloque de `$errors`.

### Decisiones
- **Sin cambios de esquema:** la regla queda en la aplicación, no en PostgreSQL. No se agregó `UNIQUE(id_venta)` ni trigger.
- **Sin limpieza de datos:** los pagos históricos se conservan. La venta 7 tiene 2 pagos (Bs 123.444 + 4.534 sobre un total de Bs 123.444) y las ventas 3–5 tienen pagos parciales; al ya tener pago, quedarán sin formulario.
- No se modificó el flujo de creación de venta ni el estado `REGISTRADA` (no existe estado `PAGADA`).

### Gotchas descubiertos
- `v_venta_total.total_bs` es la fuente del total; el monto se redondea a 2 decimales y se compara en centavos para evitar errores de flotantes.
- Al no haber restricción en BD, inserciones directas por SQL todavía podrían crear más de un pago; si se requiere invariante estricta, agregar un parche/trigger.

### Verificación
- `artisan view:cache` y `route:list --path=ventas` OK.
- Pint aplicado sobre los archivos modificados.

---

## 2026-11-15 — Precio de lista por variante y descuento %/USD

### Qué se hizo
- **Catálogo:** cada variante tiene ahora `precio_usd` (precio de lista en USD, decisión D-03 del SRS). Solo Gerente y Administrador pueden verlo/editarlo en el formulario de producto; las rutas del catálogo ya estaban restringidas a esos roles y se agregó una segunda validación en el controlador.
- **Ventas y cotizaciones:** al elegir una variante, el precio se autocompleta desde el catálogo y es de solo lectura. Solo se puede aplicar un descuento por línea, en **%** o en **monto USD sobre el total de la línea**.
- **Servidor autoritativo:** el `precio_unitario_usd` que llega del formulario se ignora; el servicio lo reemplaza por el precio del catálogo. Si la variante no tiene precio (> 0), se bloquea la operación con un mensaje claro.
- **Descuento USD:** se convierte internamente a `descuento_pct` con 2 decimales, que es lo que almacenan `detalle_venta`/`detalle_cotizacion` y lo que usan `v_venta_total` y las funciones PostgreSQL (no se tocaron).

### Archivos modificados / creados
- `Modules/Catalogo/Database/Migrations/2026_11_16_000001_agregar_precio_usd_a_variante_producto.php` — agrega `variante_producto.precio_usd monto_usd` y hace backfill con el último precio usado en ventas/cotizaciones; idempotente.
- `Modules/Catalogo/Providers/CatalogoServiceProvider.php` — `loadMigrationsFrom`.
- `Modules/Catalogo/Models/VarianteProducto.php` — `precio_usd` en fillable + cast `decimal:2`.
- `Modules/Catalogo/Requests/ProductoRequest.php` — validación `variantes.*.precio_usd` (nulable, `min:0.01`).
- `Modules/Catalogo/Controllers/ProductoController.php` — guarda el precio solo si el actor es Gerente/Administrador.
- `Modules/Catalogo/Views/productos/partials/variante-fila.blade.php` y `variantes.blade.php` — campo “Precio USD” en la fila de variante.
- `app/Support/Descuento.php` — conversión centralizada PCT/USD → porcentaje, con validaciones (no negativo, no superar subtotal ni 100%).
- `Modules/Ventas/Services/VentaService.php` — precio desde catálogo, bloqueo de variantes sin precio, descuento calculado en servidor.
- `Modules/Ventas/Requests/StoreVentaRequest.php` — `precio_unitario_usd` nullable; `descuento_tipo`/`descuento_valor`.
- `Modules/Ventas/Views/create.blade.php` — precio readonly, tipo de descuento, subtotal en vivo y auto-cálculo del descuento.
- `Modules/Ventas/Controllers/VentaController.php` — expone el mapa `precios` a la vista.
- `Modules/Cotizaciones/Services/CotizacionService.php` — mismo comportamiento en `syncLineas()`.
- `Modules/Cotizaciones/Requests/StoreCotizacionRequest.php` y `UpdateCotizacionRequest.php` — mismas reglas.
- `Modules/Cotizaciones/Controllers/CotizacionController.php` — mapa `precios` y mapeo de líneas a `descuento_tipo`/`descuento_valor`.
- `Modules/Cotizaciones/Views/_form.blade.php` — misma UI que ventas.

### Decisiones
- Migración Laravel del módulo (elección del usuario), no parche SQL.
- Precio **opcional** en catálogo; obligatorio para vender/cotizar.
- Backfill automático desde el último precio histórico (ventas o cotizaciones).
- Descuento libre para cualquier rol con acceso; no se implementó flujo de autorización (fuera de alcance).
- Al editar una cotización existente, sus líneas se reprecian con el precio actual del catálogo (el precio ya no es editable).

### Gotchas descubiertos
- `variante_producto` no tenía precio: el precio solo existía en `detalle_venta`/`detalle_cotizacion`. Por eso el backfill es necesario antes de operar.
- La variante sin historial queda con `precio_usd = NULL` hasta que Gerente/Administrador la edite; no se puede vender ni cotizar.
- El descuento en USD se guarda como porcentaje (2 decimales), por lo que puede haber diferencias de céntimos por redondeo.
- `@json($precios)` en las vistas requiere que el controlador siempre pase `precios` (en ventas se inicializa como `[]` para el modo cotización).

### Verificación
- Migración aplicada con `artisan migrate --database=pgsql_migrator`. Backfill: variantes 2 y 3 quedaron con precio (200.00 y 2000.00); la 1 sin historial sigue en NULL.
- Pruebas de servicio: precio manipulado se reemplaza por el del catálogo; descuento USD 5 sobre 2×200 → 1.25%; variante sin precio rechazada en venta y cotización.
- Render de `ventas::create`, `cotizaciones::create/edit`, `catalogo::productos.create/edit` sin errores; el mapa de precios sale como `{"2":200,"3":2000,"1":null}`.
- Guardado de precio con Gerente OK; con Vendedor el precio no cambia.
- `view:cache`, `php -l` y Pint OK.

---

## 2026-11-15 — Feature: módulo Auditoría (solo lectura, solo GERENTE)

### Qué se hizo
Nuevo CRUD de solo lectura sobre la tabla `auditoria` (ya llena por triggers PG `fn_auditar_cambio` vía `SetDatabaseUser`), accesible únicamente al rol `GERENTE`.

### Archivos creados
```
Modules/Sistema/
├── Models/Auditoria.php        ← modelo read-only ($guarded = ['*'], relación usuario())
├── Controllers/AuditoriaController.php  ← index() con filtros (fecha, usuario, módulo, operación, tabla) + paginación; middleware role:GERENTE
└── Views/auditoria/index.blade.php  ← tabla con filtros, paginación, badges por operación
```

### Archivos modificados
- `Modules/Sistema/Routes/web.php` → `Route::get('/auditoria', ...)->name('auditoria.index')->middleware('role:GERENTE')`.
- `resources/views/layouts/app.blade.php` → ítem "Auditoría" en Administración, visible solo si `$rolActual === 'GERENTE'`.

### Decisiones de diseño
- **Ubicación en `Sistema`** (no módulo nuevo): `auditoria` es de dueño `Sistema` según `docs/05-desarrollo/ubicacion-modelos.md`; `Sistema` ya tiene Provider, rutas y vistas registradas.
- **Solo lectura:** el modelo es `$guarded = ['*']`; Laravel nunca escribe en `auditoria` (100% triggers PG). Auditoría de auditoría bloqueada por `fn_auditoria_inmutable()`.
- **Filtros GET con `withQueryString`** para conservar los parámetros en la paginación.
- **Ruta protegida con `role:GERENTE`** (alias registrado en `bootstrap/app.php` vía `CheckRole`).

### Verificación
- `php artisan route:list --path=auditoria` → 1 ruta OK.
- `php artisan view:cache` → vista compila sin errores.
- Acceso restringido: cualquier rol distinto de GERENTE recibe 403 vía `CheckRole`.
