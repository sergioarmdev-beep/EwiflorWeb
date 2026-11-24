<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\Mail;
use Modules\ServicioTecnico\Mail\CodigoVerificacion;
use Modules\ServicioTecnico\Support\SesionCliente;
use Modules\ServicioTecnico\Support\VerificacionOtp;

class VerificacionOtpPublicaTest extends PostgresTestCase
{
    /** @return array{nombre: string, telefono: string, correo: string} */
    private function crearClienteOtp(): array
    {
        $this->crearCliente('otp');

        return [
            'nombre' => 'Cliente otp',
            'telefono' => '70000000',
            'correo' => 'otp@cliente.test.local',
        ];
    }

    /** @return array<string, mixed> */
    private function datosIdentificacion(array $cliente): array
    {
        return [
            'nombre' => $cliente['nombre'],
            'telefono' => $cliente['telefono'],
            'correo' => $cliente['correo'],
        ];
    }

    private function codigoEnviado(string $correo): string
    {
        $codigos = [];

        Mail::assertSent(CodigoVerificacion::class, function (CodigoVerificacion $mail) use (&$codigos, $correo) {
            if (! $mail->hasTo($correo)) {
                return false;
            }

            $codigos[] = $mail->codigo;

            return true;
        });

        return $codigos[0];
    }

    public function test_validar_envia_otp_y_no_abre_sesion(): void
    {
        Mail::fake();
        $cliente = $this->crearClienteOtp();

        $respuesta = $this->post(
            route('serviciotecnico.publico.validar'),
            $this->datosIdentificacion($cliente)
        );

        $respuesta->assertRedirect(route('serviciotecnico.publico.verificar'));
        $this->assertNull(SesionCliente::obtener());
        $this->assertNotNull(session(VerificacionOtp::KEY_PENDIENTE));
        Mail::assertSent(CodigoVerificacion::class, fn ($mail) => $mail->hasTo($cliente['correo']));
    }

    public function test_codigo_correcto_abre_la_sesion_del_cliente(): void
    {
        Mail::fake();
        $cliente = $this->crearClienteOtp();
        $this->post(route('serviciotecnico.publico.validar'), $this->datosIdentificacion($cliente));
        $codigo = $this->codigoEnviado($cliente['correo']);

        $respuesta = $this->post(route('serviciotecnico.publico.verificar.codigo'), ['codigo' => $codigo]);

        $respuesta->assertRedirect(route('serviciotecnico.publico.panel'));
        $this->assertSame('Cliente otp', SesionCliente::obtener()['nombre']);
        $this->assertNotNull(SesionCliente::idCliente());
        $this->assertNull(session(VerificacionOtp::KEY_PENDIENTE));
    }

    public function test_codigo_incorrecto_no_abre_sesion_y_se_agotan_los_intentos(): void
    {
        Mail::fake();
        $cliente = $this->crearClienteOtp();
        $this->post(route('serviciotecnico.publico.validar'), $this->datosIdentificacion($cliente));
        $incorrecto = $this->codigoEnviado($cliente['correo']) === '000000' ? '111111' : '000000';

        for ($intento = 1; $intento <= VerificacionOtp::MAX_INTENTOS; $intento++) {
            $respuesta = $this->post(route('serviciotecnico.publico.verificar.codigo'), ['codigo' => $incorrecto]);
            $respuesta->assertSessionHas('error');
            $this->assertNull(SesionCliente::idCliente());
        }

        $this->assertNull(session(VerificacionOtp::KEY_PENDIENTE));

        $respuesta = $this->post(route('serviciotecnico.publico.verificar.codigo'), ['codigo' => $incorrecto]);
        $respuesta->assertRedirect(route('serviciotecnico.publico.identificar'));
        $this->assertNull(SesionCliente::idCliente());
    }

    public function test_codigo_expirado_redirige_a_identificacion(): void
    {
        Mail::fake();
        $cliente = $this->crearClienteOtp();
        $this->post(route('serviciotecnico.publico.validar'), $this->datosIdentificacion($cliente));

        $pendiente = session(VerificacionOtp::KEY_PENDIENTE);
        $pendiente['expira_en'] = now()->subMinute()->getTimestamp();
        session([VerificacionOtp::KEY_PENDIENTE => $pendiente]);

        $respuesta = $this->post(
            route('serviciotecnico.publico.verificar.codigo'),
            ['codigo' => $this->codigoEnviado($cliente['correo'])]
        );

        $respuesta->assertRedirect(route('serviciotecnico.publico.identificar'));
        $this->assertNull(SesionCliente::idCliente());
    }

    public function test_correo_que_no_coincide_no_envia_codigo(): void
    {
        Mail::fake();
        $cliente = $this->crearClienteOtp();
        $datos = $this->datosIdentificacion($cliente);
        $datos['correo'] = 'otro@cliente.test.local';

        $respuesta = $this->post(route('serviciotecnico.publico.validar'), $datos);

        $respuesta->assertSessionHas('error');
        Mail::assertNothingSent();
        $this->assertNull(SesionCliente::idCliente());
    }

    public function test_correo_es_obligatorio(): void
    {
        Mail::fake();
        $cliente = $this->crearClienteOtp();
        $datos = $this->datosIdentificacion($cliente);
        unset($datos['correo']);

        $respuesta = $this->post(route('serviciotecnico.publico.validar'), $datos);

        $respuesta->assertSessionHasErrors('correo');
        Mail::assertNothingSent();
    }

    public function test_reenviar_emite_un_nuevo_codigo_sin_abrir_sesion(): void
    {
        Mail::fake();
        $cliente = $this->crearClienteOtp();
        $this->post(route('serviciotecnico.publico.validar'), $this->datosIdentificacion($cliente));

        $respuesta = $this->post(route('serviciotecnico.publico.reenviar'));

        $respuesta->assertRedirect(route('serviciotecnico.publico.verificar'));
        Mail::assertSent(CodigoVerificacion::class, 2);
        $this->assertNull(SesionCliente::idCliente());
    }

    public function test_panel_sin_codigo_redirige_a_identificacion(): void
    {
        $this->get(route('serviciotecnico.publico.panel'))
            ->assertRedirect(route('serviciotecnico.publico.identificar'));
    }
}
