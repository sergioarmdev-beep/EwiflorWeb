<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Base de los tests que ejercen reglas de negocio en PostgreSQL.
 *
 * Cada test corre dentro de una transacción externa y se revierte al final:
 * la auditoría es inmutable (trigger fn_auditoria_inmutable), así que borrar
 * filas después del test no es una opción. Tampoco se usa RefreshDatabase:
 * el esquema se carga una sola vez en Ewiflor_DB_MVP_test (ADR-005).
 */
abstract class PostgresTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::connection('pgsql')->beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::connection('pgsql')->rollBack();

        parent::tearDown();
    }
}
