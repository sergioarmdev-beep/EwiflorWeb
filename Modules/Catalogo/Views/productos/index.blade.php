@extends('layouts.app')

@section('title', 'Catálogo de Productos')

@section('content')
@php
    $esAdmin = in_array(Auth::user()->nombre_rol, ['GERENTE', 'ADMINISTRADOR']);
    $hayFiltros = collect($filtros)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
    $campo = 'w-full px-3 py-2 rounded-xl bg-surface-2 border border-line text-ink text-xs focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20';
@endphp

<div class="max-w-7xl mx-auto px-4 py-6 text-ink space-y-6">

    <!-- Encabezado y Botón Nuevo Producto -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-ink">Gestión de Productos</h2>
            <p class="text-xs text-muted mt-0.5">Administra el inventario, marcas, categorías y estados de la maquinaria</p>
        </div>

        @if($esAdmin)
        <a href="{{ route('catalogo.productos.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-green text-white text-xs font-semibold rounded-xl shadow-sm hover:bg-brand-green-dark transition-all duration-150 active:scale-[0.99] cursor-pointer border border-brand-green/50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Nuevo Producto</span>
        </a>
        @endif
    </div>

    <!-- Resumen -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-surface border border-line rounded-2xl px-5 py-4 shadow-soft">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Productos registrados</p>
            <p class="mt-1 text-2xl font-bold text-ink font-mono">{{ $resumen['total'] }}</p>
        </div>

        <div class="bg-surface border border-line rounded-2xl px-5 py-4 shadow-soft">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Activos</p>
            <p class="mt-1 text-2xl font-bold text-brand-green font-mono">{{ $resumen['activos'] }}</p>
        </div>

        <a href="{{ route('catalogo.productos.index', ['promocion' => 1]) }}"
            class="bg-surface border {{ $resumen['con_promocion'] ? 'border-brand-lime/40' : 'border-line' }} rounded-2xl px-5 py-4 shadow-soft hover:bg-surface-2 transition-colors">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Con promoción</p>
            <p class="mt-1 text-2xl font-bold font-mono {{ $resumen['con_promocion'] ? 'text-brand-lime' : 'text-muted' }}">{{ $resumen['con_promocion'] }}</p>
        </a>

        <a href="{{ route('catalogo.productos.index', ['stock_bajo' => 1]) }}"
            class="col-span-2 lg:col-span-1 bg-surface border {{ $resumen['stock_bajo'] ? 'border-amber-500/40' : 'border-line' }} rounded-2xl px-5 py-4 shadow-soft hover:bg-surface-2 transition-colors">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Con stock bajo</p>
            <p class="mt-1 text-2xl font-bold font-mono {{ $resumen['stock_bajo'] ? 'text-amber-400' : 'text-muted' }}">{{ $resumen['stock_bajo'] }}</p>
        </a>
    </div>

    <!-- Búsqueda y filtros -->
    <form method="GET" action="{{ route('catalogo.productos.index') }}"
        class="bg-surface border border-line rounded-2xl p-5 shadow-soft">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">

            <div class="sm:col-span-2 lg:col-span-2">
                <label for="q" class="block text-[11px] font-semibold uppercase tracking-wider text-muted mb-1.5">Buscar</label>
                <input type="search" id="q" name="q" value="{{ $filtros['q'] ?? '' }}" maxlength="100"
                    placeholder="Código, nombre, modelo o marca…" class="{{ $campo }}">
            </div>

            <div>
                <label for="marca" class="block text-[11px] font-semibold uppercase tracking-wider text-muted mb-1.5">Marca</label>
                <select id="marca" name="marca" class="{{ $campo }}">
                    <option value="">Todas</option>
                    @foreach($marcas as $marca)
                    <option value="{{ $marca->id_marca }}" {{ (string) ($filtros['marca'] ?? '') === (string) $marca->id_marca ? 'selected' : '' }}>{{ $marca->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="categoria" class="block text-[11px] font-semibold uppercase tracking-wider text-muted mb-1.5">Categoría</label>
                <select id="categoria" name="categoria" class="{{ $campo }}">
                    <option value="">Todas</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat }}" {{ ($filtros['categoria'] ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tipo" class="block text-[11px] font-semibold uppercase tracking-wider text-muted mb-1.5">Tipo</label>
                <select id="tipo" name="tipo" class="{{ $campo }}">
                    <option value="">Todos</option>
                    @foreach($tipos as $tipo)
                    <option value="{{ $tipo }}" {{ ($filtros['tipo'] ?? '') === $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="estado" class="block text-[11px] font-semibold uppercase tracking-wider text-muted mb-1.5">Estado</label>
                <select id="estado" name="estado" class="{{ $campo }}">
                    <option value="">Todos</option>
                    <option value="ACTIVO" {{ ($filtros['estado'] ?? '') === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                    <option value="INACTIVO" {{ ($filtros['estado'] ?? '') === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
        </div>

        <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

            <div class="flex flex-wrap items-center gap-5">
                <label class="inline-flex items-center gap-2 text-xs text-muted cursor-pointer">
                    <input type="checkbox" name="stock_bajo" value="1" {{ !empty($filtros['stock_bajo']) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border border-line bg-surface-2 text-brand-green focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                    Solo productos con stock bajo
                </label>

                <label class="inline-flex items-center gap-2 text-xs text-muted cursor-pointer">
                    <input type="checkbox" name="promocion" value="1" {{ !empty($filtros['promocion']) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border border-line bg-surface-2 text-brand-lime focus:border-brand-lime/50 focus:ring-2 focus:ring-brand-lime/20">
                    Solo productos con promoción vigente
                </label>
            </div>

            <div class="flex items-center gap-3">
                @if($hayFiltros)
                <a href="{{ route('catalogo.productos.index') }}"
                    class="px-4 py-2 rounded-xl border border-line bg-surface text-ink hover:text-brand-green hover:border-brand-green/50 text-xs font-semibold transition">
                    Limpiar
                </a>
                @endif
                <button type="submit"
                    class="px-4 py-2 bg-brand-green text-white text-xs font-semibold rounded-xl shadow-sm hover:bg-brand-green-dark transition-all duration-150 active:scale-[0.99] cursor-pointer border border-brand-green/50">
                    Aplicar filtros
                </button>
            </div>
        </div>
    </form>

    <!-- Contenedor Principal de la Tabla -->
    <div class="bg-surface border border-line rounded-2xl shadow-soft overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line text-sm text-left">
                <thead class="bg-surface-2 text-muted text-xs uppercase tracking-wider">
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
                <tbody class="divide-y divide-line text-muted text-xs">
                    @forelse($productos as $producto)
                    <tr class="hover:bg-surface-2 transition-colors {{ $producto->stock_bajo ? 'bg-amber-500/10' : '' }}">
                        <!-- Imagen Principal -->
                        <td class="py-3.5 px-5 text-center">
                            @if($producto->imagenPrincipal)
                            <img src="{{ $producto->imagenPrincipal->url }}" loading="lazy" class="h-10 w-10 object-cover rounded-lg mx-auto shadow-sm border border-line" alt="{{ $producto->nombre }}">
                            @else
                            <div class="h-10 w-10 rounded-lg bg-surface-2 border border-line flex items-center justify-center mx-auto text-muted text-[10px]">
                                Sin foto
                            </div>
                            @endif
                        </td>

                        <td class="py-3.5 px-5 whitespace-nowrap font-mono text-muted font-medium">{{ $producto->codigo }}</td>

                        <td class="py-3.5 px-5 font-semibold text-ink">
                            {{ $producto->nombre }}
                            @if($producto->tipo_producto === 'COMBO')
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border bg-brand-blue/20 text-brand-blue border-brand-blue/40">
                                COMBO
                            </span>
                            @endif
                            @if($producto->variantes->pluck('id_variante')->intersect(collect($promocionesVigentes)->keys())->isNotEmpty())
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border bg-brand-lime/20 text-brand-lime border-brand-lime/40">
                                PROMO
                            </span>
                            @endif
                            @if($producto->stock_bajo)
                            <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border bg-amber-500/20 text-amber-500 border-amber-500/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Stock bajo
                            </span>
                            @endif
                        </td>

                        <td class="py-3.5 px-5 text-muted">{{ $producto->marca->nombre ?? 'N/A' }}</td>
                        <td class="py-3.5 px-5 text-muted">{{ $producto->categoria }}</td>
                        <td class="py-3.5 px-5 text-muted">{{ $producto->tipo_producto }}</td>

                        <td class="py-3.5 px-5 text-center font-mono font-medium {{ $producto->stock_bajo ? 'text-amber-400' : 'text-ink' }}">
                            @if($producto->modalidad === 'INVENTARIABLE')
                            {{ (int) $producto->stock_actual }}
                            @if((int) $producto->stock_total !== (int) $producto->stock_actual)
                            <span class="block text-[10px] font-normal text-muted">de {{ (int) $producto->stock_total }} en total</span>
                            @endif
                            @else
                            <span class="text-muted" title="Producto bajo pedido: no maneja stock">—</span>
                            @endif
                        </td>

                        <td class="py-3.5 px-5 text-center font-mono text-ink font-medium">{{ $producto->stock_minimo }}</td>

                        <!-- Estado -->
                        <td class="py-3.5 px-5 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border
                                {{ $producto->estado === 'ACTIVO' ? 'border border-brand-green/40 bg-brand-green/20 text-brand-green' : 'border border-red-500/40 bg-red-500/20 text-red-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $producto->estado === 'ACTIVO' ? 'bg-brand-green' : 'bg-red-500' }}"></span>
                                {{ $producto->estado }}
                            </span>
                        </td>

                        <!-- Acciones -->
                        <td class="py-3.5 px-5 text-center">
                            @if($esAdmin)
                            <div class="flex items-center justify-center space-x-3">
                                <a href="{{ route('catalogo.productos.edit', $producto->id_producto) }}" class="text-brand-blue hover:text-brand-blue-dark font-semibold text-xs transition-colors">Editar</a>

                                @if($producto->modalidad === 'INVENTARIABLE')
                                <a href="{{ route('catalogo.stock.show', $producto->id_producto) }}" class="text-brand-green hover:text-brand-green-dark font-semibold text-xs transition-colors">Stock</a>
                                @endif

                                <form action="{{ route('catalogo.productos.toggle', $producto->id_producto) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-amber-500 hover:text-amber-400 font-semibold text-xs transition-colors cursor-pointer" title="Cambiar Estado">
                                        {{ $producto->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>

                                <form action="{{ route('catalogo.productos.destroy', $producto->id_producto) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este producto físicamente? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-400 font-semibold text-xs transition-colors cursor-pointer">Eliminar</button>
                                </form>
                            </div>
                            @elseif($producto->modalidad === 'INVENTARIABLE')
                            <a href="{{ route('catalogo.stock.show', $producto->id_producto) }}" class="text-brand-green hover:text-brand-green-dark font-semibold text-xs transition-colors">Ver stock</a>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 px-6 text-center text-muted">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                @if($hayFiltros)
                                <span class="text-xs font-medium text-muted">Ningún producto coincide con los filtros aplicados.</span>
                                @else
                                <span class="text-xs font-medium text-muted">No hay productos registrados. Haga clic en "Nuevo Producto" para agregar uno.</span>
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
