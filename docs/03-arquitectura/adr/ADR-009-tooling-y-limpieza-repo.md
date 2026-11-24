# ADR-009 — Tooling versionado y limpieza del repositorio

- **Estado:** Aceptado e implementado
- **Fecha:** 2026-11-24
- **Decisores:** Equipo EwiflorWeb + usuario
- **Módulo afectado:** `.gitignore`, `.github/workflows/ci.yml`, `tools/`, `app/Console/Commands/`
- **Relacionado:** ADR-005 (tests PostgreSQL)

## Contexto

El repositorio tenía 6.050 archivos versionados, de los cuales 5.614
pertenecían a `.taste-skill/` y `.agents/` (perfiles de Chrome de cientos de
MB). También estaban versionados `composer.phar` (3,8 MB), el instalador
`composer-setup.php`, `cloudflared-windows-amd64.msi` (19 MB) y scripts
sueltos en la raíz (`try_login.php` probaba nueve contraseñas contra todos
los usuarios; `list_users.php` y `list_cots.php` hacían diagnósticos ad-hoc).
No existía ningún workflow de CI.

## Decisión

1. **Ignorar y sacar del índice** (sin borrar del disco) los artefactos de
   tooling: `.taste-skill/`, `.agents/`, `composer.phar`,
   `composer-setup.php` y el `.msi` de Cloudflared.
2. **Eliminar `try_login.php`**: es un script de fuerza bruta y no tiene
   valor de diagnóstico legítimo.
3. **Sustituir los scripts útiles por comandos Artisan** autocubiertos por
   Laravel en `app/Console/Commands`:
   `ewiflor:usuarios:listar` (sin hashes) y
   `ewiflor:cotizaciones:listar` (con nº de líneas).
4. **Un único aprovisionamiento de BD para tests**:
   `tools/ci/prepare-test-db.sh` recrea `Ewiflor_DB_MVP_test` desde
   `v5-MVP.sql` + `v5-MVP-config.sql` + parches `A-01`…`A-05` + migraciones.
   Los parches se cargan con `role=ewiflor_owner` para heredar los grants.
   El nombre de la BD está fijado por defecto: el script nunca toca
   `Ewiflor_DB_MVP`.
5. **CI mínima** en `.github/workflows/ci.yml`: PostgreSQL 18, PHP 8.5 con
   `pdo_pgsql`, `pnpm install --frozen-lockfile`, `pnpm build`, Pint
   `--test`, `composer test`, `composer test:pg` y escaneo de secretos con
   TruffleHog (`--only-verified`).
6. La documentación de instalación describe el entorno Windows real (PHP
   8.5 en `C:\tools\php85`, pnpm, roles y contraseñas fuera del repo) y
   `crear-modulo.md` deja de estar vacío.

## Consecuencias

- Positivo: el árbol versionado pasa de 6.050 a 430 archivos; el historial
  deja de arrastrar binarios y perfiles de navegador.
- Positivo: cualquier fork puede recrear la BD de prueba con un comando y
  ejecutar la misma matriz que CI.
- Positivo: no se versionan secretos; las credenciales de los roles se
  inyectan por entorno y el escaneo falla el pipeline si aparece un secreto
  verificado.
- Negativo: el reputado `git rm --cached` deja los archivos en el disco del
  desarrollador, pero deja de seguirlos Git. Hay que ejecutarlo una sola vez
  por rama; los archivos siguen en el historial previo, por lo que no se
  consideran "borrados" de commits antiguos.
- Negativo: el script de aprovisionamiento necesita un superusuario o un rol
  con `CREATEDB`; en Windows local se documenta el equivalente manual con
  `psql`.
