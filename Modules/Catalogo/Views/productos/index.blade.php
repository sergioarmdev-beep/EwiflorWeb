@extends('layouts.app')

@section('title', 'Catálogo de Productos')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-semibold text-gray-800">Productos</h2>
    <a href="{{ route('catalogo.productos.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition duration-150 ease-in-out">
        + Nuevo Producto
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full leading-normal">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Código</th>
                <th class="py-3 px-6 text-left">Nombre</th>
                <th class="py-3 px-6 text-left">Marca</th>
                <th class="py-3 px-6 text-left">Categoría</th>
                <th class="py-3 px-6 text-left">Tipo</th>
                <th class="py-3 px-6 text-center">Stock Mín.</th>
                <th class="py-3 px-6 text-center">Estado</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
            @forelse($productos as $producto)
            <tr class="border-b border-gray-200 hover:bg-gray-50">
                <td class="py-3 px-6 text-left whitespace-nowrap">
                    <span class="font-medium">{{ $producto->codigo }}</span>
                </td>
                <td class="py-3 px-6 text-left">
                    {{ $producto->nombre }}
                </td>
                <td class="py-3 px-6 text-left">
                    {{ $producto->marca->nombre ?? 'N/A' }}
                </td>
                <td class="py-3 px-6 text-left">
                    {{ $producto->categoria }}
                </td>
                <td class="py-3 px-6 text-left">
                    {{ $producto->tipo_producto }}
                </td>
                <td class="py-3 px-6 text-center">
                    {{ $producto->stock_minimo }}
                </td>
                <td class="py-3 px-6 text-center">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                {{ $producto->estado === 'ACTIVO' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $producto->estado }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="py-6 px-6 text-center text-gray-500">
                    No hay productos registrados. Haga clic en "Nuevo Producto" para agregar uno.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection