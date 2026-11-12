@extends('layouts.app')

@section('title', 'Catálogo de Productos')

@section('content')
@php
    $esAdmin = in_array(Auth::user()->nombre_rol, ['GERENTE', 'ADMINISTRADOR']);
    $hayFiltros = collect($filtros)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
    $campo = 'w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-xl text-slate-100 text-xs focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all';
@endphp

<div class="max-w-7xl mx-auto px-4 py-6 text-slate-100 space-y-6">

    <!-- Encabezado y Botón Nuevo Producto -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-white">Gestión de Productos</h2>
            <p class="text-xs text-slate-300 mt-0.5">Administra el inventario, marcas, categorías y estados de la maquinaria</p>
        </div>

        @if($esAdmin)
        <a href="{{ route('catalogo.productos.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-150 active:scale-[0.99] cursor-pointer border border-blue-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Nuevo Producto</span>
        </a>
        @endif
    </div>

    <!-- Resumen -->
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-slate-900 border border-sky-500/40 rounded-2xl px-5 py-4 shadow-xl">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Productos registrados</p>
            <p class="mt-1 text-2xl font-bold text-white font-mono">{{ $resumen['total'] }}</p>
        </div>

        <div class="bg-slate-900 border border-sky-500/40 rounded-2xl px-5 py-4 shadow-xl">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Activos</p>
            <p class="mt-1 text-2xl font-bold text-emerald-300 font-mono">{{ $resumen['activos'] }}</p>
        </div>

        <a href="{{ route('catalogo.productos.index', ['stock_bajo' => 1]) }}"
            class="col-span-2 lg:col-span-1 bg-slate-900 border {{ $resumen['stock_bajo'] ? 'border-amber-500/50' : 'border-sky-500/40' }} rounded-2xl px-5 py-4 shadow-xl hover:bg-slate-800/60 transition-colors">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Con stock bajo</p>
            <p class="mt-1 text-2xl font-bold font-mono {{ $resumen['stock_bajo'] ? 'text-amber-300' : 'text-slate-200' }}">{{ $resumen['stock_bajo'] }}</p>
        </a>
    </div>

    <!-- Búsqueda y filtros -->
    <form method="GET" action="{{ route('catalogo.productos.index') }}"
        class="bg-slate-900 border border-sky-500/40 rounded-2xl p-5 shadow-xl">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">

            <div class="sm:col-span-2 lg:col-span-2">
                <label for="q" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Buscar</label>
                <input type="search" id="q" name="q" value="{{ $filtros['q'] ?? '' }}" maxlength="100"
                    placeholder="Código, nombre, modelo o marca…" class="{{ $campo }}">
            </div>

            <div>
                <label for="marca" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Marca</label>
                <select id="marca" name="marca" class="{{ $campo }}">
                    <option value="">Todas</option>
                    @foreach($marcas as $marca)
                    <option value="{{ $marca->id_marca }}" {{ (string) ($filtros['marca'] ?? '') === (string) $marca->id_marca ? 'selected' : '' }}>{{ $marca->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="categoria" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Categoría</label>
                <select id="categoria" name="categoria" class="{{ $campo }}">
                    <option value="">Todas</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat }}" {{ ($filtros['categoria'] ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tipo" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Tipo</label>
                <select id="tipo" name="tipo" class="{{ $campo }}">
                    <option value="">Todos</option>
                    @foreach($tipos as $tipo)
                    <option value="{{ $tipo }}" {{ ($filtros['tipo'] ?? '') === $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="estado" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Estado</label>
                <select id="estado" name="estado" class="{{ $campo }}">
                    <option value="">Todos</option>
                    <option value="ACTIVO" {{ ($filtros['estado'] ?? '') === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                    <option value="INACTIVO" {{ ($filtros['estado'] ?? '') === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
        </div>

        <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

            <label class="inline-flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                <input type="checkbox" name="stock_bajo" value="1" {{ !empty($filtros['stock_bajo']) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-500 focus:ring-offset-slate-900">
                Solo productos con stock bajo
            </label>

            <div class="flex items-center gap-3">
                @if($hayFiltros)
                <a href="{{ route('catalogo.productos.index') }}"
                    class="px-4 py-2 rounded-xl border border-slate-700 bg-slate-900 text-slate-300 hover:text-white hover:border-sky-500/50 text-xs font-semibold transition">
                    Limpiar
                </a>
                @endif
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md transition-all duration-150 active:scale-[0.99] cursor-pointer border border-blue-400">
                    Aplicar filtros
                </button>
            </div>
        </div>
    </form>

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
                        <th class="py-3.5 px-5 text-center font-semibold">Stock</th>
                        <th class="py-3.5 px-5 text-center font-semibold">Stock Mín.</th>
                        <th class="py-3.5 px-5 text-center font-semibold">Estado</th>
                        <th class="py-3.5 px-5 text-center font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300 text-xs">
                    @forelse($productos as $producto)
                    <tr class="hover:bg-slate-800/40 transition-colors {{ $producto->stock_bajo ? 'bg-amber-500/[0.04]' : '' }}">
                        <!-- Imagen Principal -->
                        <td class="py-3.5 px-5 text-center">
                            @if($producto->imagenPrincipal)
                            <img src="{{ $producto->imagenPrincipal->url }}" loading="lazy" class="h-10 w-10 object-cover rounded-lg mx-auto shadow-md border border-slate-700" alt="{{ $producto->nombre }}">
                            @else
                            <div class="h-10 w-10 rounded-lg bg-slate-800 border border-slate-700/60 flex items-center justify-center mx-auto text-slate-500 text-[10px]">
                                Sin foto
                            </div>
                            @endif
                        </td>

                        <td class="py-3.5 px-5 whitespace-nowrap font-mono text-slate-400 font-medium">{{ $producto->codigo }}</td>

                        <td class="py-3.5 px-5 font-semibold text-slate-100">
                            {{ $producto->nombre }}
                            @if($producto->stock_bajo)
                            <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border bg-amber-950/60 text-amber-300 border-amber-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                Stock bajo
                            </span>
                            @endif
                        </td>

                        <td class="py-3.5 px-5 text-slate-300">{{ $producto->marca->nombre ?? 'N/A' }}</td>
                        <td class="py-3.5 px-5 text-slate-300">{{ $producto->categoria }}</td>
                        <td class="py-3.5 px-5 text-slate-300">{{ $producto->tipo_producto }}</td>

                        <td class="py-3.5 px-5 text-center font-mono font-medium {{ $producto->stock_bajo ? 'text-amber-300' : 'text-slate-200' }}">
                            @if($producto->modalidad === 'INVENTARIABLE')
                            {{ (int) $producto->stock_actual }}
                            @if((int) $producto->stock_total !== (int) $producto->stock_actual)
                            <span class="block text-[10px] font-normal text-slate-500">de {{ (int) $producto->stock_total }} en total</span>
                            @endif
                            @else
                            <span class="text-slate-500" title="Producto bajo pedido: no maneja stock">—</span>
                            @endif
                        </td>

                        <td class="py-3.5 px-5 text-center font-mono text-slate-200 font-medium">{{ $producto->stock_minimo }}</td>

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
                            @if($esAdmin)
                            <div class="flex items-center justify-center space-x-3">
                                <a href="{{ route('catalogo.productos.edit', $producto->id_producto) }}" class="text-blue-400 hover:text-blue-300 font-semibold text-xs transition-colors">Editar</a>

                                @if($producto->modalidad === 'INVENTARIABLE')
                                <a href="{{ route('catalogo.stock.show', $producto->id_producto) }}" class="text-emerald-400 hover:text-emerald-300 font-semibold text-xs transition-colors">Stock</a>
                                @endif

                                <form action="{{ route('catalogo.productos.toggle', $producto->id_producto) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-amber-400 hover:text-amber-300 font-semibold text-xs transition-colors cursor-pointer" title="Cambiar Estado">
                                        {{ $producto->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>

                                <form action="{{ route('catalogo.productos.destroy', $producto->id_producto) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este producto físicamente? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 font-semibold text-xs transition-colors cursor-pointer">Eliminar</button>
                                </form>
                            </div>
                            @elseif($producto->modalidad === 'INVENTARIABLE')
                            <a href="{{ route('catalogo.stock.show', $producto->id_producto) }}" class="text-emerald-400 hover:text-emerald-300 font-semibold text-xs transition-colors">Ver stock</a>
                            @else
                            <span class="text-slate-600">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 px-6 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                @if($hayFiltros)
                                <span class="text-xs font-medium text-slate-400">Ningún producto coincide con los filtros aplicados.</span>
                                @else
                                <span class="text-xs font-medium text-slate-400">No hay productos registrados. Haga clic en "Nuevo Producto" para agregar uno.</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $productos->links('catalogo::partials.paginacion') }}
    </div>
</div>
@endsection
