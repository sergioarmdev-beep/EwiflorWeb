<?php

namespace Modules\Ventas\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalogo\Models\VarianteProducto;
use Modules\Clientes\Models\Cliente;
use Modules\Cotizaciones\Models\Cotizacion;
use Modules\Inventario\Models\Almacen;
use Modules\Ventas\Models\Pago;
use Modules\Ventas\Models\Venta;
use Modules\Ventas\Requests\CancelarVentaRequest;
use Modules\Ventas\Requests\RegistrarPagoRequest;
use Modules\Ventas\Requests\StoreVentaRequest;
use Modules\Ventas\Services\VentaService;

class VentaController extends Controller
{
    public function __construct(private VentaService $service) {}

    public function index(Request $request)
    {
        $q = Venta::with(['cliente', 'usuario', 'pagos']);

        if ($e = $request->input('estado')) {
            $q->where('estado', $e);
        }
        if ($s = trim((string) $request->input('buscar'))) {
            $q->where(function ($w) use ($s) {
                $w->where('numero_factura', 'ilike', "%{$s}%")
                    ->orWhereHas('cliente', fn($c) => $c->where('nombre', 'ilike', "%{$s}%"));
            });
        }
        if ($desde = $request->input('desde')) {
            $q->whereDate('fecha_hora', '>=', $desde);
        }
        if ($hasta = $request->input('hasta')) {
            $q->whereDate('fecha_hora', '<=', $hasta);
        }

        $ventas = $q->orderByDesc('id_venta')->paginate(20)->withQueryString();

        return view('ventas::index', compact('ventas'));
    }

    public function create(Request $request)
    {
        $modo = $request->boolean('desde_cotizacion') ? 'cotizacion' : 'directa';

        $data = [
            'modo'      => $modo,
            'almacenes' => Almacen::where('estado', 'ACTIVO')->orderBy('nombre')->get(),
        ];

        if ($modo === 'cotizacion') {
            $cot = Cotizacion::with(['cliente', 'detalles.variante.producto'])
                ->findOrFail($request->integer('cotizacion'));

            if (! $this->servicePuedeConvertir($cot)) {
                return redirect()
                    ->route('cotizaciones.show', $cot)
                    ->with('error', 'Esta cotización no puede convertirse (debe estar EMITIDA y vigente).');
            }

            $data['cotizacion'] = $cot;
        } else {
            $data['clientes']  = Cliente::activos()->orderBy('nombre')->get();
            $data['variantes'] = $this->variantesActivas();
            $data['lineas']    = old('lineas', []);
        }

        return view('ventas::create', $data);
    }

    public function store(StoreVentaRequest $request)
    {
        $data  = $request->validated();
        $actor = $request->user();

        $venta = $data['modo'] === 'cotizacion'
            ? $this->service->convertirCotizacion((int) $data['id_cotizacion'], (int) $data['id_almacen'], $actor)
            : $this->service->crearDirecta($data, $actor);

        return redirect()
            ->route('ventas.show', $venta)
            ->with('ok', "Venta {$venta->numero_factura} registrada correctamente.");
    }

    public function show(Venta $venta)
    {
        $venta->load([
            'cliente',
            'usuario',
            'sucursal',
            'tipoCambio',
            'detalles.variante.producto',
            'pagos',
            'comprobantes',
            'usuarioCancela',
        ]);

        return view('ventas::show', [
            'venta'   => $venta,
            'totales' => $venta->totales(),
            'formas'  => Pago::FORMAS,
        ]);
    }

    public function registrarPago(RegistrarPagoRequest $request, Venta $venta)
    {
        try {
            $this->service->registrarPago($venta, $request->validated());
            return back()->with('ok', 'Pago registrado.');
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancelar(CancelarVentaRequest $request, Venta $venta)
    {
        try {
            $this->service->cancelar($venta, $request->user(), $request->validated()['motivo']);
            return redirect()
                ->route('ventas.show', $venta)
                ->with('ok', 'Venta cancelada. Stock revertido.');
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function comprobante(Venta $venta)
    {
        $venta->load(['cliente', 'usuario', 'sucursal', 'detalles.variante.producto', 'pagos', 'comprobantes']);
        return view('ventas::comprobante', [
            'venta'   => $venta,
            'totales' => $venta->totales(),
        ]);
    }

    private function servicePuedeConvertir(Cotizacion $c): bool
    {
        if ($c->estado !== 'EMITIDA') return false;
        $vence = $c->fecha_emision->copy()->addDays($c->vigencia_dias);
        return ! now()->startOfDay()->gt($vence);
    }

    private function variantesActivas()
    {
        return VarianteProducto::with('producto')
            ->where('estado', 'ACTIVO')
            ->whereHas('producto', fn($q) => $q->where('estado', 'ACTIVO'))
            ->orderBy('sku')
            ->get();
    }
}
