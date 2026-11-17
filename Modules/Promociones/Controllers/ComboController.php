<?php

namespace Modules\Promociones\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalogo\Models\Marca;
use Modules\Catalogo\Models\Producto;
use Modules\Catalogo\Services\ProductoService;
use Modules\Promociones\Requests\ComboRequest;
use Modules\Promociones\Services\ComboService;

class ComboController extends Controller
{
    private const POR_PAGINA = 15;

    public function __construct(private ComboService $service) {}

    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => 'nullable|string|max:120',
            'estado' => 'nullable|in:ACTIVO,INACTIVO',
        ]);

        $combos = Producto::query()
            ->where('tipo_producto', 'COMBO')
            ->select('producto.*')
            ->with(['marca', 'imagenPrincipal', 'variantes', 'componentesCombo.componente:id_producto,codigo,nombre'])
            ->buscar($filtros['q'] ?? null)
            ->when($filtros['estado'] ?? null, fn ($q, $v) => $q->where('producto.estado', $v))
            ->orderBy('producto.nombre')
            ->selectRaw(ProductoService::SQL_STOCK_TOTAL.' as stock_total')
            ->selectRaw(ProductoService::SQL_STOCK_DISPONIBLE.' as stock_actual')
            ->paginate(self::POR_PAGINA)
            ->withQueryString();

        $resumen = [
            'total' => Producto::where('tipo_producto', 'COMBO')->count(),
            'activos' => Producto::where('tipo_producto', 'COMBO')->where('estado', 'ACTIVO')->count(),
            'publicos' => Producto::where('tipo_producto', 'COMBO')->where('estado', 'ACTIVO')->where('es_publico', true)->count(),
        ];

        return view('promociones::combos.index', [
            'combos' => $combos,
            'filtros' => $filtros,
            'resumen' => $resumen,
        ]);
    }

    public function create()
    {
        return view('promociones::combos.form', [
            'combo' => new Producto([
                'tipo_producto' => 'COMBO',
                'modalidad' => 'INVENTARIABLE',
                'stock_minimo' => 0,
                'garantia_meses' => 0,
                'es_publico' => true,
                'estado' => 'ACTIVO',
            ]),
            'marcas' => Marca::orderBy('nombre')->get(),
            'categorias' => Producto::CATEGORIAS,
            'componentes' => collect(),
            'componentesDisponibles' => $this->componentesDisponibles(),
            'imagenes' => collect(),
            'precioUsd' => 0,
        ]);
    }

    public function store(ComboRequest $request)
    {
        $datos = $request->validated();

        try {
            $combo = $this->service->crear($datos, $request->file('imagenes', []));
        } catch (BusinessRuleException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('promociones.combos.index')
            ->with('success', "Combo «{$combo->nombre}» creado correctamente.");
    }

    public function edit(Producto $combo)
    {
        abort_unless($combo->esCombo(), 404);

        $combo->load(['imagenes', 'variantes', 'componentesCombo.componente:id_producto,codigo,nombre']);

        return view('promociones::combos.form', [
            'combo' => $combo,
            'marcas' => Marca::orderBy('nombre')->get(),
            'categorias' => Producto::CATEGORIAS,
            'componentes' => $combo->componentesCombo,
            'componentesDisponibles' => $this->componentesDisponibles(),
            'imagenes' => $combo->imagenes()->orderBy('orden')->get(),
            'precioUsd' => (float) ($combo->variantes->first()?->precio_usd ?? 0),
        ]);
    }

    public function update(ComboRequest $request, Producto $combo)
    {
        abort_unless($combo->esCombo(), 404);

        $datos = $request->validated();

        try {
            $this->service->actualizar(
                $combo,
                $datos,
                $request->file('imagenes', []),
                $request->integer('imagen_principal') ?: null,
                array_map('intval', $request->input('eliminar_imagenes', []))
            );
        } catch (BusinessRuleException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('promociones.combos.index')
            ->with('success', "Combo «{$combo->nombre}» actualizado correctamente.");
    }

    public function toggleEstado(Producto $combo)
    {
        abort_unless($combo->esCombo(), 404);

        $combo = $this->service->alternarEstado($combo);

        $mensaje = $combo->estado === 'ACTIVO'
            ? 'Combo activado.'
            : 'Combo desactivado (ya no se venderá ni se mostrará).';

        return back()->with('success', $mensaje);
    }

    /* =========================================================
     |  HELPERS PRIVADOS
     * ========================================================= */

    /** Productos que pueden integrar un combo: individuales y activos. */
    private function componentesDisponibles()
    {
        return Producto::query()
            ->where('estado', 'ACTIVO')
            ->whereNot('tipo_producto', 'COMBO')
            ->orderBy('nombre')
            ->get(['id_producto', 'codigo', 'nombre', 'modelo', 'tipo_producto']);
    }
}
