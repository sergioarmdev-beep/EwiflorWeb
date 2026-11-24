<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;
use Modules\Catalogo\Models\Marca;
use Modules\Catalogo\Models\Producto;
use Modules\Catalogo\Models\VarianteProducto;
use Modules\Clientes\Models\Cliente;
use Modules\Inventario\Models\Almacen;
use Modules\Inventario\Models\DetalleMovimiento;
use Modules\Inventario\Models\Existencia;
use Modules\Inventario\Models\Movimiento;
use Modules\Inventario\Models\Sucursal;
use Modules\Precios\Models\TipoCambio;
use Modules\Ventas\Models\DetalleVenta;
use Modules\Ventas\Models\Pago;
use Modules\Ventas\Models\Venta;

/**
 * Seeder maestro de demostración.
 *
 * Pobla Ewiflor_DB_MVP con datos creíbles para que las estadísticas del
 * Dashboard (ventas, movimientos, existencias, usuarios y tipo de cambio)
 * tengan información real sobre la cual calcular.
 *
 * Orden de dependencias respetado:
 *   sucursal → almacén → usuario → marca → producto → variante → tipo_cambio
 *   → existencia → cliente → venta → detalle_venta → pago → movimiento → detalle_movimiento
 */
class EwiflorDemoSeeder extends Seeder
{
    public function run(): void
    {
        // El seeder es una operación de población que requiere privilegios de
        // escritura/owner. La conexión por defecto (ewiflor_app) es de solo lectura
        // en varias tablas; pgsql_migrator es IN ROLE ewiflor_owner y puede escribir.
        config(['database.default' => 'pgsql_migrator']);
        DB::purge('pgsql');
        DB::purge('pgsql_migrator');

        // 1) Sucursales y almacenes (idempotente, ya definido).
        $this->call(SucursalSeeder::class);

        // 2) Usuario administrador base (idempotente).
        $this->call(AdminUserSeeder::class);

        $this->crearUsuariosDemo();
        $this->crearCatalogo();
        $this->crearTipoCambio();
        $this->crearExistencia();
        $this->crearClientes();
        $this->crearVentasYPagos();
        $this->crearMovimientos();

        $this->command->info('EWIFLOR_DEMO_SEEDER: datos de demostración cargados.');
    }

    /* ------------------------------------------------------------------ */
    /*  Usuarios */
    /* ------------------------------------------------------------------ */

