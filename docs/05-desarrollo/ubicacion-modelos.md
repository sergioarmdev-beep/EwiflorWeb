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
| `sucursal`               | `Sucursal`              | Inventario\* | Auth, Ventas, Cotizaciones                |
| `almacen`                | `Almacen`               | Inventario   | Ventas, Precios                           |
| `existencia`             | `Existencia`            | Inventario   | Ventas, Precios                           |
| `movimiento_inventario`  | `Movimiento`            | Inventario   | Ventas (cancelación)                      |
| `detalle_movimiento`     | `DetalleMovimiento`     | Inventario   | —                                         |
| `marca`                  | `Marca`                 | Catalogo     | —                                         |
| `producto`               | `Producto`              | Catalogo     | Precios, Inventario, Ventas, Cotizaciones |
| `variante_producto`      | `VarianteProducto`      | Catalogo     | Precios, Inventario, Ventas, Cotizaciones |
| `tipo_cambio`            | `TipoCambio`            | Precios      | Cotizaciones, Ventas, Reservas            |
| `cliente`                | `Cliente`               | Clientes     | Cotizaciones, Ventas, Garantías           |
| `cotizacion`             | `Cotizacion`            | Clientes     | Ventas                                    |
| `detalle_cotizacion`     | `DetalleCotizacion`     | Clientes     | Ventas                                    |
| `venta`                  | `Venta`                 | Ventas       | Garantías, Inventario                     |
| `detalle_venta`          | `DetalleVenta`          | Ventas       | Garantías                                 |
| `pago`                   | `Pago`                  | Ventas       | —                                         |
| `reserva`                | `Reserva`               | Ventas       | —                                         |
| `comprobante`            | `Comprobante`           | Ventas       | —                                         |
| `garantia`               | `Garantia`              | Garantias    | Ventas (consulta), Solicitudes            |
| `solicitud_servicio`     | `SolicitudServicio`     | Garantias    | —                                         |
| `auditoria`              | `Auditoria`             | Sistema      | —                                         |
| `parametro_sistema`      | `ParametroSistema`      | Sistema      | todos los que lean parámetros             |
| `permiso`, `rol_permiso` | `Permiso`, `RolPermiso` | Auth         | —                                         |

\* `Sucursal` vive temporalmente en `Modules/Auth` hasta que Dev 2 cree el
módulo Inventario. Ver sección 3.

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
