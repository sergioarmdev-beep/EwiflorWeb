<?php

namespace Modules\Precios\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Precios\Requests\StoreTipoCambioManualRequest;
use Modules\Precios\Services\TipoCambioService;

class TipoCambioController extends Controller
{
    public function __construct(private TipoCambioService $service) {}

    public function index()
    {
        return view('precios::tipo-cambio.index', [
            'actuales'   => $this->service->actuales(),
            'historial'  => $this->service->historial(30),
            'estadoApi'  => $this->service->estadoApi(),
            'ultimaSync' => $this->service->ultimaSincronizacion(),
        ]);
    }

    public function refresh(Request $request)
    {
        $resultado = $this->service->refrescarDesdeApi($request->user());

        return $resultado['ok']
            ? back()->with('success', $resultado['mensaje'])
            : back()->with('error', $resultado['mensaje']);
    }

    public function storeManual(StoreTipoCambioManualRequest $request)
    {
        try {
            $tc = $this->service->registrarManual(
                $request->validated(),
                $request->user()
            );

            return back()->with(
                'success',
                "Tipo de cambio {$tc->tipo} registrado manualmente: Bs {$tc->valor} por USD."
            );
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'No se pudo registrar: ' . $e->getMessage());
        }
    }
}
