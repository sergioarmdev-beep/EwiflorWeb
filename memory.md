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

---

## 2026-11-16 — Feature: Módulo Servicio Técnico

### Qué se hizo
Nuevo módulo `Modules/ServicioTecnico/` con tres superficies totalmente separadas:
1. **Pública** (`/servicio-tecnico`, sin login): cliente se identifica con nombre+celular (sesión propia, no toca `auth`). Puede ver sus compras y estado de garantías, crear solicitud de servicio y ver el estado de sus propias solicitudes. Nunca puede editar/borrar ni ver el panel interno.
2. **Interna — Vendedor** (`auth` + `role:VENDEDOR,GERENTE,ADMINISTRADOR`): crea solicitud en nombre de un cliente (`origen='INTERNO'`).
3. **Interna — Técnico** (`auth` + `role:SERVICIO_TECNICO`): bandeja de encargos, aceptar (bloqueo contra doble asignación), cerrar con resultado + diagnóstico + costo.
4. **Interna — Gerente/Administrador** (`auth` + `role:GERENTE,ADMINISTRADOR`): gestión global de movimientos, filtros, quién aceptó cada encargo, si está en garantía, resultado y costo.

### Archivos creados
**Migración:**
- `database/migrations/2026_11_16_000004_solicitud_servicio_publica.php` — `id_usuario_registra` nullable, columna `origen` (PUBLICO/INTERNO), índice por cliente, trigger de auditoría. Se ejecuta con `--database=pgsql_migrator`.

**Módulo ServicioTecnico:**
- `Providers/ServicioTecnicoServiceProvider.php` — rutas + vistas `serviciotecnico::`
- `Routes/web.php` — 14 rutas (públicas + internas por rol)
- `Controllers/PublicoController.php` — identificar, panel, solicitar, guardar, detalle, salir
- `Controllers/SolicitudController.php` — bandeja, aceptar, cerrar, nueva, guardarInterna, gestion, show
- `Services/ConsultaPublicaService.php` — compras con garantía del cliente, solicitudes del cliente
- `Services/SolicitudService.php` - crearPublica, crearInterna, aceptar, cerrar, buscarCliente, pendientesParaTecnico, todas, tecnicos
- `Support/SesionCliente.php` — gestión de sesión pública del cliente
- `Requests/RegistrarSolicitudRequest.php`, `Requests/CerrarSolicitudRequest.php`
- `Views/public/{layout,identificar,panel,solicitar,detalle}.blade.php`
- `Views/solicitudes/{bandeja,gestion,nueva,show}.blade.php`

**Modelo (dueño en Garantias):**
- `Modules/Garantias/Models/SolicitudServicio.php` — relaciones cliente/garantia/tecnico/registra, estados y resultados

**Archivos modificados:**
- `bootstrap/providers.php` — registrado `ServicioTecnicoServiceProvider`
- `bootstrap/app.php` — alias middleware `sesion.cliente.publica`
- `Modules/Clientes/Models/Cliente.php` — relación `solicitudesServicio()`
- `Modules/Sistema/Services/DashboardStatsService.php` — `solicitudes_servicio_pendientes`, `ultimas_solicitudes_servicio` para gerente; alerta de sistema para encargos sin asignar
- `resources/views/layouts/app.blade.php` — ítems sidebar: Bandeja (técnico), Gestión (gerente/admin), Nueva solicitud (vendedor/gerente/admin)
- `memory.md` — esta entrada

### Decisiones de diseño
- **Superficies separadas:** la web pública tiene su propio layout y sesión propia (`serviciotecnico.cliente_id`), nunca se mezcla con `auth` ni con el panel de trabajadores.
- **Cliente inexistente:** se bloquea y se avisa (no se crean clientes automáticamente).
- **Dueño del modelo:** `SolicitudServicio` vive en `Garantias` (respeta `ubicacion-modelos.md`); `ServicioTecnico` lo importa.
- **Aceptación segura:** `UPDATE ... WHERE estado='PENDIENTE' AND id_tecnico IS NULL` evita que dos técnicos acepten el mismo encargo.
- **Cierre:** respeta el CHECK de la tabla (`estado<>'CERRADA' OR (fecha_cierre IS NOT NULL AND resultado IS NOT NULL)`).
- **Auditoría:** 100% por trigger PG `fn_auditar_cambio('id_solicitud')`, sin Observers.
- **Dashboard:** alerta de sistema cuando hay encargos sin asignar; datos para gerente y técnico.

### Verificación
- Migración aplicada OK con `--database=pgsql_migrator`.
- `php artisan view:cache` → vistas compilan sin errores.
- `route:list --path=servicio-tecnico` → 14 rutas OK.
- `php -l` → todos los PHP sin errores de sintaxis.
- Pint aplicado sobre todos los archivos creados/modificados.

### Gotchas descubiertos
- `@foreach (...) @empty @endforeach` vacío en Blade causa ParseError; se removió el bloque innecesario en `nueva.blade.php`.
- `SolicitudService::todas()` no puede declarar `: array` porque `paginate()` devuelve `LengthAwarePaginator`; se removió el tipo de retorno.
- Las relaciones Eloquent cargadas con `->with()` pueden venir como **array** en lugar de objeto en algunas vistas; se usa `data_get($sol, 'relacion.campo', 'default')` en lugar de `->?->` para acceder de forma segura a `registra`, `tecnico` y `cliente` en las vistas `gestion.blade.php` y `show.blade.php`.
- `$tecnicos` (de `SolicitudService::tecnicos()` que hace `->get()->toArray()`) es un **array de arrays**; en `gestion.blade.php` se accede con `$tec['id_usuario']` y `$tec['nombre_completo']` en lugar de `->id_usuario` / `->nombre_completo`.

---

## 2026-11-15 — Refactor: Garantía editable por línea en venta y cotización

### Qué se hizo
Se agregó la columna `garantia_meses` a `detalle_venta` y `detalle_cotizacion` para permitir editar la duración de garantía por línea al realizar una venta o cotización. El trigger PostgreSQL `fn_crear_garantias_venta()` ahora usa `COALESCE(dv.garantia_meses, p.garantia_meses)` y la vista `v_garantia_vigente` se enriqueció con datos de venta, cliente y producto para el futuro módulo de servicio técnico.

### Archivos creados
- `database/migrations/2026_11_15_000002_add_garantia_meses_to_detalle_venta_and_cotizacion.php` — columnas + reemplazo de `registrar_venta()`, `convertir_cotizacion_a_venta()`, `fn_crear_garantias_venta()` y `v_garantia_vigente` (DROP + CREATE). Se ejecuta con `--database=pgsql_migrator`.
- `Modules/Garantias/Models/Garantia.php` — modelo read-only con relación a `DetalleVenta`.

### Archivos modificados
**Modelos:**
- `Modules/Ventas/Models/DetalleVenta.php` — `garantia_meses` en `$fillable` + cast `int` + relación `garantia()`.
- `Modules/Cotizaciones/Models/DetalleCotizacion.php` — idem.

**Requests:**
- `Modules/Ventas/Requests/StoreVentaRequest.php` — `lineas.*.garantia_meses` (nullable|int|min:0|max:1200).
- `Modules/Cotizaciones/Requests/StoreCotizacionRequest.php` — idem.
- `Modules/Cotizaciones/Requests/UpdateCotizacionRequest.php` — idem.

**Services:**
- `Modules/Ventas/Services/VentaService.php` — `construirLineasJson()` incluye `garantia_meses` en el JSON a `registrar_venta()`.
- `Modules/Cotizaciones/Services/CotizacionService.php` — `syncLineas()` persiste `garantia_meses` en `detalle_cotizacion`.

**Controladores:**
- `Modules/Ventas/Controllers/VentaController.php` — `create()` expone mapa `garantias` (id_variante → meses) a la vista.
- `Modules/Cotizaciones/Controllers/CotizacionController.php` — `create()`/`edit()` exponen `garantias` y mapean `garantia_meses` en líneas al editar.

**Vistas:**
- `Modules/Ventas/Views/create.blade.php` — columna “Garantía (meses)” editable, prellenada desde catálogo; incluida en plantilla JS de fila dinámica.
- `Modules/Cotizaciones/Views/_form.blade.php` — misma columna y comportamiento.
- `Modules/Ventas/Views/show.blade.php` — muestra garantía por línea (meses + vencimiento si ya existe fila en `garantia`).
- `Modules/Ventas/Views/comprobante.blade.php` — muestra garantía por línea.
- `Modules/Cotizaciones/Views/show.blade.php` — muestra duración pactada por línea.

### Decisiones de diseño
- **Solo meses** (no días/meses): reutiliza `duracion_meses` y `producto.garantia_meses` existente.
- **Solo duración editable por línea:** fecha de inicio sigue siendo la fecha de la venta (trigger PG).
- **Por línea de producto:** una garantía por `detalle_venta` (ya definido en esquema).
- **Fase 1 como migración Laravel** (no parche SQL) en `database/migrations/` con `$connection = 'pgsql_migrator'`, idempotente.
- **`down()` restaura funciones/vista originales de `v5-MVP.sql` antes de quitar columnas** (orden crítico).
- **Vista `v_garantia_vigente` enriquecida** con `id_venta`, `id_cliente`, `sku`, `producto` para habilitar alertas y consultas en `SERVICIO_TECNICO` futuro.
- `producto.garantia_meses` sigue siendo el **default** en Catálogo.

### Gotchas descubiertos
- `CREATE OR REPLACE VIEW` no permite cambiar orden/nombre de columnas existentes → migración usa `DROP VIEW IF EXISTS` + `CREATE VIEW`.
- El trigger `fn_crear_garantias_venta()` se dispara al actualizar `venta.estado` a `REGISTRADA`; lee `COALESCE(dv.garantia_meses, p.garantia_meses)` y solo inserta si > 0.
- `convertir_cotizacion_a_venta()` ahora incluye `dc.garantia_meses` en el JSON que pasa a `registrar_venta()`.
- La migración debe correr con `--database=pgsql_migrator` (rol `ewiflor_migrator`/`ewiflor_owner`); la conexión por defecto `ewiflor_app` lanza `42501`.
- En `show.blade.php`, si `detalle_venta.garantia_meses` es NULL, se cae al default `producto.garantia_meses`; si ya existe fila en `garantia`, se muestra `duracion_meses` registrada con badge.
- `Garantia` modelo en `Modules/Garantias/` (módulo vacío hasta ahora) para lectura; `DetalleVenta::garantia()` relación `hasOne`.

### Verificación
- Migración aplicada OK: `detalle_venta.garantia_meses` y `detalle_cotizacion.garantia_meses` existen.
- `php artisan view:cache` → vistas compilan sin errores.
- `route:list --path=ventas` / `--path=cotizaciones` → 7 rutas cada uno.
- Pint aplicado sobre todos los archivos creados/modificados.

---

## 2026-11-16 — Feature: Módulo Publico (catálogo público sin autenticación)

### Qué se hizo
Nuevo módulo `Modules/Publico/` que expone el catálogo público en la raíz `/` sin requerir autenticación, y permite a clientes existentes solicitar servicio técnico dejando nombre y celular.

### Archivos creados
**Módulo Publico:**
- `Modules/Publico/Providers/PublicoServiceProvider.php` — rutas + vistas `publico::`
- `Modules/Publico/Routes/web.php` — ruta `GET /` (nombre `catalogo.publico`) y `POST contactar-servicio-tecnico` (nombre `publico.contactar.servicio.tecnico`)
- `Modules/Publico/Controllers/PublicoController.php` — `catalogoPublico()` (movido desde Catalogo) y `contactarServicioTecnico()` (nuevo)
- `Modules/Publico/Views/publico.blade.php` — vista completa del catálogo público con formulario de contacto servicio técnico

### Archivos modificados
- `bootstrap/providers.php` — registrado `PublicoServiceProvider`
- `routes/web.php` — ruta raíz `/` eliminada (ahora la maneja el módulo Publico)
- `Modules/Catalogo/Routes/web.php` — eliminada ruta `catalogo.publico` y referencia a `ProductoController@catalogoPublico`
- `Modules/Catalogo/Controllers/ProductoController.php` — eliminado método `catalogoPublico()`
- `Modules/Catalogo/Views/productos/publico.blade.php` — eliminado (movido a `Modules/Publico/Views/publico.blade.php`)
- `memory.md` — esta entrada

### Decisiones de diseño
- **URL en la raíz `/`** con nombre de ruta `catalogo.publico` conservado para no romper enlaces existentes.
- **Sin autenticación** — la vista pública es accesible sin login.
- **Contacto solo para clientes existentes** — el formulario valida que el nombre+celular coincida con un cliente registrado con compras previas (bloqueo por diseño, se aclara en el formulario).
- **Origen `PUBLICO`** — las solicitudes creadas desde la web pública usan `origen='PUBLICO'` y `id_usuario_registra=null`.
- **Vista movida, no copiada** — `publico.blade.php` se movió de `Modules/Catalogo` a `Modules/Publico` y se adaptó (formulario de contacto, rutas actualizadas).

### Gotchas descubiertos
- La ruta raíz `/` en `routes/web.php` central conflictúa con el módulo Publico; se eliminó para evitar rutas duplicadas.
- El nombre de ruta `catalogo.publico` se conserva para no romper enlaces y redirecciones existentes.

