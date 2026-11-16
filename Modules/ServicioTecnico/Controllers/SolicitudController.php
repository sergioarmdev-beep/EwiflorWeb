<?php

namespace Modules\ServicioTecnico\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Garantias\Models\SolicitudServicio;
use Modules\ServicioTecnico\Requests\CerrarSolicitudRequest;
use Modules\ServicioTecnico\Requests\RegistrarSolicitudRequest;
use Modules\ServicioTecnico\Services\SolicitudService;

class SolicitudController extends Controller
{
    public function __construct(private SolicitudService $solicitudes) {}

    /**
     * Técnico: bandeja de encargos pendientes y asignados.
     */
    public function bandeja(Request $request)
    {
        $tecnico = $request->user();
        $solicitudes = $this->solicitudes->pendientesParaTecnico($tecnico->id_usuario);

        return view('serviciotecnico::solicitudes.bandeja', [
            'tecnico' => $tecnico,
            'solicitudes' => $solicitudes,
        ]);
    }

    /**
     * Técnico acepta un encargo pendiente (bloqueo contra doble asignación).
     */
    public function aceptar(Request $request, SolicitudServicio $solicitud)
    {
        try {
            $this->solicitudes->aceptar($solicitud, $request->user());

            return back()->with('success', "Encargo #{$solicitud->id_solicitud} aceptado. Ahora es tu responsabilidad.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Técnico (o gerente/admin) cierra una solicitud con resultado, diagnóstico y costo.
     */
    public function cerrar(CerrarSolicitudRequest $request, SolicitudServicio $solicitud)
    {
        try {
            $this->solicitudes->cerrar($solicitud, $request->validated());

            return back()->with('success', "Solicitud #{$solicitud->id_solicitud} cerrada correctamente.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Vendedor: formulario para crear solicitud en nombre de un cliente.
     */
    public function nueva(Request $request)
    {
        $tecnicos = $this->solicitudes->tecnicos();

        return view('serviciotecnico::solicitudes.nueva', [
            'tecnicos' => $tecnicos,
        ]);
    }

    /**
     * Vendedor: guarda la solicitud interna.
     */
    public function guardarInterna(RegistrarSolicitudRequest $request)
    {
        $validado = $request->validate([
            'nombre_cliente' => ['required', 'string', 'min:2', 'max:100'],
            'telefono_cliente' => ['required', 'string', 'min:7', 'max:20'],
            'descripcion_falla' => ['required', 'string', 'min:5', 'max:1000'],
            'id_garantia' => ['nullable', 'integer', 'exists:garantia,id_garantia'],
        ]);

        $cliente = $this->solicitudes->buscarCliente($validado['nombre_cliente'], $validado['telefono_cliente']);

        if ($cliente === null) {
            return back()->withInput()->with('error', 'Cliente no encontrado con ese nombre y celular.');
        }

        $solicitud = $this->solicitudes->crearInterna($cliente->id_cliente, $request->user(), [
            'descripcion_falla' => $validado['descripcion_falla'],
            'id_garantia' => $validado['id_garantia'] ?? null,
        ]);

        return redirect()->route('serviciotecnico.show', $solicitud)
            ->with('success', "Solicitud #{$solicitud->id_solicitud} creada para {$cliente->nombre}.");
    }

    /**
     * Gerente/Administrador: gestión global de movimientos del módulo.
     */
    public function gestion(Request $request)
    {
        $filtros = $request->only(['estado', 'tecnico', 'en_garantia', 'desde', 'hasta']);
        $solicitudes = $this->solicitudes->todas($filtros);
        $tecnicos = $this->solicitudes->tecnicos();

        return view('serviciotecnico::solicitudes.gestion', [
            'solicitudes' => $solicitudes,
            'tecnicos' => $tecnicos,
            'filtros' => $filtros,
        ]);
    }

    /**
     * Detalle de una solicitud (vendedor, técnico, gerente, admin).
     */
    public function show(SolicitudServicio $solicitud)
    {
        $solicitud->load(['cliente', 'garantia', 'tecnico', 'registra']);

        return view('serviciotecnico::solicitudes.show', [
            'solicitud' => $solicitud,
        ]);
    }
}
