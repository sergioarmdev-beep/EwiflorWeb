<?php

namespace Tests\Integration;

use Modules\Auth\Models\User;
use Modules\Garantias\Models\SolicitudServicio;
use Modules\ServicioTecnico\Services\SolicitudService;

class AlcanceSolicitudTest extends PostgresTestCase
{
    private function usuario(string $rol, ?int $sucursal, int $id): User
    {
        $user = new User([
            'nombre_completo' => 'Usuario '.$rol,
            'nombre_usuario' => strtolower($rol).'_'.$id,
            'nombre_rol' => $rol,
            'id_sucursal' => $sucursal,
            'estado' => 'ACTIVO',
        ]);
        $user->id_usuario = $id;

        return $user;
    }

    private function solicitud(?int $sucursal, string $estado, ?int $idTecnico, ?int $idRegistra): SolicitudServicio
    {
        return new SolicitudServicio([
            'id_cliente' => 1,
            'id_sucursal' => $sucursal,
            'id_garantia' => null,
            'id_usuario_registra' => $idRegistra,
            'id_tecnico' => $idTecnico,
            'descripcion_falla' => 'Falla de prueba',
            'fecha_solicitud' => now(),
            'estado' => $estado,
            'origen' => 'INTERNO',
        ]);
    }

    public function test_gerente_ve_todo_y_los_demas_solo_su_sucursal(): void
    {
        $service = new SolicitudService;
        $solicitud = $this->solicitud(1, 'PENDIENTE', null, 99);

        $this->assertTrue($service->visiblePara($this->usuario('GERENTE', null, 10), $solicitud));
        $this->assertTrue($service->visiblePara($this->usuario('ADMINISTRADOR', 1, 11), $solicitud));
        $this->assertFalse($service->visiblePara($this->usuario('ADMINISTRADOR', 2, 12), $solicitud));
        $this->assertFalse($service->visiblePara($this->usuario('ADMINISTRADOR', null, 13), $solicitud));

        // Solicitudes sin sucursal: solo GERENTE (fail-closed).
        $sinSucursal = $this->solicitud(null, 'PENDIENTE', null, 99);
        $this->assertTrue($service->visiblePara($this->usuario('GERENTE', 1, 10), $sinSucursal));
        $this->assertFalse($service->visiblePara($this->usuario('ADMINISTRADOR', 1, 11), $sinSucursal));
        $this->assertFalse($service->visiblePara($this->usuario('SERVICIO_TECNICO', 1, 14), $sinSucursal));
    }

    public function test_tecnico_solo_ve_pendientes_y_asignadas_de_su_sucursal(): void
    {
        $service = new SolicitudService;
        $tecnico = $this->usuario('SERVICIO_TECNICO', 1, 20);
        $otroTecnico = $this->usuario('SERVICIO_TECNICO', 1, 21);

        $pendiente = $this->solicitud(1, 'PENDIENTE', null, 99);
        $this->assertTrue($service->visiblePara($tecnico, $pendiente));

        $asignadaAOtro = $this->solicitud(1, 'PROGRAMADA', 21, 99);
        $this->assertFalse($service->visiblePara($tecnico, $asignadaAOtro));

        $asignadaAmi = $this->solicitud(1, 'PROGRAMADA', 20, 99);
        $this->assertTrue($service->visiblePara($tecnico, $asignadaAmi));
        $this->assertFalse($service->visiblePara($otroTecnico, $asignadaAmi));

        $deOtraSucursal = $this->solicitud(2, 'PENDIENTE', null, 99);
        $this->assertFalse($service->visiblePara($tecnico, $deOtraSucursal));
    }

    public function test_vendedor_solo_ve_lo_que_registro_en_su_sucursal(): void
    {
        $service = new SolicitudService;
        $vendedor = $this->usuario('VENDEDOR', 1, 30);

        $this->assertTrue($service->visiblePara($vendedor, $this->solicitud(1, 'PENDIENTE', null, 30)));
        $this->assertFalse($service->visiblePara($vendedor, $this->solicitud(1, 'PENDIENTE', null, 31)));
        $this->assertFalse($service->visiblePara($vendedor, $this->solicitud(2, 'PENDIENTE', null, 30)));
        $this->assertFalse($service->visiblePara($vendedor, $this->solicitud(1, 'PENDIENTE', null, null)));
    }
}
