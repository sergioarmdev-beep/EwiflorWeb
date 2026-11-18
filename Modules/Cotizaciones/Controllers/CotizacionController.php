<?php

namespace Modules\Cotizaciones\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalogo\Models\VarianteProducto;
use Modules\Catalogo\Support\BuscadorVariantes;
use Modules\Clientes\Models\Cliente;
use Modules\Cotizaciones\Models\Cotizacion;
use Modules\Cotizaciones\Requests\StoreCotizacionRequest;
use Modules\Cotizaciones\Requests\UpdateCotizacionRequest;
use Modules\Cotizaciones\Services\CotizacionService;
use Modules\Promociones\Services\PromocionService;
use Modules\Sucursales\Support\AlcanceDocumentos;

class CotizacionController extends Controller
{
    public function __construct(
        private CotizacionService $service,
        private PromocionService $promociones,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $q = Cotizacion::with(['cliente', 'usuario', 'sucursal', 'detalles'])
            ->visiblesPara($user);

        if ($e = $request->input('estado')) {
            $q->where('estado', $e);
        }
        if ($cl = $request->input('id_cliente')) {
            $q->where('id_cliente', $cl);
        }
        if ($s = trim((string) $request->input('buscar'))) {
            $q->whereHas(
                'cliente',
                fn ($w) => $w->where('nombre', 'ilike', "%{$s}%")
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

        // Sucursal: solo el Gerente cambia de sucursal (el resto ya está acotado por el scope).
        if (($idSucursal = $request->integer('id_sucursal')) && AlcanceDocumentos::esGerente($user)) {
            $q->where('id_sucursal', $idSucursal);
        }
        if (($idUsuario = $request->integer('id_usuario')) && ! AlcanceDocumentos::soloSusDocumentos($user)) {
            $q->where('id_usuario', $idUsuario);
        }

        $cotizaciones = $q->orderByDesc('id_cotizacion')->paginate(20)->withQueryString();

        return view('cotizaciones::index', [
            'cotizaciones' => $cotizaciones,
            'clientes' => Cliente::activos()->orderBy('nombre')->get(['id_cliente', 'nombre']),
            'sucursales' => AlcanceDocumentos::sucursalesParaFiltro($user),
            'usuarios' => AlcanceDocumentos::usuariosVisibles($user),
            'aviso' => AlcanceDocumentos::aviso($user, 'cotizaciones', 'emitiste'),
        ]);
    }

    public function create()
    {
        $variantes = $this->variantes();
        $promociones = $this->promociones->mapaVigente($variantes);

        return view('cotizaciones::create', [
            'cotizacion' => new Cotizacion(['vigencia_dias' => 14, 'estado' => 'EMITIDA']),
            'clientes' => Cliente::activos()->orderBy('nombre')->get(),
            'variantes' => $variantes,
            'precios' => $this->precios($variantes),
            'garantias' => $this->garantias($variantes),
            'promociones' => $promociones,
            'puedeEditarDescuento' => $this->promociones->puedeEditar(request()->user()),
            'variantesBusqueda' => BuscadorVariantes::payload($variantes, $promociones),
            'lineas' => [],
        ]);
    }

    public function store(StoreCotizacionRequest $request)
    {
        $cot = $this->service->crear($request->validated(), $request->user());

        return redirect()
            ->route('cotizaciones.show', $cot)
            ->with('ok', "Cotización #{$cot->id_cotizacion} emitida correctamente.");
    }

    public function show(Request $request, Cotizacion $cotizacion)
    {
        $this->autorizarLectura($request, $cotizacion);

        $cotizacion->load(['cliente', 'usuario', 'sucursal', 'tipoCambio', 'detalles.variante.producto']);

        return view('cotizaciones::show', compact('cotizacion'));
    }

    public function edit(Request $request, Cotizacion $cotizacion)
    {
        $this->autorizarLectura($request, $cotizacion);

        if (! $this->service->puedeEditarse($cotizacion)) {
            return redirect()
                ->route('cotizaciones.show', $cotizacion)
                ->with('error', 'Solo se pueden editar cotizaciones EMITIDAS.');
        }

        $cotizacion->load('detalles');
        $variantes = $this->variantes();
        $promociones = $this->promociones->mapaVigente($variantes);

        return view('cotizaciones::edit', [
            'cotizacion' => $cotizacion,
            'clientes' => Cliente::activos()->orderBy('nombre')->get(),
            'variantes' => $variantes,
            'precios' => $this->precios($variantes),
            'garantias' => $this->garantias($variantes),
            'promociones' => $promociones,
            'puedeEditarDescuento' => $this->promociones->puedeEditar(request()->user()),
            'variantesBusqueda' => BuscadorVariantes::payload($variantes, $promociones),
            'lineas' => $cotizacion->detalles->map(fn ($d) => [
                'id_variante' => $d->id_variante,
                'cantidad' => $d->cantidad,
                'precio_unitario_usd' => $d->precio_unitario_usd,
                'descuento_tipo' => 'PCT',
                'descuento_valor' => $d->descuento_pct,
                'garantia_meses' => $d->garantia_meses,
            ])->all(),
        ]);
    }

    public function update(UpdateCotizacionRequest $request, Cotizacion $cotizacion)
    {
        $this->autorizarLectura($request, $cotizacion);

        $this->service->actualizar($cotizacion, $request->validated(), $request->user());

        return redirect()
            ->route('cotizaciones.show', $cotizacion)
            ->with('ok', 'Cotización actualizada.');
    }

    public function anular(Request $request, Cotizacion $cotizacion)
    {
        $this->autorizarLectura($request, $cotizacion);

        $this->service->anular($cotizacion);

        return redirect()
            ->route('cotizaciones.show', $cotizacion)
            ->with('ok', 'Cotización anulada.');
    }

    private function autorizarLectura(Request $request, Cotizacion $cotizacion): void
    {
        $user = $request->user();

        if ($cotizacion->puedeVer($user)) {
            return;
        }

        abort(
            403,
            AlcanceDocumentos::puedeOperarEnSucursal($user, (int) $cotizacion->id_sucursal)
                ? 'Solo puedes consultar tus propias cotizaciones.'
                : 'Esta cotización pertenece a otra sucursal; no tienes acceso a ella.'
        );
    }

    private function variantes()
    {
        return VarianteProducto::with('producto.marca')
            ->where('estado', 'ACTIVO')
            ->whereHas('producto', fn ($q) => $q->where('estado', 'ACTIVO'))
            ->orderBy('sku')
            ->get();
    }

    /** @return array<string, float|null> id_variante => precio USD */
    private function precios($variantes): array
    {
        return $variantes
            ->mapWithKeys(fn ($v) => [(string) $v->id_variante => $v->precio_usd !== null ? (float) $v->precio_usd : null])
            ->all();
    }

    /** @return array<string, int> id_variante => garantia_meses (0 si no tiene) */
    private function garantias($variantes): array
    {
        return $variantes
            ->mapWithKeys(fn ($v) => [(string) $v->id_variante => (int) ($v->producto?->garantia_meses ?? 0)])
            ->all();
    }
}