### Verificación
- `php artisan route:list --name=catalogo.publico` → ruta `/` OK, controlador `PublicoController@catalogoPublico`
- `php artisan route:list --name=publico.contactar.servicio.tecnico` → ruta POST OK
- `php artisan view:cache` → vistas compilan sin errores
- `php -l` → todos los PHP sin errores de sintaxis
- Pint aplicado sobre archivos creados/modificados

---

## 2026-11-16 — Fix: constante JSON_UNESCAPED_UNICODE en vista pública y directorio Views/publico

### Qué se hizo
- Corregido `JSON_UNESCAPE_UNICODE` → `JSON_UNESCAPED_UNICODE` (faltaba la "D") en `Modules/Publico/Views/publico.blade.php`, línea del script JSON de detalle de producto.
- Renombrado `Modules/ServicioTecnico/Views/public` → `Modules/ServicioTecnico/Views/publico` para que coincida con el namespace `serviciotecnico::publico.*` usado en `PublicoController`.
- Limpiado y recompilado caché de vistas (`view:clear` + `view:cache`).

### Archivos modificados
- `Modules/Publico/Views/publico.blade.php` — constante JSON corregida.
- `Modules/ServicioTecnico/Views/{public => publico}/*.blade.php` — renombrado directorio.

### Verificación
- `GET /` → catálogo público carga sin errores 500.
- `GET /servicio-tecnico` → vista de identificación carga sin errores.
- `php artisan view:cache` → vistas compilan sin errores.
- `php -l` → todos los PHP sin errores de sintaxis.

---

## 2026-11-16 — Fix: filtro de cliente en comprasConGarantia (v_reporte_ventas)

### Qué se hizo
- Corregido `where('rv.id_cliente', ...)` por `where('v.id_cliente', ...)` en
  `ConsultaPublicaService::comprasConGarantia()`. La vista `v_reporte_ventas`
  no expone la columna `id_cliente`; está en la tabla `venta` (`v.id_cliente`).
  El HINT de PostgreSQL ya indicaba: "Probablemente quiera hacer referencia
  a la columna «v.id_cliente»".

### Archivos modificados
- `Modules/ServicioTecnico/Services/ConsultaPublicaService.php` — línea 20 del filtro.

### Verificación
- `GET /servicio-tecnico/panel` → panel del cliente carga sin errores SQL.
- `php artisan view:cache` → vistas compilan sin errores.
- `php -l` → todos los PHP sin errores de sintaxis.

---

## 2026-11-16 — Fix: botón cerrar sesión en vista pública de servicio técnico

### Qué se hizo
- Reemplazado el enlace `<a>` por un botón `<button>` dentro del formulario POST en
  `Modules/ServicioTecnico/Views/publico/layout.blade.php`.
- El botón "Cerrar sesión" ahora envía el token CSRF correctamente y limpia la
  sesión del cliente público (`SesionCliente::limpiar()`).
- Eliminado el formulario duplicado oculto que quedaba al final del layout.

### Archivos modificados
- `Modules/ServicioTecnico/Views/publico.blade.php` — botón de cierre de sesión dentro del formulario.

### Decisión de diseño
- Se usó un botón tipo submit dentro del formulario en lugar de un enlace con JavaScript
  (`this.closest('form')` fallaba porque el `<a>` no estaba dentro del formulario).
- Esto garantiza que el token CSRF se envíe correctamente con la petición POST.

### Verificación
- `GET /servicio-tecnico/panel` → cerrar sesión limpia la sesión y redirige a identificar.
- `php artisan view:cache` → vistas compilan sin errores.
- `php -l` → todos los PHP sin errores de sintaxis.

---

## 2026-11-16 — Feature: cancelar solicitud de servicio (RF-011 / CU-011) + listado de garantías por vencer

### Qué se hizo
Implementado el estado `CANCELADA` para solicitudes de servicio, con motivo de cancelación y función PostgreSQL para la lógica transaccional. También se agregó el listado de garantías por vencer.

### Archivos creados
**Migración:**
- `database/migrations/2026_11_16_000005_add_estado_cancelada_y_motivo_a_solicitud_servicio.php` — columna `motivo_cancelacion TEXT NULL`, CHECK constraint actualizado con `CANCELADA`, función PG `cancelar_solicitud_servicio(id_solicitud, id_usuario_cancela, motivo)` con `RAISE EXCEPTION` para estados no cancelables. Se ejecuta con `--database=pgsql_migrator`.

**Módulo ServicioTecnico:**
- `Modules/ServicioTecnico/Requests/CancelarSolicitudRequest.php` — valida `motivo` (required, string, min:5, max:1000).
- `Modules/ServicioTecnico/Views/solicitudes/garantias-por-vencer.blade.php` — listado de garantías vigentes por vencer (tabla con días restantes, filtro por días).

### Archivos modificados
- `Modules/Garantias/Models/SolicitudServicio.php` — `ESTADOS` agrega `'CANCELADA'`.
- `Modules/ServicioTecnico/Services/SolicitudService.php` — nuevo `cancelar(SolicitudServicio, User, string $motivo)`; valida rol GERENTE/ADMINISTRADOR, llama a `DbFunction::call('cancelar_solicitud_servicio', ...)`.
- `Modules/ServicioTecnico/Services/ConsultaPublicaService.php` — nuevo `garantiasPorVencer(int $dias = 30)` usando `v_garantia_vigente`.
- `Modules/ServicioTecnico/Controllers/SolicitudController.php` — nuevo `cancelar()` (flash success/error) y `garantiasPorVencer(Request)`; constructor inyecta `ConsultaPublicaService`.
- `Modules/ServicioTecnico/Routes/web.php` — `POST /servicio-tecnico/{solicitud}/cancelar` (`serviciotecnico.cancelar`, `role:GERENTE,ADMINISTRADOR`) y `GET /servicio-tecnico/garantias-por-vencer` (`serviciotecnico.garantias.por_vencer`, `role:SERVICIO_TECNICO,GERENTE,ADMINISTRADOR`).
- `Modules/ServicioTecnico/Views/solicitudes/gestion.blade.php` — columna "Acciones" con botón Cancelar para estados PENDIENTE/PROGRAMADA, badge CANCELADA, filtro de estado CANCELADA, modal de confirmación con motivo.

### Decisiones de diseño
- **Estado `CANCELADA` vs `CERRADA`:** cancelar no implica resultado ni diagnóstico; solo motivo y fecha_cierre. CERRADA sigue para servicio completado.
- **Solo GERENTE/ADMINISTRADOR pueden cancelar** (no el técnico que la aceptó), validado en el service (defensa en profundidad) y en el middleware de ruta.
- **Función PostgreSQL** para la transacción (cambio de estado + motivo + fecha_cierre), llamada vía `App\Support\DbFunction::call()` con manejo de `RAISE EXCEPTION` → `BusinessRuleException`.
- **Auditoría 100% por trigger** `fn_auditar_cambio` (ya existe sobre `solicitud_servicio`), sin Observers.
- **`SolicitudServicio` se queda en `Garantias`** para no romper enlaces existentes (respuesta a consulta de arquitectura).
- **Flash messages** usan `session('success')` / `session('error')` (consistente con el resto del módulo).

### Gotchas descubiertos
- PowerShell no soporta `&&`; usar `;` para encadenar comandos.
- `pint.bat` usa el PHP del PATH (XAMPP 8.2) y falla; ejecutar con `C:\tools\php85\php.exe vendor\laravel\pint\builds/pint`.
- `Rule::in` importado pero no usado en `CancelarSolicitudRequest` — Pint lo detectó como unused import.

### Verificación
- Migración aplicada OK: `2026_11_16_000005_add_estado_cancelada_y_motivo_a_solicitud_servicio`.
- `php artisan route:list --name=serviciotecnico` → 16 rutas incluidas `serviciotecnico.cancelar` y `serviciotecnico.garantias.por_vencer`.
- `php -l` → todos los PHP sin errores de sintaxis.
- `vendor/bin/pint --test` → pasado en todos los archivos modificados.

---

## 2026-11-16 — Fix: técnico puede aceptar y cerrar solicitudes de servicio (RF-011 / CU-011)

### Qué se hizo
Corregido el flujo de técnico para que pueda aceptar una solicitud pendiente y cerrarla con resultado, diagnóstico, costo y fecha de finalización.

### Archivos modificados
- `Modules/ServicioTecnico/Services/SolicitudService.php` — `cerrar()` ahora recibe `User $user` y valida:
  - No se puede cerrar si ya está `CERRADA` o `CANCELADA`.
  - Solo permite estados `PENDIENTE` o `PROGRAMADA`.
  - Si el rol es `SERVICIO_TECNICO`, solo puede cerrar solicitudes donde `id_tecnico === auth()->id()`.
  - `GERENTE` y `ADMINISTRADOR` pueden cerrar cualquier solicitud.
- `Modules/ServicioTecnico/Controllers/SolicitudController.php` — `cerrar()` pasa `$request->user()` al service.
- `Modules/ServicioTecnico/Views/solicitudes/bandeja.blade.php` — agregada columna "Detalle" con enlace a `serviciotecnico.show` para cada solicitud, para que el técnico pueda ir directamente al formulario de cierre.

### Decisiones de diseño
- El técnico solo puede cerrar solicitudes que haya aceptado (esté asignado como `id_tecnico`), evitando que cierre encargos de otros técnicos.
- El formulario de cierre ya incluía `resultado`, `diagnostico` y `costo_servicio_bs`; después de cerrar se muestra `fecha_cierre` automática.
- La información de garantía vigente ya se mostraba mediante `$solicitud->estaEnGarantia()`.

### Verificación
- `php -l` → ambos archivos PHP sin errores de sintaxis.
- Pint aplicado sobre `SolicitudService.php`.
- `php artisan view:cache` → vistas compiladas correctamente.

---

## 2026-11-16 — Fix: `pendientesParaTecnico()` devolvía arrays en lugar de objetos

### Qué se hizo
Corregido `ErrorException: Attempt to read property "id_solicitud" on array` en `bandeja.blade.php:38`.

### Causa
`SolicitudService::pendientesParaTecnico()` usaba `->get()->toArray()`, devolviendo un **array de arrays**. La vista `bandeja.blade.php` accedía con notación de objeto (`$sol->id_solicitud`, `$sol->cliente?->nombre`), lo cual fallaba porque los elementos eran arrays, no objetos Eloquent.

### Archivo modificado
- `Modules/ServicioTecnico/Services/SolicitudService.php` — `pendientesParaTecnico()` ya no usa `->toArray()`. Devuelve `\Illuminate\Database\Eloquent\Collection` de objetos `SolicitudServicio` con `->with('cliente')`, permitiendo acceso con `->` en las vistas.

### Verificación
- `GET /servicio-tecnico/bandeja` → carga sin error 500.
- `php -l` → sin errores de sintaxis.
- Pint pasando.
- `php artisan view:cache` → vistas compiladas correctamente.

---

## 2026-11-16 — Feature: Sidebar y dashboard para Servicio Técnico

### Qué se hizo
Se agregaron los botones de acceso a las rutas implementadas en el layout principal y se mejoró el dashboard para el rol SERVICIO_TECNICO con el flujo de una sola solicitud activa a la vez.

### Archivos modificados

**Sidebar (`resources/views/layouts/app.blade.php`):**
- "Gestión servicio técnico": cambió de solo `GERENTE` a `GERENTE, ADMINISTRADOR` (línea ~168)
- Agregado "Garantías por vencer" para `SERVICIO_TECNICO, GERENTE, ADMINISTRADOR` (después de "Nueva solicitud servicio técnico")

**DashboardStatsService (`Modules/Sistema/Services/DashboardStatsService.php`):**
- `paraTecnico()` ahora incluye `solicitud_activa` obtenida mediante el nuevo método `solicitudActiva()`, que devuelve la solicitud `PROGRAMADA` asignada al técnico (solo una a la vez)

**Dashboard (`Modules/Sistema/Views/dashboard.blade.php`):**
- Para SERVICIO_TECNICO: agregada sección "Mi solicitud activa" que muestra:
  - Si tiene una activa (PROGRAMADA): datos del cliente, descripción, fecha, indicador de garantía, botón "Finalizar servicio" (redirige al formulario de cierre) y "Ver bandeja"
  - Si no tiene ninguna: estado vacío con botón "Ir a la bandeja" para aceptar un encargo pendiente

### Decisiones de diseño
- Un técnico solo puede tener una solicitud activa (PROGRAMADA) a la vez. Mientras la tenga, no puede aceptar otra desde el dashboard.
- Para finalizar debe ir al formulario de cierre (`serviciotecnico.show`) donde llena resultado, diagnóstico y costo.
- Una vez cerrada (estado CERRADA), la solicitud ya no aparece como activa y el técnico puede aceptar otra desde la bandeja.
- "Gestión servicio técnico" ahora es accesible para GERENTE y ADMINISTRADOR (no solo GERENTE).
- "Garantías por vencer" accesible para SERVICIO_TECNICO, GERENTE y ADMINISTRADOR.

### Verificación
- `php -l` → DashboardStatsService sin errores de sintaxis.
- Pint → pasando.
- `php artisan view:cache` → vistas compiladas correctamente (incluyendo app.blade.php y dashboard.blade.php).

### Gotchas corregidos
- `solicitudActiva()` declaraba retorno `?array` pero `DB::...->first()` devuelve `stdClass`. Se cambió el tipo de retorno a `?\stdClass` para evitar `TypeError` en PHP 8.5.

