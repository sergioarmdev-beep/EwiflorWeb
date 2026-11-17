<?php

namespace Modules\Promociones\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalogo\Models\VarianteProducto;
use Modules\Promociones\Models\Promocion;
use Modules\Promociones\Requests\PromocionRequest;
use Modules\Promociones\Services\PromocionService;

class PromocionController extends Controller
{
    private const POR_PAGINA = 15;

    public function __construct(private PromocionService $service) {}

    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => 'nullable|string|max:120',
            'estado' => 'nullable|in:ACTIVA,INACTIVA',
            'vigentes' => 'nullable|boolean',
        ]);

        $promociones = Promocion::query()
            ->with(['usuario:id_usuario,nombre_usuario', 'variantes:id_variante'])
            ->withCount('variantes')
            ->buscar($filtros['q'] ?? null)
            ->when($filtros['estado'] ?? null, fn ($q, $v) => $q->where('estado', $v))
            ->when(! empty($filtros['vigentes']), fn ($q) => $q->vigentes())
            ->orderByDesc('id_promocion')
            ->paginate(self::POR_PAGINA)
            ->withQueryString();

        $resumen = [
            'activas' => Promocion::where('estado', 'ACTIVA')->count(),
            'vigentes' => Promocion::vigentes()->count(),
            'variantes' => Promocion::vigentes()->withCount('variantes')->get()->sum('variantes_count'),
        ];

        return view('promociones::promociones.index', [
            'promociones' => $promociones,
            'filtros' => $filtros,
            'resumen' => $resumen,
        ]);
    }

    public function create()
    {
        return view('promociones::promociones.form', [
            'promocion' => new Promocion([
                'tipo_descuento' => 'PCT',
                'fecha_inicio' => now()->toDateString(),
                'estado' => 'ACTIVA',
            ]),
            'variantesPromocion' => collect(),
            'variantesBusqueda' => $this->variantesBusqueda(),
        ]);
    }

    public function store(PromocionRequest $request)
    {
        $datos = $request->validated();

        try {
            $promocion = $this->service->crear($datos, $datos['variantes'], $request->user());
        } catch (BusinessRuleException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('promociones.index')
            ->with('success', "Promoción «{$promocion->nombre}» creada correctamente.");
    }

    public function edit(Promocion $promocion)
    {
        $promocion->load('variantes:id_variante,sku,color');

        return view('promociones::promociones.form', [
            'promocion' => $promocion,
            'variantesPromocion' => $promocion->variantes,
            'variantesBusqueda' => $this->variantesBusqueda(),
        ]);
    }

    public function update(PromocionRequest $request, Promocion $promocion)
    {
        $datos = $request->validated();

        try {
            $this->service->actualizar($promocion, $datos, $datos['variantes']);
        } catch (BusinessRuleException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('promociones.index')
            ->with('success', "Promoción «{$promocion->nombre}» actualizada correctamente.");
    }

    public function toggleEstado(Promocion $promocion)
    {
        try {
            $this->service->alternarEstado($promocion);
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        }

        $mensaje = $promocion->estado === 'ACTIVA'
            ? 'Promoción activada.'
            : 'Promoción desactivada (ya no se precargará en ventas ni cotizaciones).';

        return back()->with('success', $mensaje);
    }

    public function destroy(Promocion $promocion)
    {
        try {
            $promocion->delete();
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Promoción «{$promocion->nombre}» eliminada.");
    }

    /* =========================================================
     |  HELPERS PRIVADOS
     * ========================================================= */

    /** Variantes elegibles: activas, de productos activos, con su producto para agrupar. */
    private function variantesBusqueda()
    {
        return VarianteProducto::with('producto:id_producto,codigo,nombre,estado')
            ->where('estado', 'ACTIVO')
            ->whereHas('producto', fn ($q) => $q->where('estado', 'ACTIVO'))
            ->orderBy('sku')
            ->get()
            ->map(fn ($v) => [
                'id_variante' => $v->id_variante,
                'sku' => $v->sku,
                'color' => $v->color,
                'precio' => $v->precio_usd !== null ? (float) $v->precio_usd : null,
                'id_producto' => $v->id_producto,
                'producto' => $v->producto?->nombre ?? '',
                'codigo_producto' => $v->producto?->codigo ?? '',
            ])
            ->values();
    }
}
