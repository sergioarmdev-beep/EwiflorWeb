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

Debe mostrarse la versión instalada de Git.

### 4.2. Verificar PHP

```powershell
php -v
php --ini
php -m
where.exe php
```

Estos comandos permiten comprobar:

- La versión activa de PHP.
- El archivo `php.ini` utilizado.
- Las extensiones cargadas.
- Las ubicaciones de los ejecutables encontrados en el `PATH`.

Si existen varias instalaciones de PHP, como una instalación independiente y otra incluida en XAMPP, debe confirmarse que PowerShell utiliza la versión prevista para el proyecto.

En el entorno de referencia, PHP 8.5.11 está instalado en:

```text
C:\tools\php85
```

La instalación independiente se utiliza para el desarrollo de EwiflorWeb, mientras que la instalación de PHP incluida en XAMPP se conserva sin modificaciones.

Si `php -v` muestra una versión incorrecta, revisar el `PATH` de Windows antes de continuar.

### 4.3. Verificar Composer

```powershell
composer --version
composer diagnose
```

`composer diagnose` comprueba aspectos de la instalación y conectividad. Si aparece un error de red temporal, puede repetirse la comprobación antes de modificar la configuración.

En el entorno de referencia se utilizó Composer 2.10.3.

### 4.4. Verificar Node.js y pnpm

```powershell
node --version
npm --version
pnpm --version
```

En el entorno de referencia se utilizaron:

```text
Node.js: 24.13.0
pnpm: 12.4.2
```

Estas versiones describen el entorno probado y no sustituyen la comprobación de compatibilidad de futuras dependencias.

### 4.5. Verificar PostgreSQL

Una vez instalado PostgreSQL, comprobar que su cliente está disponible:

```powershell
psql --version
```

En el entorno de referencia se utilizó PostgreSQL 18.3.

Si el comando no se reconoce, comprobar la instalación y el `PATH`. La ausencia del cliente no demuestra por sí sola que el servidor no esté instalado.

También debe confirmarse que el servicio de PostgreSQL está iniciado:

```powershell
Get-Service -Name postgresql*
```

El estado debe ser `Running`. Si no lo está, iniciarlo desde `services.msc` o con:

```powershell
Start-Service postgresql-x64-XX   # sustituir XX por la versión instalada
```

## 5. Obtener el código fuente

El repositorio privado de EwiflorWeb está alojado en GitHub:

```text
https://github.com/sergioarmdev-beep/EwiflorWeb.git
```

El acceso requiere una cuenta autorizada y la configuración correspondiente de autenticación de GitHub.

### 5.1. Clonar el repositorio

Elegir un directorio de trabajo y ejecutar:

```powershell
New-Item -ItemType Directory -Force "$HOME\Projects"
Set-Location "$HOME\Projects"
git clone https://github.com/sergioarmdev-beep/EwiflorWeb.git
Set-Location "$HOME\Projects\EwiflorWeb"
```

Si el directorio ya contiene el repositorio, no volver a ejecutar `git clone`. En ese caso, acceder directamente al directorio existente.

### 5.2. Verificar la rama y el estado

```powershell
git branch --show-current
git status
```

La rama de trabajo inicial es `main`. Debe comprobarse que el repositorio está sincronizado y que no existen modificaciones locales inesperadas.

Antes de comenzar a trabajar en una tarea, consultar las instrucciones de colaboración y el flujo de ramas acordado por el equipo.

## 6. Instalar las dependencias de PHP

Desde la raíz del proyecto, ejecutar:

```powershell
composer install
```

Composer instalará las dependencias declaradas en `composer.lock`, procurando mantener las mismas versiones entre los integrantes del equipo.

No se recomienda utilizar `composer update` como parte de la instalación rutinaria, ya que puede modificar las versiones resueltas y el archivo de bloqueo.

A continuación, verificar los requisitos de la plataforma:

```powershell
composer check-platform-reqs
```

Si aparecen extensiones ausentes o incompatibilidades de versión, corregirlas antes de continuar.

**No se debe versionar la carpeta `vendor/`**, ya que Composer puede reconstruirla a partir de los archivos de dependencias.

## 7. Configurar las variables de entorno

### 7.1. Crear el archivo `.env`

En la raíz del proyecto:

```powershell
Copy-Item .env.example .env
```

Este paso se realiza únicamente cuando el archivo `.env` todavía no existe. Si ya existe, no debe sobrescribirse, porque podría contener configuración local.

El archivo `.env` contiene valores específicos del entorno, como la conexión a la base de datos y la clave de cifrado de Laravel.

### 7.2. Generar la clave de aplicación

```powershell
php artisan key:generate
```

Laravel generará una clave de aplicación y la escribirá en el archivo `.env`.

La clave debe mantenerse privada. No debe compartirse en capturas, mensajes, repositorios ni archivos de configuración versionados.

### 7.3. Configurar el entorno de desarrollo