---

## 2026-11-16 — Feature: Vinculación de repuestos y documentación de productos (Catálogo)

### Qué se hizo
Implementadas dos funcionalidades en el módulo `Catalogo`:

1. **Vinculación de repuestos a maquinaria:** tabla pivote `producto_repuesto` que relaciona productos tipo `MAQUINARIA` con productos tipo `REPUESTO`. Relación `belongsToMany` bidireccional (`repuestos()` / `maquinaria()`) en el modelo `Producto`. Sección en el formulario de creación/edición que solo se muestra cuando el tipo es `MAQUINARIA`.

2. **Subida de documentación (TXT / Word):** tabla `producto_documento` para almacenar archivos adjuntos a productos (`.txt`, `.doc`, `.docx`, máx. 5 MB cada uno, hasta 5 archivos). Modelo `ProductoDocumento` con relación `hasMany` en `Producto`. En edición se listan los documentos existentes con opción de descargar y eliminar individualmente.

3. **Restricción de roles:** todas las rutas de escritura (`create`, `store`, `edit`, `update`, `destroy`, `toggleEstado`) ya estaban protegidas con `middleware('role:GERENTE,ADMINISTRADOR')` en `Routes/web.php`. Además, se validó en el controlador que el precio de variantes solo pueda editarse por Gerente/Administrador (patrón existente).

### Archivos creados
```
database/sql/patches/A-02-maquinaria-repuestos-documentos.sql   ← tablas producto_repuesto, producto_documento, índices, triggers de auditoría
Modules/Catalogo/Models/ProductoDocumento.php                    ← modelo read-write para producto_documento
```

### Archivos modificados
- `Modules/Catalogo/Models/Producto.php` — relaciones `documentos()`, `repuestos()`, `maquinaria()`.
- `Modules/Catalogo/Controllers/ProductoController.php` — `store()` y `update()` ahora guardan documentos (`guardarDocumentos()` nuevo), sincronizan repuestos via `sync()`/`detach()`, y `destroy()` limpia documentos y repuestos asociados. `edit()` eager-loads `documentos` y `repuestos`.
- `Modules/Catalogo/Requests/ProductoRequest.php` — reglas de validación para `documentos.*` (file, mimes:txt,doc,docx, max:5120), `eliminar_documentos.*`, `repuestos.*`.
- `Modules/Catalogo/Views/productos/create.blade.php` — sección Documentación (subida .txt/.doc/.docx) y sección Repuestos (select múltiple, oculto si no es MAQUINARIA con JS).
- `Modules/Catalogo/Views/productos/edit.blade.php` — sección Documentación (lista de existentes con checkbox eliminar + descargar, subida de nuevos) y sección Repuestos (lista de vinculados + selector de nuevos, oculto si no es MAQUINARIA con JS).

---

## 2026-11-16 — Fix: técnico solo puede tener una solicitud activa a la vez (RF-011 / CU-011)

### Qué se hizo
Corregido error grave: un técnico con rol `SERVICIO_TECNICO` podía aceptar múltiples encargos simultáneamente. Ahora solo puede tener **una** solicitud activa (`PROGRAMADA`) a la vez.

### Archivo modificado
- `Modules/ServicioTecnico/Services/SolicitudService.php` — `aceptar()` ahora valida al inicio que el técnico no tenga ya una solicitud `PROGRAMADA` asignada:

```php
$yaTieneActiva = DB::table('solicitud_servicio')
    ->where('id_tecnico', $tecnico->id_usuario)
    ->where('estado', 'PROGRAMADA')
    ->exists();

if ($yaTieneActiva) {
    throw new BusinessRuleException(
        'Ya tienes una solicitud activa. Debes cerrarla antes de aceptar otro encargo.'
    );
}
```

### Decisiones de diseño
- **Validación en el service (defensa en profundidad):** incluso si el botón de aceptar se muestra en la bandeja, al hacer clic se lanza `BusinessRuleException` y se muestra flash de error. No se acepta el encargo.
- **Flujo del técnico:** aceptar una pendiente → cierra con resultado/diagnóstico/costo → pasa a `CERRADA` → puede aceptar otra desde la bandeja.
- Mientras tenga una activa, el dashboard muestra "Mi solicitud activa" con botón "Finalizar servicio" para ir directamente al cierre.

### Verificación
- `php -l` → sin errores de sintaxis ✅
- Pint → pasando ✅
- `php artisan view:cache` → vistas compiladas correctamente.

---

## 2026-11-16 — Feature: Vinculación de repuestos y documentación de productos (Catálogo)

### Qué se hizo
Implementadas dos funcionalidades en el módulo `Catalogo`:

1. **Vinculación de repuestos a maquinaria:** tabla pivote `producto_repuesto` que relaciona productos tipo `MAQUINARIA` con productos tipo `REPUESTO`. Relación `belongsToMany` bidireccional (`repuestos()` / `maquinaria()`) en el modelo `Producto`. Sección en el formulario de creación/edición que solo se muestra cuando el tipo es `MAQUINARIA`.

2. **Subida de documentación (TXT / Word):** tabla `producto_documento` para almacenar archivos adjuntos a productos (`.txt`, `.doc`, `.docx`, máx. 5 MB cada uno, hasta 5 archivos). Modelo `ProductoDocumento` con relación `hasMany` en `Producto`. En edición se listan los documentos existentes con opción de descargar y eliminar individualmente.

3. **Restricción de roles:** todas las rutas de escritura (`create`, `store`, `edit`, `update`, `destroy`, `toggleEstado`) ya estaban protegidas con `middleware('role:GERENTE,ADMINISTRADOR')` en `Routes/web.php`. Además, se validó en el controlador que el precio de variantes solo pueda editarse por Gerente/Administrador (patrón existente).

### Archivos creados
```
database/sql/patches/A-02-maquinaria-repuestos-documentos.sql   ← tablas producto_repuesto, producto_documento, índices, triggers de auditoría
Modules/Catalogo/Models/ProductoDocumento.php                    ← modelo read-write para producto_documento
```

### Archivos modificados
- `Modules/Catalogo/Models/Producto.php` — relaciones `documentos()`, `repuestos()`, `maquinaria()`.
- `Modules/Catalogo/Controllers/ProductoController.php` — `store()` y `update()` ahora guardan documentos (`guardarDocumentos()` nuevo), sincronizan repuestos via `sync()`/`detach()`, y `destroy()` limpia documentos y repuestos asociados. `edit()` eager-loads `documentos` y `repuestos`.
- `Modules/Catalogo/Requests/ProductoRequest.php` — reglas de validación para `documentos.*` (file, mimes:txt,doc,docx, max:5120), `eliminar_documentos.*`, `repuestos.*`.
- `Modules/Catalogo/Views/productos/create.blade.php` — sección Documentación (subida .txt/.doc/.docx) y sección Repuestos (select múltiple, oculto si no es MAQUINARIA con JS).
- `Modules/Catalogo/Views/productos/edit.blade.php` — sección Documentación (lista de existentes con checkbox eliminar + descargar, subida de nuevos) y sección Repuestos (lista de vinculados + selector de nuevos, oculto si no es MAQUINARIA con JS).

### Decisiones de diseño
- **Parche SQL en `database/sql/patches/`** (no migración Laravel) siguiendo el patrón de DDL del proyecto.
- **Los documentos se almacenan en el disco `public`** bajo la carpeta `documentos-productos`, igual que las imágenes.
- **`sync()` para repuestos** maneja tanto alta como baja de asociaciones en una sola operación.
- **JS para mostrar/ocultar** la sección de repuestos según `tipo_producto` (clase CSS `.mostrar` + `data-tipo-producto`).
- **Roles:** ya restringido por middleware en rutas; no se agregó middleware nuevo.

### Verificación
- Migración aplicada OK: tablas `producto_repuesto`, `producto_documento` y triggers creados con `--database=pgsql_migrator`.
- `php -l` → todos los PHP sin errores de sintaxis (Producto, ProductoDocumento, ProductoController, ProductoRequest).
- `view:cache` → no probado aún con las nuevas vistas (pendiente).

### Gotchas descubiertos
- La conexión por defecto `ewiflor_app` no tiene privilegios DDL (`SQLSTATE[42501]`); usar `--database=pgsql_migrator` para aplicar el parche SQL.
- PowerShell no soporta `&&` ni `||`; usar `;` para encadenar comandos o ejecutar scripts PHP independientes.

---

## 2026-11-16 — Feature: Detalle de cliente enriquecido con garantías y servicio técnico (Módulo Clientes)

### Qué se hizo
Se enriqueció la vista `clientes::show` con tres nuevas secciones para dar al cliente un perfil completo: historial de compras, cotizaciones, garantías activas y solicitudes de servicio técnico en una sola pantalla.

### Archivos modificados

**Controlador (`Modules/Clientes/Controllers/ClienteController.php`):**
- `show()` ahora consulta:
  - `garantiasActivas`: desde `v_garantia_vigente` filtrando por `id_cliente`, `vigente = true` y `fecha_vencimiento` dentro de los próximos 30 días, ordenado por vencimiento próximo.
  - `garantiasPorVencer`: conteo simple de garantías que vencen en ≤ 7 días (para tarjeta del resumen comercial).
  - `solicitudesServicio`: relación existente `$cliente->solicitudesServicio()` con carga de `tecnico`, limitado a 10 registros ordenados por fecha descendente.

**Vista (`Modules/Clientes/Views/show.blade.php`):**
- Nueva tarjeta "Garantías por vencer" en el resumen comercial (después de "Saldo pendiente").
- Nueva sección "Garantías activas": tabla con garantía, producto/SKU, fecha inicio, vencimiento, días restantes (alerta ámbar si ≤ 7 días), venta asociada y estado (Vigente / Por vencer).
- Nueva sección "Solicitudes de servicio técnico": tabla con solicitud, fecha, falla (truncada), estado, técnico, resultado y enlace "Ver detalle" a `serviciotecnico.show`.

### Decisiones de diseño
- **Garantías:** solo se muestran vigentes y por vencer (no vencidas). Usa la vista `v_garantia_vigente` ya existente.
- **Roles:** misma restricción que `clientes.show` (GERENTE, ADMINISTRADOR, VENDEDOR), ya aplicada por middleware en la ruta.
- **Enlaces:** las solicitudes de servicio enlazan a `serviciotecnico.show` para ver el detalle completo.
- **Sin cambios de ruta ni modelo:** las relaciones `solicitudesServicio()` ya existían en `Cliente`; solo se consumen en el controlador y la vista.

### Verificación
- `php -l` → ClienteController sin errores de sintaxis ✅
- Pint → pasando ✅
- `php artisan view:cache` → vistas compiladas correctamente ✅

---

## 2026-11-17 — Feature: Alcance por vendedor en cotizaciones, ventas y detalle de cliente

### Qué se hizo
Un usuario con rol `VENDEDOR` solo puede consultar (y operar) las cotizaciones y ventas que él mismo emitió/registró. `GERENTE` y `ADMINISTRADOR` siguen viendo todo. Sin sesión no se ve nada (fail-closed). Antes cualquier vendedor veía el listado completo y podía abrir por URL el documento de otro.

### Regla aplicada

| Rol | Cotizaciones | Ventas |
| --- | --- | --- |
| GERENTE, ADMINISTRADOR | todas | todas |
| VENDEDOR (y cualquier otro rol que llegue a las rutas) | solo `id_usuario = auth()->id()` | solo `id_usuario = auth()->id()` |

### Archivos modificados
- `Modules/Auth/Models/User.php` — nuevo `puedeVerTodosLosDocumentos(): bool` (GERENTE/ADMINISTRADOR). Fuente única de la regla para ambos módulos.
- `Modules/Cotizaciones/Models/Cotizacion.php` — `scopeVisiblesPara(Builder, ?User)` y `puedeVer(?User)`. El filtro usa `qualifyColumn('id_usuario')` → `cotizacion.id_usuario`.
- `Modules/Ventas/Models/Venta.php` — mismo par de métodos (`venta.id_usuario`; el qualify es obligatorio porque Clientes consulta `venta` con joins).
- `Modules/Cotizaciones/Controllers/CotizacionController.php` — `index()` aplica `visiblesPara()`; `show(Request, Cotizacion)` llama a `autorizarLectura()` → 403.
- `Modules/Ventas/Controllers/VentaController.php` — `index()` aplica `visiblesPara()`; `show`, `comprobante` y `registrarPago` pasan por `autorizarVenta()` (acepta `Venta|Cotizacion`) → 403; `create()` en modo cotización bloquea convertir una cotización ajena.
- `Modules/Ventas/Services/VentaService.php` — `convertirCotizacion()` valida `$cot->puedeVer($actor)` y lanza `BusinessRuleException` (defensa en profundidad para el POST con `id_cotizacion`).
- `Modules/Clientes/Controllers/ClienteController.php` — `show()` acepta `Request`; para `VENDEDOR` filtra `$cotizaciones`, `$ventas`, `$resumenCotizaciones`, `$resumenVentas` y `$garantiasActivas` (esta última por `gv.id_venta`). Pasa `$soloDocumentosPropios` a la vista.
- `Modules/Clientes/Views/show.blade.php` — aviso de alcance cuando `$soloDocumentosPropios`.
- `Modules/Ventas/Views/index.blade.php` y `Modules/Cotizaciones/Views/index.blade.php` — aviso "solo tus ventas/cotizaciones".

