<?php

namespace Modules\Comisiones\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\Models\User;
use Modules\Comisiones\Models\Comision;
use Modules\Comisiones\Services\ComisionService;

class ComisionController extends Controller
{
    public function __construct(private ComisionService $service) {}

    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        // Casteo a int: los valores vienen como string desde la query string.
        // Sin esto, la comparación estricta (===) en la vista nunca coincide
        // y el <select> muestra la primera opción (2024 / enero) en vez del mes/año real.
        $anio = (int) $request->input('anio', (int) now()->year);
        $mes = (int) $request->input('mes', (int) now()->month);
        $idSucursal = $request->input('id_sucursal')
            ? (int) $request->input('id_sucursal')
            : null;
        // Sin filtro por defecto: mostrar TODAS las comisiones (pagadas y pendientes).
        // Si se pusiera PENDIENTE por defecto, las ya pagadas parecería que "no existen".
        $estado = $request->input('estado');

        // Refresca el periodo actual para que nuevas ventas/pagos se reflejen
        // automáticamente al entrar (los estados PAGADO se conservan).
        $this->service->calcularYGuardar($anio, $mes, $idSucursal, $user);

        $comisiones = $this->service->listar($anio, $mes, $idSucursal, $estado, $user);

        return view('comisiones::index', [
            'comisiones' => $comisiones,
            'resumen' => $this->resumen($comisiones),
            'sucursales' => $this->service->sucursalesParaFiltro($user),
            'vendedores' => $this->service->vendedoresParaFiltro($idSucursal, $user),
            'filtros' => [
                'anio' => $anio,
                'mes' => $mes,
                'id_sucursal' => $idSucursal,
                'estado' => $estado,
            ],
            'meses' => range(1, 12),
            // Rango amplio: últimos 3 años + el actual + el próximo, para asegurar
            // que haya siempre datos visibles y poder cambiar de fecha.
            'anios' => range((int) now()->year - 2, (int) now()->year + 1),
        ]);
    }

    public function generar(Request $request)
    {
        $request->validate([
            'anio' => ['required', 'integer', 'min:2020', 'max:2100'],
            'mes' => ['required', 'integer', 'min:1', 'max:12'],
            'id_sucursal' => ['nullable', 'integer'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $anio = (int) $request->input('anio');
        $mes = (int) $request->input('mes');
        $idSucursal = $request->input('id_sucursal')
            ? (int) $request->input('id_sucursal')
            : null;

        try {
            $guardadas = $this->service->calcularYGuardar($anio, $mes, $idSucursal, $user);

            return back()->with('ok', sprintf(
                'Comisiones generadas para %02d/%d: %d vendedor(es) procesado(s).',
                $mes,
                $anio,
                count($guardadas)
            ))->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function pagar(Request $request, int $comision)
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $this->service->marcarPagado($comision, $user);

            return back()->with('ok', 'Comisión marcada como pagada.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function resumen($comisiones): array
    {
        return [
            'cantidad' => $comisiones->count(),
            'total_ventas_bs' => $comisiones->sum(fn ($c) => (float) $c->total_ventas_bs),
            'monto_comision_bs' => $comisiones->sum(fn ($c) => (float) $c->monto_comision_bs),
        ];
    }
}
