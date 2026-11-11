@extends('layouts.app')

@section('title', 'Catálogo de Productos')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-semibold text-gray-800">Productos</h2>
    @if(in_array(Auth::user()->nombre_rol, ['GERENTE', 'ADMINISTRADOR']))
    <a href="{{ route('catalogo.productos.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition duration-150 ease-in-out">
        + Nuevo Producto
    </a>
    @endif
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full leading-normal">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-center">Imagen</th>
                <th class="py-3 px-6 text-left">Código</th>
                <th class="py-3 px-6 text-left">Nombre</th>
                <th class="py-3 px-6 text-left">Marca</th>
                <th class="py-3 px-6 text-left">Categoría</th>
                <th class="py-3 px-6 text-left">Tipo</th>
                <th class="py-3 px-6 text-center">Stock Mín.</th>
                <th class="py-3 px-6 text-center">Estado</th>
                <th class="py-3 px-6 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
            @forelse($productos as $producto)
            <tr class="border-b border-gray-200 hover:bg-gray-50">
                <!-- Imagen Principal -->
                <td class="py-3 px-6 text-center">
                    @if($producto->imagenPrincipal)
                    <img src="{{ asset('storage/' . $producto->imagenPrincipal->ruta_imagen) }}" class="h-10 w-10 object-cover rounded mx-auto shadow-sm">
                    @else
                    <span class="text-gray-400 text-xs">Sin foto</span>
                    @endif
                </td>

                <!-- Código -->
                <td class="py-3 px-6 text-left whitespace-nowrap">
                    <span class="font-medium">{{ $producto->codigo }}</span>
                </td>

                <!-- Nombre -->
                <td class="py-3 px-6 text-left">
                    {{ $producto->nombre }}
                </td>

                <!-- Marca -->
                <td class="py-3 px-6 text-left">
                    {{ $producto->marca->nombre ?? 'N/A' }}
                </td>

                <!-- Categoría -->
                <td class="py-3 px-6 text-left">
                    {{ $producto->categoria }}
                </td>

                <!-- Tipo -->
                <td class="py-3 px-6 text-left">
                    {{ $producto->tipo_producto }}
                </td>

                <!-- Stock Mínimo -->
                <td class="py-3 px-6 text-center">
                    {{ $producto->stock_minimo }}
                </td>

                <!-- Estado -->
                <td class="py-3 px-6 text-center">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                {{ $producto->estado === 'ACTIVO' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $producto->estado }}
                    </span>
                </td>

                <!-- Acciones -->
                <td class="py-3 px-6 text-center">
                    <div class="flex item-center justify-center space-x-2">
                        <!-- Botón Editar -->
                        <a href="{{ route('catalogo.productos.edit', $producto->id_producto) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs transition duration-150">Editar</a>

                        <!-- Botón Activar/Desactivar -->
                        <form action="{{ route('catalogo.productos.toggle', $producto->id_producto) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-yellow-600 hover:text-yellow-900 font-semibold text-xs transition duration-150" title="Cambiar Estado">
                                {{ $producto->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>

                        <!-- Botón Eliminar Físico -->
                        <form action="{{ route('catalogo.productos.destroy', $producto->id_producto) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este producto físicamente? Esta acción no se puede deshacer.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold text-xs transition duration-150">Eliminar</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="py-6 px-6 text-center text-gray-500">
                    No hay productos registrados. Haga clic en "Nuevo Producto" para agregar uno.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection