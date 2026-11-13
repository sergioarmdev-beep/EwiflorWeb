@extends('layouts.app')
@section('title', 'Cotizaciones')

@section('content')
<div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Cotizaciones</h1>
        <a href="{{ route('cotizaciones.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            + Nueva cotización
        </a>
    </div>

    @if (session('ok'))
    <div class="bg-green-100 text-green-800 px-4 py-2 rounded">{{ session('ok') }}</div>
    @endif
    @if (session('error'))
    <div class="bg-red-100 text-red-800 px-4 py-2 rounded">{{ session('error') }}</div>
    @endif

    <form method="GET" class="bg-white p-4 rounded shadow grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="text" name="buscar" value="{{ request('buscar') }}"
            placeholder="Cliente o teléfono" class="border rounded px-3 py-2">
        <select name="estado" class="border rounded px-3 py-2">
            <option value="">— Estado —</option>
            @foreach (['EMITIDA','CONVERTIDA','ANULADA'] as $e)
            <option value="{{ $e }}" @selected(request('estado')===$e)>{{ $e }}</option>
            @endforeach
        </select>
        <select name="condicion" class="border rounded px-3 py-2">
            <option value="">— Condición —</option>
            <option value="VIGENTE" @selected(request('condicion')==='VIGENTE' )>Vigente</option>
            <option value="VENCIDA" @selected(request('condicion')==='VENCIDA' )>Vencida</option>
        </select>
        <div class="flex gap-2">
            <button class="bg-gray-800 text-white px-4 py-2 rounded">Filtrar</button>
            <a href="{{ route('cotizaciones.index') }}" class="border px-4 py-2 rounded">Limpiar</a>
        </div>
    </form>

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">Fecha</th>
                    <th class="px-4 py-2 text-left">Cliente</th>
                    <th class="px-4 py-2 text-left">Vendedor</th>
                    <th class="px-4 py-2 text-right">Total USD</th>
                    <th class="px-4 py-2 text-left">Estado</th>
                    <th class="px-4 py-2 text-left">Condición</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cotizaciones as $c)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $c->id_cotizacion }}</td>
                    <td class="px-4 py-2">{{ $c->fecha_emision->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">{{ $c->cliente?->nombre ?? '—' }}</td>
                    <td class="px-4 py-2">{{ $c->usuario?->nombre_completo ?? '—' }}</td>
                    <td class="px-4 py-2 text-right">$ {{ number_format($c->totalUsd(), 2) }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs
                                {{ $c->estado === 'EMITIDA' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $c->estado === 'CONVERTIDA' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $c->estado === 'ANULADA' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $c->estado }}
                        </span>
                    </td>
                    <td class="px-4 py-2">
                        @php $cond = $c->condicionTemporal(); @endphp
                        <span class="text-xs
                                {{ $cond === 'VIGENTE' ? 'text-green-700' : '' }}
                                {{ $cond === 'VENCIDA' ? 'text-red-700 font-semibold' : '' }}
                                {{ $cond === 'CERRADA' ? 'text-gray-500' : '' }}">
                            {{ $cond }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <a href="{{ route('cotizaciones.show', $c) }}" class="text-blue-600">Ver</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">Sin cotizaciones.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $cotizaciones->links() }}</div>
</div>
@endsection