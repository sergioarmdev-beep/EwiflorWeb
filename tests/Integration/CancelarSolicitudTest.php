<?php

namespace Tests\Integration;

use App\Exceptions\BusinessRuleException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Garantias\Models\SolicitudServicio;
use Modules\ServicioTecnico\Services\SolicitudService;

/**
 * Ejercita la función PG cancelar_solicitud_servicio() (parche A-05) a través
 * del Service, incluida la transición terminal de CANCELADA.
 */
class CancelarSolicitudTest extends PostgresTestCase
{
    private function usuario(int $id): User
    {
        return User::findOrFail($id);
    }

    private function solicitud(int $id): SolicitudServicio
    {
        return SolicitudServicio::findOrFail($id);
    }

    public function test_cancelar_pendiente_guarda_motivo_y_cierra_la_solicitud(): void
    {
        $sucursal = $this->crearSucursal('Sucursal cancelar');
        $gerente = $this->crearUsuario('GERENTE', $sucursal, 'gerentecancelar');
        $cliente = $this->crearCliente('cancelar');
        $id = $this->crearSolicitud($cliente, ['id_sucursal' => $sucursal]);
        $this->actor($gerente);

        $resultado = (new SolicitudService)->cancelar(
            $this->solicitud($id),
            $this->usuario($gerente),
            'El cliente desistió del servicio'
        );

        $this->assertSame('CANCELADA', $resultado->estado);
        $this->assertSame('El cliente desistió del servicio', $resultado->motivo_cancelacion);
        $this->assertNotNull($resultado->fecha_cierre);
        $this->assertSame(
            'CANCELADA',
            DB::table('solicitud_servicio')->where('id_solicitud', $id)->value('estado')
        );
    }

    public function test_administrador_puede_cancelar_en_su_sucursal(): void
    {
        $sucursal = $this->crearSucursal('Sucursal admin cancela');
        $admin = $this->crearUsuario('ADMINISTRADOR', $sucursal, 'admincancela');
        $cliente = $this->crearCliente('admincancela');
        $id = $this->crearSolicitud($cliente, ['id_sucursal' => $sucursal, 'estado' => 'PROGRAMADA']);
        $this->actor($admin);

        $resultado = (new SolicitudService)->cancelar(
            $this->solicitud($id),
            $this->usuario($admin),
            'Fue cancelada por el administrador'
        );

        $this->assertSame('CANCELADA', $resultado->estado);
    }

    public function test_administrador_no_puede_cancelar_de_otra_sucursal(): void
    {
        $sucursalA = $this->crearSucursal('Sucursal A cancelar');
        $sucursalB = $this->crearSucursal('Sucursal B cancelar');
        $adminB = $this->crearUsuario('ADMINISTRADOR', $sucursalB, 'adminotracancela');
        $cliente = $this->crearCliente('otracancela');
        $id = $this->crearSolicitud($cliente, ['id_sucursal' => $sucursalA]);
        $this->actor($adminB);

        try {
            (new SolicitudService)->cancelar(
                $this->solicitud($id),
                $this->usuario($adminB),
                'Intento desde otra sucursal'
            );
            $this->fail('Debió rechazar la cancelación fuera de la sucursal.');
        } catch (BusinessRuleException $e) {
            $this->assertStringContainsString('otra sucursal', $e->getMessage());
        }

        $this->assertSame('PENDIENTE', DB::table('solicitud_servicio')->where('id_solicitud', $id)->value('estado'));
    }

    public function test_no_se_puede_cancelar_una_solicitud_cerrada(): void
    {
        $sucursal = $this->crearSucursal('Sucursal cerrada cancelar');
        $gerente = $this->crearUsuario('GERENTE', $sucursal, 'gerentecerradacancelar');
        $cliente = $this->crearCliente('cerradacancelar');
        $id = $this->crearSolicitud($cliente, [
            'id_sucursal' => $sucursal,
            'estado' => 'CERRADA',
            'fecha_cierre' => now(),
            'resultado' => 'REPARACION',
            'diagnostico' => 'Reparación finalizada',
        ]);
        $this->actor($gerente);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('Solo se pueden cancelar solicitudes en estado PENDIENTE o PROGRAMADA');

        (new SolicitudService)->cancelar($this->solicitud($id), $this->usuario($gerente), 'Ya estaba cerrada');
    }

    public function test_una_solicitud_cancelada_no_puede_reabrirse(): void
    {
        $sucursal = $this->crearSucursal('Sucursal reactivar');
        $gerente = $this->crearUsuario('GERENTE', $sucursal, 'gerentereactivar');
        $cliente = $this->crearCliente('reactivar');
        $id = $this->crearSolicitud($cliente, ['id_sucursal' => $sucursal]);
        $this->actor($gerente);

        (new SolicitudService)->cancelar($this->solicitud($id), $this->usuario($gerente), 'Cancelada');

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('No se puede reactivar una solicitud cancelada');

        DB::table('solicitud_servicio')->where('id_solicitud', $id)->update(['estado' => 'PENDIENTE']);
    }

    public function test_el_check_de_estado_rechaza_valores_fuera_del_canon(): void
    {
        $sucursal = $this->crearSucursal('Sucursal check estado');
        $cliente = $this->crearCliente('checkestado');
        $id = $this->crearSolicitud($cliente, ['id_sucursal' => $sucursal]);

        $this->expectException(QueryException::class);

        DB::table('solicitud_servicio')->where('id_solicitud', $id)->update(['estado' => 'ARCHIVADA']);
    }
}
