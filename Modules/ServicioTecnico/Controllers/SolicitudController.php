<?php

namespace Modules\ServicioTecnico\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Garantias\Models\SolicitudServicio;
use Modules\ServicioTecnico\Requests\CancelarSolicitudRequest;
use Modules\ServicioTecnico\Requests\CerrarSolicitudRequest;
use Modules\ServicioTecnico\Requests\RegistrarSolicitudRequest;
use Modules\ServicioTecnico\Services\ConsultaPublicaService;
use Modules\ServicioTecnico\Services\SolicitudService;

class SolicitudController extends Controller
{
    public function __construct(
        private SolicitudService $solicitudes,
        private ConsultaPublicaService $consultaPublica,
    ) {}

    /**
     * Técnico: bandeja de encargos pendientes y asignados.
     */
    public function bandeja(Request $request)
    {
        $tecnico = $request->user();
        $solicitudes = $this->solicitudes->pendientesParaTecnico($tecnico);

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
            return $this->fallar('No se pudo aceptar el encargo. Inténtalo de nuevo más tarde.', $e);
        }
    }

    /**
     * Técnico (o gerente/admin) cierra una solicitud con resultado, diagnóstico y costo.
     */
    public function cerrar(CerrarSolicitudRequest $request, SolicitudServicio $solicitud)
    {
        try {
            $this->solicitudes->cerrar($solicitud, $request->user(), $request->validated());

            return back()->with('success', "Solicitud #{$solicitud->id_solicitud} cerrada correctamente.");
        } catch (\Throwable $e) {
            return $this->fallar('No se pudo cerrar la solicitud. Inténtalo de nuevo más tarde.', $e);
        }
    }

    /**
     * Gerente/Administrador: cancela una solicitud con motivo.
     */
    public function cancelar(CancelarSolicitudRequest $request, SolicitudServicio $solicitud)
    {
        try {
            $this->solicitudes->cancelar($solicitud, $request->user(), $request->validated()['motivo']);

            return back()->with('success', "Solicitud #{$solicitud->id_solicitud} cancelada correctamente.");
        } catch (\Throwable $e) {
            return $this->fallar('No se pudo cancelar la solicitud. Inténtalo de nuevo más tarde.', $e, true);
        }
    }

    /**
     * Vendedor: formulario para crear solicitud en nombre de un cliente.
     */
    public function nueva(Request $request)
    {
        $tecnicos = $this->solicitudes->tecnicos($request->user());

        return view('serviciotecnico::solicitudes.nueva', [
            'tecnicos' => $tecnicos,
        ]);
    }

    /**
     * Vendedor: guarda la solicitud interna.
     */
    public function guardarInterna(RegistrarSolicitudRequest $request)
    {
        $validado = $request->validated();

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
     * Listado de garantías vigentes que vencen en los próximos días.
     */
    public function garantiasPorVencer(Request $request)
    {
        $dias = (int) $request->get('dias', 30);
        $garantias = $this->consultaPublica->garantiasPorVencer($request->user(), $dias);

        return view('serviciotecnico::solicitudes.garantias-por-vencer', [
            'garantias' => $garantias,
            'dias' => $dias,
        ]);
    }

    /**
     * Gerente/Administrador: gestión global de movimientos del módulo.
     */
    public function gestion(Request $request)
    {
        $filtros = $request->only(['estado', 'tecnico', 'en_garantia', 'desde', 'hasta']);
        $solicitudes = $this->solicitudes->todas($request->user(), $filtros);
        $tecnicos = $this->solicitudes->tecnicos($request->user());

        return view('serviciotecnico::solicitudes.gestion', [
            'solicitudes' => $solicitudes,
            'tecnicos' => $tecnicos,
            'filtros' => $filtros,
        ]);
    }

    /**
     * Detalle de una solicitud (vendedor, técnico, gerente, admin).
     */
    public function show(Request $request, SolicitudServicio $solicitud)
    {
        // 404 en vez de 403 para no revelar la existencia de encargos ajenos.
        abort_unless($this->solicitudes->visiblePara($request->user(), $solicitud), 404);

        $solicitud->load(['cliente', 'garantia', 'tecnico', 'registra']);

        return view('serviciotecnico::solicitudes.show', [
            'solicitud' => $solicitud,
        ]);
    }

    /**
     * Las reglas de negocio (P0001) se muestran tal cual; los errores de
     * infraestructura se loguean y se sustituyen por un mensaje genérico.
     */
    private function fallar(string $mensajeGenerico, \Throwable $e, bool $conInput = false)
    {
        if ($e instanceof BusinessRuleException) {
            $redirect = back();

            return $conInput
                ? $redirect->withInput()->with('error', $e->getMessage())
                : $redirect->with('error', $e->getMessage());
        }

        Log::error('Fallo al procesar una acción de servicio técnico', [
            'usuario' => auth()->id(),
            'exception' => $e,
        ]);

        $redirect = back();

        return $conInput
            ? $redirect->withInput()->with('error', $mensajeGenerico)
            : $redirect->with('error', $mensajeGenerico);
    }
}
