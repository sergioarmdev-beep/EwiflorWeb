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
