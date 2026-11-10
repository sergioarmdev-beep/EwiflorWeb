@extends('layouts.app')

@section('title', 'Registrar Nuevo Producto')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8">
    <div class="mb-6 border-b pb-4">
        <h2 class="text-2xl font-semibold text-gray-800">Registrar Nuevo Producto</h2>
        <p class="text-sm text-gray-500 mt-1">Complete los datos del producto y sus variantes base.</p>
    </div>

    <form action="{{ route('catalogo.productos.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Código -->
            <div>
                <label for="codigo" class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
                <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm uppercase">
                @error('codigo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Nombre -->
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre del Producto *</label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Marca -->
            <div>
                <label for="id_marca" class="block text-sm font-medium text-gray-700 mb-1">Marca *</label>
                <select name="id_marca" id="id_marca" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="">Seleccione una marca</option>
                    @foreach($marcas as $marca)
                    <option value="{{ $marca->id_marca }}" {{ old('id_marca') == $marca->id_marca ? 'selected' : '' }}>
                        {{ $marca->nombre }}
                    </option>
                    @endforeach
                </select>
                @error('id_marca') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Categoría -->
            <div>
                <label for="categoria" class="block text-sm font-medium text-gray-700 mb-1">Categoría *</label>
                <select name="categoria" id="categoria" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="">Seleccione categoría</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat }}" {{ old('categoria') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                @error('categoria') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Tipo de Producto -->
            <div>
                <label for="tipo_producto" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Producto *</label>
                <select name="tipo_producto" id="tipo_producto" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="">Seleccione tipo</option>
                    @foreach($tipos as $tipo)
                    <option value="{{ $tipo }}" {{ old('tipo_producto') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                    @endforeach
                </select>
                @error('tipo_producto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Modalidad -->
            <div>
                <label for="modalidad" class="block text-sm font-medium text-gray-700 mb-1">Modalidad *</label>
                <select name="modalidad" id="modalidad" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="INVENTARIABLE" {{ old('modalidad') == 'INVENTARIABLE' ? 'selected' : '' }}>INVENTARIABLE</option>
                    <option value="BAJO_PEDIDO" {{ old('modalidad') == 'BAJO_PEDIDO' ? 'selected' : '' }}>BAJO PEDIDO</option>
                </select>
                @error('modalidad') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Modelo -->
            <div>
                <label for="modelo" class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                <input type="text" name="modelo" id="modelo" value="{{ old('modelo') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>

            <!-- Stock Mínimo -->
            <div>
                <label for="stock_minimo" class="block text-sm font-medium text-gray-700 mb-1">Stock Mínimo *</label>
                <input type="number" name="stock_minimo" id="stock_minimo" value="{{ old('stock_minimo', 0) }}" min="0" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                @error('stock_minimo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Garantía -->
            <div>
                <label for="garantia_meses" class="block text-sm font-medium text-gray-700 mb-1">Garantía (Meses) *</label>
                <input type="number" name="garantia_meses" id="garantia_meses" value="{{ old('garantia_meses', 0) }}" min="0" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                @error('garantia_meses') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Descripción (Ancho completo) -->
        <div class="mt-6">
            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <textarea name="descripcion" id="descripcion" rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('descripcion') }}</textarea>
        </div>

        <!-- Es Público -->
        <div class="mt-6 flex items-center">
            <input type="checkbox" name="es_publico" id="es_publico" value="1" {{ old('es_publico', true) ? 'checked' : '' }}
                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <label for="es_publico" class="ml-2 block text-sm text-gray-900">
                Mostrar en el catálogo público (Web)
            </label>
        </div>

        <!-- Botones -->
        <div class="mt-8 flex justify-end space-x-4">
            <a href="{{ route('catalogo.productos.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded transition duration-150 ease-in-out">
                Cancelar
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition duration-150 ease-in-out">
                Guardar Producto
            </button>
        </div>
    </form>
</div>
@endsection