Revisar las siguientes variables en `.env`:

```dotenv
APP_NAME=EwiflorWeb
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
```

Estos valores corresponden a un entorno de desarrollo local. No deben copiarse automáticamente a producción, especialmente `APP_DEBUG`.

### 7.4. Configurar la conexión con PostgreSQL

Las variables de base de datos se organizan en dos grupos, cada uno con un rol distinto de PostgreSQL. Este diseño sigue el principio de menor privilegio: la aplicación cotidiana (`ewiflor_app`) no puede alterar el esquema ni escribir directamente en la auditoría, mientras que las tareas administrativas usan un rol separado (`ewiflor_migrator`).

````dotenv
# Aplicación (rol principal, uso cotidiano)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE="Ewiflor_DB_MVP"
DB_USERNAME=ewiflor_app
DB_PASSWORD=<CONTRASEÑA_APP>

# Migraciones (rol propietario del esquema)
DB_MIGRATOR_CONNECTION=pgsql_migrator
DB_MIGRATOR_HOST=127.0.0.1
DB_MIGRATOR_PORT=5432
DB_MIGRATOR_DATABASE="Ewiflor_DB_MVP"
DB_MIGRATOR_USERNAME=ewiflor_migrator
DB_MIGRATOR_PASSWORD=<CONTRASEÑA_MIGRATOR>

### 7.5. Configurar las conexiones adicionales en `config/database.php`

Además de la conexión `pgsql` estándar, el archivo `config/database.php` define conexiones paralelas que reutilizan el mismo servidor con distintos roles:

```php
'pgsql_migrator' => [
    'driver'         => 'pgsql',
    'host'           => env('DB_HOST', '127.0.0.1'),
    'port'           => env('DB_PORT', '5432'),
    'database'       => env('DB_DATABASE', 'laravel'),
    'username'       => env('DB_MIGRATOR_USERNAME', 'ewiflor_migrator'),
    'password'       => env('DB_MIGRATOR_PASSWORD', ''),
    'charset'        => env('DB_CHARSET', 'utf8'),
    'prefix'         => '',
    'prefix_indexes' => true,
    'search_path'    => 'public',
    'sslmode'        => env('DB_SSLMODE', 'prefer'),
],
'pgsql_auth' => [
    'driver'         => 'pgsql',
    'host'           => env('DB_HOST', '127.0.0.1'),
    'port'           => env('DB_PORT', '5432'),
    'database'       => env('DB_DATABASE', 'laravel'),
    'username'       => env('DB_AUTH_USERNAME', 'ewiflor_auth'),
    'password'       => env('DB_AUTH_PASSWORD', ''),
    'charset'        => env('DB_CHARSET', 'utf8'),
    'prefix'         => '',
    'prefix_indexes' => true,
    'search_path'    => 'public',
    'sslmode'        => env('DB_SSLMODE', 'prefer'),
],
'pgsql_backup' => [
    'driver'         => 'pgsql',
    'host'           => env('DB_HOST', '127.0.0.1'),
    'port'           => env('DB_PORT', '5432'),
    'database'       => env('DB_DATABASE', 'laravel'),
    'username'       => env('DB_BACKUP_USERNAME', 'ewiflor_backup'),
    'password'       => env('DB_BACKUP_PASSWORD', ''),
    'charset'        => env('DB_CHARSET', 'utf8'),
    'prefix'         => '',
    'prefix_indexes' => true,
    'search_path'    => 'public',
    'sslmode'        => env('DB_SSLMODE', 'prefer'),
],
````

La conexión `pgsql` (sin sufijo) sigue siendo la conexión por defecto y es la que usa `ewiflor_app`.

---

## Sección 7.5

### 7.5. Configurar las conexiones adicionales en `config/database.php`

Además de la conexión `pgsql` estándar, el archivo `config/database.php` define una conexión paralela que reutiliza el mismo servidor con el rol migrador:

````php
'pgsql_migrator' => [
    'driver'         => 'pgsql',
    'host'           => env('DB_MIGRATOR_HOST', '127.0.0.1'),
    'port'           => env('DB_MIGRATOR_PORT', '5432'),
    'database'       => env('DB_MIGRATOR_DATABASE', 'Ewiflor_DB_MVP'),
    'username'       => env('DB_MIGRATOR_USERNAME', 'ewiflor_migrator'),
    'password'       => env('DB_MIGRATOR_PASSWORD', ''),
    'charset'        => env('DB_CHARSET', 'utf8'),
    'prefix'         => '',
    'prefix_indexes' => true,
    'search_path'    => 'public',
    'sslmode'        => env('DB_SSLMODE', 'prefer'),
],

## 8. Instalar las dependencias frontend

El proyecto utiliza pnpm para administrar las dependencias de JavaScript y Vite para compilar los recursos frontend.

Desde la raíz del repositorio:

```powershell
pnpm install
````

