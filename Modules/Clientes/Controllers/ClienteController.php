<?php

namespace Modules\Clientes\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Clientes\Models\Cliente;
use Modules\Clientes\Requests\StoreClienteRequest;
use Modules\Clientes\Requests\UpdateClienteRequest;
use Illuminate\Support\Facades\DB;
use Modules\Cotizaciones\Models\Cotizacion;
use Modules\Ventas\Models\Venta;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $q = Cliente::query();

        if ($s = trim((string) $request->input('buscar'))) {
            $q->where(function ($w) use ($s) {
                $w->where('nombre', 'ilike', "%{$s}%")
                    ->orWhere('telefono', 'like', "%{$s}%")
                    ->orWhere('correo', 'ilike', "%{$s}%");
            });
        }

        if ($t = $request->input('tipo_cliente')) {
            $q->where('tipo_cliente', $t);
        }

        if ($request->filled('es_mayorista')) {
            $q->where('es_mayorista', $request->boolean('es_mayorista'));
        }

        if ($e = $request->input('estado')) {
            $q->where('estado', $e);
        }

        $clientes = $q->orderByDesc('id_cliente')
            ->paginate(20)
            ->withQueryString();

        return view('clientes::index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes::create', ['cliente' => new Cliente()]);
    }

    public function store(StoreClienteRequest $request)
    {
        $cliente = Cliente::create($request->validated() + ['estado' => 'ACTIVO']);

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('ok', 'Cliente registrado correctamente.');
    }

    public function show(Cliente $cliente)
    {
        // Historial de cotizaciones
        $cotizaciones = $cliente->cotizaciones()
            ->with([
                'tipoCambio',
                'detalles',
            ])
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id_cotizacion')
            ->limit(10)
            ->get();

        // Subconsulta: total pagado por venta
        $pagosAgrupados = DB::table('pago')
            ->select('id_venta')
            ->selectRaw('SUM(monto_bs) AS total_pagado_bs')
            ->groupBy('id_venta');

        // Historial de ventas, incluyendo total y saldo
        $ventas = $cliente->ventas()
            ->leftJoin(
                'v_venta_total as vt',
                'venta.id_venta',
                '=',
                'vt.id_venta'
            )
            ->leftJoinSub(
                $pagosAgrupados,
                'pa',
                'venta.id_venta',
                '=',
                'pa.id_venta'
            )
            ->select([
                'venta.*',
                'vt.total_usd',
                'vt.total_bs',
                'pa.total_pagado_bs',
            ])
            ->with('pagos')
            ->orderByDesc('venta.fecha_hora')
            ->orderByDesc('venta.id_venta')
            ->limit(10)
            ->get();

        // Indicadores generales de cotizaciones
        $resumenCotizaciones = $cliente->cotizaciones()
            ->selectRaw("
            COUNT(*) AS total,
            COUNT(*) FILTER (WHERE estado = 'EMITIDA') AS emitidas,
            COUNT(*) FILTER (WHERE estado = 'CONVERTIDA') AS convertidas,
            COUNT(*) FILTER (WHERE estado = 'ANULADA') AS anuladas
        ")
            ->first();

        // Indicadores financieros de ventas registradas
        $resumenVentas = DB::table('venta')
            ->leftJoin(
                'v_venta_total as vt',
                'venta.id_venta',
                '=',
                'vt.id_venta'
            )
            ->leftJoinSub(
                $pagosAgrupados,
                'pa',
                'venta.id_venta',
                '=',
                'pa.id_venta'
            )
            ->where('venta.id_cliente', $cliente->id_cliente)
            ->where('venta.estado', 'REGISTRADA')
            ->selectRaw("
            COUNT(*) AS total,
            COALESCE(SUM(vt.total_usd), 0) AS total_usd,
            COALESCE(SUM(vt.total_bs), 0) AS total_bs,
            COALESCE(SUM(pa.total_pagado_bs), 0) AS pagado_bs,
            COALESCE(
                SUM(vt.total_bs - COALESCE(pa.total_pagado_bs, 0)),
                0
            ) AS saldo_bs
        ")
            ->first();

        return view('clientes::show', compact(
            'cliente',
            'cotizaciones',
            'ventas',
            'resumenCotizaciones',
            'resumenVentas'
        ));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes::edit', compact('cliente'));
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        $cliente->update($request->validated());

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('ok', 'Cliente actualizado.');
    }

    public function toggleEstado(Cliente $cliente)
    {
        $cliente->estado = $cliente->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $cliente->save();

        return back()->with('ok', "Cliente {$cliente->estado}.");
    }
}
