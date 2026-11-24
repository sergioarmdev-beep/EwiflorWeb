<?php

namespace Tests\Integration;

use App\Exceptions\BusinessRuleException;
use App\Support\DbFunction;
use Illuminate\Support\Facades\DB;

class ConvertirCotizacionTest extends PostgresTestCase
{
    private function cotizacionConGarantia(int $sucursal, int $usuario, int $variante, int $garantiaMeses): int
    {
        $cliente = $this->crearCliente('cotizacion');
        $tipoCambio = $this->crearTipoCambio($usuario);

        $idCotizacion = DB::table('cotizacion')->insertGetId([
            'id_cliente' => $cliente,
            'id_usuario' => $usuario,
            'id_sucursal' => $sucursal,
            'id_tipo_cambio' => $tipoCambio,
            'fecha_emision' => now()->toDateString(),
            'vigencia_dias' => 14,
            'estado' => 'EMITIDA',
        ], 'id_cotizacion');

        DB::table('detalle_cotizacion')->insert([
            'id_cotizacion' => $idCotizacion,
            'id_variante' => $variante,
            'cantidad' => 2,
            'precio_unitario_usd' => 150,
            'descuento_pct' => 0,
            'garantia_meses' => $garantiaMeses,
        ]);

        return $idCotizacion;
    }

    public function test_propaga_garantia_meses_de_la_cotizacion(): void
    {
        $sucursal = $this->crearSucursal('Sucursal cotizacion');
        $almacen = $this->crearAlmacen($sucursal, 'Almacén cotizacion');
        $gerente = $this->crearUsuario('GERENTE', $sucursal, 'gerentecotizacion');
        $variante = $this->crearVarianteConStock($almacen, 10, 12);
        $cotizacion = $this->cotizacionConGarantia($sucursal, $gerente, $variante, 18);
        $this->actor($gerente);

        $idVenta = DbFunction::scalar('convertir_cotizacion_a_venta', [
            $cotizacion, $gerente, $almacen,
        ]);

        $this->assertNotNull($idVenta);
        $this->assertSame(
            18,
            (int) DB::table('detalle_venta')
                ->where('id_venta', $idVenta)
                ->value('garantia_meses'),
            'La conversión debe conservar garantia_meses de la cotización (regresión A-03).'
        );
        $this->assertSame('CONVERTIDA', DB::table('cotizacion')->where('id_cotizacion', $cotizacion)->value('estado'));
        $this->assertSame(
            8,
            (int) DB::table('existencia')
                ->where('id_almacen', $almacen)
                ->where('id_variante', $variante)
                ->value('cantidad_total')
        );
    }

    public function test_rechaza_almacen_de_otra_sucursal(): void
    {
        $sucursalA = $this->crearSucursal('Sucursal cotizacion A');
        $sucursalB = $this->crearSucursal('Sucursal cotizacion B');
        $almacenA = $this->crearAlmacen($sucursalA, 'Almacén cotizacion A');
        $almacenB = $this->crearAlmacen($sucursalB, 'Almacén cotizacion B');
        $gerente = $this->crearUsuario('GERENTE', $sucursalA, 'gerentecotizacionb');
        $variante = $this->crearVarianteConStock($almacenA, 10);
        $cotizacion = $this->cotizacionConGarantia($sucursalA, $gerente, $variante, 12);
        $this->actor($gerente);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('no pertenece a la sucursal de la cotización');

        DbFunction::scalar('convertir_cotizacion_a_venta', [
            $cotizacion, $gerente, $almacenB,
        ]);
    }
}