`````

Este comando instala las dependencias definidas en `package.json` y el archivo de bloqueo `pnpm-lock.yaml`.

Si pnpm solicita autorizar scripts de compilación de dependencias, revisar el paquete afectado antes de aprobarlo. En el entorno de referencia se autorizó el script de compilación de `esbuild`, necesario para completar el flujo de compilación.

Después, generar los recursos:

```powershell
pnpm run build
```

La compilación debe finalizar sin errores y generar, entre otros archivos, el manifiesto de Vite en:

```text
public/build/manifest.json
```

La carpeta `public/build/` contiene archivos generados. No debe añadirse al repositorio si está excluida por `.gitignore`.

Durante el desarrollo frontend también puede utilizarse:

```powershell
pnpm run dev
```

Este comando inicia el servidor de desarrollo de Vite. Debe mantenerse activo en una terminal separada mientras se utiliza esa modalidad de desarrollo.

## 9. Configurar PostgreSQL

La base de datos de EwiflorWeb se crea ejecutando un script SQL consolidado que define el esquema completo, los roles técnicos y los datos iniciales. El script está versionado junto al proyecto y debe ejecutarse **antes** de cualquier migración de Laravel.

---

## Sección 9.1 (reemplazar completa)

````markdown
### 9.1. Estructura de roles

El diseño usa cuatro roles de PostgreSQL para separar responsabilidades:

| Rol                | Uso                                           | LOGIN | Comentario                                    |
| ------------------ | --------------------------------------------- | ----- | --------------------------------------------- |
| `ewiflor_owner`    | Propietario lógico de los objetos del esquema | No    | Rol de agrupación; no se conecta directamente |
| `ewiflor_migrator` | Ejecuta migraciones y cambios de esquema      | Sí    | Miembro de `ewiflor_owner`                    |
| `ewiflor_app`      | Conexión habitual de la aplicación            | Sí    | Sin permiso `CREATE` sobre el esquema         |
| `ewiflor_backup`   | Lectura para respaldos                        | Sí    | Solo `SELECT`                                 |

Esta separación evita que la aplicación pueda modificar el esquema o escribir directamente en la auditoría, y sigue el principio de menor privilegio.

### 9.2. Crear la base de datos y ejecutar el script SQL

Como superusuario (`postgres`), crear la base:

```sql
CREATE DATABASE "Ewiflor_DB_MVP"
    WITH ENCODING 'UTF8'
    LC_COLLATE 'Spanish_Bolivia.1252'
    LC_CTYPE  'Spanish_Bolivia.1252'
    TEMPLATE  template0;
`````

Los nombres de las reglas de colación pueden variar según la instalación; si el sistema no las reconoce, usar las que devuelva `SELECT * FROM pg_collation;`.

A continuación, ejecutar el script SQL consolidado en el orden indicado por sus propios comentarios:

1. **PASO 0 — Extensiones** (como superusuario, fuera de transacción):

   ```sql
   CREATE EXTENSION IF NOT EXISTS pgcrypto;
   CREATE EXTENSION IF NOT EXISTS pg_trgm;
   CREATE EXTENSION IF NOT EXISTS unaccent;
   ```

2. **PASO 1 — Esquema** (transaccional). Crea todas las tablas, vistas, funciones, triggers y datos iniciales. Termina con `COMMIT`.

3. **PASO 2 — Roles técnicos**. Crea los roles, transfiere la propiedad de las funciones a `ewiflor_owner` y concede los permisos iniciales.

4. **PASO 3 — Límites por rol**. Aplica `statement_timeout`, `lock_timeout`, `search_path` y `CONNECTION LIMIT` a cada rol.

El script es idempotente en la mayoría de sus bloques (`CREATE ... IF NOT EXISTS`, `DO $$ ... $$`), pero conviene ejecutarlo sobre una base vacía para evitar duplicaciones de datos iniciales.

### 9.3. Asignar contraseñas a los roles

El script crea los roles sin contraseña. Hay que asignarlas antes de usarlos, como superusuario:

```sql
ALTER ROLE ewiflor_app      WITH LOGIN PASSWORD '<CONTRASEÑA_APP>';
ALTER ROLE ewiflor_migrator WITH LOGIN PASSWORD '<CONTRASEÑA_MIGRATOR>';
ALTER ROLE ewiflor_auth     WITH LOGIN PASSWORD '<CONTRASEÑA_AUTH>';
ALTER ROLE ewiflor_backup   WITH LOGIN PASSWORD '<CONTRASEÑA_BACKUP>';
```

Reglas para las contraseñas: sin `#`, sin espacios, sin comillas simples. Anotarlas fuera del repositorio.

Además, para que las migraciones se ejecuten como `ewiflor_owner` y los objetos nuevos hereden los privilegios correctos:

```sql
ALTER ROLE ewiflor_migrator SET role = ewiflor_owner;
```

### 9.4. Traspasar la propiedad de los objetos a `ewiflor_owner`

