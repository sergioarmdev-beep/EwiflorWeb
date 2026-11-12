<?php

namespace Modules\Catalogo\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Catalogo\Models\Producto;

class StockController extends Controller
{
    /* =========================================================
     |  CONSULTA DE STOCK DE UN PRODUCTO
     * ========================================================= */

    public function show(Producto $producto)
    {
        $producto->load(['marca', 'variantes' => fn ($q) => $q->orderBy('sku')]);

        // Existencias por variante y almacén (solo filas que ya tienen registro).
        $existencias = DB::table('existencia as e')
            ->join('variante_producto as v', 'v.id_variante', '=', 'e.id_variante')
            ->join('almacen as a', 'a.id_almacen', '=', 'e.id_almacen')
            ->join('sucursal as s', 's.id_sucursal', '=', 'a.id_sucursal')
            ->where('v.id_producto', $producto->id_producto)
            ->orderBy('v.sku')
            ->orderBy('s.nombre')
            ->orderBy('a.nombre')
            ->get([
                'v.sku',
                'v.color',
                'a.nombre as almacen',
                's.nombre as sucursal',
                'e.ubicacion_interna',
                'e.cantidad_total',
                'e.cantidad_reservada',
            ]);

        $total = (int) $existencias->sum('cantidad_total');
        $reservado = (int) $existencias->sum('cantidad_reservada');

        $totales = [
            'total' => $total,
            'reservado' => $reservado,
            'disponible' => $total - $reservado,
        ];

        // Almacenes activos, para el formulario de entrada.
        $almacenes = DB::table('almacen as a')
            ->join('sucursal as s', 's.id_sucursal', '=', 'a.id_sucursal')
            ->where('a.estado', 'ACTIVO')
            ->orderBy('s.nombre')
            ->orderBy('a.nombre')
            ->get(['a.id_almacen', 'a.nombre as almacen', 's.nombre as sucursal']);

        return view('catalogo::productos.stock', [
            'producto' => $producto,
            'variantesActivas' => $producto->variantes->where('estado', 'ACTIVO')->values(),
            'existencias' => $existencias,
            'totales' => $totales,
            'almacenes' => $almacenes,
        ]);
    }

    /* =========================================================
     |  REGISTRAR ENTRADA DE STOCK
     * ========================================================= */

    public function entrada(Request $request, Producto $producto)
    {
        if ($producto->modalidad !== 'INVENTARIABLE') {
            return back()->with('error', 'Este producto es bajo pedido y no maneja stock.');
        }

        $datos = $request->validate([
            'id_variante' => [
                'required', 'integer',
                // La variante debe pertenecer a este producto y estar activa.
                Rule::exists('variante_producto', 'id_variante')
                    ->where('id_producto', $producto->id_producto)
                    ->where('estado', 'ACTIVO'),
            ],
            'id_almacen' => [
                'required', 'integer',
                Rule::exists('almacen', 'id_almacen')->where('estado', 'ACTIVO'),
            ],
            'cantidad' => ['required', 'integer', 'min:1', 'max:1000000'],
            'motivo' => ['nullable', 'string', 'max:200'],
        ], [
            'id_variante.required' => 'Seleccione la variante que ingresa.',
            'id_variante.exists' => 'La variante seleccionada no es válida o está inactiva.',
            'id_almacen.required' => 'Seleccione el almacén de destino.',
            'id_almacen.exists' => 'El almacén seleccionado no es válido o está inactivo.',
            'cantidad.required' => 'Ingrese la cantidad.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
            'cantidad.max' => 'La cantidad es demasiado grande.',
        ], [
            'id_variante' => 'variante',
            'id_almacen' => 'almacén',
        ]);

        $motivo = trim((string) ($datos['motivo'] ?? '')) ?: 'Ingreso de mercadería';

        try {
            // La función de la BD crea el movimiento (kardex) y suma a `existencia`
            // en una sola operación atómica.
            DB::select(
                'select registrar_movimiento_inventario(?::tipo_movimiento, ?::varchar, ?::integer, ?::integer, ?::integer, ?::integer, ?::jsonb) as id_movimiento',
                [
                    'ENTRADA',
                    $motivo,
                    Auth::id(),
                    null,                       // almacén origen (no aplica a una entrada)
                    (int) $datos['id_almacen'], // almacén destino
                    null,                       // venta asociada (no aplica)
                    json_encode([[
                        'id_variante' => (int) $datos['id_variante'],
                        'cantidad' => (int) $datos['cantidad'],
                    ]]),
                ]
            );
        } catch (QueryException $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'No se pudo registrar la entrada de stock. Intente nuevamente.');
        }

        return redirect()
            ->route('catalogo.stock.show', $producto->id_producto)
            ->with('success', "Se registraron {$datos['cantidad']} unidades en el stock.");
    }
}
