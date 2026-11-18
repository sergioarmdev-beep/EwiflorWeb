<?php

namespace Modules\Ventas\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Catalogo\Models\VarianteProducto;
use Modules\Catalogo\Support\BuscadorVariantes;
use Modules\Clientes\Models\Cliente;
use Modules\Cotizaciones\Models\Cotizacion;
use Modules\Promociones\Services\PromocionService;
use Modules\Sucursales\Support\AlcanceDocumentos;
use Modules\Ventas\Models\Pago;
use Modules\Ventas\Models\Venta;
use Modules\Ventas\Requests\CancelarVentaRequest;
use Modules\Ventas\Requests\RegistrarPagoRequest;
use Modules\Ventas\Requests\StoreVentaRequest;
use Modules\Ventas\Services\VentaService;

class VentaController extends Controller
{
    public function __construct(private VentaService $service, private PromocionService $promociones) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $ventas = $this->queryVentas($request)
            ->orderByDesc('venta.fecha_hora')
            ->orderByDesc('venta.id_venta')
            ->paginate(20)
            ->withQueryString();

        // Resumen del filtro completo (no de la página actual): una consulta más.
        $resumen = $this->queryVentas($request)
            ->select(DB::raw(
                'count(*) as cantidad, '
                .'coalesce(sum(rv.total_usd), 0) as total_usd, '
                .'coalesce(sum(rv.total_bs), 0) as total_bs, '
                .'coalesce(sum(rv.pagado_bs), 0) as pagado_bs'
            ))
            ->toBase()
            ->first();

