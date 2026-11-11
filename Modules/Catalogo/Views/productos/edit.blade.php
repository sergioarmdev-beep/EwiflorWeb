@extends('layouts.app')

@section('title', 'Editar Producto')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8">
    <div class="mb-6 border-b pb-4">
        <h2 class="text-2xl font-semibold text-gray-800">Editar Producto</h2>
        <p class="text-sm text-gray-500 mt-1">Modifique los datos del producto y sus variantes base.</p>
    </div>

    <form action="{{ route('catalogo.productos.update', $producto->id_producto) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Código -->
            <div>
                <label for="codigo" class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
                <input type="text" name="codigo" id="codigo" value="{{ old('codigo', $producto->codigo) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm uppercase">
                @error('codigo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Nombre -->
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre del Producto *</label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $producto->nombre) }}" required
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
                    <option value="{{ $marca->id_marca }}" {{ old('id_marca', $producto->id_marca) == $marca->id_marca ? 'selected' : '' }}>
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
                    <option value="{{ $cat }}" {{ old('categoria', $producto->categoria) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
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
                    <option value="{{ $tipo }}" {{ old('tipo_producto', $producto->tipo_producto) == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                    @endforeach
                </select>
                @error('tipo_producto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Modalidad -->
            <div>
                <label for="modalidad" class="block text-sm font-medium text-gray-700 mb-1">Modalidad *</label>
                <select name="modalidad" id="modalidad" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="INVENTARIABLE" {{ old('modalidad', $producto->modalidad) == 'INVENTARIABLE' ? 'selected' : '' }}>INVENTARIABLE</option>
                    <option value="BAJO_PEDIDO" {{ old('modalidad', $producto->modalidad) == 'BAJO_PEDIDO' ? 'selected' : '' }}>BAJO PEDIDO</option>
                </select>
                @error('modalidad') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Modelo -->
            <div>
                <label for="modelo" class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                <input type="text" name="modelo" id="modelo" value="{{ old('modelo', $producto->modelo) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                @error('modelo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Stock Mínimo -->
            <div>
                <label for="stock_minimo" class="block text-sm font-medium text-gray-700 mb-1">Stock Mínimo *</label>
                <input type="number" name="stock_minimo" id="stock_minimo" value="{{ old('stock_minimo', $producto->stock_minimo) }}" min="0" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                @error('stock_minimo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Garantía -->
            <div>
                <label for="garantia_meses" class="block text-sm font-medium text-gray-700 mb-1">Garantía (Meses) *</label>
                <input type="number" name="garantia_meses" id="garantia_meses" value="{{ old('garantia_meses', $producto->garantia_meses) }}" min="0" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                @error('garantia_meses') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Público / Visible (Checkbox) -->
            <div class="flex items-center mt-6">
                <input type="checkbox" name="es_publico" id="es_publico" value="1" {{ old('es_publico', $producto->es_publico) ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="es_publico" class="ml-2 block text-sm text-gray-900 font-medium">Hacer producto visible al público</label>
            </div>
        </div>

        <!-- Sección de Imagen Principal -->
        <div class="mt-6 border-t pt-4">
            <label for="imagen" class="block text-sm font-medium text-gray-700 mb-2">Imagen Principal del Producto</label>

            @if($imagenPrincipal)
            <div class="mb-3 flex items-center space-x-3">
                <img src="{{ asset('storage/' . $imagenPrincipal->ruta_imagen) }}" class="h-20 w-20 object-cover rounded border shadow-sm">
                <div>
                    <p class="text-xs text-gray-500 font-medium">Imagen actual en catálogo.</p>
                    <p class="text-xs text-gray-400">Sube un nuevo archivo solo si deseas reemplazarla.</p>
                </div>
            </div>
            @endif

            <input type="file" name="imagen" id="imagen" accept="image/*"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="text-xs text-gray-500 mt-1">Formatos permitidos: JPG, PNG, WEBP. Máx: 2MB.</p>
            @error('imagen') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Descripción (Ancho completo) -->
        <div class="mt-6">
            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <textarea name="descripcion" id="descripcion" rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('descripcion', $producto->descripcion) }}</textarea>
            @error('descripcion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Botones de Acción -->
        <div class="mt-8 border-t pt-4 flex justify-end space-x-3">
            <a href="{{ route('catalogo.productos.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection