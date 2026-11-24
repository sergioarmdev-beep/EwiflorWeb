<?php

namespace Modules\Clientes\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Clientes\Models\Cliente;
use Modules\Clientes\Requests\StoreClienteRequest;
use Modules\Clientes\Requests\UpdateClienteRequest;
use Modules\ServicioTecnico\Services\SolicitudService;
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
        return view('clientes::create', ['cliente' => new Cliente]);
    }

    public function store(StoreClienteRequest $request)
    {
        $data = $request->safe()->except('forzar_creacion');

        // Alta rápida desde el formulario de cotización/venta: se responde JSON
        // y, si el teléfono ya pertenece a un cliente activo, se devuelve ese
        // cliente para reutilizarlo sin crear un duplicado.
        if ($request->expectsJson()) {
            $existente = Cliente::activos()
                ->where('telefono', trim((string) $data['telefono']))
                ->first();

            if ($existente && ! $request->boolean('forzar_creacion')) {
                return response()->json([
                    'existente' => true,
                    'id_cliente' => $existente->id_cliente,
                    'nombre' => $existente->nombre,
                    'telefono' => $existente->telefono,
                    'etiqueta' => "{$existente->nombre} — {$existente->telefono}",
                ]);
            }
        }

        $cliente = Cliente::create($data + ['estado' => 'ACTIVO']);

        if ($request->expectsJson()) {
            return response()->json([
                'id_cliente' => $cliente->id_cliente,
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefono,
                'etiqueta' => "{$cliente->nombre} — {$cliente->telefono}",
            ], 201);
        }

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('ok', 'Cliente registrado correctamente.');
    }

    public function show(Request $request, Cliente $cliente, SolicitudService $solicitudes)
    {
        $user = $request->user();

        // Solo el VENDEDOR queda limitado a sus propias operaciones; Gerente y
        // Administrador ven todo y Servicio técnico conserva el historial
        // completo (lo necesita para atender garantías y servicios).
        $soloDocumentosPropios = $user !== null && $user->nombre_rol === 'VENDEDOR';

        // Historial de cotizaciones
        $cotizaciones = $cliente->cotizaciones()
            ->with([
                'tipoCambio',
                'detalles',
            ])
            ->when($soloDocumentosPropios, fn ($q) => $q->visiblesPara($user))
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
            ->when($soloDocumentosPropios, fn ($q) => $q->visiblesPara($user))
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
            ->when($soloDocumentosPropios, fn ($q) => $q->visiblesPara($user))
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
            ->when($soloDocumentosPropios, fn ($q) => $q->where('venta.id_usuario', $user->id_usuario))
            ->selectRaw('
            COUNT(*) AS total,
            COALESCE(SUM(vt.total_usd), 0) AS total_usd,
            COALESCE(SUM(vt.total_bs), 0) AS total_bs,
            COALESCE(SUM(pa.total_pagado_bs), 0) AS pagado_bs,
            COALESCE(
                SUM(vt.total_bs - COALESCE(pa.total_pagado_bs, 0)),
                0
            ) AS saldo_bs
        ')
            ->first();

        // Garantías activas (vigentes y por vencer en los próximos 30 días)
        $garantiasActivas = DB::table('v_garantia_vigente as gv')
            ->where('gv.id_cliente', $cliente->id_cliente)
            ->where('gv.vigente', true)
            ->where('gv.fecha_vencimiento', '>=', now()->startOfDay())
            ->where('gv.fecha_vencimiento', '<=', now()->startOfDay()->addDays(30))
            ->when($soloDocumentosPropios, fn ($q) => $q->whereIn(
                'gv.id_venta',
                DB::table('venta')->select('id_venta')->where('id_usuario', $user->id_usuario)
            ))
            ->orderBy('gv.fecha_vencimiento')
            ->get([
                'gv.id_garantia',
                'gv.id_detalle_venta',
                'gv.id_venta',
                'gv.sku',
                'gv.producto',
                'gv.fecha_inicio',
                'gv.duracion_meses',
                'gv.fecha_vencimiento',
            ]);

        // Garantías por vencer (resumen simple para tarjeta comercial)
        $garantiasPorVencer = $garantiasActivas->where(
            fn ($g) => Carbon::parse($g->fecha_vencimiento)->diffInDays(now()) <= 7
        )->count();

        // Solicitudes de servicio técnico del cliente (ADR-006): el listado
        // ya revela descripción y estado, así que aplica el mismo alcance
        // que el detalle. GERENTE ve todo; el resto, solo su sucursal, y
        // VENDEDOR además solo las que él registró.
        $querySolicitudes = $cliente->solicitudesServicio();

        if ($user !== null) {
            $solicitudes->aplicarAlcanceVisible(
                $querySolicitudes,
                $user,
                'solicitud_servicio.id_sucursal'
            );

            if ($user->nombre_rol === 'VENDEDOR') {
                $querySolicitudes->where(
                    'solicitud_servicio.id_usuario_registra',
                    $user->id_usuario
                );
            }
        } else {
            $querySolicitudes->whereRaw('1 = 0');
        }

        $solicitudesServicio = $querySolicitudes
            ->with('tecnico')
            ->orderByDesc('fecha_solicitud')
            ->limit(10)
            ->get();

        return view('clientes::show', compact(
            'cliente',
            'cotizaciones',
            'ventas',
            'resumenCotizaciones',
            'resumenVentas',
            'garantiasActivas',
            'garantiasPorVencer',
            'solicitudesServicio',
            'soloDocumentosPropios'
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
