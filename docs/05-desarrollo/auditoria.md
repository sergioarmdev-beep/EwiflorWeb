# Auditoría — cómo funciona y qué NO hacer

## Resumen

La auditoría de operaciones se registra **automáticamente desde PostgreSQL**
mediante triggers. **No se escriben observers en Laravel.**

## Triggers activos (definidos en `v5-MVP.sql`)

| Trigger                         | Tabla                   | Función                               |
| ------------------------------- | ----------------------- | ------------------------------------- |
| `trg_aud_usuario`               | `usuario`               | `fn_auditar_cambio('id_usuario')`     |
| `trg_aud_producto`              | `producto`              | `fn_auditar_cambio('id_producto')`    |
| `trg_aud_variante_producto`     | `variante_producto`     | `fn_auditar_cambio('id_variante')`    |
| `trg_aud_cliente`               | `cliente`               | `fn_auditar_cambio('id_cliente')`     |
| `trg_aud_cotizacion`            | `cotizacion`            | `fn_auditar_cambio('id_cotizacion')`  |
| `trg_aud_venta`                 | `venta`                 | `fn_auditar_cambio('id_venta')`       |
| `trg_aud_movimiento_inventario` | `movimiento_inventario` | `fn_auditar_cambio('id_movimiento')`  |
| `trg_aud_tipo_cambio`           | `tipo_cambio`           | `fn_auditar_cambio('id_tipo_cambio')` |

Además, `trg_auditoria_no_update` bloquea `UPDATE` y `DELETE` sobre la tabla
`auditoria` a nivel de base de datos (RNF-005).

## Cómo se identifica al usuario que ejecutó la acción

`fn_auditar_cambio` lee `current_setting('app.id_usuario', true)`.
Ese valor lo fija el middleware `SetDatabaseUser` al inicio de cada request:

```php
DB::statement('SELECT set_config(?, ?, false)', ['app.id_usuario', auth()->id()]);
```

Si no hay usuario autenticado (login, catálogo público), la auditoría se
registra con `id_usuario = NULL`.

## Regla

- No agregar `Observer` a `User`, `Producto`, `Venta`, etc.
- No insertar manualmente en `auditoria` desde Laravel.
- Confiar en los triggers.
- Si se agrega una tabla nueva que necesita auditoría, crear su trigger
  en una migración (no en código PHP).

## Verificación rápida

```sql
SELECT tabla_afectada, operacion, COUNT(*)
FROM auditoria
GROUP BY tabla_afectada, operacion
ORDER BY tabla_afectada, operacion;
```

Si el conteo es 0 después de operar desde la app, algo falta: revisar que
`SetDatabaseUser` esté en el grupo `web` de `bootstrap/app.php`.