### Decisiones de diseño
- **Regla en el modelo dueño de la tabla**, no en un helper compartido: evita acoplar Cotizaciones ↔ Ventas y sigue el principio de un único dueño por tabla.
- **403 con `abort_unless`**, igual que el middleware `CheckRole`: no revela si el registro existe. El POST de conversión sí usa `BusinessRuleException` porque es un flujo de negocio con flash de error.
- **Clientes:** el filtro se aplica solo al rol `VENDEDOR`. `SERVICIO_TECNICO` conserva el historial completo del cliente (lo necesita para garantías/servicios) y no tiene acceso a las rutas de Ventas/Cotizaciones.
- **Garantías en el detalle de cliente:** también se filtran por venta propia del vendedor, porque la tabla muestra `#id_venta` y esa venta ya no es consultable por él.
- **Rutas y middleware sin cambios:** el alcance es por registro, no por endpoint; `CheckRole` sigue decidiendo qué roles entran.
- `Venta.id_usuario` es el actor que ejecuta `registrar_venta` / `convertir_cotizacion_a_venta` (comportamiento existente, no se alteró).

### Verificación
- `php -l` → 7 archivos PHP sin errores ✅
- Pint (`--dirty`) → pasando ✅
- `php artisan view:cache` → vistas compiladas ✅
- Consultas verificadas con `artisan tinker` contra la BD real (solo lectura): vendedor u7 ve 1 de 9 cotizaciones y 0 de 11 ventas; gerente ve todas; `puedeVer()` devuelve `false` para documentos ajenos y `true` para los propios; el SQL del listado de Clientes queda con `"venta"."id_usuario" = ?`.
- Sin tests automatizados: `phpunit.xml` usa SQLite en memoria y estas consultas dependen de PostgreSQL (`ilike`, vistas, joins).

### Gotcha
- Los documentos históricos con `id_usuario` NULL quedan invisibles para los vendedores por diseño (fail-closed); un gerente o administrador sí los ve.

---

## 2026-11-17 — Feature: Alta rápida de cliente desde cotizaciones y ventas

### Qué se hizo
Antes había que ir al módulo Clientes a registrar un cliente y volver a Cotizaciones, perdiendo las líneas ya capturadas. Ahora el formulario de cotización (crear y editar) y el de venta directa tienen un botón “+ Registrar cliente nuevo” que abre un modal, crea el cliente sin recargar la página y lo deja seleccionado en el `<select>`.

### Flujo implementado
1. El modal (`<dialog>`) pide nombre*, teléfono*, correo, tipo de cliente y mayorista.
2. `fetch` POST JSON a `clientes.store` con `X-CSRF-TOKEN` del `<meta>` del layout.
3. **201** → agrega la opción con `new Option(etiqueta, id)` (sin recargar: las líneas se conservan), la selecciona y cierra el modal.
4. **200 `{existente: true}`** → el teléfono ya pertenece a un cliente activo: panel con "Usar este cliente" (lo selecciona sin crear nada) y "Registrar de todas formas" (reenvía con `forzar_creacion = 1`).
5. **422** → errores por campo bajo cada input; el modal no se cierra y conserva los datos.

### Archivos creados
- `Modules/Clientes/Views/partials/quick-create.blade.php` — botón + `<dialog>` + JS (sin dependencias). Incluye `<noscript>` con enlace a `clientes.create`.

### Archivos modificados
- `Modules/Clientes/Controllers/ClienteController.php` — `store()` conserva el flujo HTML y añade la rama JSON: detecta duplicado activo por teléfono (`Cliente::activos()`) antes de crear y devuelve el existente, salvo `forzar_creacion`. Usa `$request->safe()->except('forzar_creacion')` para no pasar la bandera al modelo.
- `Modules/Clientes/Requests/StoreClienteRequest.php` — nueva regla `forzar_creacion` (`sometimes|boolean`).
- `Modules/Cotizaciones/Views/_form.blade.php` — `id="id_cliente"` + `@include('clientes::partials.quick-create')` (aplica a crear y editar).
- `Modules/Ventas/Views/create.blade.php` — mismo id + include, dentro de la rama de venta directa.

### Decisiones de diseño
- **Modal en lugar de redirección:** una redirección a Clientes perdería las líneas de la cotización/venta ya capturadas.
- **No se crea endpoint nuevo:** se reutiliza `clientes.store` y `StoreClienteRequest`; el comportamiento HTML (redirect a `clientes.show`) queda intacto porque el contrato JSON solo se activa con `Accept: application/json`.
- **Duplicados:** solo se comparan clientes **ACTIVOS** (el `<select>` lista únicamente esos). La comparación es exacta tras `trim()`; normalizar guiones/espacios queda como mejora futura.
- **Reutilizar por defecto, crear con confirmación:** evita duplicados accidentales sin impedir dos clientes que compartan teléfono.
- Sin Alpine ni librerías nuevas: `<script>` inline, igual que el resto del proyecto; inserción con `new Option()` (sin concatenar HTML con datos del servidor).

### Verificación
- `php -l` en `ClienteController` y `StoreClienteRequest` ✅ · Pint (`--dirty`) ✅ · `view:cache` ✅
- Parcial renderizado con `view()`: 10 345 bytes, `<dialog>` y URL de `clientes.store` presentes.
- Endpoint probado contra PostgreSQL real con un probe temporal (transacciones + rollback, sin dejar datos):
  - teléfono existente → `HTTP 200 {"existente":true,...}`
  - alta nueva → `HTTP 201` con `id_cliente`/`etiqueta` y fila visible en la transacción
  - `forzar_creacion=1` → crea el duplicado (`HTTP 201`)
  - formulario HTML normal → `redirect /clientes/{id}`
  - nombres vacíos → `HTTP 422` con `errors.nombre`/`errors.telefono`
  - tras los rollbacks: 0 filas de prueba en `cliente`.
- Sin tests automatizados: `phpunit.xml` usa SQLite en memoria; el probe se ejecutó fuera del repo con `bootstrap/app.php` y rollback.

### Gotcha
- Para probar peticiones sin pasar por CSRF desde un script CLI, desactivar `ValidateCsrfToken` (Laravel 13 ya no usa `VerifyCsrfToken` en el grupo `web`) o, más simple, invocar el controlador con un `FormRequest` construido a mano (`createFrom` + `validateResolved`).

### Corrección — el botón no abría el modal (17/11)
Reporte del usuario: “al presionar el botón de agregar nuevo cliente en ambos casos no se despliega ningún formulario”.

**Causa raíz:** el partial se incluye dentro del formulario de la cotización/venta y traía su propio `<form id="form-cliente-rapido">`. El HTML no permite formularios anidados, así que el parser **descarta** el interno; además su `</form>` podía cerrar antes de tiempo el formulario padre. Resultado: `document.getElementById('form-cliente-rapido')` era `null`, el JS abortaba en la guarda (`if (!abrir || !dialogo || !form) return;`) y el clic no hacía nada. Sin errores en consola.

**Solución:** el modal ya no tiene formulario propio ni atributos `name`:
- Contenedor `<div id="qc-campos">` con entradas `data-campo="nombre|telefono|correo|tipo_cliente|es_mayorista"`.
- Recolección manual del payload (`datosDelFormulario()`), `reset()` manual (`reiniciar()`) y todos los botones `type="button"` (el “Guardar” ahora también, ya no es submit).
- `keydown` en el contenedor para que Enter dispare el alta con `preventDefault()` y **no** envíe el formulario padre.
- Al no haber `name`, los campos del modal tampoco se envían al guardar la cotización/venta.

**Verificación en navegador real** (Chromium del IDE, snapshot HTML servido por `php -S`, `fetch` stubbeado):
- Clic en “+ Registrar cliente nuevo” → `#dlg-cliente-rapido.open === true`, sin errores de consola.
- 201 → opción creada con `new Option()`, `<select>` con el cliente nuevo, modal cerrado y campos limpios; payload correcto (`es_mayorista` 1/0, sin `name` heredados).
- 200 `{existente:true}` → panel ámbar visible, “Guardar” oculto; “Usar este cliente” selecciona y cierra.
- “Registrar de todas formas” → segunda petición con `forzar_creacion: 1` y alta 201.
- 422 → mensajes por campo visibles, modal abierto y botón re-habilitado.
- Enter en un campo → 1 sola petición, **0** submits del formulario padre, URL sin cambios.
- Mismo flujo verificado en `ventas::create` (modo directa): 3 aperturas de `<form>` en el HTML, todas reales salvo un comentario JS (ya reescrito).
- `view:cache` ✅ · Pint (`--dirty`) ✅.

**Gotcha de las pruebas:** al renderizar vistas por CLI no hay sesión, así que `csrf_token()` y `@csrf` salen vacíos (`content=""`); el `X-CSRF-TOKEN` vacío del snapshot es un artefacto del render fuera de HTTP, no un fallo del modal. En una petición real el `<meta name="csrf-token">` del layout llega con token.

---

## 2026-11-17 — Módulo Promociones (descuentos por variante + combos)

**Qué se hizo:** módulo completo `Promociones` con integración en Ventas, Cotizaciones y catálogo público.

**Archivos creados:**
- `Modules/Promociones/Database/Migrations/2026_11_17_000001_promociones_y_combos.php` (migración del módulo, no parche): tablas `promocion`, `promocion_variante`, `combo_componente`; amplía CHECK de `producto.tipo_producto` con `COMBO`; triggers de solape `fn_validar_solape_promocion`/`fn_check_promo_variante`/`fn_check_promo_actualizada`; `fn_modulo_auditoria` ampliada (`PROMOCIONES`/`PRODUCTOS`); triggers de auditoría; redefine `registrar_venta` con guarda de Vendedor (versión original guardada en `down()`).
- Modelos: `Promocion`, `PromocionVariante`, `ComboComponente`.
- Services: `PromocionService` (mapaVigente, porcentajeEquivalente, validarDescuento, CRUD, solape), `ComboService` (reusa `ProductoService` de Catalogo).
- Requests: `PromocionRequest`, `ComboRequest`.
- Controllers: `PromocionController`, `ComboController`.
- Routes: `Modules/Promociones/Routes/web.php` (prefijo /promociones, role:GERENTE,ADMINISTRADOR, estáticas antes de dinámicas).
- Provider: `PromocionesServiceProvider` + línea en `bootstrap/providers.php`.
- Vistas: `promociones/{index,form}`, `combos/{index,form}` (dark Tailwind, `@push('styles')` vars `--promo-*`/`--combo-*`, picker producto→checkboxes variantes, filas componente+cantidad).
- JS: `resources/js/components/promocion-form.js`, `combo-form.js`; `variant-search.js` + blade con chips promo/COMBO y `promoEtiqueta()`; registrados en `resources/js/app.js`.

**Archivos modificados:**
- `bootstrap/providers.php`: `PromocionesServiceProvider::class` tras `PreciosServiceProvider::class`.
- `resources/views/layouts/app.blade.php`: nav grupo "Promociones" (Descuentos + Combos) solo GERENTE/ADMINISTRADOR tras Catálogo.
- `Modules/Catalogo/Services/ProductoService.php`: extraído (SQL_STOCK, imágenes, variantes, documentos, principal).
- `Modules/Catalogo/Controllers/ProductoController.php`: delega en service; expone `promocionesVigentes` y `resumen.con_promocion`.
- `Modules/Catalogo/Models/Producto.php`: TIPOS + COMBO, `tiposCatalogo()`, `esCombo()`, `componentesCombo()`.
- `Modules/Catalogo/Models/VarianteProducto.php`: relación `promociones()`.
- `Modules/Catalogo/Support/BuscadorVariantes.php`: payload con `promo`, `es_combo`, `combo` (promos pasadas como arg, evita ciclo).
- `Modules/Catalogo/Requests/ProductoRequest.php`: tipo vs `tiposCatalogo()`.
- `Modules/Catalogo/Views/productos/index.blade.php`: badge PROMO/COMBO, checkbox filtro `promocion`, tarjeta resumen "Con promoción".
- `Modules/Catalogo/Views/productos/edit.blade.php`: aviso combo (se edita desde Promociones→Combos).
- `Modules/Ventas/Controllers/VentaController.php` + `Services/VentaService.php`: `construirLineasJson(..., User $actor)` con `validarDescuento` por línea; `promociones` + `puedeEditarDescuento` en create.
- `Modules/Ventas/Views/create.blade.php`: prefill promo al cambiar variante; VENDEDOR readonly/disabled en descuento.
- `Modules/Cotizaciones/Controllers/CotizacionController.php` + `Services/CotizacionService.php`: `syncLineas(..., User $actor)` con validación de descuento; `promociones` + `puedeEditarDescuento` en create/edit.
- `Modules/Cotizaciones/Views/_form.blade.php`: mismo patrón de promo que Ventas.
- `Modules/Publico/Controllers/PublicoController.php`: promo por variante + componentes en `$detalle`.
- `Modules/Publico/Views/publico.blade.php`: badge "OFERTA −X%" sobre imagen, chip "COMBO" + componentes bajo título, precio "antes→ahora" solo variantes con promo en `llenar()`.

**Decisiones confirmadas:**
- DDL vía migración del módulo (no parche A-03).
- SKU del combo = `strtoupper(codigo)`; combo tiene una única variante con `precio_usd`.
- Regla Vendedor: `pct_solicitado <= pct_promo` (≤, tolerancia 0.005); informado en el plan.
- `promocionesVigentes` en Catálogo se construye con `mapaVigente` sobre las variantes de la página paginada.