    private function crearUsuariosDemo(): void
    {
        $sucursales = Sucursal::where('estado', 'ACTIVO')->orderBy('id_sucursal')->get();
        $s1 = $sucursales->first()?->id_sucursal ?? null;
        $s2 = $sucursales->skip(1)->first()?->id_sucursal ?? $s1;

        $usuarios = [
            [
                'nombre_usuario' => 'gerente_central',
                'nombre_completo' => 'Gerente Central',
                'correo' => 'gerente@ewiflor.local',
                'nombre_rol' => 'GERENTE',
                'id_sucursal' => null, // ve todas las sucursales
            ],
            [
                'nombre_usuario' => 'admin_sc',
                'nombre_completo' => 'Admin Santa Cruz',
                'correo' => 'admin.sc@ewiflor.local',
                'nombre_rol' => 'ADMINISTRADOR',
                'id_sucursal' => $s2,
            ],
            [
                'nombre_usuario' => 'vendedor_lp',
                'nombre_completo' => 'Vendedor La Paz',
                'correo' => 'vendedor.lp@ewiflor.local',
                'nombre_rol' => 'VENDEDOR',
                'id_sucursal' => $s1,
                'porcentaje_comision_pct' => 5,
            ],
            [
                'nombre_usuario' => 'vendedor_sc',
                'nombre_completo' => 'Vendedor Santa Cruz',
                'correo' => 'vendedor.sc@ewiflor.local',
                'nombre_rol' => 'VENDEDOR',
                'id_sucursal' => $s2,
                'porcentaje_comision_pct' => 5,
            ],
            [
                'nombre_usuario' => 'tecnico1',
                'nombre_completo' => 'Técnico Demo',
                'correo' => 'tecnico@ewiflor.local',
                'nombre_rol' => 'SERVICIO_TECNICO',
                'id_sucursal' => $s1,
            ],
        ];

        foreach ($usuarios as $u) {
            User::updateOrCreate(
                ['nombre_usuario' => $u['nombre_usuario']],
                array_merge($u, [
                    'contrasena_hash' => Hash::make('password123'),
                    'estado' => 'ACTIVO',
                ])
            );
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Catálogo */
    /* ------------------------------------------------------------------ */

    private function crearCatalogo(): void
    {
        // Marcas
        $marcas = [];
        foreach (['Ewiflor', 'TechHogar', 'MegaImport', 'Refacciones Del Norte'] as $nombre) {
            $marcas[] = Marca::updateOrCreate(
                ['nombre' => $nombre],
                ['pais_origen' => fake()->randomElement(['Bolivia', 'Brasil', 'Argentina', 'Estados Unidos', 'China'])]
            );
        }

        // Productos
        $categorias = Producto::CATEGORIAS;
        $tipos = array_diff(Producto::TIPOS, ['COMBO']);
        $productos = [];
        for ($i = 1; $i <= 12; $i++) {
            $productos[] = Producto::updateOrCreate(
                ['codigo' => sprintf('PROD-%03d', $i)],
                [
                    'nombre' => ucfirst(fake()->unique()->word()).' '.fake()->word(),
                    'descripcion' => fake()->sentence(),
                    'categoria' => $categorias[array_rand($categorias)],
                    'id_marca' => $marcas[array_rand($marcas)]->id_marca,
                    'modelo' => sprintf('MOD-%02d', $i),
                    'tipo_producto' => $tipos[array_rand($tipos)],
                    'modalidad' => fake()->randomElement(['INVENTARIABLE', 'BAJO_PEDIDO']),
                    'stock_minimo' => fake()->numberBetween(0, 20),
                    'garantia_meses' => fake()->randomElement([0, 3, 6, 12]),
                    'es_publico' => true,
                    'estado' => 'ACTIVO',
                ]
            );
        }

        // Variantes (2-3 por producto), SKU único determinístico para evitar duplicados entre runs
        $sku = 1;
        foreach ($productos as $producto) {
            $cantidadVariantes = fake()->numberBetween(2, 3);
            for ($j = 0; $j < $cantidadVariantes; $j++) {
                VarianteProducto::updateOrCreate(
                    ['sku' => sprintf('SKU-%04d', $sku)],
                    [
                        'id_producto' => $producto->id_producto,
                        'color' => fake()->randomElement(['Negro', 'Blanco', 'Rojo', 'Azul', 'Gris', 'Plateado']),
                        'caracteristicas' => fake()->sentence(6),
                        'precio_usd' => fake()->randomFloat(2, 10, 500),
                        'estado' => 'ACTIVO',
                    ]
                );
                $sku++;
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Tipo de cambio */
    /* ------------------------------------------------------------------ */

    private function crearTipoCambio(): void
    {
        $usuario = User::first() ?? User::create([
            'nombre_usuario' => 'sistema',
            'nombre_completo' => 'Sistema',
            'correo' => 'sistema@ewiflor.local',
            'contrasena_hash' => Hash::make('x'),
            'nombre_rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $hoy = Carbon::today();
        for ($d = 60; $d >= 0; $d--) {
            $fecha = $hoy->copy()->subDays($d)->format('Y-m-d');
            foreach (['PARALELO', 'OFICIAL'] as $tipo) {
                // Solo insertar si no existe: updateOrCreate haría UPDATE y
                // dispara el trigger PL/pgSQL fn_tipo_cambio_inmutable().
                if (! TipoCambio::where('fecha', $fecha)->where('tipo', $tipo)->exists()) {
                    TipoCambio::create([
                        'fecha' => $fecha,
                        'tipo' => $tipo,
                        'valor' => $tipo === 'PARALELO'
                            ? fake()->randomFloat(4, 7.0, 7.6)
                            : fake()->randomFloat(4, 6.8, 7.0),
                        'origen' => 'MANUAL',
                        'id_usuario' => $usuario->id_usuario,
                    ]);
                }
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Existencia */
    /* ------------------------------------------------------------------ */

    private function crearExistencia(): void
    {
        $almacenes = Almacen::where('estado', 'ACTIVO')->get();
        $variantes = VarianteProducto::where('estado', 'ACTIVO')->get();

        foreach ($almacenes as $almacen) {
            foreach ($variantes as $variante) {
                $total = fake()->numberBetween(0, 120);

                // PK compuesta (id_almacen, id_variante) sin columna "id":
                // updateOrCreate fallaría al hacer UPDATE con WHERE id.
                if (! Existencia::where('id_almacen', $almacen->id_almacen)
                    ->where('id_variante', $variante->id_variante)->exists()) {
                    Existencia::create([
                        'id_almacen' => $almacen->id_almacen,
                        'id_variante' => $variante->id_variante,
                        'cantidad_total' => $total,
                        'cantidad_reservada' => fake()->numberBetween(0, $total),
                        'ubicacion_interna' => fake()->optional()->bothify('LOC-??'),
                    ]);
                }
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Clientes */
    /* ------------------------------------------------------------------ */

    private function crearClientes(): void
    {
        $tipos = Cliente::TIPOS;
        for ($i = 1; $i <= 15; $i++) {
            Cliente::updateOrCreate(
                ['correo' => sprintf('cliente%02d@ejemplo.local', $i)],
                [
                    'nombre' => fake()->company(),
                    'telefono' => fake()->phoneNumber(),
                    'tipo_cliente' => $tipos[array_rand($tipos)],
                    'es_mayorista' => fake()->boolean(20),
                    'estado' => 'ACTIVO',
                ]
            );
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Ventas, detalles y pagos */
    /* ------------------------------------------------------------------ */

    private function crearVentasYPagos(): void
    {
        $clientes = Cliente::where('estado', 'ACTIVO')->pluck('id_cliente')->toArray();
        $vendedores = User::where('nombre_rol', 'VENDEDOR')->where('estado', 'ACTIVO')->pluck('id_usuario')->toArray();
        if (empty($vendedores)) {
            $vendedores = User::where('estado', 'ACTIVO')->pluck('id_usuario')->toArray();
        }
        $sucursales = Sucursal::where('estado', 'ACTIVO')->pluck('id_sucursal')->toArray();
        $variantes = VarianteProducto::where('estado', 'ACTIVO')->get(['id_variante', 'precio_usd']);

        $numeroVenta = 1000;
        $hoy = Carbon::today();

        // Últimos 90 días, ~1 venta por día en promedio, concentrados en los 2 últimos meses
        for ($d = 90; $d >= 0; $d--) {
            if (fake()->boolean(30)) { // ~70% de probabilidad de venta ese día
                continue;
            }

            $fecha = $hoy->copy()->subDays($d)->setTime(fake()->numberBetween(8, 20), fake()->numberBetween(0, 59));
            $idSucursal = $sucursales[array_rand($sucursales)];
            $idVendedor = $vendedores[array_rand($vendedores)];
            $idCliente = $clientes[array_rand($clientes)];

            // Tipo de cambio vigente en esa fecha
            $tc = TipoCambio::whereDate('fecha', '<=', $fecha->format('Y-m-d'))
                ->orderByDesc('fecha')
                ->first();
            if (! $tc) {
                continue;
            }

            $venta = Venta::create([
                'id_cliente' => $idCliente,
                'id_usuario' => $idVendedor,
                'id_sucursal' => $idSucursal,
                'id_cotizacion' => null,
                'id_tipo_cambio' => $tc->id_tipo_cambio,
                'fecha_hora' => $fecha,
                'estado' => 'REGISTRADA',
                'numero_factura' => null,
                'fecha_entrega' => null,
            ]);

            // Detalles: entre 1 y 3 variantes distintas por venta (evita UNIQUE id_venta+id_variante)
            if ($variantes->isEmpty()) {
                continue;
            }
            $seleccionadas = $variantes->random(min(3, $variantes->count()));

            $totalUsd = 0.0;
            foreach ($seleccionadas as $variante) {
                $idVariante = (int) $variante->id_variante;
                $precioUsd = (float) $variante->precio_usd;
                $cantidad = fake()->numberBetween(1, 5);
                $descuento = fake()->randomElement([0, 0, 0, 5, 10]);

                DetalleVenta::create([
                    'id_venta' => $venta->id_venta,
                    'id_variante' => $idVariante,
                    'cantidad' => $cantidad,
                    'precio_unitario_usd' => round($precioUsd, 2),
                    'descuento_pct' => (float) $descuento,
                    'garantia_meses' => fake()->randomElement([0, 0, 3, 6, 12]),
                ]);

                $totalUsd += $cantidad * $precioUsd * (1 - $descuento / 100);
            }

            $totalBs = round($totalUsd * (float) $tc->valor, 2);

            // Pago: 70% completo, 20% parcial, 10% sin pago (para que haya saldo pendiente)
            $azar = fake()->numberBetween(1, 100);
            if ($azar <= 70) {
                $monto = $totalBs;
            } elseif ($azar <= 90) {
                $monto = round($totalBs * fake()->randomFloat(2, 0.4, 0.9), 2);
            } else {
                $monto = null;
            }

            if ($monto !== null && $monto > 0) {
                Pago::create([
                    'id_venta' => $venta->id_venta,
                    'forma_pago' => fake()->randomElement(Pago::FORMAS),
                    'monto_bs' => $monto,
                    'fecha_hora' => $fecha->copy()->addMinutes(fake()->numberBetween(0, 120)),
                    'referencia' => fake()->optional()->bothify('REF-####'),
                ]);
            }

            $numeroVenta++;
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Movimientos de inventario */
    /* ------------------------------------------------------------------ */

    private function crearMovimientos(): void
    {
        $usuarios = User::where('estado', 'ACTIVO')->pluck('id_usuario')->toArray();
        $almacenes = Almacen::where('estado', 'ACTIVO')->pluck('id_almacen')->toArray();
        $variantes = VarianteProducto::where('estado', 'ACTIVO')->get(['id_variante']);

        $tipos = ['ENTRADA', 'SALIDA', 'AJUSTE', 'TRANSFERENCIA'];

        for ($i = 1; $i <= 20; $i++) {
            $tipo = $tipos[array_rand($tipos)];

            if ($tipo === 'ENTRADA') {
                $origen = null;
                $destino = $almacenes[array_rand($almacenes)];
            } elseif ($tipo === 'SALIDA') {
                $origen = $almacenes[array_rand($almacenes)];
                $destino = null;
            } elseif ($tipo === 'TRANSFERENCIA') {
                $origen = $almacenes[array_rand($almacenes)];
                do {
                    $destino = $almacenes[array_rand($almacenes)];
                } while ($destino === $origen && count($almacenes) > 1);
            } else { // AJUSTE
                $origen = $almacenes[array_rand($almacenes)];
                $destino = null;
            }

            $movimiento = Movimiento::create([
                'tipo' => $tipo,
                'motivo' => fake()->sentence(),
                'id_usuario' => $usuarios[array_rand($usuarios)],
                'id_almacen_origen' => $origen,
                'id_almacen_destino' => $destino,
                'id_venta' => null,
                'fecha_hora' => fake()->dateTimeBetween('-90 days', 'today'),
            ]);

            // Detalles del movimiento: 1-3 variantes
            if ($variantes->isEmpty()) {
                continue;
            }
            $vars = $variantes->random(min(3, $variantes->count()))->pluck('id_variante')->toArray();

            foreach ($vars as $idVariante) {
                DetalleMovimiento::create([
                    'id_movimiento' => $movimiento->id_movimiento,
                    'id_variante' => (int) $idVariante,
                    'cantidad' => fake()->numberBetween(1, 20),
                    // El sentido solo aplica a movimientos AJUSTE (trigger fn_validar_sentido_ajuste).
                    'sentido' => $tipo === 'AJUSTE'
                        ? fake()->randomElement(['INCREMENTO', 'DECREMENTO'])
                        : null,
                    'observacion' => fake()->sentence(),
                ]);
            }
        }
    }
}
