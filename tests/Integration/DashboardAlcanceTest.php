<?php

namespace Tests\Integration;

use App\Support\DbFunction;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Dashboard\Services\AdministradorDashboardService;
use Modules\Dashboard\Services\DashboardAlcance;
use Modules\Dashboard\Services\GerenteDashboardService;
use Modules\Dashboard\Services\SoporteDashboardService;
use Modules\Dashboard\Services\VendedorDashboardService;

/**
 * ADR-006 en los dashboards: GERENTE conserva la visión global; los demás
 * roles quedan fijados a su sucursal y, sin sucursal, fail-closed.
 */
class DashboardAlcanceTest extends PostgresTestCase
{
    private function usuario(int $id): User
    {
        return User::findOrFail($id);
    }

    private function registrarVenta(int $sucursal, int $almacen, int $cliente, int $vendedor, int $cantidad = 1): int
    {
        $this->actor($vendedor);
        $tipoCambio = $this->crearTipoCambio($vendedor);
        $variante = $this->crearVarianteConStock($almacen, 10);

        return (int) DbFunction::scalar('registrar_venta', [
            $cliente, $vendedor, $sucursal, $tipoCambio, json_encode([[
                'id_variante' => $variante,
                'cantidad' => $cantidad,
                'precio_unitario_usd' => 100,
                'id_almacen' => $almacen,
            ]]),
        ]);
    }

    public function test_desde_usuario_clasifica_el_alcance_segun_rol_y_sucursal(): void
    {
        $global = DashboardAlcance::desdeUsuario('GERENTE', null);
        $this->assertTrue($global->global);
        $this->assertNull($global->idSucursal);

        $sucursal = DashboardAlcance::desdeUsuario('ADMINISTRADOR', 7);
        $this->assertFalse($sucursal->global);
        $this->assertSame(7, $sucursal->idSucursal);

        $sinSucursal = DashboardAlcance::desdeUsuario('VENDEDOR', null);
        $this->assertFalse($sinSucursal->global);
        $this->assertNull($sinSucursal->idSucursal);
    }

    public function test_aplicar_filtra_por_sucursal_y_falla_cerrado(): void
    {
        $sucursalA = $this->crearSucursal('Sucursal alcance A');
        $sucursalB = $this->crearSucursal('Sucursal alcance B');
        $cliente = $this->crearCliente('alcance');
        $idA = $this->crearSolicitud($cliente, ['id_sucursal' => $sucursalA]);
        $idB = $this->crearSolicitud($cliente, ['id_sucursal' => $sucursalB]);

        $ids = function (DashboardAlcance $alcance) use ($idA, $idB): array {
            $query = DB::table('solicitud_servicio')->whereIn('id_solicitud', [$idA, $idB]);
            $alcance->aplicar($query, 'id_sucursal');

            return array_map('intval', $query->pluck('id_solicitud')->all());
        };

        $this->assertSame([$idA, $idB], $ids(DashboardAlcance::global()));
        $this->assertSame([$idA], $ids(DashboardAlcance::sucursal($sucursalA)));
        $this->assertSame([], $ids(DashboardAlcance::sinSucursal()));
    }

    public function test_administrador_solo_ve_ventas_y_almacenes_de_su_sucursal(): void
    {
        $sucursalA = $this->crearSucursal('Sucursal dashboard A');
        $sucursalB = $this->crearSucursal('Sucursal dashboard B');
        $almacenA = $this->crearAlmacen($sucursalA, 'Almacén dashboard A');
        $almacenB = $this->crearAlmacen($sucursalB, 'Almacén dashboard B');
        $adminA = $this->crearUsuario('ADMINISTRADOR', $sucursalA, 'admindashA');
        $vendedorA = $this->crearUsuario('VENDEDOR', $sucursalA, 'vendedordashA');
        $vendedorB = $this->crearUsuario('VENDEDOR', $sucursalB, 'vendedordashB');
        $gerente = $this->crearUsuario('GERENTE', $sucursalA, 'gerentedash');
        $clienteA = $this->crearCliente('dashA');
        $clienteB = $this->crearCliente('dashB');

        $idVentaA = $this->registrarVenta($sucursalA, $almacenA, $clienteA, $vendedorA);
        $idVentaB = $this->registrarVenta($sucursalB, $almacenB, $clienteB, $vendedorB, 2);

        $admin = app(AdministradorDashboardService::class)->para($this->usuario($adminA));

        $this->assertSame(1, $admin['ventas']['cantidad_mes']);
        $this->assertEquals(4000, $admin['ventas']['total_mes']);
        $this->assertSame(
            [$idVentaA],
            array_map('intval', array_column($admin['ultimas_ventas'], 'id_venta'))
        );
        $this->assertSame(
            ['Almacén dashboard A'],
            array_column($admin['existencias_por_almacen'], 'almacen')
        );

        $global = app(GerenteDashboardService::class)->para($this->usuario($gerente));

        $this->assertSame(2, $global['ventas']['cantidad_mes']);
        $this->assertEquals(12000, $global['ventas']['total_mes']);
        $this->assertCount(2, $global['existencias_por_almacen']);
        $this->assertContains($idVentaB, array_map('intval', array_column($global['ultimas_ventas'], 'id_venta')));
    }