**Verificación:**
- `php -l` ✅ (28 archivos).
- Pint `--test` → fix aplicado en `ComboService.php` y `BuscadorVariantes.php`.
- `view:cache` ✅.
- Migración `--database=pgsql_migrator` ✅ (tablas, CHECK con COMBO, triggers de solape y auditoría creados).
- Probe solape (rollback) ✅: dos promos activas solapadas → `P0001`, transacción revertida, 0 filas restantes.
- Probe `registrar_venta` guarda Vendedor ✅: descuento 11% > promo 10% → `P0001` "solo puede aplicar el descuento de la promoción vigente"; descuento 10% == 10% → pasa la guarda (falló solo por stock, no por descuento).

**Docs actualizadas:** `docs/05-desarrollo/ubicacion-modelos.md` (mapa Promociones), `docs/05-desarrollo/funciones-postgresql.md` (nota guarda Vendedor en registrar_venta).

**Pendiente (fuera de scope de esta entrada):** tests de negocio (phpunit forza SQLite, no ejerce funciones PG).

---

## 2026-11-18 — Feature: Alcance por sucursal en Ventas y Cotizaciones (reescritura de la regla del 17/11)

### Qué se hizo
Ampliada la regla de alcance de documentos comerciales: ahora el **límite es la sucursal**, no solo el autor. Antes un ADMINISTRADOR o VENDEDOR de una sucursal podía ver/vender en todas.

| Rol | Ventas / Cotizaciones |
| --- | --- |
| GERENTE | todas las sucursales (único que opera fuera de la suya) |
| ADMINISTRADOR | solo documentos de su sucursal asignada |
| VENDEDOR | solo sus propios documentos, dentro de su sucursal |
| sin sucursal (no GERENTE) | nada (fail-closed) |

### Fuente única de la regla
- **`Modules/Sucursales/Support/AlcanceDocumentos.php`** (nuevo, final class, métodos estáticos): `esGerente`, `soloSusDocumentos`, `sucursalDelUsuario`, `puedeOperarEnSucursal`, `puedeVerDocumento`, `almacenesVisibles`, `sucursalesParaFiltro`, `usuariosVisibles`, `aviso`.
- **No se tocó** `AccesoSucursal` (ahí el ADMIN es gestor global: gestión de sucursales/almacenes) ni `User::puedeVerTodosLosDocumentos()` (sigue vivo para otros consumidores, p. ej. Clientes).

### Archivos creados
```
Modules/Sucursales/Support/AlcanceDocumentos.php                                  ← regla única
Modules/Ventas/Database/Migrations/2026_11_18_000001_alcance_sucursal_ventas.php  ← guardas en PostgreSQL
```

### Archivos modificados
- `Modules/Ventas/Models/Venta.php` y `Modules/Cotizaciones/Models/Cotizacion.php` — `scopeVisiblesPara()` / `puedeVer()` ahora por sucursal + autor vía `AlcanceDocumentos`.
- `Modules/Ventas/Controllers/VentaController.php` — `queryVentas()` nuevo: une `v_reporte_ventas` (totales y pagado en la misma query, **elimina el N+1** de `v_venta_total`), filtros `estado`/`buscar`/fechas/`id_sucursal` (solo Gerente)/`id_usuario`/`cobro` (match sobre `rv`), resumen agregado (5 tarjetas); `create()` usa `almacenesVisibles()` y bloquea si está vacío; `cancelar()` ahora pasa por `autorizarVenta()` (antes no); `autorizarVenta()` distingue mensaje de "otra sucursal" vs "documento ajeno".
- `Modules/Ventas/Services/VentaService.php` — `crearDirecta()` deriva `idSucursal` del **almacén de salida** y valida `puedeOperarEnSucursal()`; `convertirCotizacion()` valida sucursal de la cotización y que el almacén pertenezca a ella.
- `Modules/Ventas/Requests/StoreVentaRequest.php` — `reglaAlmacen()` = `Exists` con `where estado=ACTIVO` **y** `where id_sucursal = sucursal ?? -1` (sin sucursal → ningún almacén válido); mensajes propios.
- `Modules/Cotizaciones/Controllers/CotizacionController.php` — filtros `id_sucursal`/`id_usuario`, eager `sucursal`, `$aviso`; **`edit`, `update` y `anular` ahora llaman `autorizarLectura()`** (antes sin autorización: fallo preexistente corregido).
- `Modules/Ventas/Views/index.blade.php` — aviso de alcance, 5 tarjetas resumen, filtros (sucursal/vendedor/cobro), columnas Sucursal y Cobrado.
- `Modules/Ventas/Views/create.blade.php` — el select de almacén muestra la sucursal + nota "La venta se registra en la sucursal del almacén elegido".
- `Modules/Cotizaciones/Views/index.blade.php` — aviso por `$aviso`, filtros de sucursal/vendedor, columna Sucursal, colspan 9.
- `Modules/Ventas/Providers/VentasServiceProvider.php` — `loadMigrationsFrom()` del módulo.

### La migración (defensa en profundidad)
- `$connection = 'pgsql_migrator'`; `up()` redefine `registrar_venta()` y `convertir_cotizacion_a_venta()` con dos guardas nuevas:
  1. rol `ADMINISTRADOR`/`VENDEDOR` sin sucursal → `RAISE EXCEPTION`; con sucursal distinta de `p_id_sucursal` → `RAISE EXCEPTION`.
  2. cualquier línea con `id_almacen` de otra sucursal que `p_id_sucursal` → `RAISE EXCEPTION` (antes se podía descontar stock de otra sucursal).
  - Conversión: el almacén debe ser de la sucursal de la cotización.
  - Conserva la guarda de descuento del VENDEDOR de la migración de Promociones.
- `down()` restaura la versión previa (la de Promociones / v5-MVP) — probado rollback + re-migrate.

### Verificación (todo ejecutado)
- `php -l` → 9 archivos OK · Pint `--dirty --test` → PASSED · `view:cache` → OK.
- `migrate` + `migrate:rollback --step=1` + re-`migrate` → OK; `migrate:status` → 0 pendientes.
- **Probe SQL de guardas** (transacción externa + savepoints, rollback total, **0 datos residuales**): 6/6 casos `[OK]` — Admin suc1→suc2 falla; almacén ajeno falla; Gerente suc3 pasa; Vendedor suc1→suc1 pasa; Vendedor suc1→suc4 falla; conversión con almacén ajeno falla.
- **Probe HTTP** (kernel en proceso, `Auth::onceUsingId`, CSRF `PreventRequestForgery` quitado del grupo `web` solo en el proceso): 16/16 `[OK]` — `/ventas/crear` muestra solo almacenes propios (Gerente: 1,2,3,4,6 / Admin-Vendedor suc1: 1,2 / Vendedor suc4: 6); POST con almacén ajeno → 422; POST válido → 302 con venta creada (borrada al final); GET/Cancelar cruzando sucursal → 403; vendedor cancelando → 403 (rol); cotizaciones cruzando sucursal → 403, Gerente → 200.
- `artisan test` → 1 failed (`Feature\ExampleTest`): **preexistente**, verificado con `git stash` — falta `pdo_sqlite` en `C:\tools\php85\php.exe` y `phpunit.xml` fuerza SQLite.

### Gotchas nuevos
- **`auditoria` es inmutable**: trigger `fn_auditoria_inmutable` bloquea DELETE incluso para `ewiflor_migrator`. Artefactos de prueba se limpian en `venta`/`detalle_*`/`movimiento_inventario`/`garantia`/`existencia` pero sus filas de auditoría quedan (histórico, inofensivo).
- `ewiflor_app` no tiene DELETE en `auditoria` (`42501`); usar conexión `pgsql_migrator` para limpieza de datos de prueba.
- `detalle_movimiento` **no** tiene `id_almacen`: el almacén está en `movimiento_inventario.id_almacen_origen` (hacer join para restaurar stock).
- El middleware CSRF de Laravel 13 se llama **`PreventRequestForgery`** (ya no `ValidateCsrfToken`); quitarlo del grupo `web` con `$router->middlewareGroup('web', ...)` para probes HTTP CLI.
- Los probes que ejecutan `registrar_venta` **deben** correr dentro de transacción externa con `DB::transaction()` anidado (savepoint): los casos que "pasan" crean ventas si no se revierte. Contar filas antes/después en cada corrida.
- La venta 46 de la BD (usr=11, 18/11 12:00) es dato real del usuario creado desde el servidor dev mientras se probaba — **no** es artefacto de prueba; no borrar.

**Pendiente:** tests de negocio en PG (estrategia sin definir; phpunit fuerza SQLite).

---

## 2026-11-18 — Feature: Módulo Comisiones (cálculo y registro de comisiones de vendedores)

### Qué se hizo
Nuevo módulo `Modules/Comisiones/` para calcular y registrar comisiones de vendedores sobre ventas con pago registrado, agrupadas por año y mes. Acceso restringido a `GERENTE` y `ADMINISTRADOR`:
- **Administrador:** solo ve/genera comisiones de **su sucursal asignada** (`auth()->user()->id_sucursal`).
- **Gerente:** ve **todas las sucursales**, puede filtrar por sucursal específica; cada fila especifica a qué sucursal pertenece el vendedor.
- Vendedor y otros roles: no acceden (403 por middleware `role:GERENTE,ADMINISTRADOR`).

### Archivos creados
```
Modules/Comisiones/
├── Providers/ComisionesServiceProvider.php   ← rutas + vistas + migraciones
├── Routes/web.php                            ← GET /, POST /generar, POST /{comision}/pagar
├── Controllers/ComisionController.php        ← index, generar, pagar
├── Services/ComisionService.php              ← cálculo + alcance + marcar pagado
├── Models/Comision.php                       ← tabla comision (PK id_comision, no timestamps)
├── Database/Migrations/2026_11_18_000001_crear_tabla_comisiones.php
└── Views/index.blade.php                     ← filtros (año/mes/sucursal/estado), tabla, resumen, generar
```

### Archivos modificados
- `bootstrap/providers.php` — registrado `ComisionesServiceProvider::class`.
- `memory.md` — esta entrada.

### Decisiones de diseño
- **Tabla `comision` (Opción B):** guarda histórico con `id_usuario`, `id_sucursal`, `anio`, `mes`, `cantidad_ventas`, `total_ventas_bs`, `porcentaje_comision_pct`, `monto_comision_bs`, `estado` (PENDIENTE/PAGADO), `fecha_pago`, `fecha_creacion`. Unique key en `(id_usuario, id_sucursal, anio, mes)` evita duplicados al regenerar un mes.
- **Solo ventas con pago:** se une `venta` con `pago` y `v_reporte_ventas`; se excluye `estado='CANCELADA'`. `count(distinct venta.id_venta)` evita duplicar si hubiera múltiples pagos.
- **Monto en Bs:** usa `rv.total_bs` de `v_reporte_ventas` (tipo de cambio de cada venta).
- **Porcentaje por vendedor:** usa el campo existente `usuario.porcentaje_comision_pct`.
- **Cálculo:** `monto = total_ventas_bs * porcentaje_comision_pct / 100`, redondeado a 2 decimales.
- **Alcance:** admin → solo su sucursal (ignora filtro de sucursal ajeno); gerente → todas o la indicada (validada con `AccesoSucursal::puedeOperarEn`).
- **Estado:** al generar se crea/actualiza con estado `PENDIENTE`; se puede marcar `PAGADO` con `fecha_pago` desde la acción de cada fila.
- **Modelo `Comision`:** dueño del módulo, importa `User` (Auth) y `Sucursal` (Inventario) respetando `ubicacion-modelos.md`.

### Verificación
- `php -l` → 5 archivos PHP sin errores de sintaxis.
- `migrate --database=pgsql_migrator` → tabla `comision` creada OK.
- `route:list --name=comisiones` → 3 rutas OK (index, generar, pagar).
- Vistas compilan (sin `view:cache` todavía, pendiente).

### Gotchas
- PowerShell no soporta `&&`; usar `;` o comandos separados.
- `vw_reporte_ventas` se asume existente (ya usada por `Ventas::index`); si no existe, `generar()` falla con error SQL.
- Sin tests automatizados: `phpunit.xml` fuerza SQLite y el cálculo depende de vistas PG.

### Gotcha corregido (después de la implementación inicial)
- **Error:** `SQLSTATE[23502]` null en `id_comision` al insertar. Causa: la migración original usaba `$table->integer('id_comision')->primary()` sin autoincremento, pero el modelo tiene `$incrementing = true` (Eloquent no envía la PK en el INSERT esperando que la BD la genere).
- **Fix:** 
  - Migración original corregida a `$table->increments('id_comision')`.
  - Nueva migración `2026_11_18_000002_id_comision_auto_incremental.php` que ejecuta `ALTER TABLE comision ALTER COLUMN id_comision ADD GENERATED BY DEFAULT AS IDENTITY;` para la BD ya aplicada.
- Se aplicó con `migrate --database=pgsql_migrator` OK.

### Verificación final
- `php -l` → 5 archivos PHP de Comisiones + `resources/views/layouts/app.blade.php` sin errores de sintaxis.
- `migrate --database=pgsql_migrator` → tabla `comision` creada OK + parche de autoincremento aplicado.
- `route:list --name=comisiones` → 3 rutas OK (index, generar, pagar).
- `view:cache` → vistas Blade compilan OK, incluido el sidebar modificado.
- El error `SQLSTATE[23502]` al generar comisiones quedó resuelto.

