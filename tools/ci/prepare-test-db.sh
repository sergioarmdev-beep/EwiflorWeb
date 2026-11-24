#!/usr/bin/env bash
# ADR-005: aprovisiona Ewiflor_DB_MVP_test desde cero para CI / entorno local.
# NUNCA toca Ewiflor_DB_MVP: el nombre de la BD está fijado por defecto y
# phpunit.xml / phpunit.pg.xml usan la conexión pgsql_test.
#
# Requisitos: psql, php y un rol con privilegios de superusuario (en CI, el
# usuario del servicio postgres). Variables opcionales:
#   TEST_DB_NAME           (por defecto Ewiflor_DB_MVP_test)
#   PGHOST / PGPORT / PGUSER / PGPASSWORD
#   TEST_APP_PASSWORD      (por defecto ewiflor_app)
#   TEST_MIGRATOR_PASSWORD (por defecto ewiflor_migrator)
#   PHP_BIN                (por defecto php)

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
DB="${TEST_DB_NAME:-Ewiflor_DB_MVP_test}"
PSQL="${PSQL:-psql}"
PHP_BIN="${PHP_BIN:-php}"
APP_PASSWORD="${TEST_APP_PASSWORD:-ewiflor_app}"
MIGRATOR_PASSWORD="${TEST_MIGRATOR_PASSWORD:-ewiflor_migrator}"

psql_db() {
    "$PSQL" -v ON_ERROR_STOP=1 -q "$@"
}

echo "==> Recreando base de datos desechable ${DB}"
psql_db -d postgres -c "DROP DATABASE IF EXISTS \"${DB}\" WITH (FORCE)"
psql_db -d postgres -c "CREATE DATABASE \"${DB}\""

echo "==> Esquema canónico (v5-MVP.sql)"
psql_db -d "$DB" -f "$ROOT/database/sql/v5-MVP.sql"

echo "==> Configuración de roles y tabla migrations (v5-MVP-config.sql)"
psql_db -d "$DB" -f "$ROOT/database/sql/v5-MVP-config.sql"

echo "==> Parches A-01..A-05 (como ewiflor_owner, para heredar grants)"
for parche in "$ROOT"/database/sql/patches/A-*.sql; do
    echo "    $(basename "$parche")"
    PGOPTIONS='-c role=ewiflor_owner' psql_db -d "$DB" -f "$parche"
done

echo "==> Contraseñas de los roles de prueba"
psql_db -d "$DB" \
    -v app_password="$APP_PASSWORD" \
    -v migrator_password="$MIGRATOR_PASSWORD" \
    -c "ALTER ROLE ewiflor_app PASSWORD :'app_password'; ALTER ROLE ewiflor_migrator PASSWORD :'migrator_password';"

echo "==> Migraciones Laravel (pgsql_migrator)"
cd "$ROOT"
DB_MIGRATOR_DATABASE="$DB" \
DB_MIGRATOR_USERNAME="${DB_MIGRATOR_USERNAME:-ewiflor_migrator}" \
DB_MIGRATOR_PASSWORD="$MIGRATOR_PASSWORD" \
    "$PHP_BIN" artisan config:clear
DB_MIGRATOR_DATABASE="$DB" \
DB_MIGRATOR_USERNAME="${DB_MIGRATOR_USERNAME:-ewiflor_migrator}" \
DB_MIGRATOR_PASSWORD="$MIGRATOR_PASSWORD" \
    "$PHP_BIN" artisan migrate --database=pgsql_migrator --force --no-interaction

echo "==> ${DB} lista para las suites PG"
