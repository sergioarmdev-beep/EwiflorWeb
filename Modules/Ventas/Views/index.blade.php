@extends('layouts.app')
@section('title', 'Ventas')

@section('content')
<div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Ventas</h1>
        <a href="{{ route('ventas.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            + Nueva venta
        </a>
    </div>

    @if (session('ok')) <div class="bg-green-100 text-green-800 px-4 py-2 rounded">{{ session('ok') }}</div> @endif
    @if (session('error')) <div class="bg-red-100 text-red-800 px-4 py-2 rounded">{{ session('error') }}</div> @endif

    @if ($aviso ?? null)
    <div class="bg-sky-50 border border-sky-200 text-sky-900 px-4 py-2 rounded text-sm">
        {{ $aviso }}
    </div>
    @endif

    @php
        $cant = (int) ($resumen->cantidad ?? 0);
        $totUsd = (float) ($resumen->total_usd ?? 0);
        $totBs = (float) ($resumen->total_bs ?? 0);
        $pagadoBs = (float) ($resumen->pagado_bs ?? 0);
        $saldoBs = max(0, $totBs - $pagadoBs);
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="bg-white p-4 rounded shadow text-sm">
            <p class="text-gray-500">Ventas del filtro</p>
            <p class="text-xl font-semibold">{{ number_format($cant) }}</p>
        </div>
        <div class="bg-white p-4 rounded shadow text-sm">
            <p class="text-gray-500">Total USD</p>
            <p class="text-xl font-semibold">$ {{ number_format($totUsd, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded shadow text-sm">
            <p class="text-gray-500">Total Bs</p>
            <p class="text-xl font-semibold">Bs {{ number_format($totBs, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded shadow text-sm">
            <p class="text-gray-500">Cobrado Bs</p>
            <p class="text-xl font-semibold text-green-700">Bs {{ number_format($pagadoBs, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded shadow text-sm">
            <p class="text-gray-500">Saldo Bs</p>
            <p class="text-xl font-semibold {{ $saldoBs > 0 ? 'text-red-700' : 'text-green-700' }}">
                Bs {{ number_format($saldoBs, 2) }}
            </p>
        </div>
    </div>

    <form method="GET" class="bg-white p-4 rounded shadow grid grid-cols-1 md:grid-cols-6 gap-3">
        <input type="text" name="buscar" value="{{ request('buscar') }}"
            placeholder="Factura o cliente" class="border rounded px-3 py-2">
        <select name="estado" class="border rounded px-3 py-2">
            <option value="">— Estado —</option>
            @foreach (['EN_PROCESO','REGISTRADA','CANCELADA'] as $e)
            <option value="{{ $e }}" @selected(request('estado')===$e)>{{ $e }}</option>
            @endforeach
        </select>
        <select name="cobro" class="border rounded px-3 py-2">
            <option value="">— Cobro —</option>
            @foreach (['PAGADA','PARCIAL','PENDIENTE'] as $c)
            <option value="{{ $c }}" @selected(request('cobro')===$c)>
                {{ ['PAGADA' => 'Pagada', 'PARCIAL' => 'Pago parcial', 'PENDIENTE' => 'Sin pagar'][$c] }}
            </option>
            @endforeach
        </select>

        @if (($sucursales ?? collect())->isNotEmpty())
        <select name="id_sucursal" class="border rounded px-3 py-2">
            <option value="">— Todas las sucursales —</option>
            @foreach ($sucursales as $s)
            <option value="{{ $s->id_sucursal }}" @selected((string) request('id_sucursal')===(string) $s->id_sucursal)>
                {{ $s->nombre }}
            </option>
            @endforeach
        </select>
        @endif

        @if (($usuarios ?? collect())->isNotEmpty())
        <select name="id_usuario" class="border rounded px-3 py-2">
            <option value="">— Todos los vendedores —</option>
            @foreach ($usuarios as $u)
            <option value="{{ $u->id_usuario }}" @selected((string) request('id_usuario')===(string) $u->id_usuario)>
                {{ $u->nombre_completo }}
            </option>
            @endforeach
        </select>
        @endif

        <div class="flex gap-2">
            <button class="bg-gray-800 text-white px-4 py-2 rounded">Filtrar</button>
            <a href="{{ route('ventas.index') }}" class="border px-4 py-2 rounded">Limpiar</a>
        </div>

        <div class="md:col-span-6 flex gap-3 items-center text-sm">
            <label class="flex items-center gap-1">
                Desde <input type="date" name="desde" value="{{ request('desde') }}" class="border rounded px-2 py-1">
            </label>
            <label class="flex items-center gap-1">
                Hasta <input type="date" name="hasta" value="{{ request('hasta') }}" class="border rounded px-2 py-1">
            </label>
        </div>
    </form>

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Factura</th>
                    <th class="px-4 py-2 text-left">Fecha</th>
                    <th class="px-4 py-2 text-left">Cliente</th>
                    <th class="px-4 py-2 text-left">Vendedor</th>
                    <th class="px-4 py-2 text-left">Sucursal</th>
                    <th class="px-4 py-2 text-right">Total USD</th>
                    <th class="px-4 py-2 text-right">Total Bs</th>
                    <th class="px-4 py-2 text-right">Cobrado Bs</th>
                    <th class="px-4 py-2 text-left">Estado</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ventas as $v)
                <tr class="border-t">
                    <td class="px-4 py-2 font-mono">{{ $v->numero_factura ?? '—' }}</td>
                    <td class="px-4 py-2">{{ $v->fecha_hora?->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2">{{ $v->cliente?->nombre ?? '—' }}</td>
                    <td class="px-4 py-2">{{ $v->usuario?->nombre_completo ?? '—' }}</td>
                    <td class="px-4 py-2">{{ $v->sucursal?->nombre ?? '—' }}</td>
                    <td class="px-4 py-2 text-right">$ {{ number_format($v->total_usd ?? 0, 2) }}</td>
                    <td class="px-4 py-2 text-right">Bs {{ number_format($v->total_bs ?? 0, 2) }}</td>
                    <td class="px-4 py-2 text-right">Bs {{ number_format($v->pagado_bs ?? 0, 2) }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs
                                {{ $v->estado === 'REGISTRADA' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $v->estado === 'EN_PROCESO' ? 'bg-amber-100 text-amber-800' : '' }}
                                {{ $v->estado === 'CANCELADA' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $v->estado }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <a href="{{ route('ventas.show', $v) }}" class="text-blue-600">Ver</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-4 py-6 text-center text-gray-500">Sin ventas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $ventas->links() }}</div>
</div>
@endsection
