<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
    /** Evita colisiones de UNIQUE(fecha, tipo, fecha_registro) entre fixtures. */
    private static int $secuenciaTipoCambio = 0;

    protected function setUp(): void
    {
        parent::setUp();

        // La conexión por defecto es pgsql_test en phpunit.pg.xml (ADR-005).
        // Fijar 'pgsql' aquí dejaría los fixtures en la BD sin rollback.
        DB::connection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::connection()->rollBack();

        parent::tearDown();
    }

    /** Fija el actor de auditoría como los middleware de la app. */
    protected function actor(int $idUsuario): void
    {
        DB::statement("SELECT set_config('app.id_usuario', ?, false)", [(string) $idUsuario]);
    }

    protected function crearSucursal(string $nombre): int
    {
        return DB::table('sucursal')->insertGetId([
            'nombre' => $nombre,
            'departamento' => 'LA_PAZ',
            'direccion' => 'Dirección de prueba',
            'telefono' => '77777777',
            'estado' => 'ACTIVO',
        ], 'id_sucursal');
    }

    protected function crearAlmacen(int $idSucursal, string $nombre): int
    {
        return DB::table('almacen')->insertGetId([
            'nombre' => $nombre,
            'tamano' => 'GRANDE',
            'id_sucursal' => $idSucursal,
            'estado' => 'ACTIVO',
        ], 'id_almacen');
    }

    protected function crearUsuario(string $rol, ?int $idSucursal, string $prefijo): int
    {
        $nombre = strtolower($prefijo);

        return DB::table('usuario')->insertGetId([
            'nombre_completo' => 'Usuario '.$prefijo,
            'nombre_usuario' => $nombre,
            'correo' => $nombre.'@test.local',
            'contrasena_hash' => Hash::make('clave-de-prueba'),
            'nombre_rol' => $rol,
            'id_sucursal' => $idSucursal,
            'estado' => 'ACTIVO',
        ], 'id_usuario');
    }

    protected function crearCliente(string $prefijo): int
    {
        return DB::table('cliente')->insertGetId([
            'nombre' => 'Cliente '.$prefijo,
            'telefono' => '70000000',
            'correo' => strtolower($prefijo).'@cliente.test.local',
            'tipo_cliente' => 'PERSONA_NATURAL',
            'es_mayorista' => false,
            'estado' => 'ACTIVO',
        ], 'id_cliente');
    }

    /** Crea una solicitud de servicio con valores por defecto válidos. */
    protected function crearSolicitud(int $idCliente, array $datos = []): int
    {
        return DB::table('solicitud_servicio')->insertGetId(array_merge([
            'id_cliente' => $idCliente,
            'descripcion_falla' => 'Falla de prueba',
            'fecha_solicitud' => now(),
            'estado' => 'PENDIENTE',
            'origen' => 'INTERNO',
        ], $datos), 'id_solicitud');
    }

    protected function crearTipoCambio(int $idUsuario): int
    {
        return DB::table('tipo_cambio')->insertGetId([
            'fecha' => now()->toDateString(),
            'tipo' => 'PARALELO',
            'valor' => 40,
            'origen' => 'MANUAL',
            'id_usuario' => $idUsuario,
            'fecha_registro' => now()->addSeconds(++self::$secuenciaTipoCambio),
        ], 'id_tipo_cambio');
    }

    /**
     * Crea producto + variante con existencia inicial y devuelve id_variante.
     */
    protected function crearVarianteConStock(int $idAlmacen, int $cantidad, int $garantiaMeses = 12): int
    {
        $prefijo = uniqid('p', false);
        $idMarca = DB::table('marca')->insertGetId(['nombre' => 'Marca '.$prefijo], 'id_marca');
        $idProducto = DB::table('producto')->insertGetId([
            'codigo' => 'COD-'.$prefijo,
            'nombre' => 'Producto '.$prefijo,
            'categoria' => 'REPOSTERIA',
            'id_marca' => $idMarca,
            'modelo' => 'M1',
            'tipo_producto' => 'MAQUINARIA',
            'modalidad' => 'INVENTARIABLE',
            'stock_minimo' => 0,
            'garantia_meses' => $garantiaMeses,
            'es_publico' => true,
            'estado' => 'ACTIVO',
        ], 'id_producto');
        $idVariante = DB::table('variante_producto')->insertGetId([
            'id_producto' => $idProducto,
            'sku' => 'SKU-'.$prefijo,
            'color' => 'BLANCO',
            'estado' => 'ACTIVO',
        ], 'id_variante');
        DB::table('existencia')->insert([
            'id_almacen' => $idAlmacen,
            'id_variante' => $idVariante,
            'cantidad_total' => $cantidad,
            'cantidad_reservada' => 0,
        ]);

        return $idVariante;
    }
}
