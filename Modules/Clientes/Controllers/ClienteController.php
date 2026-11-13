<?php

namespace Modules\Clientes\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Clientes\Models\Cliente;
use Modules\Clientes\Requests\StoreClienteRequest;
use Modules\Clientes\Requests\UpdateClienteRequest;

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
        $cliente->load([
            'cotizaciones' => fn($query) => $query
                ->with(['tipoCambio', 'detalles'])
                ->orderByDesc('id_cotizacion')
                ->limit(10),

            'ventas' => fn($query) => $query
                ->with(['pagos'])
                ->orderByDesc('id_venta')
                ->limit(10),
        ]);

        return view('clientes::show', compact('cliente'));
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
