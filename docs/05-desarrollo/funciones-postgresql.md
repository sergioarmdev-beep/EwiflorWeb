# Llamada a funciones PostgreSQL desde Laravel

**Propósito:** estandarizar cómo Laravel invoca las funciones de negocio que
viven en PostgreSQL (`registrar_venta`, `registrar_movimiento_inventario`,
`registrar_reserva`, `cancelar_venta`, `convertir_cotizacion_a_venta`), y
cómo se transforman los errores de negocio (`RAISE EXCEPTION`) en mensajes
para el usuario.

**Regla de oro:** nunca se usa `DB::select()` crudo en controladores ni en
modelos. Toda llamada pasa por `App\Support\DbFunction`.

---

## 1. ¿Por qué la lógica vive en PostgreSQL?

Según el ADR (documento de arquitectura, D-05 y sección 8), las reglas de
negocio que tocan varias tablas a la vez se implementan como funciones
transaccionales en la base de datos. Motivos:

- **Atomicidad:** una venta toca `venta`, `detalle_venta`, `movimiento_inventario`,
  `detalle_movimiento`, `existencia` y `garantia`. Si algo falla a mitad,
  PostgreSQL revierte todo.
- **Consistencia bajo concurrencia:** los `SELECT ... FOR UPDATE` y los
  `CHECK` declarativos garantizan que dos cajeros no vendan la última unidad
  a la vez.
- **Una sola fuente de verdad:** las reglas críticas no se duplican entre
  Laravel y la base.

Laravel no reimplementa esas reglas: **solo las invoca** y traduce sus
errores.

---

## 2. Funciones disponibles

Todas viven en el esquema `public` y fueron creadas por `v5-MVP.sql` y el
parche `A-01`. Firma exacta según el script:

| Función                           | Parámetros                                                                                            | Retorno                                        |
| --------------------------------- | ----------------------------------------------------------------------------------------------------- | ---------------------------------------------- |
| `registrar_movimiento_inventario` | `(tipo_movimiento, VARCHAR, INTEGER, INTEGER, INTEGER, INTEGER, JSONB)`                               | `INTEGER` (id_movimiento)                      |
| `registrar_venta`                 | `(INTEGER, INTEGER, INTEGER, INTEGER, JSONB, INTEGER)`                                                | `INTEGER` (id_venta)                           |
| `convertir_cotizacion_a_venta`    | `(INTEGER, INTEGER, INTEGER)`                                                                         | `INTEGER` (id_venta)                           |
| `registrar_reserva`               | `(VARCHAR, INTEGER, INTEGER, INTEGER, INTEGER, NUMERIC, INTEGER, NUMERIC, VARCHAR, INTEGER, INTEGER)` | `INTEGER` (id_reserva)                         |
| `cancelar_venta`                  | `(INTEGER, INTEGER, VARCHAR)`                                                                         | `VOID`                                         |
| `fn_autenticar_usuario`           | `(VARCHAR, TEXT)`                                                                                     | `TABLE(id_usuario, nombre_rol, exito, motivo)` |

> `fn_autenticar_usuario` **no se usa desde Laravel**. La autenticación la
> hace `Auth::attempt()` con bcrypt nativo (ADR-001). La función existe en la
> base por si en el futuro se requiere un login a nivel de BD, pero el MVP
> no la invoca.

### 2.1 Reglas que aplican las funciones

- `registrar_movimiento_inventario`: rechaza salidas, transferencias y
  ajustes negativos sin disponibilidad; los ajustes exigen `sentido`.
- `registrar_venta`: valida la cotización (estado `EMITIDA` y no vencida),
  exige almacén por línea si el producto es inventariable, descuenta stock,
  marca la venta como `REGISTRADA` y convierte la cotización.
- `registrar_reserva`: exige anticipo mínimo del 10 %, vigencia máxima de
  30 días (parámetro `RESERVA_VIGENCIA_DIAS`) y disponibilidad para
  `RESERVA`; `BAJO_PEDIDO` no bloquea stock.
- `cancelar_venta`: exige motivo no vacío, revierte el stock con movimientos
  de `ENTRADA` y no permite reactivar.
- `convertir_cotizacion_a_venta`: helper que arma las líneas desde
  `detalle_cotizacion` y llama a `registrar_venta`.

---

## 3. El wrapper `App\Support\DbFunction`

### 3.1 Código

**Archivo:** `app/Support/DbFunction.php`

```php
<?php

namespace App\Support;

use App\Exceptions\BusinessRuleException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class DbFunction
{
    /**
     * Invoca una función PostgreSQL y devuelve la primera fila (o null).
     *
     * @throws BusinessRuleException si la función lanza RAISE EXCEPTION (SQLSTATE P0001)
     * @throws QueryException        si el error no es de negocio
     */
    public static function call(string $function, array $params = []): mixed
    {
        $placeholders = implode(',', array_fill(0, count($params), '?'));
        $sql = "SELECT * FROM {$function}({$placeholders})";

        try {
            $rows = DB::select($sql, array_values($params));
            return $rows[0] ?? null;
        } catch (QueryException $e) {
            if ($e->getCode() === 'P0001') {
                $message = $e->errorInfo[2]
                    ?? $e->getPrevious()?->getMessage()
                    ?? 'Error de negocio';
                throw new BusinessRuleException($message, previous: $e);
            }
            throw $e;
        }
    }

    /**
     * Igual que call(), pero devuelve solo el primer campo de la primera fila.
     * Útil para funciones que retornan un escalar (INTEGER, BOOLEAN, etc.).
     */
    public static function scalar(string $function, array $params = []): mixed
    {
        $row = self::call($function, $params);
        if ($row === null) {
            return null;
        }
        return array_values((array) $row)[0] ?? null;
    }
}
```