El script original transfiere la propiedad de las **funciones** a `ewiflor_owner`, pero deja las **tablas, vistas y secuencias** a nombre del rol que ejecutó el PASO 1 (habitualmente `postgres`). Eso genera una inconsistencia: varias funciones son `SECURITY DEFINER` y se ejecutan con los privilegios de su propietario (`ewiflor_owner`); si ese rol no es dueño de las tablas ni tiene permisos sobre ellas, cualquier trigger falla con `permiso denegado`. Es el caso concreto del trigger `trg_aud_usuario` al insertar en `usuario`.

Para alinear todo bajo el mismo propietario, ejecutar como superusuario:

```sql
DO $$
DECLARE
    r RECORD;
    owner_oid oid := (SELECT oid FROM pg_roles WHERE rolname = 'ewiflor_owner');
BEGIN
    -- 1) Tablas y vistas
    FOR r IN
        SELECT c.relname, c.relkind
          FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
         WHERE n.nspname = 'public'
           AND c.relkind IN ('r','p','v')
           AND c.relowner <> owner_oid
    LOOP
        IF r.relkind = 'v' THEN
            EXECUTE format('ALTER VIEW  public.%I OWNER TO ewiflor_owner', r.relname);
        ELSE
            EXECUTE format('ALTER TABLE public.%I OWNER TO ewiflor_owner', r.relname);
        END IF;
    END LOOP;

    -- 2) Secuencias NO enlazadas a tablas (las enlazadas ya cambiaron con su tabla)
    FOR r IN
        SELECT c.relname
          FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
         WHERE n.nspname = 'public'
           AND c.relkind = 'S'
           AND c.relowner <> owner_oid
           AND NOT EXISTS (
                 SELECT 1 FROM pg_depend d
                  WHERE d.objid = c.oid
                    AND d.deptype IN ('a','i')
               )
    LOOP
        EXECUTE format('ALTER SEQUENCE public.%I OWNER TO ewiflor_owner', r.relname);
    END LOOP;

    -- 3) Permisos de respaldo por si algún objeto quedó fuera
    EXECUTE 'GRANT USAGE ON SCHEMA public TO ewiflor_owner';
    EXECUTE 'GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES    IN SCHEMA public TO ewiflor_owner';
    EXECUTE 'GRANT USAGE, SELECT                  ON ALL SEQUENCES IN SCHEMA public TO ewiflor_owner';
END $$;
```

**Advertencia sobre secuencias enlazadas:** las secuencias creadas por columnas `GENERATED ALWAYS AS IDENTITY` (como `usuario_id_usuario_seq`) están vinculadas a su tabla y **no se pueden cambiar de dueño directamente**. Un `ALTER SEQUENCE ... OWNER TO ...` sobre ellas falla con `SQLSTATE 0A000: no se puede cambiar el dueño de la secuencia ... está enlazada a la tabla`. El bloque anterior las excluye a propósito: cambian automáticamente al transferir la tabla de la que dependen.

Cambiar la propiedad no revoca los privilegios ya concedidos. Aun así, conviene reaplicar los GRANT explícitos a `ewiflor_app` para asegurarse de que todo quedó bien:

```sql
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES    IN SCHEMA public TO ewiflor_app;
GRANT USAGE, SELECT                  ON ALL SEQUENCES IN SCHEMA public TO ewiflor_app;

REVOKE UPDATE, DELETE ON auditoria, auditoria_detalle FROM ewiflor_app;
REVOKE UPDATE, DELETE ON tipo_cambio                  FROM ewiflor_app;
REVOKE INSERT, UPDATE, DELETE ON rol, permiso, rol_permiso FROM ewiflor_app;
GRANT  SELECT                ON rol, permiso, rol_permiso TO ewiflor_app;
```

#### Verificación del traspaso

Para confirmar que ningún objeto quedó a nombre de `postgres`:

```sql
SELECT c.relkind,
       count(*) FILTER (WHERE c.relowner <> owner_oid) AS aun_de_postgres,
       count(*) FILTER (WHERE c.relowner  = owner_oid) AS de_ewiflor_owner
  FROM pg_class c
  JOIN pg_namespace n ON n.oid = c.relnamespace
  CROSS JOIN (SELECT oid AS owner_oid FROM pg_roles WHERE rolname = 'ewiflor_owner') o
 WHERE n.nspname = 'public'
   AND c.relkind IN ('r','p','v','S')
 GROUP BY c.relkind
 ORDER BY c.relkind;
```

La columna `aun_de_postgres` debe ser `0` en todas las filas. Si alguna tabla o vista quedó fuera (por ejemplo porque se creó después del bloque `DO`), se puede traspasar puntualmente:

```sql
ALTER TABLE public.nombre_tabla OWNER TO ewiflor_owner;
-- o
ALTER VIEW  public.nombre_vista OWNER TO ewiflor_owner;
```

