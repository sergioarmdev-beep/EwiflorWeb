<?php

namespace Modules\ServicioTecnico\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Clientes\Models\Cliente;
use Modules\Garantias\Models\SolicitudServicio;
use Modules\ServicioTecnico\Requests\RegistrarSolicitudRequest;
use Modules\ServicioTecnico\Services\ConsultaPublicaService;
use Modules\ServicioTecnico\Services\SolicitudService;
use Modules\ServicioTecnico\Support\SesionCliente;

class PublicoController extends Controller
{
    public function __construct(
        private ConsultaPublicaService $consulta,
        private SolicitudService $solicitudes,
    ) {}

    public function identificar()
    {
        return view('serviciotecnico::publico.identificar');
    }

    public function validar(Request $request)
    {
        $validado = $request->validate([
            'nombre' => ['required', 'string', 'min:2', 'max:100'],
            'telefono' => ['required', 'string', 'min:7', 'max:20'],
        ]);

        $cliente = $this->solicitudes->buscarCliente($validado['nombre'], $validado['telefono']);

        if ($cliente === null) {
            return back()
                ->withInput()
                ->with('error', 'No se encontró un cliente con ese nombre y número de celular. Asegúrate de estar registrado con compras previas.');
        }

        SesionCliente::establecer($cliente->id_cliente, $cliente->nombre, $cliente->telefono);

        return redirect()->route('serviciotecnico.publico.panel')
            ->with('success', 'Bienvenido, '.e($cliente->nombre).'. Aquí puedes ver tus compras y solicitar servicio técnico.');
    }

    public function panel()
    {
        $cliente = SesionCliente::obtener();

        if ($cliente === null) {
            return redirect()->route('serviciotecnico.publico.identificar');
        }

        $compras = $this->consulta->comprasConGarantia($cliente['id_cliente']);
        $solicitudes = $this->consulta->solicitudesDelCliente($cliente['id_cliente']);

        return view('serviciotecnico::publico.panel', [
            'cliente' => $cliente,
            'compras' => $compras,
            'solicitudes' => $solicitudes,
        ]);
    }

    public function solicitar()
    {
        $cliente = SesionCliente::obtener();

        if ($cliente === null) {
            return redirect()->route('serviciotecnico.publico.identificar');
        }

        $compras = $this->consulta->comprasConGarantia($cliente['id_cliente']);

        return view('serviciotecnico::publico.solicitar', [
            'cliente' => $cliente,
            'compras' => $compras,
        ]);
    }

    public function guardar(RegistrarSolicitudRequest $request)
    {
        $cliente = SesionCliente::obtener();

        if ($cliente === null) {
            return redirect()->route('serviciotecnico.publico.identificar');
        }

        $solicitud = $this->solicitudes->crearPublica($cliente['id_cliente'], $request->validated());

        return redirect()->route('serviciotecnico.publico.detalle', $solicitud)
            ->with('success', 'Tu solicitud de servicio técnico ha sido registrada. Pronto un técnico la revisará.');
    }

    public function detalle(SolicitudServicio $solicitud)
    {
        $cliente = SesionCliente::obtener();

        if ($cliente === null) {
            return redirect()->route('serviciotecnico.publico.identificar');
        }

        // Solo el dueño del cliente puede ver su solicitud. 404 para no revelar
        // la existencia de encargos ajenos (coherente con el detalle interno).
        if ((int) $solicitud->id_cliente !== $cliente['id_cliente']) {
            abort(404);
        }

        $solicitud->load(['tecnico', 'garantia']);

        return view('serviciotecnico::publico.detalle', [
            'cliente' => $cliente,
            'solicitud' => $solicitud,
        ]);
    }

    public function salir(Request $request)
    {
        SesionCliente::limpiar();

        return redirect('/')->with('success', 'Sesión de cliente cerrada.');
    }
}