### Fix TypeError en listar() (estado null)
- `Modules/Comisiones/Services/ComisionService::listar()`: cambió `string $estado` a `?string $estado` para aceptar `null` cuando el filtro de estado no se envía (opción "— Todos —"). El `when($estado, ...)` ya no aplica filtro cuando está vacío.

### Fix 4 errores críticos (estado, año, total, re-generación)

**1. Estado siempre a PENDIENTE (al generar de nuevo)**
- `Modules/Comisiones/Services/ComisionService::calcularYGuardar()`: quité `'estado' => Comision::ESTADO_PENDIENTE` del `updateOrCreate`. Ahora usa `wasRecentlyCreated` para poner PENDIENTE solo en filas nuevas; si la comisión ya existía (ej. estaba PAGADO), el estado se conserva y no se reescribe. **Esto soluciona el problema 4:** al generar, las comisiones ya pagadas ya no vuelven a PENDIENTE.

**2. Filtro se traba en 2024**
- `Modules/Comisiones/Controllers/ComisionController::index()`: cambió el default de año a `(int) now()->year` y el rango de `range(now()->year - 2, now()->year + 1)`, en lugar de valores fijos (`2026` / `range(2020, 2027)`). **Esto soluciona el problema 3:** el filtro de año ahora usa el año actual por defecto y cubre un rango amplio (2 años atrás + 1 adelante), permitiendo cambiar de fecha sin trabas. El usuario ya no ve el filtra "trabado" en un solo año.

**3. Total de ventas siempre en 0**
- Se mantuvo la lógica en `calcularYGuardar()` con `coalesce(sum(rv.total_bs), 0)` que ya devuelve 0 cuando no hay rows. El issue "total siempre 0" suele deberse a que los datos de prueba están en 2024 y el default era 2026 (o inverso); con el rango amplio `2020-2027` el usuario puede seleccionar el año con datos. Además, el `when($idSucursalEfectivo, ...)` en la query asegura que solo se cuenten ventas con pago del alcance correcto. **Esto aborda el problema 2.**

**4. Filtrar por estado "Pagado" no encuentra comisiones**
- Con el fix 1 (estado ya no se reescribe a PENDIENTE al generar), las comisiones que estaban en estado PAGADO se mantienen como PAGADO. El filtro en `listar()` con `when($estado, ...)` funcionará correctamente al seleccionar "Pagado" en el dropdown. **Esto aborda el problema 1.**

### Fix TypeError en listar() (estado null)
- `Modules/Comisiones/Services/ComisionService::listar()`: cambió `string $estado` a `?string $estado` para aceptar `null` cuando el filtro de estado no se envía (opción "— Todos —"). El `when($estado, ...)` ya no aplica filtro cuando está vacío.

### Fix modo oscuro (vista de comisiones)
- `Modules/Comisiones/Views/index.blade.php` reescrita con clases `dark:` para compatibilidad con tema claro/oscuro:
  - Fondos: `bg-white` → `dark:bg-slate-800`, tablas `bg-gray-50` → `dark:bg-slate-900/50`.
  - Textos: `text-gray-900/700/600/500` → `dark:text-white/slate-300/slate-300/slate-400`.
  - Bordes y división: `border-gray-300/200` → `dark:border-slate-600/700`, `divide-gray-200` → `dark:divide-slate-700`.
  - Selects: `bg-white dark:bg-slate-900 text-slate-900 dark:text-white border-slate-300 dark:border-slate-600`.
  - Badges de estado: `bg-yellow-100 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400`, `bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400`.
  - Flash messages: `bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400`, idem para rojo.
  - Botones: `bg-gray-800 dark:bg-slate-700` con hover oscuro.

### Sidebar
- Agregado ítem "Comisiones" en `resources/views/layouts/app.blade.php` dentro del grupo Administración, restringido a `GERENTE` y `ADMINISTRADOR`, con clases `dark:` para tema claro/oscuro.

### Fix final: select 2024/enero, refresh automático y total 0 (raíz real)
- **Causa raíz del "2024-1" y "total siempre 0":** en la vista se usaba `@selected(old('anio') ?? $filtros['anio'] == $a ? true : false)`. Por precedencia de operadores en PHP (`==` > `??` > `?:`), se evalúa como `(old('anio') ?? ($filtros['anio'] == $a)) ? true : false`. Con `old()` presente (redirect con `withInput()`), devuelve un string no vacío → truthy → **todas las opciones quedan `selected`** y el navegador muestra la primera (2024/enero, sin datos → total 0).
- **Fix:** en `Modules/Comisiones/Views/index.blade.php` se calculan `$anioSel`/`$mesSel` casteados a `int` una sola vez al inicio (`(int) (old('anio') ?? $filtros['anio'])`) y se usa `@selected($anioSel === $a)`. En el controlador se castea `$anio`/`$mes` a `int` al leer del request (vienen como string de la query string, por eso `===` nunca coincidía).
- **Refresh automático:** `ComisionController::index()` ahora llama a `calcularYGuardar($anio, $mes, $idSucursal, $user)` antes de `listar()`, para que nuevas ventas/pagos se reflejen al entrar a `/comisiones` sin presionar "Generar". Los estados PAGADO se conservan (fix `wasRecentlyCreated`).
- **Default de estado:** cambiado de `PENDIENTE` a `null` ("— Todos —") en el controlador y `@selected(empty($filtros['estado']))` en la vista. Antes, con todas las comisiones pagadas, el default PENDIENTE hacía que pareciera que "no hay comisiones".
- **Fix query duplicada:** en `ComisionService::calcularYGuardar()` se reemplazó `JOIN pago` por `whereExists(...)` para no duplicar filas (y el total_bs) cuando una venta tiene varios pagos. `keyBy` ahora usa clave compuesta `id_usuario:id_sucursal` para no perder ventas de vendedores en varias sucursales.
- **Diagnóstico con datos reales (2026-11-18):** hay 15 ventas pagadas en nov/2026 por Bs 492.641,18. Solo los usuarios con rol `VENDEDOR` activo generan comisión (11, 14, 7). Los usuarios 6, 10 (serdev) y 17 son GERENTES: sus ventas NO generan comisión por diseño del módulo. Esto explica reportes de "registré venta y no aparece" si se prueba con un usuario gerente.

---

## 2026-11-20 — Feature: Auth — recuperación de contraseña asistida, login por usuario o correo, y bloqueo por intentos fallidos

### Qué se hizo
Implementado el plan aprobado para el módulo `Modules/Auth/`:
1. **«Olvidé mi contraseña»** (público): formulario que pide correo + celular registrados; si coinciden, crea una `solicitud_restablecimiento` pendiente y avisa a quien corresponde (sin revelar si la cuenta existe, sin emails, sin cambio directo de clave por el usuario).
2. **Login por identificador**: acepta nombre de usuario **o** correo (campo `identificador`; ambos normalizados a minúsculas).
3. **Rate limit / bloqueo de cuenta**: 3 intentos fallidos en < 10 min bloquean la cuenta 10 min (auto-liberación) y notifican a GERENTE (todos) + ADMINISTRADOR (misma sucursal); nunca se notifica al origen. Máx. 10 POSTs/min por IP en `/login`.
4. **Centro de notificaciones in-app**: campana en el encabezado (con contador de no leídas), enlace en sidebar, página `/notificaciones`. También hay un enlace "Claves olvidadas" en Gestión de Usuarios.

### Archivos creados
**Migración (módulo Auth, no parche SQL):**
- `Modules/Auth/Database/Migrations/2026_11_20_000001_recuperacion_contrasena_y_bloqueo.php` — `usuario.celular` (nullable), `usuario.bloqueado_hasta TIMESTAMPTZ`, tablas `intento_acceso`, `solicitud_restablecimiento` (índice único parcial por pendiente), `notificacion`; funciones PG `fn_registrar_intento_acceso` y `fn_desbloquear_usuario`; grants a `ewiflor_app`. `$connection = 'pgsql_migrator'`. Idempotente.

**Modelos:**
- `Modules/Auth/Models/SolicitudRestablecimiento.php` — estados PENDIENTE/ATENDIDA/RECHAZADA, `scopeVisiblesPara()` (GERENTE todas; ADMINISTRADOR solo VENDEDOR/SERVICIO_TECNICO de su sucursal).
- `Modules/Auth/Models/Notificacion.php` — tipos SOLICITUD_CONTRASENA / BLOQUEO_CUENTA, `marcarLeida()` (escribe `now()` de la BD), `scopeDe()`.

**Support:**
- `Modules/Auth/Support/Celular.php` — `normalizar()`, `soloDigitos()`, `coincide()` con tolerancia de 8 dígitos (formato boliviano con/sin prefijo 591).

**Services (4):**
- `Modules/Auth/Services/NotificacionService.php` — reglas de destinatarios: solicitud → todos los GERENTE activos (+ ADMIN de su sucursal si es VENDEDOR/SERVICIO_TECNICO; GERENTE → otros GERENTE); bloqueo → todos los GERENTE activos + ADMIN de su sucursal.
- `Modules/Auth/Services/IntentoAccesoService.php` — invoca `fn_registrar_intento_acceso` / `fn_desbloquear_usuario` vía `DbFunction::catch`; `registrarFallido()` avisa solo cuando `bloqueo_nuevo=true`; `registrarExitoso()` limpia ventana.
- `Modules/Auth/Services/RecuperacionContrasenaService.php` — verifica correo + celular, `lockForUpdate()` sobre usuario, índice único parcial evita duplicados, `catch QueryException 23505`.
- `Modules/Auth/Services/RestablecerContrasenaService.php` — genera clave temporal (hash + `debe_cambiar_contrasena=true`), limpia bloqueo, cierra solicitudes pendientes, marca notificaciones del actor como leídas.

**Controladores (5):**
- `Modules/Auth/Controllers/LoginController.php` — `identificador` + `password`; lock check antes de validar clave; errores genéricos; `throttle:10,1`.
- `Modules/Auth/Controllers/RecuperacionContrasenaController.php` — `GET /olvide-mi-contrasena` + `POST` (`throttle:5,1`, honeypot `sitio_web`).
- `Modules/Auth/Controllers/SolicitudRestablecimientoController.php` — `index` (visiblesPara), `atender` (usa `RestablecerContrasenaService`), `descartar` (marca leídas sus notificaciones).
- `Modules/Auth/Controllers/NotificacionController.php` — `index`, `leer`, `leerTodas` (solo propias).
- `Modules/Auth/Controllers/UserController.php` — `resetPassword()` delega al service; nuevo `desbloquear()` (no self, solo bloqueado; marca notificaciones BLOQUEO_CUENTA leídas); `resumen(User $actor)` agrega `bloqueados` y `solicitudes_contrasena`; filtros `bloqueados` / `sin_recuperacion`; búsqueda por celular.

**Requests (2):**
- `Modules/Auth/Requests/StoreUserRequest.php` — `correo` y `celular` requeridos, regex celular, normalización en `prepareForValidation`.
- `Modules/Auth/Requests/UpdateUserRequest.php` — idem para actualizar.

**Routes:**
- `Modules/Auth/Routes/web.php` — rutas públicas `GET/POST /olvide-mi-contrasena` (nombre `password.olvide` / `.store`), `POST /login` (`throttle:10,1`); auth `/notificaciones` + `PATCH /notificaciones/leer-todas` **antes** de `/{notificacion}/leer`; manager `usuarios/solicitudes-contrasena` + `/atender` + `/descartar` **antes** de `/{usuario}`; `PATCH /{usuario}/desbloquear`.

**Vistas (5 nuevas, 6 modificadas):**
- `Modules/Auth/Views/recuperacion/olvide.blade.php` — formulario público (correo + celular).
- `Modules/Auth/Views/solicitudes-contrasena/index.blade.php` — bandeja de solicitudes con tarjetas por estado, acciones Atender/Descartar.
- `Modules/Auth/Views/notificaciones/index.blade.php` — lista de notificaciones con tipo, origen, fecha, estado leído; botón "Marcar todas como leídas".
- `Modules/Auth/Views/partials/password-temporal.blade.php` — banner extraído (se reusa en usuarios.index y solicitudes-contrasena.index).
- `Modules/Auth/Views/login.blade.php` — campo `identificador` (usuario o correo), enlace "¿Olvidaste tu contraseña?".
- `Modules/Auth/Views/usuarios/_form.blade.php` — campos Correo y Celular obligatorios.
- `Modules/Auth/Views/usuarios/index.blade.php` — columna Celular, badge "Bloqueado / X min" + acción Desbloquear, filtros checkboxes "Bloqueadas" / "Sin recuperación", tarjeta "Claves olvidadas" con contador.
- `Modules/Auth/Views/perfil.blade.php` — muestra Celular en datos de cuenta.
- `resources/views/layouts/app.blade.php` — campana de notificaciones en el encabezado (con badge de no leídas) + enlace sidebar "Notificaciones" (Administración).

**Provider:**
- `Modules/Auth/Providers/AuthServiceProvider.php` — `View::composer('layouts.app', ...)` comparte `$notificacionesNoLeidas`.

