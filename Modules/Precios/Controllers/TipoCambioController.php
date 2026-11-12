<?php

namespace Modules\Precios\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Precios\Requests\FiltroHistorialRequest;
use Modules\Precios\Requests\StoreTipoCambioManualRequest;
use Modules\Precios\Services\TipoCambioService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TipoCambioController extends Controller
{
    public function __construct(private TipoCambioService $service) {}

    public function index(FiltroHistorialRequest $request)
    {
        $filtros = $request->filtros();
        $resumen = $this->service->resumen();

        return view('precios::tipo-cambio.index', [
            'actuales'    => $resumen['actuales'],
            'variaciones' => $resumen['variaciones'],
            'brecha'      => $resumen['brecha'],
            'historial'   => $this->service->historialPaginado($filtros, 20),
            'filtros'     => $filtros,
            'estadoApi'   => $this->service->estadoApi(),
            'ultimaSync'  => $this->service->ultimaSincronizacion(),
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
            Log::error('Precios: error al registrar tipo de cambio manual', [
                'error'   => $e->getMessage(),
                'usuario' => $request->user()?->getKey(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo registrar el tipo de cambio. Intenta de nuevo; si persiste, contacta al administrador.');
        }
    }

    public function exportar(FiltroHistorialRequest $request): StreamedResponse
    {
        $filtros = $request->filtros();
        $nombre  = 'tipo_cambio_' . now('America/La_Paz')->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($filtros) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel respete los acentos

            fputcsv($out, [
                'Fecha', 'Tipo', 'Venta (Bs)', 'Compra (Bs)', 'Origen',
                'Registrado', 'Referencia API', 'Usuario',
            ], ';');

            foreach ($this->service->historialParaExportar($filtros) as $h) {
                fputcsv($out, [
                    $h->fecha?->format('d/m/Y'),
                    $h->tipo,
                    number_format((float) $h->valor, 4, '.', ''),
                    $h->valor_compra !== null ? number_format((float) $h->valor_compra, 4, '.', '') : '',
                    $h->origen,
                    $h->fecha_registro?->format('d/m/Y H:i'),
                    $h->fecha_referencia_api?->format('d/m/Y H:i'),
                    $this->celdaSegura($h->usuario?->nombre_completo),
                ], ';');
            }

            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Evita inyección de fórmulas al abrir el CSV en Excel. */
    private function celdaSegura(?string $valor): string
    {
        $valor = $valor ?? '';

        return $valor !== '' && str_contains('=+-@', $valor[0]) ? "'" . $valor : $valor;
    }
}
