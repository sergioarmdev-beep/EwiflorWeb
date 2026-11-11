# Arquitectura Modular de EwiflorWeb

## Decisión

Se ha optado por una arquitectura modular nativa sin paquetes externos. Toda la lógica de negocio se agrupa por dominios en la carpeta raíz `Modules/`.

## Estructura de un Módulo

Cada módulo (ej. `Auth`, `Catalogo`, `Ventas`) debe tener la siguiente estructura:

- `Controllers/`: Controladores del módulo.
- `Models/`: Modelos Eloquent del módulo.
- `Routes/`: Archivo `web.php` con las rutas.
- `Views/`: Vistas Blade (se llaman con el prefijo del namespace, ej: `auth::login`).
- `Providers/`: Proveedor de servicios para registrar rutas y vistas.

## Reglas para el equipo

1. **No crear archivos en `app/Models` o `app/Http/Controllers`** a menos que sean utilidades compartidas globales (ej. Middleware base).
2. **Namespaces:** Todo archivo dentro de `Modules/NombreModulo/...` debe llevar el namespace correspondiente: `namespace Modules\NombreModulo\...`.
3. **Rutas:** Las rutas se definen en `Modules/NombreModulo/Routes/web.php`. No se debe tocar el archivo central `routes/web.php` a menos que sea para redirecciones base.
4. **Vistas:** Las vistas se referencian con `modulo::nombre_vista` (ej. `catalogo::productos.index`).
