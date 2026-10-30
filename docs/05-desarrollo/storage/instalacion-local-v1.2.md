# Instalación y configuración del entorno local

**Proyecto:** EwiflorWeb  
**Documento:** Instalación local  
**Versión:** 1.2  
**Estado:** Aprobado por el equipo  
**Stack:** Laravel 13, PHP 8.5, Blade, PostgreSQL 18, Node.js, pnpm y Vite  
**Sistema operativo de referencia:** Windows 10/11 con PowerShell

---

## 1. Objetivo

Este documento establece el procedimiento para preparar el entorno de desarrollo local de EwiflorWeb, obtener el código fuente, instalar las dependencias, configurar las variables de entorno, conectar la aplicación con PostgreSQL y ejecutar las comprobaciones básicas.

El procedimiento busca que todos los integrantes del equipo trabajen con una configuración coherente y reproducible, reduciendo los problemas derivados de diferencias entre entornos.

## 2. Alcance

Esta guía cubre:

- Instalación y verificación de las herramientas de desarrollo.
- Obtención del repositorio desde GitHub.
- Instalación de dependencias PHP y JavaScript.
- Configuración del archivo de entorno.
- Preparación de la conexión con PostgreSQL, incluidos los roles de base de datos.
- Traspaso de propiedad de los objetos del esquema y ajuste de privilegios.
- Ejecución de migraciones y pruebas.
- Compilación de recursos frontend.
- Inicio y verificación de la aplicación.

Esta guía no cubre el despliegue en producción, la configuración de un servidor público ni la implementación de módulos funcionales de inventario, compras, ventas o administración.

## 3. Requisitos previos

### 3.1. Herramientas necesarias

| Herramienta       | Requisito                                                               |
| ----------------- | ----------------------------------------------------------------------- |
| Sistema operativo | Windows 10/11 de 64 bits                                                |
| Git               | Instalado y disponible desde PowerShell                                 |
| PHP               | PHP 8.3 o superior; se ha verificado el proyecto con PHP 8.5.11         |
| Composer          | Composer 2.x; se ha utilizado 2.10.3                                    |
| Node.js           | Versión compatible con el proyecto; se ha utilizado Node.js 24.13.0     |
| pnpm              | Gestor de paquetes del proyecto; se ha utilizado pnpm 12.4.2            |
| PostgreSQL        | Servidor local instalado y accesible; se ha utilizado PostgreSQL 18.3   |
| `psql`            | Cliente de línea de comandos de PostgreSQL, instalado junto al servidor |
| Editor            | Visual Studio Code u otro editor compatible                             |

**Importante:** PHP, Composer, Node.js y pnpm son herramientas diferentes. Tener una instalada no garantiza que las demás estén disponibles o utilicen la versión correcta.

### 3.2. Extensiones de PHP

La instalación de PHP debe incluir las extensiones requeridas por Laravel y sus dependencias. En el entorno de referencia se habilitaron:

- `curl`
- `fileinfo`
- `gd`
- `intl`
- `mbstring`
- `openssl`
- `pdo_pgsql`
- `pgsql`
- `zip`

También deben estar disponibles las extensiones exigidas por las dependencias de Composer.

No es suficiente con que los comandos de PHP funcionen: deben comprobarse tanto la versión activa como las extensiones cargadas. La extensión `pdo_pgsql` es imprescindible para que Laravel pueda comunicarse con PostgreSQL.

## 4. Verificación del entorno

Abrir PowerShell y ejecutar los siguientes comandos individualmente.

### 4.1. Verificar Git

```powershell
git --version
```