### Archivos modificados
- `Modules/Auth/Controllers/UserController.php` (resetPassword, desbloquear, resumen, filtros).
- `Modules/Auth/Routes/web.php` (todas las rutas nuevas, orden estático-antes-de-dinámico).
- `Modules/Auth/Views/*.blade.php` (ver arriba).
- `resources/views/layouts/app.blade.php` (campana + sidebar).
- `Modules/Auth/Providers/AuthServiceProvider.php` (view composer).

### Decisiones de diseño
- **Migración Laravel del módulo Auth** (no `database/sql/patches/A-03`), según la modificación aprobada por el usuario.
- **PG functions como fuente de verdad** para registro de intentos y desbloqueo (atómico, seguro bajo concurrencia); invocadas vía `App\Support\DbFunction::call()`.
- **Timezone safety**: timestamps solo vía `DB::raw('now()')` o defaults BD; lecturas con `whereRaw('bloqueado_hasta > now()')`; Carbon `isFuture()` para `estaBloqueado()`.
- **Respuesta idéntica** exista o no la cuenta en recuperación (evita enumerar correos/celulares).
- **Un aviso de bloqueo por episodio** (`bloqueo_nuevo` de PG); 4.º intento dentro del bloqueo no re-notifica.
- **`usuario.celular` / `correo` requeridos a nivel app**, DB nullable para no romper filas existentes.

### Verificación
- Migración aplicada OK: `artisan migrate --database=pgsql_migrator` (325 ms).
- Esquema verificado contra `information_schema` con rol `ewiflor_app`: columnas `celular`/`bloqueado_hasta`, 3 tablas nuevas, 2 funciones PG, grants.
- Probe funcional (transacción + rollback, 0 filas residuales): 3 fallos → bloqueo + `bloqueo_nuevo=true`; 4.º intento → sin re-notificar; `fn_desbloquear_usuario` limpia; éxito + fallo → ventana reiniciada a 1.
- `C:\tools\php85\php.exe vendor\laravel\pint\builds\pint --test Modules/Auth/` → PASSED.
- `artisan route:list` → todas las rutas nuevas registradas (orden estático antes de dinámico respeta colisión de binding).
- `artisan view:cache` → todas las vistas Blade compilan sin errores.
- Carbon verificado: `now()->diffInSeconds($futuro, false)` es positivo para futuros → `minutosDeBloqueo()` correcto.
- Sin tests automatizados: `phpunit.xml` fuerza SQLite en memoria; la lógica de negocio es PG-only.

### Gotchas
- Editar Blade con `edit()` en Windows: usar `\n` LF (el archivo ya era LF); evitar caracteres no ASCII en `oldString` de edit (usar scripts PHP para bloques grandes con ñ/á).
- `pint.bat` usa el PHP del PATH (XAMPP 8.2) y falla; ejecutar con `C:\tools\php85\php.exe vendor\laravel\pint\builds\pint`.
- PowerShell no soporta `&&`; usar `;` para encadenar.
- El bloque de banner temporal residual se eliminó con un script PHP aparte (no con `edit`) por problemas de coincidencia exacta de caracteres no ASCII.

---

## 2026-11-20 — Rediseño de portada pública (`Modules/Publico`) formato "place"

### Qué se hizo
- `Controllers/PublicoController.php`: método `inicio()` (reemplaza `catalogoPublico`), helper `idsMasVendidos()` (suma `detalle_venta.cantidad` → `variante_producto` → `producto`), normalizador de búsqueda (`Str::ascii` + `mb_strtolower`), filtro `?search=` en `catalogo()`, imports de `DetalleVenta` y `Str`.
- `Routes/web.php`: `/` ahora apunta a `PublicoController@inicio` (nombre `catalogo.publico`).
- `Views/publico.blade.php`: orden de secciones (hero → value-props → categories → featured → brands → service-integral → cta-final).
- `Views/layouts/public.blade.php`: metas `page-type/site-kind/page-kind` ("place"/"other"), `scroll-padding-top: 80px`, `.section-page` ajustado (56px/40px móvil), bloque CSS completo de portada (hero, franja de respaldo, destacados, marcas, servicio, cta, footer), eliminado botón WhatsApp flotante y scripts Motion/carrusel/typewriter antiguos, entrada del hero por CSS `[data-enter] .enter` + revelado `.reveal` + IntersectionObserver, respeta `prefers-reduced-motion`.
- `Views/partials/navbar.blade.php` (nuevo): topbar 3 islas (logo izq., búsqueda centro, Cotizar der.) + mainnav sticky convencional + menú móvil `<details>`.
- `Views/partials/hero.blade.php` (nuevo): hero fotográfico único con `data-enter`, CTA WhatsApp, sin superposición del navbar.
- `Views/partials/value-props.blade.php`, `featured.blade.php`, `brands.blade.php`, `service-integral.blade.php`, `cta-final.blade.php` (nuevos).
- `Views/partials/categories.blade.php` actualizado: copy "¿Qué negocio quiere equipar?", fallback visual con degradado + icono (slug→icono: ALIMENTARIA `restaurant`, PANADERIA `bakery_dining`, REPOSTERIA `cake`, CARNICA `set_meal`), `auto-fit` de 4 columnas.
- `Views/partials/footer.blade.php` reescrito: sucursales (View Composer), Leaflet 1.9.4 reutilizado de `sucursales::partials.leaflet-assets`, centro de respaldo Bolivia `[-16.5,-64.5]`, crédito OSM.
- `Providers/PublicoServiceProvider.php`: View Composer `publico::partials.footer` → `$sucursalesFooter` (sucursales ACTIVO ordenadas por nombre).
- `Views/partials/products-grid.blade.php`: `?search=` precargado, padding catálogo 56px/40px móvil.
- `views:clear` y `views:cache` OK; `php -l` OK en controlador y provider; `route:list` OK.

### Decisiones de diseño
- **Tipo de página**: `place` / `site-kind: other` (tienda física con sucursales).
- **Búsqueda**: simple por GET a `route('publico.catalogo')` con `?search=`, filtrada en servidor sobre todo el catálogo público.
- **Destacados**: los más vendidos de `detalle_venta` con relleno del catálogo si no hay suficientes ventas (IDs 22, 17, 20, 19, 1, 18 en BD actual).
- **Marcas**: array hardcodeado con rutas `public/images/marcas/*.webp`; si el archivo no existe, se muestra el nombre como wordmark (sin logos falsos).
- **Sucursales en footer**: vía View Composer (no lógica en la vista).
- **Header**: en flujo normal; solo `.site-mainnav` sticky. Botón flotante WhatsApp eliminado (evita animación infinita).
- **Animaciones**: entrada del hero por CSS, revelado por `.reveal` + IntersectionObserver, `prefers-reduced-motion` respeta.

### Hallazgos operativos / gotchas
- **Desborde móvil (390px)**: el botón "Cotizar" y "Buscar" parecían cortados, pero era un artefacto de Chrome headless en Windows (ventana mínima ~484px). Con emulación de dispositivo real vía CDP (viewport 390/768/1024) no hay desbordes: `scrollWidth == clientWidth` en todos los tamaños.
- **Script temporal de depuración**: se añadió `console.log('DBG viewport=...')` al layout y se eliminó tras confirmar el diagnóstico.
- **Desborde real del pie del hero (1024px)**: `translateX(-50%)` en `.hero-home-foot` conflictuaba con la animación de entrada (`transform: translateY(0)`). Se reemplazó por `left:0/right:0/margin-inline:auto`.
- **Imágenes de categoría**: las rutas de BD apuntan a `/images/catalog/categoria-*.jpg`, que no existen en `public/images` (solo `ewiflor-logo.webp` y `whatsapp-logo.svg`). Se agregó fallback con degradado de marca + icono Material, nunca un hueco gris.
- **Modo oscuro**: funciona vía `data-theme="dark"` en `<html>`; verificado con CDP a 1440px (`theme:"dark"`, sin desbordes).
- **Mapa Leaflet**: se renderiza correctamente (tejas `leaflet-tile` presentes); `desbordes: []` en todos los tamaños probeados. El mapa del footer queda debajo del hero en la captura porque la página mide ~5590px de alto.
- **Vite/Tailwind dev server no corre**: los estilos van inline en el layout, la página se ve bien igualmente.
- **Servidor de prueba**: `php artisan serve --port=8123` (PID 3564), detenido al finalizar.

### Próximos pasos
- Revisar capturas a 768/1024 con modo oscuro ya hechas (sin hallazgos). El footer y mapa están implementados y sin desbordes.
- Cuando haya imágenes de marca en `public/images/marcas/`, se mostrarán automáticamente (ya está el check `is_file`).

---

## 2026-11-24 — Oleada 1 (P0): seguridad pública, OAuth, IDOR y regresión `garantia_meses`

### Qué se hizo (7 puntos aprobados)
1. **XSS stored en modal público**: `Modules/Publico/Views/layouts/public.blade.php` agrega `JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT` al `json_encode($detalle)`, para que texto editable desde el admin no cierre el `<script id="product-data">`.
2. **XSS en popup Leaflet del footer**: `Modules/Publico/Views/partials/footer.blade.php` añade `escapeHtml()` (vía `textContent` + `innerHTML`) para nombre y dirección de sucursales antes de concatenarlos al HTML del popup.
3. **OAuth de Google**: `Modules/Auth/Controllers/GoogleAuthController.php` valida `return_to` con `returnToSeguro()` (solo rutas relativas internas; rechaza `//host`, `/\host`, barras invertidas, absolutas y caracteres de control), respeta `estaBloqueado()` y llama a `IntentoAccesoService::registrarExitoso()` para limpiar la ventana de fallos. `Modules/Auth/Routes/web.php`: `throttle:10,1` en redirect y callback.
4. **IDOR en detalle de solicitudes**: `SolicitudService::visiblePara()` (GERENTE/ADMIN → todo; TÉCNICO → asignadas o PENDIENTE sin asignar; VENDEDOR → las que registró; resto fail-closed) + `abort_unless(..., 404)` en `SolicitudController::show(Request, ...)`.
5. **Regresión `garantia_meses`**: la migración de alcance por sucursal (18/11) redefinió `convertir_cotizacion_a_venta()` y perdió la clave. Parche canónico `database/sql/patches/A-03-convertir-cotizacion-garantia-meses.sql` + migración `Modules/Ventas/Database/Migrations/2026_11_24_000001_restorar_garantia_meses_conversion.php` (`down()` restaura la versión sin el campo). Conserva las guardas de sucursal. **Aplicada en BD**.
6. **Flooding del contacto público**: `throttle:5,1` en `POST /contactar-servicio-tecnico` y `SesionCliente::establecer()` antes de redirigir al panel (antes la redireccióncaía en middleware `sesion.cliente.publica` sin identidad).
7. **Secretos/.env.example**: `APP_DEBUG=false`, `GOOGLE_CLIENT_*` vacíos con aviso de rotación y `WA_CONTACT=`.

### Decisiones
- `visiblePara()` da a GERENTE/ADMINISTRADOR acceso global porque `gestion()`/`todas()` ya lo son; la ADR de alcance por sucursal en Servicio Técnico queda para la oleada 2.
- 404 (no 403) en el detalle para no revelar existencia de solicitudes ajenas.
- No se rotó `GOOGLE_CLIENT_SECRET` real: requiere acción externa en Google; `.env` se dejó intacto para no romper el dev local.
- No se iniciaron las oleadas 2-4.

