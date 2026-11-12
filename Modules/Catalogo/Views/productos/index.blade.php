@extends('layouts.app')

@section('title', 'Catálogo de Productos')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-slate-100 space-y-6">

    <!-- Encabezado y Botón Nuevo Producto -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-white">Gestión de Productos</h2>
            <p class="text-xs text-slate-300 mt-0.5">Administra el inventario, marcas, categorías y estados de la maquinaria</p>
        </div>

        @if(in_array(Auth::user()->nombre_rol, ['GERENTE', 'ADMINISTRADOR']))
        <a href="{{ route('catalogo.productos.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-150 active:scale-[0.99] cursor-pointer border border-blue-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Nuevo Producto</span>
        </a>
        @endif
    </div>

    <!-- Contenedor Principal de la Tabla -->
    <div class="bg-slate-900 border border-sky-500/40 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-sm text-left">
                <thead class="bg-slate-950/50 text-slate-400 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-5 text-center font-semibold">Imagen</th>
                        <th class="py-3.5 px-5 font-semibold">Código</th>
                        <th class="py-3.5 px-5 font-semibold">Nombre</th>
                        <th class="py-3.5 px-5 font-semibold">Marca</th>
                        <th class="py-3.5 px-5 font-semibold">Categoría</th>
                        <th class="py-3.5 px-5 font-semibold">Tipo</th>
                        <th class="py-3.5 px-5 text-center font-semibold">Stock Mín.</th>
                        <th class="py-3.5 px-5 text-center font-semibold">Estado</th>
                        <th class="py-3.5 px-5 text-center font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300 text-xs">
                    @forelse($productos as $producto)
                    <tr class="hover:bg-slate-800/40 transition-colors">
                        <!-- Imagen Principal -->
                        <td class="py-3.5 px-5 text-center">
                            @if($producto->imagenPrincipal)
                            <img src="{{ asset('storage/' . $producto->imagenPrincipal->ruta_imagen) }}" class="h-10 w-10 object-cover rounded-lg mx-auto shadow-md border border-slate-700" alt="{{ $producto->nombre }}">
                            @else
                            <div class="h-10 w-10 rounded-lg bg-slate-800 border border-slate-700/60 flex items-center justify-center mx-auto text-slate-500 text-[10px]">
                                Sin foto
                            </div>
                            @endif
                        </td>

                        <!-- Código -->
                        <td class="py-3.5 px-5 whitespace-nowrap font-mono text-slate-400 font-medium">
                            {{ $producto->codigo }}
                        </td>

                        <!-- Nombre -->
                        <td class="py-3.5 px-5 font-semibold text-slate-100">
                            {{ $producto->nombre }}
                        </td>

                        <!-- Marca -->
                        <td class="py-3.5 px-5 text-slate-300">
                            {{ $producto->marca->nombre ?? 'N/A' }}
                        </td>

                        <!-- Categoría -->
                        <td class="py-3.5 px-5 text-slate-300">
                            {{ $producto->categoria }}
                        </td>

                        <!-- Tipo -->
                        <td class="py-3.5 px-5 text-slate-300">
                            {{ $producto->tipo_producto }}
                        </td>

                        <!-- Stock Mínimo -->
                        <td class="py-3.5 px-5 text-center font-mono text-slate-200 font-medium">
                            {{ $producto->stock_minimo }}
                        </td>

                        <!-- Estado -->
                        <td class="py-3.5 px-5 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border
                                {{ $producto->estado === 'ACTIVO' ? 'bg-emerald-950/60 text-emerald-300 border-emerald-500/30' : 'bg-red-950/60 text-red-300 border-red-500/30' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $producto->estado === 'ACTIVO' ? 'bg-emerald-400' : 'bg-red-400' }}"></span>
                                {{ $producto->estado }}
                            </span>
                        </td>

                        <!-- Acciones -->
                        <td class="py-3.5 px-5 text-center">
                            <div class="flex items-center justify-center space-x-3">
                                <!-- Botón Editar -->
                                <a href="{{ route('catalogo.productos.edit', $producto->id_producto) }}" class="text-blue-400 hover:text-blue-300 font-semibold text-xs transition-colors">Editar</a>

                                <!-- Botón Activar/Desactivar -->
                                <form action="{{ route('catalogo.productos.toggle', $producto->id_producto) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-amber-400 hover:text-amber-300 font-semibold text-xs transition-colors cursor-pointer" title="Cambiar Estado">
                                        {{ $producto->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>

                                <!-- Botón Eliminar Físico -->
                                <form action="{{ route('catalogo.productos.destroy', $producto->id_producto) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este producto físicamente? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 font-semibold text-xs transition-colors cursor-pointer">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 px-6 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <span class="text-xs font-medium text-slate-400">No hay productos registrados. Haga clic en "Nuevo Producto" para agregar uno.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection