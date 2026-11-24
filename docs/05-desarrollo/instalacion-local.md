# Instalación y configuración del entorno local

**Proyecto:** EwiflorWeb  
**Documento:** Instalación local  
**Versión:** 1.3.0  
**Estado:** Aprobado por el equipo; ampliado con la oleada 4 (tests PG y CI)  
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
| PHP               | PHP 8.3 o superior; se ha verificado el proyecto con PHP 8.5.12         |
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

**Gotcha de Windows:** el `php` del PATH suele ser XAMPP 8.2 y no sirve:
Composer exige ≥ 8.4.1. En el entorno de referencia se usa
`C:\tools\php85\php.exe` (8.5.12). Todos los comandos `artisan`, Composer y
Pint de esta guía deben ejecutarse con ese binario.

### 3.3. Rutas de referencia

```powershell
$php = 'C:\tools\php85\php.exe'
$composer = 'C:\ProgramData\ComposerSetup\bin\composer.phar'
$psql = 'C:\Program Files\PostgreSQL\18\bin\psql.exe'
```

---

## 4. Obtención del código y dependencias

```powershell
git clone <URL-del-repositorio> EwiflorWeb
cd EwiflorWeb

& $php $composer install
copy .env.example .env
& $php artisan key:generate

pnpm install
pnpm build
```

El lockfile es de **pnpm** (`pnpm-lock.yaml`); los scripts de `composer.json`
dicen `npm`, pero para el frontend se usa `pnpm install` / `pnpm dev` /
`pnpm build`. `composer setup` funciona como atajo, aunque instala con npm.

---

## 5. Base de datos de desarrollo (`Ewiflor_DB_MVP`)

El nombre es `Ewiflor_DB_MVP` (**no** `ewiflor_db`). Los roles son
cluster-wide; si ya existen de otra BD, los scripts los reutilizan.

```powershell
$env:PGPASSWORD = '<contraseña-de-superusuario>'
& $psql -h 127.0.0.1 -U postgres -d postgres -c 'CREATE DATABASE "Ewiflor_DB_MVP";'

& $psql -h 127.0.0.1 -U postgres -d Ewiflor_DB_MVP -v ON_ERROR_STOP=1 -f database/sql/v5-MVP.sql
& $psql -h 127.0.0.1 -U postgres -d Ewiflor_DB_MVP -v ON_ERROR_STOP=1 -f database/sql/v5-MVP-config.sql
Get-ChildItem database/sql/patches/A-*.sql | Sort-Object Name | ForEach-Object {
    & $psql -h 127.0.0.1 -U postgres -d Ewiflor_DB_MVP -v ON_ERROR_STOP=1 -f $_.FullName
}
```

Poner contraseñas a los roles de aplicación (no versionarlas):

```sql
ALTER ROLE ewiflor_app PASSWORD '<contraseña>';
ALTER ROLE ewiflor_migrator PASSWORD '<contraseña>';
ALTER ROLE ewiflor_backup PASSWORD '<contraseña>';
```

`v5-MVP.sql` es la fuente de verdad del esquema: **no se edita in situ**.
Los cambios posteriores van en `database/sql/patches/A-*.sql` y, cuando
Laravel necesita versionarlos, en migraciones bajo `database/migrations/` o
`Modules/<Modulo>/Database/Migrations/`.

Rellenar `.env` con los mismos valores:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=Ewiflor_DB_MVP
DB_USERNAME=ewiflor_app
DB_PASSWORD=<contraseña>
DB_MIGRATOR_USERNAME=ewiflor_migrator
DB_MIGRATOR_PASSWORD=<contraseña>
```

Migraciones (se ejecutan con el rol migrador, **nunca** con `ewiflor_app`):

```powershell
$env:DB_MIGRATOR_DATABASE = 'Ewiflor_DB_MVP'
& $php artisan migrate --database=pgsql_migrator --force
```

**Usuario admin de demo:** el script inserta un hash `crypt()` de pgcrypto
que PHP 8.5 rechaza (`This password does not use the Bcrypt algorithm`). Si
no se puede entrar con `admin` / `admin123`, regenerar el hash:

```powershell
$hash = & $php -r "require 'vendor/autoload.php'; echo Illuminate\Support\Facades\Hash::make('admin123');"
& $psql -h 127.0.0.1 -U ewiflor_migrator -d Ewiflor_DB_MVP -c "UPDATE usuario SET contrasena_hash = '$hash' WHERE nombre_usuario = 'admin';"
```

---

## 6. Base de datos de pruebas (`Ewiflor_DB_MVP_test`)

Ninguna suite debe escribir nunca en `Ewiflor_DB_MVP`. `phpunit.xml` y
`phpunit.pg.xml` fijan `DB_CONNECTION=pgsql_test` y
`DB_TEST_DATABASE=Ewiflor_DB_MVP_test` (ADR-005). La conexión `pgsql_test`
se define en `config/database.php` y admite `DB_TEST_HOST`, `DB_TEST_PORT`,
`DB_TEST_USERNAME`, `DB_TEST_PASSWORD` y `DB_TEST_URL`.

En CI la BD se recrea desde cero con:

```bash
PGPASSWORD=postgres_ci bash tools/ci/prepare-test-db.sh
```

El script carga `v5-MVP.sql`, `v5-MVP-config.sql`, los parches `A-01`…`A-05`
(como `ewiflor_owner`, para heredar los grants) y ejecuta las migraciones con
`pgsql_migrator`. Requiere un rol con `CREATEDB`.

En Windows, sin `bash`, se puede preparar manualmente con los mismos pasos:
crear la BD, cargar los SQL anteriores, crear la tabla `migrations` con el
dueño `ewiflor_owner` y ejecutar:

```powershell
$env:DB_MIGRATOR_DATABASE = 'Ewiflor_DB_MVP_test'
& $php artisan migrate --database=pgsql_migrator --force
```

Si aparece `permiso denegado a la tabla ...`, conceder grants sobre el esquema
`public` a `ewiflor_app` (ver `v5-MVP-config.sql`).

---

## 7. Comprobaciones y ejecución

```powershell
& $php $composer test          # Unit + Feature sobre pgsql_test
& $php $composer test:pg       # Integración: funciones PG y reglas transaccionales
& $php vendor/bin/pint --test
& $php artisan route:list
& $php artisan view:cache
& $php artisan view:clear
pnpm build

& $php $composer dev           # serve + queue + pail + vite
```

La aplicación queda en `http://127.0.0.1:8000`. `FORCE_HTTPS=true` (o
`APP_ENV=production`) fuerza HTTPS desde `AppServiceProvider`.

### Herramientas de apoyo

Los antiguos scripts sueltos de la raíz se sustituyeron por comandos Artisan:

```powershell
& $php artisan ewiflor:usuarios:listar       # sin exponer hashes
& $php artisan ewiflor:cotizaciones:listar   # cotizaciones y nº de líneas
```

`tools/ci/prepare-test-db.sh` es el único aprovisionamiento desechable
soportado. `composer.phar`, `composer-setup.php`, el instalador de
Cloudflared y los perfiles de `.taste-skill/` / `.agents/` están ignorados
por Git y no deben versionarse.

---

## 8. Referencias

- `AGENTS.md` (reglas de arquitectura y base de datos).
- `docs/03-arquitectura/adr/ADR-001-configuracion-tras-v5-mvp.md`.
- `docs/03-arquitectura/adr/ADR-005-estrategia-tests-postgresql.md`.
- `docs/05-desarrollo/funciones-postgresql.md` y `docs/05-desarrollo/auditoria.md`.

