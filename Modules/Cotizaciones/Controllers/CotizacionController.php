<?php

namespace Modules\Cotizaciones\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\Models\User;
use Modules\Catalogo\Models\VarianteProducto;
use Modules\Clientes\Models\Cliente;
use Modules\Cotizaciones\Models\Cotizacion;
use Modules\Cotizaciones\Requests\StoreCotizacionRequest;
use Modules\Cotizaciones\Requests\UpdateCotizacionRequest;
use Modules\Cotizaciones\Services\CotizacionService;

class CotizacionController extends Controller
{
    public function __construct(private CotizacionService $service) {}

    public function index(Request $request)
    {
        $q = Cotizacion::with(['cliente', 'usuario', 'detalles']);

        if ($e = $request->input('estado')) {
            $q->where('estado', $e);
        }
        if ($cl = $request->input('id_cliente')) {
            $q->where('id_cliente', $cl);
        }
        if ($s = trim((string) $request->input('buscar'))) {
            $q->whereHas(
                'cliente',
                fn($w) =>
                $w->where('nombre', 'ilike', "%{$s}%")
                    ->orWhere('telefono', 'like', "%{$s}%")
            );
        }
        if ($request->input('condicion') === 'VENCIDA') {
            $q->where('estado', 'EMITIDA')
                ->whereRaw('(fecha_emision + vigencia_dias) < current_date');
        } elseif ($request->input('condicion') === 'VIGENTE') {
            $q->where('estado', 'EMITIDA')
                ->whereRaw('(fecha_emision + vigencia_dias) >= current_date');
        }

        $cotizaciones = $q->orderByDesc('id_cotizacion')->paginate(20)->withQueryString();

        return view('cotizaciones::index', [
            'cotizaciones' => $cotizaciones,
            'clientes'     => Cliente::activos()->orderBy('nombre')->get(['id_cliente', 'nombre']),
        ]);
    }

    public function create()
    {
        return view('cotizaciones::create', [
            'cotizacion' => new Cotizacion(['vigencia_dias' => 14, 'estado' => 'EMITIDA']),
            'clientes'   => Cliente::activos()->orderBy('nombre')->get(),
            'variantes'  => $this->variantes(),
            'lineas'     => [],
        ]);
    }

    public function store(StoreCotizacionRequest $request)
    {
        $cot = $this->service->crear($request->validated(), $request->user());

        return redirect()
            ->route('cotizaciones.show', $cot)
            ->with('ok', "Cotización #{$cot->id_cotizacion} emitida correctamente.");
    }

    public function show(Cotizacion $cotizacion)
    {
        $cotizacion->load(['cliente', 'usuario', 'sucursal', 'tipoCambio', 'detalles.variante.producto']);
        return view('cotizaciones::show', compact('cotizacion'));
    }

    public function edit(Cotizacion $cotizacion)
    {
        if (! $this->service->puedeEditarse($cotizacion)) {
            return redirect()
                ->route('cotizaciones.show', $cotizacion)
                ->with('error', 'Solo se pueden editar cotizaciones EMITIDAS.');
        }

        $cotizacion->load('detalles');

        return view('cotizaciones::edit', [
            'cotizacion' => $cotizacion,
            'clientes'   => Cliente::activos()->orderBy('nombre')->get(),
            'variantes'  => $this->variantes(),
            'lineas'     => $cotizacion->detalles->map(fn($d) => [
                'id_variante'         => $d->id_variante,
                'cantidad'            => $d->cantidad,
                'precio_unitario_usd' => $d->precio_unitario_usd,
                'descuento_pct'       => $d->descuento_pct,
            ])->all(),
        ]);
    }

    public function update(UpdateCotizacionRequest $request, Cotizacion $cotizacion)
    {
        $this->service->actualizar($cotizacion, $request->validated());

        return redirect()
            ->route('cotizaciones.show', $cotizacion)
            ->with('ok', 'Cotización actualizada.');
    }

    public function anular(Cotizacion $cotizacion)
    {
        $this->service->anular($cotizacion);

        return redirect()
            ->route('cotizaciones.show', $cotizacion)
            ->with('ok', 'Cotización anulada.');
    }

    private function variantes()
    {
        return VarianteProducto::with('producto')
            ->where('estado', 'ACTIVO')
            ->whereHas('producto', fn($q) => $q->where('estado', 'ACTIVO'))
            ->orderBy('sku')
            ->get();
    }
}
