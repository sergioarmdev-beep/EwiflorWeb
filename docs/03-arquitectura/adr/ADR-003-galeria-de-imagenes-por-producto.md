# 4. Cambio de base de datos: galería de imágenes por producto

- **Fecha:** 2026-11-10
- **Estado:** Aceptado
- **Autores:** [Tu Nombre/Usuario]
- **Proyecto:** Ewiflor Importaciones
- **Módulo afectado:** Catálogo de productos
- **Motor de base de datos:** PostgreSQL

## Contexto

La tabla `producto` actual contiene los datos descriptivos y comerciales del producto, pero no contempla campos para imágenes ni existe una tabla relacionada que las administre. Por tanto, el esquema anterior no permite almacenar una galería de imágenes por producto.

Se requiere incorporar soporte para que un producto tenga varias imágenes, una de ellas marcada como principal y con un orden definido para su presentación en el catálogo.

## Decisión

### 1. Estructura y Diseño del Esquema

El cambio se implementa mediante una tabla independiente llamada `producto_imagen`. Rechazamos explícitamente agregar columnas fijas (`imagen_1`, `imagen_2`, etc.) a la tabla `producto` porque esa solución limita la cantidad de imágenes y obliga a modificar el esquema cada vez que cambie ese límite.

Se define la tabla con los siguientes campos y restricciones:

| Campo               | Tipo         | Restricciones / Propósito                               |
| :------------------ | :----------- | :------------------------------------------------------ |
| `id_imagen`         | SERIAL       | Clave primaria autoincremental                          |
| `id_producto`       | INTEGER      | FK a `producto.id_producto`, obligatorio                |
| `ruta_imagen`       | VARCHAR(500) | Ruta relativa del archivo almacenado                    |
| `texto_alternativo` | VARCHAR(255) | Texto alternativo opcional                              |
| `orden`             | SMALLINT     | Posición de la imagen; valor inicial 0 y no negativo    |
| `es_principal`      | BOOLEAN      | Indica si es la imagen principal; valor inicial `false` |
| `fecha_creacion`    | TIMESTAMPTZ  | Fecha de registro; valor inicial `CURRENT_TIMESTAMP`    |

### 2. Relaciones y Reglas de Integridad

- **Cardinalidad:** Un producto puede tener cero, una o muchas imágenes; cada imagen pertenece a un único producto.
- **Compatibilidad:** La clave foránea usa `INTEGER` para coincidir exactamente con el tipo de dato de `producto.id_producto`.
- **Eliminación:** Al eliminar un producto, PostgreSQL eliminará en cascada (`ON DELETE CASCADE`) sus registros en `producto_imagen`.

### 3. Restricciones e Índices Específicos (PostgreSQL)

Aprovechando las capacidades nativas de PostgreSQL, la migración incluirá:

- `chk_producto_imagen_orden`: Restricción `CHECK` que impide valores negativos en la columna `orden`.
- `uq_producto_imagen_principal`: Índice único parcial (`UNIQUE INDEX ... WHERE es_principal = true`) que permite como máximo **una** imagen principal por producto.
- `idx_producto_imagen_orden`: Índice compuesto para facilitar y optimizar la recuperación de las imágenes de un producto en el orden establecido.

### 4. Estrategia de Almacenamiento de Archivos

Los archivos binarios **no** se guardarán en PostgreSQL. La base de datos conservará únicamente la ruta relativa en `ruta_imagen`. En Laravel se guardarán las imágenes en un disco configurado de `Storage` (ej. el disco `public`) y se persistirá la ruta relativa devuelta por el almacenamiento (Ej: `productos/15/imagen-principal.webp`). Queda prohibido guardar rutas absolutas locales del equipo de desarrollo.

---

## Consecuencias

### Positivas

- **Escalabilidad:** Permite un número ilimitado de imágenes por producto sin alterar el esquema en el futuro.
- **Integridad a nivel de Datos:** El índice único parcial de PostgreSQL garantiza por software que ningún producto tenga más de una imagen principal de manera simultánea.
- **Rendimiento:** El índice especializado optimiza las consultas del catálogo público al ordenar la galería.

### Negativas / Fricciones de Implementación

- **Falta de Cobertura de Negocio en DB:** La restricción de imagen principal permite _cero_ imágenes principales; la regla de que todo producto tenga obligatoriamente una imagen principal debe ser resuelta e impuesta por la aplicación desde el flujo del CRUD.
- **Gestión de Archivos Huérfanos:** `ON DELETE CASCADE` solo borra registros en la DB. La aplicación (Laravel) debe encargarse explícitamente de la eliminación física de los archivos del disco al eliminar un producto o imagen para evitar acumular basura.

---

## Plan de Ajustes Requeridos en la Aplicación (CRUD)

Este ADR delimita que la migración sólo altera el esquema. Para completar la funcionalidad, el equipo debe adaptar el módulo del catálogo bajo las siguientes pautas:

- **Crear producto:** Añadir carga múltiple con validación MIME/tamaño. Ejecutar la persistencia del registro y las rutas dentro de una **transacción de base de datos**.
- **Editar producto:** Permitir reordenar, añadir y eliminar. Al cambiar la imagen principal, la lógica de Laravel debe primero desmarcar (`false`) la anterior y luego marcar (`true`) la nueva dentro de una transacción para no romper el índice único parcial de PostgreSQL.
- **Consultar catálogo:** Recuperar imágenes ordenadas por `orden` y secundariamente por `id_imagen`. Utilizar `es_principal` para listados generales y proveer una imagen de respaldo (_placeholder_) si el producto no tiene fotos.

## Verificación Posterior a la Migración

Tras ejecutar `php artisan migrate`, se deberán validar los siguientes escenarios en el entorno de pruebas:

1. Rechazo de inserciones con valores negativos en `orden`.
2. Rechazo de inserción si se intentan marcar dos imágenes como `es_principal = true` para el mismo `id_producto`.
3. Borrado automático de filas en `producto_imagen` al eliminar un registro en `producto`.
