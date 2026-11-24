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

- **Módulos:** `Cotizaciones`, `Precios`, `Dashboard`.
- **Tareas (SRS):**
  - MVP-06: Tipo de Cambio (registro manual diario) → `Modules/Precios`.
  - MVP-07: Cotizaciones (interfaz y controlador) → `Modules/Cotizaciones`.
  - MVP-09: Reportes y dashboards → `Modules/Dashboard` (dashboard canónico;
    no crear un segundo dashboard en `Sistema`).
- **Archivos a crear:** `Modules/Cotizaciones/`, `Modules/Precios/`,
  `Modules/Dashboard/`.

### Módulos transversales

`Garantias` (modelos; las filas las crea el trigger
`fn_crear_garantias_venta`), `ServicioTecnico`, `Publico`, `Sucursales`,
`Promociones` y `Comisiones`. No hay módulo `Reportes` ni `TipoCambio`: sus
responsabilidades están en `Dashboard` y `Precios`. El mapa vigente de modelos
por módulo dueño está en `docs/05-desarrollo/ubicacion-modelos.md`.

## Reglas de Integración

1. Para las operaciones que afectan el stock o el dinero, **NO usen lógica de
   Laravel**. Deben llamar a las funciones de PostgreSQL (`registrar_venta`,
   `registrar_movimiento_inventario`, etc.) mediante
   `App\Support\DbFunction::call()`, `::scalar()` o `::query()`, nunca con
   `DB::select()` crudo. Un `RAISE EXCEPTION` se traduce en
   `BusinessRuleException`.
2. Para los reportes, se prefieren las vistas SQL mapeadas en modelos de solo
   lectura. El alcance por sucursal se resuelve con
   `Modules\Dashboard\Services\DashboardAlcance` (ADR-006), no duplicando
   consultas en cada módulo.
3. La auditoría la escriben los triggers de PostgreSQL. No crear Observers ni
   inserciones manuales en `auditoria` desde Laravel.