### 9.5. Permisos para objetos futuros

Una vez que `ewiflor_owner` es propietario de todo, los objetos que se creen en el futuro a través de la conexión `pgsql_migrator` (que hace `SET ROLE ewiflor_owner`) también quedarán a nombre de `ewiflor_owner`. Por eso los `ALTER DEFAULT PRIVILEGES` relevantes son los de ese rol, no los de `postgres`:

```sql
-- La app debe poder usar las tablas y secuencias que se creen en el futuro
ALTER DEFAULT PRIVILEGES FOR ROLE ewiflor_owner IN SCHEMA public
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO ewiflor_app;
ALTER DEFAULT PRIVILEGES FOR ROLE ewiflor_owner IN SCHEMA public
    GRANT USAGE, SELECT ON SEQUENCES TO ewiflor_app;

-- Backup: solo lectura sobre lo que se cree en el futuro
ALTER DEFAULT PRIVILEGES FOR ROLE ewiflor_owner IN SCHEMA public
    GRANT SELECT ON TABLES TO ewiflor_backup;
ALTER DEFAULT PRIVILEGES FOR ROLE ewiflor_owner IN SCHEMA public
    GRANT SELECT ON SEQUENCES TO ewiflor_backup;
```

Si además se quiere cubrir el caso de que algún objeto se cree como `postgres` (por ejemplo desde pgAdmin sin `SET ROLE`), añadir los mismos bloques para ese rol:

```sql
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO ewiflor_app;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public
    GRANT USAGE, SELECT ON SEQUENCES TO ewiflor_app;
```

**Nota sobre tablas concretas:** los `ALTER DEFAULT PRIVILEGES` solo afectan a objetos creados **después** de su definición. Las tablas `migrations`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches` y `failed_jobs` ya existían antes de configurar estos privilegios, así que `ewiflor_app` necesita un GRANT explícito si en algún momento se recrean:

```sql
GRANT SELECT, INSERT, UPDATE, DELETE
    ON migrations, sessions, cache, cache_locks, jobs, job_batches, failed_jobs
    TO ewiflor_app;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO ewiflor_app;
```

Las restricciones intencionadas sobre las tablas de dominio de la aplicación siguen vigentes y **no se ven afectadas por el cambio de propietario**:

```sql
REVOKE UPDATE, DELETE ON auditoria, auditoria_detalle FROM ewiflor_app;
REVOKE UPDATE, DELETE ON tipo_cambio                  FROM ewiflor_app;
REVOKE INSERT, UPDATE, DELETE ON rol, permiso, rol_permiso FROM ewiflor_app;
GRANT  SELECT                ON rol, permiso, rol_permiso TO ewiflor_app;
```

### 9.6. Comprobar la conexión

Con el servidor, la base de datos, los roles y las credenciales configurados:

```powershell
php artisan config:clear
php artisan db:show
```

Si la conexión funciona, `db:show` muestra el motor, host, puerto, base de datos, usuario y el listado de tablas. Se esperan **59 tablas** en el estado inicial: 52 del script SQL y 7 de Laravel.

También puede usarse `psql` directamente para verificar un rol concreto:

```powershell
psql -h 127.0.0.1 -p 5432 -U ewiflor_app -d ewiflor_db -c "\conninfo"
```

Si aparece un error, verificar:

1. Que el servicio de PostgreSQL esté iniciado.
2. Que el host y el puerto sean correctos.
3. Que la base de datos exista.
4. Que el usuario y la contraseña sean válidos.
5. Que el usuario tenga permisos suficientes.
6. Que PHP tenga cargada la extensión `pdo_pgsql`.

No utilizar `migrate:fresh` como procedimiento de diagnóstico: elimina las tablas existentes y puede provocar pérdida de datos.

## 10. Ejecutar las migraciones

Las migraciones de Laravel crean tablas de infraestructura (`sessions`, `cache`, `jobs`, etc.) que no forman parte del script SQL. Deben ejecutarse con la conexión `pgsql_migrator`, porque `ewiflor_app` no tiene privilegio `CREATE` sobre el esquema `public`:

```powershell
php artisan migrate --database=pgsql_migrator
```

Salida esperada en el estado inicial:

```text
   INFO  Preparing database.

  0001_01_01_000000_create_users_table ......................................... DONE
  0001_01_01_000001_create_cache_table ......................................... DONE
  0001_01_01_000002_create_jobs_table .......................................... DONE
