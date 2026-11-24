<?php

namespace Modules\ServicioTecnico\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Modules\Garantias\Models\SolicitudServicio;
use Modules\ServicioTecnico\Mail\CodigoVerificacion;
use Modules\ServicioTecnico\Requests\IdentificarClienteRequest;
use Modules\ServicioTecnico\Requests\RegistrarSolicitudRequest;
use Modules\ServicioTecnico\Requests\VerificarCodigoRequest;
use Modules\ServicioTecnico\Services\ConsultaPublicaService;
use Modules\ServicioTecnico\Services\SolicitudService;
use Modules\ServicioTecnico\Support\SesionCliente;
use Modules\ServicioTecnico\Support\VerificacionOtp;

class PublicoController extends Controller
{
    public function __construct(
        private ConsultaPublicaService $consulta,
        private SolicitudService $solicitudes,
        private VerificacionOtp $otp,
    ) {}

    public function identificar()
    {
        return view('serviciotecnico::publico.identificar');
    }

    /**
     * Paso 1: valida los datos y envía el OTP. Todavía no abre la sesión.
     */
    public function validar(IdentificarClienteRequest $request)
    {
        $validado = $request->validated();

        $cliente = $this->solicitudes->buscarCliente(
            $validado['nombre'],
            $validado['telefono'],
            $validado['correo']
        );

        if ($cliente === null) {
            return back()
                ->with('error', 'No se encontró un cliente con esos datos. Verifica tu nombre, celular y correo, o comunícate con Ewiflor.');
        }

        $codigo = VerificacionOtp::generarCodigo();
        $this->otp->iniciar(
            $cliente->id_cliente,
            $cliente->nombre,
            $cliente->telefono,
            $validado['correo'],
            $codigo
        );

        Mail::to($validado['correo'])->send(new CodigoVerificacion($codigo, $cliente->nombre));

        return redirect()->route('serviciotecnico.publico.verificar')
            ->with('success', 'Enviamos un código de 6 dígitos a '.$validado['correo'].'.');
    }

    public function verificar()
    {
        if (SesionCliente::obtener() !== null) {
            return redirect()->route('serviciotecnico.publico.panel');
        }

        $pendiente = $this->otp->obtener();

        if ($pendiente === null) {
            return redirect()->route('serviciotecnico.publico.identificar')
                ->with('error', 'Inicia la identificación para recibir un código de verificación.');
        }

        return view('serviciotecnico::publico.verificar', ['correo' => $pendiente['correo']]);
    }

    /**
     * Paso 2: canjea el OTP por la sesión del cliente.
     */
    public function verificarCodigo(VerificarCodigoRequest $request)
    {
        $pendiente = $this->otp->obtener();

        if ($pendiente === null) {
            return redirect()->route('serviciotecnico.publico.identificar')
                ->with('error', 'El código venció o ya fue usado. Vuelve a identificarte.');
        }

        $resultado = $this->otp->verificar($request->validated()['codigo']);

        if ($resultado['estado'] === VerificacionOtp::ESTADO_OK) {
            $request->session()->regenerate();
            SesionCliente::establecer(
                (int) $pendiente['id_cliente'],
                $pendiente['nombre'],
                $pendiente['telefono']
            );
            $this->otp->limpiar();

            return redirect()->route('serviciotecnico.publico.panel')
                ->with('success', 'Bienvenido, '.$pendiente['nombre'].'. Aquí puedes ver tus compras y solicitar servicio técnico.');
        }

        if ($resultado['estado'] === VerificacionOtp::ESTADO_INCORRECTO) {
            return back()->with('error', 'El código no es correcto. Te quedan '.$resultado['intentos_restantes'].' intentos.');
        }

        return redirect()->route('serviciotecnico.publico.identificar')
            ->with('error', 'El código venció o agotaste los intentos. Vuelve a identificarte para recibir uno nuevo.');
    }

    public function reenviar()
    {
        $pendiente = $this->otp->obtener();

        if ($pendiente === null) {
            return redirect()->route('serviciotecnico.publico.identificar')
                ->with('error', 'Inicia la identificación para recibir un código de verificación.');
        }

        $codigo = VerificacionOtp::generarCodigo();
        $this->otp->iniciar(
            $pendiente['id_cliente'],
            $pendiente['nombre'],
            $pendiente['telefono'],
            $pendiente['correo'],
            $codigo
        );

        Mail::to($pendiente['correo'])->send(new CodigoVerificacion($codigo, $pendiente['nombre']));

        return redirect()->route('serviciotecnico.publico.verificar')
            ->with('success', 'Enviamos un nuevo código a '.$pendiente['correo'].'.');
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
        $this->otp->limpiar();

        return redirect('/')->with('success', 'Sesión de cliente cerrada.');
    }
}