    public function test_administrador_sin_sucursal_ve_todo_en_cero(): void
    {
        $sucursal = $this->crearSucursal('Sucursal sin sucursal admin');
        $almacen = $this->crearAlmacen($sucursal, 'Almacén sin sucursal admin');
        $admin = $this->crearUsuario('ADMINISTRADOR', null, 'adminsinsucursal');
        $vendedor = $this->crearUsuario('VENDEDOR', $sucursal, 'vendedorsinsucursal');
        $cliente = $this->crearCliente('sinsucursal');
        $this->registrarVenta($sucursal, $almacen, $cliente, $vendedor);
        $this->crearSolicitud($cliente, ['id_sucursal' => $sucursal]);

        $datos = app(AdministradorDashboardService::class)->para($this->usuario($admin));

        $this->assertSame(0, $datos['ventas']['cantidad_mes']);
        $this->assertEquals(0, $datos['ventas']['total_mes']);
        $this->assertSame([], $datos['ultimas_ventas']);
        $this->assertSame([], $datos['existencias_por_almacen']);
        $this->assertSame(0, $datos['solicitudes_servicio_pendientes']);
        $this->assertSame(0, $datos['operaciones_pendientes']['total']);
    }

    public function test_vendedor_solo_ve_sus_propias_ventas_dentro_de_su_sucursal(): void
    {
        $sucursal = $this->crearSucursal('Sucursal vendedor dashboard');
        $almacen = $this->crearAlmacen($sucursal, 'Almacén vendedor dashboard');
        $vendedorA = $this->crearUsuario('VENDEDOR', $sucursal, 'vendedordashpropiasA');
        $vendedorB = $this->crearUsuario('VENDEDOR', $sucursal, 'vendedordashpropiasB');
        $clienteA = $this->crearCliente('dashpropiasA');
        $clienteB = $this->crearCliente('dashpropiasB');

        $idVentaA = $this->registrarVenta($sucursal, $almacen, $clienteA, $vendedorA);
        $this->registrarVenta($sucursal, $almacen, $clienteB, $vendedorB, 3);

        $datos = app(VendedorDashboardService::class)->para($this->usuario($vendedorA));

        $this->assertSame(1, $datos['ventas']['cantidad_mes']);
        $this->assertEquals(4000, $datos['ventas']['total_mes']);
        $this->assertSame(
            [$idVentaA],
            array_map('intval', array_column($datos['ultimas_ventas'], 'id_venta'))
        );
    }

    public function test_soporte_solo_ve_solicitudes_de_su_sucursal(): void
    {
        $sucursalA = $this->crearSucursal('Sucursal soporte A');
        $sucursalB = $this->crearSucursal('Sucursal soporte B');
        $tecnico = $this->crearUsuario('SERVICIO_TECNICO', $sucursalA, 'tecnicodash');
        $sinSucursal = $this->crearUsuario('SERVICIO_TECNICO', null, 'tecnicodashnull');
        $cliente = $this->crearCliente('dashsoporte');

        $idA = $this->crearSolicitud($cliente, ['id_sucursal' => $sucursalA]);
        $this->crearSolicitud($cliente, ['id_sucursal' => $sucursalB]);

        $datos = app(SoporteDashboardService::class)->para($this->usuario($tecnico));

        $this->assertSame(1, $datos['solicitudes_servicio_pendientes']);
        $this->assertSame(
            [$idA],
            array_map('intval', array_column($datos['ultimas_solicitudes'], 'id_solicitud'))
        );

        $sinAlcance = app(SoporteDashboardService::class)->para($this->usuario($sinSucursal));

        $this->assertSame(0, $sinAlcance['solicitudes_servicio_pendientes']);
        $this->assertSame([], $sinAlcance['ultimas_solicitudes']);
    }
}