```

El archivo `0001_01_01_000000_create_users_table.php` ha sido adaptado para **no crear** las tablas `users` ni `password_reset_tokens`, que no se usan porque la autenticación vive en la tabla `usuario`. Solo crea `sessions`.

Verificar el estado con la conexión normal:

```powershell
php artisan migrate:status
```

Debe mostrar las tres migraciones como `Ran`.

En esta etapa, las migraciones corresponden únicamente a la estructura de infraestructura. La ejecución satisfactoria no significa que los módulos empresariales estén implementados.

Antes de aplicar migraciones en una base de datos compartida, revisar los cambios y confirmar que no afecten datos que deban conservarse.

## 11. Ejecutar las pruebas automatizadas

Desde la raíz del proyecto:

```powershell
php artisan test
```

En el estado inicial de referencia, las pruebas de ejemplo finalizan correctamente con dos pruebas aprobadas. Ese resultado sirve como comprobación básica del entorno; no representa una cobertura exhaustiva de los requisitos del sistema.

**Importante:** las pruebas automatizadas no deben ejecutarse contra la base de datos de desarrollo con datos reales. Antes de ampliar la suite, definir una base de datos separada para testing y ajustar `phpunit.xml` o `.env.testing` en consecuencia.

## 12. Iniciar la aplicación

Una vez completados los pasos anteriores, ejecutar:

```powershell
php artisan serve
```

Por defecto, Laravel inicia el servidor de desarrollo en:

```text
http://127.0.0.1:8000
```

Abrir esa dirección en el navegador para verificar que la aplicación responde.

El servidor incorporado de Laravel está destinado al desarrollo local. No debe utilizarse como sustituto de una configuración de producción.

Si se está utilizando Vite en modo desarrollo, mantener `pnpm run dev` activo en otra terminal. Si se desea comprobar el flujo con recursos compilados, ejecutar primero `pnpm run build`.

## 13. Verificación final

Antes de considerar preparado el entorno, comprobar los siguientes puntos:

- [ ] Git accede al repositorio correcto y la rama de trabajo es la esperada.
- [ ] PHP utiliza una versión compatible con Laravel 13.
- [ ] Las extensiones PHP requeridas están disponibles, incluida `pdo_pgsql`.
- [ ] `composer install` finaliza correctamente.
- [ ] `composer check-platform-reqs` no presenta incompatibilidades.
- [ ] Existe un `.env` local y contiene una clave de aplicación.
- [ ] `.env` permanece excluido del control de versiones.
- [ ] `pnpm install` finaliza correctamente.
- [ ] `pnpm run build` genera los recursos frontend sin errores.
- [ ] PostgreSQL está iniciado y la base `ewiflor_db` existe.
- [ ] Los roles `ewiflor_app`, `ewiflor_migrator`, `ewiflor_auth` y `ewiflor_backup` tienen contraseña asignada.
- [ ] El script SQL consolidado se ejecutó correctamente.
- [ ] La propiedad de tablas, vistas y secuencias se traspasó a `ewiflor_owner`.
- [ ] Las migraciones se ejecutan con `--database=pgsql_migrator`.
- [ ] `php artisan test` finaliza correctamente.
- [ ] La aplicación responde en `http://127.0.0.1:8000`.

## 14. Problemas frecuentes

### PHP muestra una versión incorrecta

Ejecutar:

```powershell
where.exe php
php -v
php --ini
```

Si hay varias instalaciones, corregir el orden del `PATH` de Windows y abrir una nueva terminal. No es necesario desinstalar XAMPP si se utiliza otra instalación de PHP para el proyecto.

### Composer informa de extensiones ausentes

Comprobar el archivo `php.ini` activo con `php --ini` y la lista de extensiones mediante `php -m`. Habilitar las extensiones requeridas en la instalación de PHP utilizada por Composer y Artisan.

### `fe_sendauth: no password supplied` al ejecutar `db:show`

Laravel está enviando una contraseña vacía. Causas habituales:

- `DB_PASSWORD` está vacío en `.env`.
- La contraseña contiene un `#` sin comillas y el resto se interpretó como comentario.

Revisar el `.env`, asegurarse de que la línea `DB_PASSWORD` no tenga comentarios en la misma línea, y ejecutar `php artisan config:clear` antes de reintentar.

### `permiso denegado al esquema public` al ejecutar `migrate`

El usuario configurado en `DB_USERNAME` no tiene privilegio `CREATE` sobre el esquema `public`. Esto es intencionado para `ewiflor_app`. Solución: ejecutar las migraciones con la conexión `pgsql_migrator`:

```powershell
php artisan migrate --database=pgsql_migrator
```

Si el problema persiste, comprobar que `ewiflor_migrator` tiene la contraseña asignada y que `ALTER ROLE ewiflor_migrator SET role = ewiflor_owner;` está aplicado.

### `permiso denegado a la tabla auditoria` al insertar en `usuario`

El trigger `trg_aud_usuario` ejecuta una función `SECURITY DEFINER` cuyo propietario es `ewiflor_owner`, pero ese rol no era dueño de las tablas ni tenía permisos sobre ellas. Solución: aplicar el traspaso de propiedad descrito en el apartado 9.4. Como verificación, ejecutar la consulta de comprobación incluida en ese mismo apartado: la columna `aun_de_postgres` debe ser `0`.

