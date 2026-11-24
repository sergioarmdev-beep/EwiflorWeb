# Ubicación de modelos compartidos

**Propósito:** evitar que dos módulos creen el mismo modelo con nombres,
campos o lógica distintos. Cada tabla del esquema PostgreSQL tiene **un
único módulo dueño**, y los demás módulos la consumen importando el modelo.

**Regla de oro:** el módulo dueño define el modelo, la migración (si
aplica) y las relaciones. Los módulos consumidores **solo importan**.
Nunca se duplica un modelo.

---

## 1. Mapa de modelos por módulo dueño

| Tabla PostgreSQL         | Modelo                  | Módulo dueño | Consumido por                             |
| ------------------------ | ----------------------- | ------------ | ----------------------------------------- |
| `usuario`                | `User`                  | Auth         | todos                                     |
| `sucursal`               | `Sucursal`              | Inventario   | Auth, Ventas, Cotizaciones, Sucursales    |
| `almacen`                | `Almacen`               | Inventario   | Ventas, Precios                           |
| `existencia`             | `Existencia`            | Inventario   | Ventas, Precios                           |
| `movimiento_inventario`  | `Movimiento`            | Inventario   | Ventas (cancelación)                      |
| `detalle_movimiento`     | `DetalleMovimiento`     | Inventario   | —                                         |
| `marca`                  | `Marca`                 | Catalogo     | —                                         |
| `producto`               | `Producto`              | Catalogo     | Precios, Inventario, Ventas, Cotizaciones |
| `producto_imagen`        | `ProductoImagen`        | Catalogo     | Publico (ADR-003)                         |
| `producto_documento`     | `ProductoDocumento`     | Catalogo     | Catalogo                                  |
| `categoria`              | `Categoria`             | Catalogo     | Publico (`Modules/Catalogo/Models/categoria.php`) |
| `variante_producto`      | `VarianteProducto`      | Catalogo     | Precios, Inventario, Ventas, Cotizaciones |
| `tipo_cambio`            | `TipoCambio`            | Precios      | Cotizaciones, Ventas, Reservas            |
| `cliente`                | `Cliente`               | Clientes     | Cotizaciones, Ventas, Garantías           |
| `cotizacion`             | `Cotizacion`            | Cotizaciones | Ventas, ServicioTecnico                   |
| `detalle_cotizacion`     | `DetalleCotizacion`     | Cotizaciones | Ventas                                    |
| `venta`                  | `Venta`                 | Ventas       | Garantías, Inventario                     |
| `detalle_venta`          | `DetalleVenta`          | Ventas       | Garantías                                 |
| `pago`                   | `Pago`                  | Ventas       | —                                         |
| `comprobante`            | `Comprobante`           | Ventas       | —                                         |
| `garantia`               | `Garantia`              | Garantias    | Ventas (consulta), Solicitudes            |
| `solicitud_servicio`     | `SolicitudServicio`     | Garantias    | ServicioTecnico, Clientes, Dashboard     |
| `auditoria`              | `Auditoria`             | Sistema      | — (solo lectura; escritura por triggers)  |
| `promocion`              | `Promocion`             | Promociones  | Ventas, Cotizaciones, Publico, Catalogo   |
| `promocion_variante`     | `PromocionVariante`     | Promociones  | Catalogo (variantes.promociones)          |
| `combo_componente`       | `ComboComponente`       | Promociones  | Catalogo (Producto::componentesCombo)     |
| `notificacion`           | `Notificacion`          | Auth         | layout global (`layouts/app.blade.php`)   |
| `solicitud_restablecimiento` | `SolicitudRestablecimiento` | Auth    | Auth (ADR-004)                            |
| `solicitud_cuenta`       | `SolicitudCuenta`       | Auth         | Auth (ADR-004)                            |
| `comision`               | `Comision`              | Comisiones   | —                                         |

No tienen modelo propio (se accede por funciones PostgreSQL o query builder):

- `intento_acceso`: `fn_registrar_intento_acceso`, `fn_desbloquear_usuario`.
- `reserva`: `registrar_reserva` y el resto de funciones de reservas.
- `parametro_sistema`: lectura directa desde los servicios que lo necesitan.
- `permiso`, `rol_permiso`: tablas de configuración, gestionadas por SQL.
- El OTP de la identificación pública no es una tabla: `VerificacionOtp` es
  un helper de sesión en `Modules/ServicioTecnico/Support` (ADR-007).

`Sucursal` vive en `Modules/Inventario/Models/Sucursal.php`. El helper
`Modules/Sucursales\Support\AlcanceDocumentos` importa ese modelo; no existe
un modelo `Sucursal` en `Modules/Auth`.

---

## 2. Patrón de importación

### 2.1 Relación directa (FK)

El modelo consumidor declara la relación apuntando a la **ruta completa**
del modelo dueño:

```php
// Modules/Ventas/Models/Venta.php
use Modules\Clientes\Models\Cliente;
use Modules\Precios\Models\TipoCambio;
use Modules\Inventario\Models\Sucursal;

public function cliente(): BelongsTo
{
    return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
}

public function tipoCambio(): BelongsTo
{
    return $this->belongsTo(TipoCambio::class, 'id_tipo_cambio', 'id_tipo_cambio');
}
```
