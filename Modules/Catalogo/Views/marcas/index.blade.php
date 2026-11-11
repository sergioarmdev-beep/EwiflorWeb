@extends('layouts.app')

@section('title', 'Gestión de Marcas')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Formulario de Creación -->
    <div class="bg-white p-6 rounded-lg shadow-md h-fit">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Nueva Marca</h3>
        <form action="{{ route('catalogo.marcas.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Marca *</label>
                <input type="text" name="nombre" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">País de Origen</label>
                <input type="text" name="pais_origen" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">Guardar Marca</button>
        </form>
    </div>

    <!-- Tabla de Marcas -->
    <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Marcas Registradas</h3>
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Nombre</th>
                    <th class="py-3 px-6 text-left">País</th>
                    <th class="py-3 px-6 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                @foreach($marcas as $marca)
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <form action="{{ route('catalogo.marcas.update', $marca->id_marca) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <td class="py-2 px-6"><input type="text" name="nombre" value="{{ $marca->nombre }}" class="w-full border-gray-300 rounded text-sm"></td>
                        <td class="py-2 px-6"><input type="text" name="pais_origen" value="{{ $marca->pais_origen }}" class="w-full border-gray-300 rounded text-sm"></td>
                        <td class="py-2 px-6 text-center">
                            <div class="flex items-center space-x-2 justify-center">
                                <button type="submit" class="text-blue-600 hover:text-blue-900 font-semibold text-xs">Actualizar</button>
                            </div>
                        </td>
                    </form>
                    <!-- El botón de eliminar va FUERA del formulario de edición para no anidar formularios -->
                    <td class="py-2 px-2 text-center border-b border-gray-200">
                        <form action="{{ route('catalogo.marcas.destroy', $marca->id_marca) }}" method="POST" onsubmit="return confirm('¿Eliminar esta marca?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold text-xs">Eliminar</button>
                        </form>
                    </td>
                    </form>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection