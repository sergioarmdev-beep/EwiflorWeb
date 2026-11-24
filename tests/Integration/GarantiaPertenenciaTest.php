<?php

namespace Tests\Integration;

use App\Exceptions\BusinessRuleException;
use App\Support\DbFunction;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\ServicioTecnico\Services\SolicitudService;

/**
 * Una garantía solo puede vincularse a la solicitud del cliente que la compró
 * y, en creación interna, a una solicitud de su misma sucursal.
 */
class GarantiaPertenenciaTest extends PostgresTestCase
{
    private function usuario(int $id): User
    {
        return User::findOrFail($id);
    }

    /** Registra una venta y devuelve la garantía creada por el trigger PG. */
    private function garantiaDeVenta(int $sucursal, int $almacen, int $cliente, int $usuario): int
    {
        $this->actor($usuario);
        $tipoCambio = $this->crearTipoCambio($usuario);
        $variante = $this->crearVarianteConStock($almacen, 10);

        $idVenta = DbFunction::scalar('registrar_venta', [
            $cliente, $usuario, $sucursal, $tipoCambio, json_encode([[
                'id_variante' => $variante,
                'cantidad' => 1,
                'precio_unitario_usd' => 100,
                'id_almacen' => $almacen,
            ]]),
        ]);

        $idDetalle = DB::table('detalle_venta')->where('id_venta', $idVenta)->value('id_detalle_venta');

        return (int) DB::table('garantia')->where('id_detalle_venta', $idDetalle)->value('id_garantia');
    }

    public function test_creacion_interna_rechaza_garantia_de_otro_cliente(): void
    {
        $sucursal = $this->crearSucursal('Sucursal garantia cliente');
        $almacen = $this->crearAlmacen($sucursal, 'Almacén garantia cliente');
        $vendedor = $this->crearUsuario('VENDEDOR', $sucursal, 'vendedorgarantia');
        $clienteA = $this->crearCliente('garantiaA');
        $clienteB = $this->crearCliente('garantiaB');
        $idGarantia = $this->garantiaDeVenta($sucursal, $almacen, $clienteA, $vendedor);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('no pertenece al cliente indicado');

        (new SolicitudService)->crearInterna($clienteB, $this->usuario($vendedor), [
            'descripcion_falla' => 'Equipo con falla',
            'id_garantia' => $idGarantia,
        ]);
    }

    public function test_creacion_publica_rechaza_garantia_de_otro_cliente(): void
    {
        $sucursal = $this->crearSucursal('Sucursal garantia publica');
        $almacen = $this->crearAlmacen($sucursal, 'Almacén garantia publica');
        $vendedor = $this->crearUsuario('VENDEDOR', $sucursal, 'vendedorgarantiapub');
        $clienteA = $this->crearCliente('garantiapubA');
        $clienteB = $this->crearCliente('garantiapubB');
        $idGarantia = $this->garantiaDeVenta($sucursal, $almacen, $clienteA, $vendedor);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('no pertenece al cliente indicado');

        (new SolicitudService)->crearPublica($clienteB, [
            'descripcion_falla' => 'Equipo con falla',
            'id_garantia' => $idGarantia,
        ]);
    }

    public function test_creacion_interna_rechaza_garantia_de_otra_sucursal(): void
    {
        $sucursalA = $this->crearSucursal('Sucursal A garantia');
        $sucursalB = $this->crearSucursal('Sucursal B garantia');
        $almacenA = $this->crearAlmacen($sucursalA, 'Almacén A garantia');
        $vendedorA = $this->crearUsuario('VENDEDOR', $sucursalA, 'vendedorgarantiaA');
        $vendedorB = $this->crearUsuario('VENDEDOR', $sucursalB, 'vendedorgarantiaB');
        $cliente = $this->crearCliente('garantiaotrasucursal');
        $idGarantia = $this->garantiaDeVenta($sucursalA, $almacenA, $cliente, $vendedorA);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('pertenece a otra sucursal');

        (new SolicitudService)->crearInterna($cliente, $this->usuario($vendedorB), [
            'descripcion_falla' => 'Equipo con falla',
            'id_garantia' => $idGarantia,
        ]);
    }

    public function test_creacion_interna_acepta_la_garantia_correcta(): void
    {
        $sucursal = $this->crearSucursal('Sucursal garantia valida');
        $almacen = $this->crearAlmacen($sucursal, 'Almacén garantia valida');
        $vendedor = $this->crearUsuario('VENDEDOR', $sucursal, 'vendedorgarantiav');
        $cliente = $this->crearCliente('garantiav');
        $idGarantia = $this->garantiaDeVenta($sucursal, $almacen, $cliente, $vendedor);

        $solicitud = (new SolicitudService)->crearInterna($cliente, $this->usuario($vendedor), [
            'descripcion_falla' => 'El equipo no enciende',
            'id_garantia' => $idGarantia,
        ]);

        $this->assertSame($idGarantia, (int) $solicitud->id_garantia);
        $this->assertSame($sucursal, (int) $solicitud->id_sucursal);
        $this->assertSame('PENDIENTE', $solicitud->estado);
        $this->assertSame('INTERNO', $solicitud->origen);
    }
}
