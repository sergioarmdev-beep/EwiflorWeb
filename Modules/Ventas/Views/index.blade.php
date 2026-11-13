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

    <form method="GET" class="bg-white p-4 rounded shadow grid grid-cols-1 md:grid-cols-5 gap-3">
        <input type="text" name="buscar" value="{{ request('buscar') }}"
            placeholder="Factura o cliente" class="border rounded px-3 py-2">
        <select name="estado" class="border rounded px-3 py-2">
            <option value="">— Estado —</option>
            @foreach (['EN_PROCESO','REGISTRADA','CANCELADA'] as $e)
            <option value="{{ $e }}" @selected(request('estado')===$e)>{{ $e }}</option>
            @endforeach
        </select>
        <input type="date" name="desde" value="{{ request('desde') }}" class="border rounded px-3 py-2">
        <input type="date" name="hasta" value="{{ request('hasta') }}" class="border rounded px-3 py-2">
        <div class="flex gap-2">
            <button class="bg-gray-800 text-white px-4 py-2 rounded">Filtrar</button>
            <a href="{{ route('ventas.index') }}" class="border px-4 py-2 rounded">Limpiar</a>
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
                    <th class="px-4 py-2 text-right">Total USD</th>
                    <th class="px-4 py-2 text-right">Total Bs</th>
                    <th class="px-4 py-2 text-left">Estado</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ventas as $v)
                @php $t = $v->totales(); @endphp
                <tr class="border-t">
                    <td class="px-4 py-2 font-mono">{{ $v->numero_factura ?? '—' }}</td>
                    <td class="px-4 py-2">{{ $v->fecha_hora?->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2">{{ $v->cliente?->nombre ?? '—' }}</td>
                    <td class="px-4 py-2">{{ $v->usuario?->nombre_completo ?? '—' }}</td>
                    <td class="px-4 py-2 text-right">$ {{ number_format($t->total_usd ?? 0, 2) }}</td>
                    <td class="px-4 py-2 text-right">Bs {{ number_format($t->total_bs ?? 0, 2) }}</td>
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
                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">Sin ventas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $ventas->links() }}</div>
</div>
@endsection