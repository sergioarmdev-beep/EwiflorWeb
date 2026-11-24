<?php

namespace Tests\Integration;

use App\Support\DbFunction;
use Illuminate\Support\Facades\DB;

class IntentoAccesoTest extends PostgresTestCase
{
    public function test_tres_fallos_bloquean_y_el_exito_limpia_la_ventana(): void
    {
        $idSucursal = $this->crearSucursal('Sucursal intentos');
        $idUsuario = $this->crearUsuario('VENDEDOR', $idSucursal, 'intentos');
        $this->actor($idUsuario);

        $primero = DbFunction::call('fn_registrar_intento_acceso', [$idUsuario, 'intentos', '127.0.0.1', false]);
        $this->assertSame(1, (int) $primero->intentos);
        $this->assertFalse((bool) $primero->bloqueo_nuevo);

        $segundo = DbFunction::call('fn_registrar_intento_acceso', [$idUsuario, 'intentos', '127.0.0.1', false]);
        $this->assertSame(2, (int) $segundo->intentos);
        $this->assertFalse((bool) $segundo->bloqueo_nuevo);

        $tercero = DbFunction::call('fn_registrar_intento_acceso', [$idUsuario, 'intentos', '127.0.0.1', false]);
        $this->assertSame(3, (int) $tercero->intentos);
        $this->assertTrue((bool) $tercero->bloqueo_nuevo);
        $this->assertNotNull($tercero->bloqueado_hasta);

        $cuarto = DbFunction::call('fn_registrar_intento_acceso', [$idUsuario, 'intentos', '127.0.0.1', false]);
        $this->assertSame(4, (int) $cuarto->intentos);
        $this->assertFalse((bool) $cuarto->bloqueo_nuevo, 'El bloqueo solo se notifica una vez.');

        $this->assertNotNull(
            DB::table('usuario')->where('id_usuario', $idUsuario)->value('bloqueado_hasta')
        );

        $exito = DbFunction::call('fn_registrar_intento_acceso', [$idUsuario, 'intentos', '127.0.0.1', true]);
        $this->assertSame(0, (int) $exito->intentos);
        $this->assertNull($exito->bloqueado_hasta);
        $this->assertNull(
            DB::table('usuario')->where('id_usuario', $idUsuario)->value('bloqueado_hasta')
        );
        $this->assertSame(0, DB::table('intento_acceso')
            ->where('id_usuario', $idUsuario)
            ->where('exitoso', false)
            ->count());
    }
}
