<?php

namespace Tests\Integration;

use App\Exceptions\BusinessRuleException;
use App\Support\DbFunction;
use Illuminate\Support\Facades\DB;

class RegistrarVentaTest extends PostgresTestCase
{
    public function test_registrar_venta_descuenta_stock_y_registra_la_venta(): void
    {
        $sucursal = $this->crearSucursal('Sucursal venta');
        $almacen = $this->crearAlmacen($sucursal, 'Almacén venta');
        $vendedor = $this->crearUsuario('VENDEDOR', $sucursal, 'vendedorventa');
        $cliente = $this->crearCliente('venta');
        $tipoCambio = $this->crearTipoCambio($vendedor);
        $variante = $this->crearVarianteConStock($almacen, 10);
        $this->actor($vendedor);

        $lineas = [[
            'id_variante' => $variante,
            'cantidad' => 3,
            'precio_unitario_usd' => 100,
            'descuento_pct' => 0,
            'id_almacen' => $almacen,
        ]];

        $idVenta = DbFunction::scalar('registrar_venta', [
            $cliente, $vendedor, $sucursal, $tipoCambio, json_encode($lineas),
        ]);

        $this->assertNotNull($idVenta);
        $this->assertSame('REGISTRADA', DB::table('venta')->where('id_venta', $idVenta)->value('estado'));
        $this->assertSame(1, DB::table('detalle_venta')->where('id_venta', $idVenta)->count());
        $this->assertSame(
            3,
            (int) DB::table('detalle_venta')->where('id_venta', $idVenta)->value('cantidad')
        );
        $this->assertSame(
            7,
            (int) DB::table('existencia')
                ->where('id_almacen', $almacen)
                ->where('id_variante', $variante)
                ->value('cantidad_total')
        );
    }

    public function test_rechaza_almacen_de_otra_sucursal(): void
    {
        $sucursalA = $this->crearSucursal('Sucursal A venta');
        $sucursalB = $this->crearSucursal('Sucursal B venta');
        $almacenB = $this->crearAlmacen($sucursalB, 'Almacén B venta');
        $gerente = $this->crearUsuario('GERENTE', $sucursalA, 'gerenteventa');
        $cliente = $this->crearCliente('ventaB');
        $tipoCambio = $this->crearTipoCambio($gerente);
        $variante = $this->crearVarianteConStock($almacenB, 10);
        $this->actor($gerente);

        $lineas = [[
            'id_variante' => $variante,
            'cantidad' => 1,
            'precio_unitario_usd' => 100,
            'id_almacen' => $almacenB,
        ]];

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('Los almacenes de la venta deben pertenecer a la sucursal');

        DbFunction::scalar('registrar_venta', [
            $cliente, $gerente, $sucursalA, $tipoCambio, json_encode($lineas),
        ]);
    }

    public function test_vendedor_no_puede_vender_en_otra_sucursal(): void
    {
        $sucursalA = $this->crearSucursal('Sucursal A vendor');
        $sucursalB = $this->crearSucursal('Sucursal B vendor');
        $almacenA = $this->crearAlmacen($sucursalA, 'Almacén A vendor');
        $vendedor = $this->crearUsuario('VENDEDOR', $sucursalA, 'vendedorotra');
        $cliente = $this->crearCliente('vendor');
        $tipoCambio = $this->crearTipoCambio($vendedor);
        $variante = $this->crearVarianteConStock($almacenA, 10);
        $this->actor($vendedor);

        $lineas = [[
            'id_variante' => $variante,
            'cantidad' => 1,
            'precio_unitario_usd' => 100,
            'id_almacen' => $almacenA,
        ]];

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('Solo puedes registrar ventas de tu sucursal');

        DbFunction::scalar('registrar_venta', [
            $cliente, $vendedor, $sucursalB, $tipoCambio, json_encode($lineas),
        ]);
    }
}
