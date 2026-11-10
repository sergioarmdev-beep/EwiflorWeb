# División de Módulos y Responsabilidades

## Arquitectura

Se utiliza una arquitectura modular nativa en la carpeta `Modules/`. Cada desarrollador es dueño de sus carpetas y no debe modificar los archivos de otros módulos sin coordinación previa. Las vistas globales están en `resources/views/layouts/app.blade.php`.

## Asignación de Módulos (MVP)

### Desarrollador 1 (Coordinador / Core) - Tú

- **Módulos:** `Auth` (Login/Seguridad), `Catalogo` (Productos/Marcas/Variantes).
- **Responsabilidad:** Mantener el layout principal (`layouts/app.blade.php`), asegurar la integración de los módulos de los demás y supervisar los pull requests.

### Desarrollador 2 - Módulo Comercial

- **Módulos:** `Clientes`, `Ventas`, `Inventario`.
- **Tareas (SRS):**
  - MVP-03, MVP-04: Inventario (Entradas, salidas, ajustes, transferencias).
  - MVP-05: CRUD de Clientes.
  - MVP-08: Registrar Venta (interfaz y controlador). _Nota: La lógica transaccional pesada ya está en la función SQL `registrar_venta()`_.
- **Archivos a crear:** `Modules/Clientes/`, `Modules/Ventas/`, `Modules/Inventario/`.

### Desarrollador 3 - Módulo Operativo

- **Módulos:** `Cotizaciones`, `Reportes`, `TipoCambio`.
- **Tareas (SRS):**
  - MVP-06: Tipo de Cambio (registro manual diario).
  - MVP-07: Cotizaciones (interfaz y controlador).
  - MVP-09: Reportes básicos (usar las vistas SQL `v_reporte_ventas` y `v_reporte_existencias`).
- **Archivos a crear:** `Modules/Cotizaciones/`, `Modules/Reportes/`.

## Reglas de Integración

1. Para las operaciones que afectan el stock o el dinero, **NO usen lógica de Laravel**. Deben llamar a las funciones de PostgreSQL (`registrar_venta`, `registrar_movimiento_inventario`) mediante `DB::select()`.
2. Para los reportes, **NO hagan consultas complejas en Eloquent**. Usen las vistas SQL (`v_reporte_ventas`, etc.) mapeadas en modelos de solo lectura.
