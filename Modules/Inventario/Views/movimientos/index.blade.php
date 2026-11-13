@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold text-gray-800">Movimientos de inventario</h1>
        @if (in_array(auth()->user()->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true))
        <a href="{{ route('inventario.movimientos.create') }}"
            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
            + Nuevo movimiento
        </a>
        @endif
    </div>

    <form method="GET" class="mb-4 flex gap-3">
        <select name="tipo" class="border-gray-300 rounded-md shadow-sm">
            <option value="">Todos los tipos</option>
            @foreach (['ENTRADA', 'SALIDA', 'AJUSTE', 'TRANSFERENCIA'] as $t)
            <option value="{{ $t }}" @selected(($filtros['tipo'] ?? '' )===$t)>{{ $t }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-800">Filtrar</button>
    </form>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">#</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Fecha</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Tipo</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Producto</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Cant.</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Origen → Destino</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Motivo</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Usuario</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($movimientos as $m)
                <tr>
                    <td class="px-4 py-2 text-gray-500">#{{ $m->id_movimiento }}</td>
                    <td class="px-4 py-2">{{ $m->fecha_hora?->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                @switch($m->tipo)
                                    @case('ENTRADA') bg-green-100 text-green-800 @break
                                    @case('SALIDA') bg-red-100 text-red-800 @break
                                    @case('AJUSTE') bg-yellow-100 text-yellow-800 @break
                                    @case('TRANSFERENCIA') bg-blue-100 text-blue-800 @break
                                @endswitch">
                            {{ $m->tipo }}
                        </span>
                    </td>
                    <td class="px-4 py-2">
                        @foreach ($m->detalles as $d)
                        <div>
                            {{ $d->variante?->producto?->nombre ?? '—' }}
                            <span class="text-xs text-gray-500 font-mono">({{ $d->variante?->sku }})</span>
                            @if ($d->sentido)
                            <span class="text-xs text-gray-500">— {{ $d->sentido }}</span>
                            @endif
                        </div>
                        @endforeach
                    </td>
                    <td class="px-4 py-2 text-right font-mono">
                        @foreach ($m->detalles as $d)
                        <div>{{ $d->cantidad }}</div>
                        @endforeach
                    </td>
                    <td class="px-4 py-2 text-gray-600">
                        {{ $m->almacenOrigen?->nombre ?? '—' }}
                        →
                        {{ $m->almacenDestino?->nombre ?? '—' }}
                    </td>
                    <td class="px-4 py-2 text-gray-600">{{ $m->motivo }}</td>
                    <td class="px-4 py-2">{{ $m->usuario?->nombre_completo ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        No hay movimientos registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $movimientos->links() }}</div>
</div>
@endsection