### `no se puede cambiar el dueño de la secuencia ... está enlazada a la tabla`

Las secuencias creadas por `GENERATED ALWAYS AS IDENTITY` no se pueden cambiar de dueño directamente. Se traspasan automáticamente al cambiar la tabla de la que dependen. El bloque `DO` del apartado 9.4 las excluye a propósito.

### Laravel no puede conectarse a PostgreSQL

Verificar el servicio, el puerto, la existencia de la base de datos, las credenciales y `pdo_pgsql`. No cambiar repetidamente los parámetros sin identificar primero el origen del error.

### Vite indica que falta el manifiesto

Ejecutar:

```powershell
pnpm install
pnpm run build
```

Si el problema persiste, revisar los mensajes de error de Vite y la configuración de `vite.config.js`.

### Aparecen archivos modificados después de ejecutar comandos

Ejecutar:

```powershell
git status
```

Identificar cada archivo antes de añadirlo al commit. No versionar `.env`, `vendor/`, `node_modules/`, cachés ni archivos generados que estén excluidos por `.gitignore`.

## 15. Buenas prácticas de trabajo

- No compartir contraseñas, claves de aplicación ni otros secretos.
- No sobrescribir archivos `.env` existentes.
- No ejecutar comandos destructivos sobre bases de datos sin comprobar sus efectos (`migrate:fresh`, `db:wipe`, `DROP DATABASE`, etc.).
- No ejecutar migraciones con `ewiflor_app`; usar siempre `--database=pgsql_migrator`.
- No modificar las dependencias sin una razón concreta y sin revisar el archivo de bloqueo correspondiente.
- Mantener `composer.lock` y `pnpm-lock.yaml` versionados.
- Ejecutar las pruebas antes de publicar cambios relevantes.
- Consultar la documentación oficial de Laravel cuando una actualización cambie los requisitos o procedimientos.

## 16. Referencias oficiales

- Laravel: https://laravel.com/docs/13.x
- Laravel — instalación: https://laravel.com/docs/13.x/installation
- Laravel — configuración: https://laravel.com/docs/13.x/configuration
- Composer: https://getcomposer.org/doc/
- Node.js: https://nodejs.org/en/download
- pnpm: https://pnpm.io/installation
- PostgreSQL: https://www.postgresql.org/docs/
- PostgreSQL — roles y privilegios: https://www.postgresql.org/docs/current/user-manag.html
- PostgreSQL — `SET ROLE`: https://www.postgresql.org/docs/current/sql-set-role.html
- PostgreSQL — `ALTER DEFAULT PRIVILEGES`: https://www.postgresql.org/docs/current/sql-alterdefaultprivileges.html

## 17. Historial de cambios

| Versión | Estado                                          | Descripción                                                                                                                                                                                                                                                                                                                                                                                                                            |
| ------- | ----------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1.0     | Histórica                                       | Primera versión del procedimiento de instalación local. Incluye la configuración inicial de Laravel y deja la conexión definitiva con PostgreSQL sujeta a verificación.                                                                                                                                                                                                                                                                |
| 1.1     | **Borrador técnico — no subida al repositorio** | Se completa la sección de PostgreSQL: base `ewiflor_db`, roles técnicos, asignación de contraseñas, traspaso de propiedad de tablas/vistas/secuencias a `ewiflor_owner`, permisos de `ewiflor_app`, conexiones `pgsql_migrator`/`pgsql_auth`/`pgsql_backup`, migración con `--database=pgsql_migrator`. Se documenta el modelo `User` mapeado a la tabla `usuario` y los problemas frecuentes encontrados durante la puesta en marcha. |

### 17.1. Estado de esta versión

La versión 1.1 **todavía no ha sido revisada por el equipo ni subida a GitHub**. Vive solo en el entorno local del autor. Antes de publicarla:

- [ ] El equipo debe revisar la guía de punta a punta en un entorno limpio.
- [ ] Hay que resolver las discrepancias listadas en 17.3.
- [ ] Confirmar que `.env.example` versionado coincide con lo que describe esta guía.
- [ ] Verificar que el script SQL consolidado y sus parches (`v5-MVP.sql`, `v5-MVP-config.sql`, `A-01-reserva-id-almacen.sql`) están disponibles en `database/sql/` y `database/sql/patches/` antes de referenciarlos.
- [ ] Decidir si esta guía se publica como v1.1 tal cual o se consolida como v1.2 con las correcciones de 17.3.
- [ ] Solo después: `git add docs/05-desarrollo/instalacion-local.md` y commit con mensaje `docs(instalacion): guía v1.1 para revisión del equipo`.

### 17.2. Entorno en el que se validó

El cuerpo del documento se redactó durante la puesta en marcha del entorno local del autor. Los resultados empíricos que respaldan los pasos son:

