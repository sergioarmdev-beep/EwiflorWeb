# ADR-002: Arquitectura Modular Nativa

## Contexto

El proyecto requiere escalar en funcionalidades (Catálogo, Ventas, Inventario, etc.). Un monolito tradicional de Laravel (todo en `app/Models` y `app/Http/Controllers`) tiende a desordenarse rápidamente cuando trabajan varias personas. Se evaluó el uso de paquetes como `nwidart/laravel-modules`, pero se descartó por la sobrecarga de configuración y la dependencia de terceros.

## Decisión

Implementar una **arquitectura modular nativa** utilizando el estándar PSR-4 de Composer. Toda la lógica de negocio se agrupará en la carpeta raíz `Modules/`. Cada módulo tendrá su propio namespace, controladores, modelos, rutas y vistas, y se registrará mediante un `ServiceProvider`.

## Consecuencias

- **Positivas:** Separación clara de responsabilidades, menos conflictos en Git (cada quien toca su carpeta), fácil extracción a microservicios en el futuro si fuera necesario.
- **Negativas:** Requiere que el equipo entienda el concepto de _Namespaces_ y `ServiceProviders` de Laravel.
- **Regla:** Queda prohibido crear lógica de negocio en `app/Models` o `app/Http/Controllers`, salvo utilidades globales (ej. Middleware base). Los módulos se ubican en `Modules/[NombreModulo]`.
