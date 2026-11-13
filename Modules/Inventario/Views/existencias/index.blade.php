@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold text-gray-800">Existencias</h1>
        @if (in_array(auth()->user()->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true))
        <a href="{{ route('inventario.movimientos.create') }}"
            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
            + Nuevo movimiento
        </a>
        @endif
    </div>

    <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="text" name="buscar" value="{{ $filtros['buscar'] ?? '' }}"
            placeholder="Producto, código o SKU"
            class="border-gray-300 rounded-md shadow-sm w-full">

        <select name="almacen" class="border-gray-300 rounded-md shadow-sm w-full">
            <option value="">Todos los almacenes</option>
            @foreach ($almacenes as $a)
            <option value="{{ $a->nombre }}" @selected(($filtros['almacen'] ?? '' )===$a->nombre)>
                {{ $a->nombre }}
            </option>
            @endforeach
        </select>

        <label class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-md bg-white">
            <input type="checkbox" name="solo_alerta" value="1"
                @checked(($filtros['solo_alerta'] ?? '' )==='1' )>
            <span class="text-sm text-gray-700">Solo alertas</span>
        </label>

        <button type="submit"
            class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-800">
            Filtrar
        </button>
    </form>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Sucursal</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Almacén</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Producto</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">SKU</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Total</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Reservado</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Disponible</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Mín.</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Alerta</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($existencias as $e)
                <tr class="{{ $e->alerta === 'REPONER' ? 'bg-red-50' : '' }}">
                    <td class="px-4 py-2 text-gray-600">{{ $e->sucursal }}</td>
                    <td class="px-4 py-2">{{ $e->almacen }}</td>
                    <td class="px-4 py-2">{{ $e->producto }}</td>
                    <td class="px-4 py-2 font-mono text-xs">{{ $e->sku }}</td>
                    <td class="px-4 py-2 text-right font-mono">{{ $e->cantidad_total }}</td>
                    <td class="px-4 py-2 text-right font-mono text-gray-500">{{ $e->cantidad_reservada }}</td>
                    <td class="px-4 py-2 text-right font-mono font-semibold
                            {{ (int) $e->disponible <= (int) $e->stock_minimo ? 'text-red-600' : '' }}">
                        {{ $e->disponible }}
                    </td>
                    <td class="px-4 py-2 text-right text-gray-500">{{ $e->stock_minimo }}</td>
                    <td class="px-4 py-2 text-center">
                        @if ($e->alerta === 'REPONER')
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                            REPONER
                        </span>
                        @else
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                            OK
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                        No hay existencias registradas.
                        @if (in_array(auth()->user()->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true))
                        <br><a href="{{ route('inventario.movimientos.create') }}"
                            class="text-indigo-600 hover:underline">Registrar la primera entrada</a>.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $existencias->links() }}</div>
</div>
@endsection