        return view('ventas::index', [
            'ventas' => $ventas,
            'resumen' => $resumen,
            'sucursales' => AlcanceDocumentos::sucursalesParaFiltro($user),
            'usuarios' => AlcanceDocumentos::usuariosVisibles($user),
            'aviso' => AlcanceDocumentos::aviso($user, 'ventas'),
        ]);
    }

    /**
     * Listado filtrado. Se une a v_reporte_ventas para traer totales y pagado
     * en la misma query: antes se consultaba v_venta_total una vez por fila (N+1).
     */
    private function queryVentas(Request $request): Builder
    {
        $user = $request->user();

        $q = Venta::query()
            ->with(['cliente', 'usuario', 'sucursal'])
            ->join('v_reporte_ventas as rv', 'rv.id_venta', '=', 'venta.id_venta')
            ->select('venta.*', 'rv.total_usd', 'rv.total_bs', 'rv.pagado_bs')
            ->visiblesPara($user);

        if ($e = $request->input('estado')) {
            $q->where('venta.estado', $e);
        }
        if ($s = trim((string) $request->input('buscar'))) {
            $q->where(function ($w) use ($s) {
                $w->where('venta.numero_factura', 'ilike', "%{$s}%")
                    ->orWhereHas('cliente', fn ($c) => $c->where('nombre', 'ilike', "%{$s}%"));
            });
        }
        if ($desde = $request->input('desde')) {
            $q->whereDate('venta.fecha_hora', '>=', $desde);
        }
        if ($hasta = $request->input('hasta')) {
            $q->whereDate('venta.fecha_hora', '<=', $hasta);
        }

        // Sucursal: solo el Gerente cambia de sucursal; el resto ya está acotado por el scope.
        if (($idSucursal = $request->integer('id_sucursal')) && AlcanceDocumentos::esGerente($user)) {
            $q->where('venta.id_sucursal', $idSucursal);
        }

        // Vendedor: no lo usa el VENDEDOR (solo ve lo suyo).
        if (($idUsuario = $request->integer('id_usuario')) && ! AlcanceDocumentos::soloSusDocumentos($user)) {
            $q->where('venta.id_usuario', $idUsuario);
        }

        if ($cobro = $request->input('cobro')) {
            $q->whereRaw(match ($cobro) {
                'PAGADA' => 'rv.total_bs > 0 AND rv.pagado_bs >= rv.total_bs',
                'PENDIENTE' => 'rv.total_bs > 0 AND rv.pagado_bs = 0',
                'PARCIAL' => 'rv.pagado_bs > 0 AND rv.pagado_bs < rv.total_bs',
                default => '1 = 1',
            });
        }

        return $q;
    }

    public function create(Request $request)
    {
        $modo = $request->boolean('desde_cotizacion') ? 'cotizacion' : 'directa';
        $user = $request->user();

        $almacenes = AlcanceDocumentos::almacenesVisibles($user);
        if ($almacenes->isEmpty()) {
            return redirect()
                ->route('ventas.index')
                ->with('error', AlcanceDocumentos::esGerente($user)
                    ? 'No hay almacenes activos para registrar ventas.'
                    : 'Tu usuario no tiene sucursal asignada (o no tiene almacenes activos): no puedes registrar ventas.');
        }

        $variantes = $this->variantesActivas();
        $precios = $variantes->mapWithKeys(
            fn ($v) => [(string) $v->id_variante => $v->precio_usd !== null ? (float) $v->precio_usd : null]
        );
        $garantias = $variantes->mapWithKeys(
            fn ($v) => [(string) $v->id_variante => (int) ($v->producto?->garantia_meses ?? 0)]
        );

        // Promociones vigentes por variante (se precargan como descuento sugerido).
        $promociones = $this->promociones->mapaVigente($variantes);

        $data = [
            'modo' => $modo,
            'almacenes' => $almacenes,
            'precios' => $precios,
            'garantias' => $garantias,
            'promociones' => $promociones,
            'puedeEditarDescuento' => $this->promociones->puedeEditar($user),
            'variantesBusqueda' => BuscadorVariantes::payload($variantes, $promociones),
        ];

        if ($modo === 'cotizacion') {
            $cot = Cotizacion::with(['cliente', 'detalles.variante.producto'])
                ->findOrFail($request->integer('cotizacion'));

            if (! $this->servicePuedeConvertir($cot)) {
                return redirect()
                    ->route('cotizaciones.show', $cot)
                    ->with('error', 'Esta cotización no puede convertirse (debe estar EMITIDA y vigente).');
            }

            $this->autorizarVenta($request, $cot, 'Solo puedes convertir tus propias cotizaciones.');

            $data['cotizacion'] = $cot;
        } else {
            $data['clientes'] = Cliente::activos()->orderBy('nombre')->get();
            $data['variantes'] = $variantes;
            $data['lineas'] = old('lineas', []);
        }

        return view('ventas::create', $data);
    }

    public function store(StoreVentaRequest $request)
    {
        $data = $request->validated();
        $actor = $request->user();

        $venta = $data['modo'] === 'cotizacion'
            ? $this->service->convertirCotizacion((int) $data['id_cotizacion'], (int) $data['id_almacen'], $actor)
            : $this->service->crearDirecta($data, $actor);

        return redirect()
            ->route('ventas.show', $venta)
            ->with('ok', "Venta {$venta->numero_factura} registrada correctamente.");
    }

    public function show(Request $request, Venta $venta)
    {
        $this->autorizarVenta($request, $venta, 'Solo puedes consultar tus propias ventas.');

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

        $totales = $venta->totales();
        $totalBs = round((float) ($totales->total_bs ?? 0), 2);

        return view('ventas::show', [
            'venta' => $venta,
            'totales' => $totales,
            'formas' => Pago::FORMAS,
            'puedePagar' => $venta->estado !== 'CANCELADA'
                && $venta->pagos->isEmpty()
                && $totalBs > 0,
            'totalBs' => $totalBs,
        ]);
    }

    public function registrarPago(RegistrarPagoRequest $request, Venta $venta)
    {
        $this->autorizarVenta($request, $venta, 'Solo puedes registrar pagos de tus propias ventas.');

        try {
            $this->service->registrarPago($venta, $request->validated());

            return back()->with('ok', 'Pago registrado.');
        } catch (BusinessRuleException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function cancelar(CancelarVentaRequest $request, Venta $venta)
    {
        $this->autorizarVenta($request, $venta, 'Solo puedes cancelar ventas de tu sucursal.');

        try {
            $this->service->cancelar($venta, $request->user(), $request->validated()['motivo']);

            return redirect()
                ->route('ventas.show', $venta)
                ->with('ok', 'Venta cancelada. Stock revertido.');
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function comprobante(Request $request, Venta $venta)
    {
        $this->autorizarVenta($request, $venta, 'Solo puedes consultar tus propias ventas.');

        $venta->load(['cliente', 'usuario', 'sucursal', 'detalles.variante.producto', 'pagos', 'comprobantes']);

        return view('ventas::comprobante', [
            'venta' => $venta,
            'totales' => $venta->totales(),
        ]);
    }

    /**
     * Bloquea el acceso a ventas/cotizaciones que el usuario no puede consultar
     * (alcance por sucursal y autor: ver AlcanceDocumentos).
     *
     * Se distingue "otra sucursal" de "documento ajeno" para dar un mensaje útil.
     */
    private function autorizarVenta(Request $request, Venta|Cotizacion $documento, string $mensaje): void
    {
        $user = $request->user();

        if ($documento->puedeVer($user)) {
            return;
        }

        abort(
            403,
            AlcanceDocumentos::puedeOperarEnSucursal($user, (int) $documento->id_sucursal)
                ? $mensaje
                : 'Este documento pertenece a otra sucursal; no tienes acceso a él.'
        );
    }

    private function servicePuedeConvertir(Cotizacion $c): bool
    {
        if ($c->estado !== 'EMITIDA') {
            return false;
        }
        $vence = $c->fecha_emision->copy()->addDays($c->vigencia_dias);

        return ! now()->startOfDay()->gt($vence);
    }

    private function variantesActivas()
    {
        return VarianteProducto::with('producto.marca')
            ->where('estado', 'ACTIVO')
            ->whereHas('producto', fn ($q) => $q->where('estado', 'ACTIVO'))
            ->orderBy('sku')
            ->get();
    }
}