- `php artisan db:show` funcionó contra `Ewiflor_DB_MVP` con `ewiflor_app`.
- `php artisan migrate --database=pgsql_migrator` ejecutó las tres migraciones de infraestructura.
- `Auth::attempt(['nombre_usuario' => 'admin', 'password' => 'admin123'])` devolvió `true` con `SESSION_DRIVER=file`.
- El trigger `fn_auditar_cambio()` registró correctamente `app.id_usuario`, `modulo`, `operacion`, `tabla_afectada` e `id_registro`.
- `ewiflor_app` fue rechazado al intentar `DELETE` sobre `auditoria` (comportamiento esperado, D6).

Lo anterior **no significa que el documento esté alineado con el código**: refleja el entorno donde se probaron los pasos, pero el texto puede contener referencias heredadas que ya no son válidas. Ver 17.3.

### 17.3. Discrepancias conocidas respecto a la configuración vigente (ADR-001)

El cuerpo del documento (secciones 1 a 16) se redactó antes de la sesión del 29-oct-2026 en la que se cerraron las decisiones D1–D8 y se aplicaron los parches `v5-MVP-config.sql` y `A-01-reserva-id-almacen.sql`. Antes de publicar, revisar:

| Sección | Estado actual del documento                                           | Configuración real (ADR-001)                                                                                |
| ------- | --------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------- |
| 7.4     | `DB_DATABASE=ewiflor_db`                                              | `DB_DATABASE=Ewiflor_DB_MVP`                                                                                |
| 7.4     | `SESSION_DRIVER=database`                                             | `SESSION_DRIVER=file` (D3)                                                                                  |
| 7.4     | Menciona `DB_AUTH_*` y `ewiflor_auth`                                 | El rol `ewiflor_auth` no existe en v5-MVP (D1)                                                              |
| 7.5     | Incluye `pgsql_auth` como conexión paralela                           | No aplica con D1; la configuración solo tiene `pgsql` y `pgsql_migrator`                                    |
| 9.1     | Tabla de roles incluye `ewiflor_auth`                                 | No existe; solo `ewiflor_owner`, `ewiflor_migrator`, `ewiflor_app`, `ewiflor_backup`                        |
| 9.2     | PASO 0 crea `pgcrypto`, `pg_trgm`, `unaccent`                         | El script v5-MVP solo crea `pgcrypto`                                                                       |
| 9.2     | Menciona `auditoria_detalle`                                          | La tabla no existe en v5-MVP                                                                                |
| 9.4     | Menciona `trg_aud_usuario` y `auditoria_detalle`                      | `trg_aud_usuario` existe; `auditoria_detalle` no                                                            |
| 9.5     | Reaplica `REVOKE ... ON auditoria_detalle, rol, permiso, rol_permiso` | `rol` y `rol_permiso` no existen; `permiso` y `rol_permiso` sí (con otra semántica); `auditoria_detalle` no |
| 9.6     | "Se esperan **59 tablas**"                                            | El esquema v5-MVP tiene ~21 tablas + `migrations` (22 en total)                                             |
| 10      | Dice que `create_users_table` "solo crea `sessions`"                  | Correcto si se adaptó esa migración; verificar en el repo antes de publicar                                 |
| 10      | Ejecuta `php artisan migrate:status` con `pgsql` (app)                | Puede fallar por permisos sobre `migrations`; en la práctica hay que usar `--database=pgsql_migrator`       |
| 13      | Checklist dice "base `ewiflor_db` existe"                             | Debe decir `Ewiflor_DB_MVP`                                                                                 |
| 13      | Checklist no incluye regeneración del hash de `admin`                 | Falta, según C1 del ADR-001                                                                                 |
| 13      | Checklist no incluye drivers `file`/`file`/`sync`                     | Falta, según D3                                                                                             |
| 14      | No menciona `This password does not use the Bcrypt algorithm`         | Falta, según C1 del ADR-001                                                                                 |
| 14      | No menciona `relation "sessions" does not exist` en `Auth::attempt`   | Falta, según C2 del ADR-001                                                                                 |
| 14      | No menciona los falsos positivos de Intelephense en `SetDatabaseUser` | Falta, según C3 del ADR-001                                                                                 |
| 15      | Habla de "no ejecutar migraciones con `ewiflor_app`"                  | Correcto y se mantiene                                                                                      |

Estas discrepancias **no se corrigen en esta versión 1.1** porque el autor pidió no introducir más cambios hasta que el equipo revise la guía. Se resolverán en la siguiente revisión (v1.2 o v2.0), una vez validada por el equipo.

### 17.4. Próxima revisión

Cuando el equipo apruebe esta guía:

1. Aplicar las correcciones de 17.3 en el cuerpo del documento.
2. Actualizar el número de versión a 1.2.
3. Añadir una fila en esta tabla con el resumen de los cambios.
4. Publicar en `docs/05-desarrollo/instalacion-local.md` y enlazar desde `docs/README.md`.
5. Cerrar la deuda "documentación de instalación publicada" del informe general.