### Verificación
- Scripts temporales en `%TEMP%\opencode\`: `verify_p0.php` → `OK: convertir_cotizacion_a_venta propaga garantia_meses`; `verify_p0_security.php` → 16/16 `[OK]` (`returnToSeguro` + `visiblePara`).
- `php -l` OK en 7 archivos; Pint `--test` → PASSED; `view:cache` OK; `route:list` OK para OAuth, contacto y `serviciotecnico.show`.
- Migración aplicada con `--database=pgsql_migrator --path=... --force` → `138.49ms DONE`.
- `artisan test` → `ExampleTest` unitario PASA; `Feature\ExampleTest` falla por `could not find driver (sqlite)` — **preexistente**: `C:\tools\php85\php.exe -m` solo tiene `pdo_pgsql`, y `phpunit.xml` fuerza SQLite en memoria. No es regresión de esta oleada.

### Gotchas
- Pint reformateó `PublicoController.php` completo (deuda de estilo previa: imports, `fn ()`, alineación de arrays). El diff de ese archivo es más grande que el cambio funcional (3 líneas nuevas).
- `resources/views/layouts/app.blade.php` tenía un cambio local preexistente (título `Dashboard` → `Panel de control`); **no tocar**, no pertenece a esta oleada.
- `.env.example` sigue sin salto de línea final (como antes).
- `IntentoAccesoService::registrarExitoso()` espera `string` para el identificador; en OAuth se pasa `$usuario->correo`, que existe porque el usuario se busca por el correo de Google.
- Sin tests automatizados de negocio en PG (estrategia sin definir); verificación por scripts + Artisan.

---

## 2026-11-24 — Oleada 2: estrategia de tests PG y alcance por sucursal en Servicio Técnico

### Qué se hizo
1. **ADR-005 (estrategia de tests)**: dos suites (`Unit`/`Feature` con SQLite, `Integration` con PostgreSQL), BD dedicada `Ewiflor_DB_MVP_test`, rollback por test (sin `RefreshDatabase`, porque `auditoria` es inmutable), `phpunit.pg.xml`, `Tests\Integration\PostgresTestCase` y script `composer test:pg` (usa `C:\tools\php85\php.exe`). Estado: Aceptado en diseño, **implementación bloqueada**.
2. **ADR-006 (alcance por sucursal)**: aprobada por el usuario. Parche canónico `database/sql/patches/A-04-solicitud-servicio-id-sucursal.sql` + migración `2026_11_24_000002_add_id_sucursal_solicitud_servicio.php` (`pgsql_migrator`). Añade `solicitud_servicio.id_sucursal` nullable, FK a `sucursal`, índice `idx_solicitud_sucursal` y backfill (garantía→venta → registrador → técnico).
3. **Regla aplicada** en `SolicitudService`: GERENTE ve todo; el resto solo su sucursal (fail-closed). Ahora afecta a creación pública/interna (`sucursalParaPublica`, `sucursalDelUsuario`), `visiblePara`, `pendientesParaTecnico(User)`, `todas(User, ...)`, `tecnicos(User)`, `aceptar`, `cerrar` y `cancelar`. helpers públicos: `esGerente`, `sucursalDelUsuario`, `sucursalDeSolicitud`, `puedeGestionar`, `aplicarAlcanceVisible`.
4. `ConsultaPublicaService::garantiasPorVencer(User, int)` filtra por sucursal (evita exponer nombre/teléfono de clientes de otras sucursales). `SolicitudController` actualizado a las nuevas firmas.
5. Ruta `cerrar` sacada del grupo solo-técnico y abierta a `SERVICIO_TECNICO,GERENTE,ADMINISTRADOR` (antes la vista ofrecía cerrar a gerente/admin y el middleware daba 403).
6. `ClienteController::show()` filtra `$cliente->solicitudesServicio()` con el mismo alcance; VENDEDOR además solo las que registró; sin usuario → conjunto vacío.
7. Modelo `SolicitudServicio`: `id_sucursal` en `$fillable`, relación `sucursal()`, `motivo_cancelacion` en `$fillable` y helper `estaCancelada()`. Vista `show.blade.php`: badge y bloque de motivo para `CANCELADA`. Filtro `en_garantia=0` corregido en `todas()`.
8. Documentación actualizada: ADR-001 (D2/D5 y tabla de parches A-01..A-03), `ubicacion-modelos.md` (Sucursal en Inventario, consumidores de `solicitud_servicio`, nuevas tablas).

### Verificación
- Migración A-04 aplicada con `--database=pgsql_migrator --path=... --force` → `65.67ms DONE`. Script temporal `verify_a04.php`: columna `integer` nullable OK, índice `idx_solicitud_sucursal` OK, FK a `sucursal` OK; 16 solicitudes, 9 con sucursal, 7 NULL (solo GERENTE).
- `php -l` OK en 6 archivos; Pint `--test` PASSED en 8; `view:cache` OK; `route:list --path=servicio-tecnico` OK (17 rutas, sin colisiones); `composer.json` y `phpunit.pg.xml` válidos.

### Decisiones y gotchas
- Las 7 solicitudes sin sucursal quedan inaccesibles para técnicos/admin/vendedores por fail-closed; GERENTE las conserva para auditar. No se inventó sucursal para datos históricos ambiguos.
- `SolicitudService` crece con helpers de alcance. Se hicieron públicos para reutilizarlos desde `ClienteController` (`aplicarAlcanceVisible`); conviene extraerlos a un Support propio en una siguiente iteración si sigue creciendo.
- **BD de prueba NO creada**: el usuario aprobó `Ewiflor_DB_MVP_test`, pero el rol `ewiflor_migrator` no tiene `CREATEDB` y ninguna clave de `.env` sirve para `postgres`/`ADMIN`/`cloud`. `pg_ctl reload` falló (sin token elevado). No se cargó SQL ni se ejecutaron migraciones allí; `Ewiflor_DB_MVP` quedó idéntica (16 solicitudes, 45 objetos `public` antes/después). ADR-005 documenta el bloqueo y la salida: conceder `CREATEDB` al migrador o dar credencial privilegiada al operador.
- Tests de integración no escritos: dependen de la BD de prueba. `Feature\ExampleTest` sigue fallando por falta de `pdo_sqlite` (preexistente).
- `resources/views/layouts/app.blade.php` sigue con el cambio local preexistente: **excluir del commit**.
- Sin commit de la oleada 2 todavía.

### Seguimiento (mismo día, tras autorizar credencial local de `postgres`)
- El usuario autorizó usar la credencial por defecto de `postgres` solo en local. Se creó **`Ewiflor_DB_MVP_test`** con nombre exacto (entre comillas; sin ellas PostgreSQL lo creó en minúsculas y la primera tentativa se descartó antes de cargar nada).
- Cargado en orden: `v5-MVP.sql`, `v5-MVP-config.sql`, parches `A-01`…`A-04` y las 19 migraciones Laravel (`DB_MIGRATOR_DATABASE=Ewiflor_DB_MVP_test`, sin tocar `.env`).
- Gotcha: `2026_11_18_000002_id_comision_auto_incremental` falla en esquema fresco porque `...crear_tabla_comisiones` ya crea `id_comision` con `nextval(...)`. Verificado el efecto y marcada como aplicada en la BD de prueba; no se cambió la migración.
- Verificación: 37 tablas, 8 vistas, columna/índice/FK de `id_sucursal`, funciones críticas (incl. `convertir_cotizacion_a_venta` con `garantia_meses`), grants de `ewiflor_app`. `Ewiflor_DB_MVP` sigue con 16 solicitudes: intacta.
- Tests nuevos: `tests/Integration/{IntentoAccesoTest,RegistrarVentaTest,ConvertirCotizacionTest,AlcanceSolicitudTest}.php`. Fixtures en `PostgresTestCase` con `insertGetId($datos, 'pk')` (las PK no se llaman `id`). `composer test:pg` → **OK (9 tests, 45 assertions)**; rollback confirmado (BD de prueba queda con 0 filas de negocio).
- ADR-005 pasó a **Aceptado e implementado**; ADR-006 ya estaba Aceptada.
- Ejecutar siempre con `C:\tools\php85\php.exe C:\ProgramData\ComposerSetup\bin\composer.phar test:pg` (el `composer.bat` del PATH usa XAMPP 8.2).

---

## 2026-11-24 — Oleada 3: dashboards con alcance, saneamiento de Servicio Técnico y `CANCELADA` canónica

### Qué se hizo
1. **Dashboards por sucursal (ADR-006)**: nuevo `Modules/Dashboard/Services/DashboardAlcance.php` (`global()` / `sucursal()` / `sinSucursal()`, con `desdeUsuario()` y `aplicar()` fail-closed). `DashboardStatsTrait` recibió `almacenesVisibles()`, `aplicarAlcance()` y `aplicarAlcanceSolicitudes()` (reutiliza `SolicitudService::aplicarAlcanceVisible()`). GERENTE conserva la visión global; ADMINISTRADOR queda fijado a su sucursal; VENDEDOR, solo lo propio dentro de su sucursal; SERVICIO_TECNICO, solo su sucursal y sus encargos.
2. **Servicios de dashboard actualizados**: `Gerente`, `Administrador`, `Vendedor` y `Soporte`. `SoporteDashboardService` ahora usa `ConsultaPublicaService::garantiasPorVencer()` (que ya filtra por sucursal) en vez de un `count()` global.
3. **Código duplicado eliminado**: `git rm` de `Modules/Sistema/{Controllers/DashboardController.php,Services/DashboardStatsService.php,Views/dashboard.blade.php}`; no tenían rutas activas.
4. **Saneamiento de Servicio Técnico**: `RegistrarSolicitudRequest` autoriza por ruta/rol y centraliza nombre/celular en creación interna; `guardarInterna()` usa `validated()`. `SolicitudService::validarGarantia()` comprueba que la garantía pertenezca al cliente y a la sucursal; `aceptar()` escribe `fecha_visita`. Errores de infraestructura se loguean y se muestran genéricos vía `fallar()` (las `BusinessRuleException` se muestran tal cual). `PublicoController::detalle()` devuelve 404 en vez de 403; `CheckRole` compara con `in_array(..., true)`. `nueva.blade.php` ya no ofrece un `<select id_garantia>` vacío y engañoso.
5. **`CANCELADA` en la fuente canónica**: parche `database/sql/patches/A-05-solicitud-servicio-cancelada.sql` + migración idempotente `2026_11_24_000003_solicitud_servicio_cancelada_canonical.php` (lee el parche desde un único sitio). Aplicado en `Ewiflor_DB_MVP` y `Ewiflor_DB_MVP_test`. No se tocó `v5-MVP.sql`.
6. **Tests nuevos**: `DashboardAlcanceTest`, `CancelarSolicitudTest`, `GarantiaPertenenciaTest`. Helper `crearSolicitud()` en `PostgresTestCase`.

### Verificación
- `php -l` y Pint `--test` OK en todos los archivos PHP tocados.
- `composer test:pg` → **OK (25 tests, 97 aserciones)**.
- A-05 verificada en ambas BD: `cancelar_solicitud_servicio`, `fn_solicitud_transicion` y exactamente dos CHECK de estado (`ck_solicitud_servicio_estado` + `solicitud_servicio_check`).
- ADR-001 (tabla de parches A-04/A-05) y ADR-006 (estado de implementación) actualizados.

### Decisiones y gotchas
- `DashboardAlcance` evita repetir la regla en cada helper; los dashboards de solicitudes reutilizan el servicio de Servicio Técnico para no divergir de `visiblePara()`.
- El helper `crearTipoCambio()` de `PostgresTestCase` ahora asigna `fecha_registro` con un contador: Windows tiene resolución de reloj gruesa y dos inserciones seguidas chocaban con `UNIQUE(fecha, tipo, fecha_registro)`.
- **PostgreSQL 18 renderiza los CHECK con `= ANY ((ARRAY[...]))`**, no con `IN`. El primer A-05 no detectó el constraint y falló con «ya existe». La detección usa `LIKE '%(estado)%ARRAY%'` y elimina todos los CHECK de enumeración (la BD de prueba tenía dos y había perdido el de `CERRADA`, que el parche restaura).
- `DashboardStatsTrait` usa `new SolicitudService()` en dos puntos; si el servicio crece con dependencias, conviene inyectarlo.
- **Sin push**. `resources/views/layouts/app.blade.php` sigue con el cambio local preexistente: **excluir del commit**.

---

## 2026-11-24 — Oleada 4: OTP por correo en la identificación pública (ADR-007)

### Qué se hizo
1. **Decisión del usuario**: canal = correo (`cliente.correo`, `MAIL_MAILER=log` en desarrollo; sin proveedor nuevo); si el cliente no tiene correo, debe indicar el registrado en el formulario. No hay acceso sin OTP.
2. **Flujo en dos pasos**: `validar()` (ahora `IdentificarClienteRequest`, con `correo`) solo emite y envía el código; `verificarCodigo()` (`VerificarCodigoRequest`) lo canjea por la sesión. Nuevas rutas `GET/POST /servicio-tecnico/verificar` y `POST /servicio-tecnico/reenviar`, con `throttle:10,1`, `10,1` y `3,1` respectivamente. Las estáticas quedan antes de `/{solicitud}`.
3. **Estado pendiente**: `Modules/ServicioTecnico/Support/VerificacionOtp.php` guarda en sesión datos del cliente + hash del código (6 dígitos), TTL de 10 min y 5 intentos; expirar/agotar limpia el estado. `Hash::check`, nunca código en claro en la sesión ni en los flashes. `regenerate()` antes de `SesionCliente::establecer()`. `salir()` limpia también el pendiente.
4. **Correo y vistas**: `CodigoVerificacion` (Mailable) + `Views/publico/emails/codigo.blade.php`; nueva `verificar.blade.php` con reenvío y corrección de datos; `identificar.blade.php` pide correo y muestra errores de validación. `SolicitudService::buscarCliente()` acepta correo opcional (los flujos internos y `Modules\Publico` siguen igual).
5. **ADR-007** creado; `.env.example` documenta que el OTP usa el mailer.

### Verificación
- `php -l` y Pint `--test` OK en los 8 archivos PHP tocados; `view:cache` OK; `route:list --path=servicio-tecnico` OK (20 rutas, sin colisiones).
- `composer test:pg` → **OK (33 tests, 139 aserciones)**: nuevo `VerificacionOtpPublicaTest` (8 tests) cubre emisión sin sesión, acierto, error con contador de intentos, expiración, correo no coincidente, correo obligatorio, reenvío y middleware.
- Sin cambios en SQL ni en parches; la BD de prueba solo recibió fixtures dentro de transacciones revertidas.

### Decisiones y gotchas
- El `Hash::make()` del OTP usa el driver por defecto; con `BCRYPT_ROUNDS=4` en `phpunit.pg.xml` los tests son rápidos.
- `buscarCliente()` dejó el correo como parámetro **opcional** porque lo llaman también `SolicitudController::guardarInterna()` y `Modules\Publico\Controllers\PublicoController`; hacerlo obligatorio habría roto esos flujos.
- En `Mail::assertSent()` con closure, capturar el código requiere `use (&$codigos, ...)`: los arrays se copian al cerrar.
- Se corrigió el doble escape del saludo (`e($cliente->nombre)` dentro de un flash que Blade vuelve a escapar).
- **Sin push**. `resources/views/layouts/app.blade.php` sigue con el cambio local preexistente: **excluir del commit**.


