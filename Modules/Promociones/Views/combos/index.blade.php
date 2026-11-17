@extends('layouts.app')

@section('title', 'Combos')

@php
    $campo = 'w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-xl text-slate-100 text-xs focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all';
    $hayFiltros = collect($filtros)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-slate-100 space-y-6" style="--combo-acento: #38bdf8; --combo-suave: rgba(56, 189, 248, 0.12);">

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-white">Combos</h2>
            <p class="text-xs text-slate-300 mt-0.5">Se venden como productos normales con precio y stock propios</p>
        </div>

        <a href="{{ route('promociones.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-600 transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.8.9.9a3.5 3.5 0 0 0 2.1.7 2.6 2.6 0 0 0 2.6-2.6 2.4 2.4 0 0 0-1.2-2.2l-2.9-1.4a2.4 2.4 0 0 1-1.2-2.2A2.6 2.6 0 0 1 11.9 6a3.5 3.5 0 0 1 2.1.7l.9.9" />
            </svg>
            <span>Ir a Promociones</span>
        </a>
    </div>

    <!-- Resumen -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-slate-900 border border-sky-500/40 rounded-2xl px-5 py-4 shadow-xl">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Combos registrados</p>
            <p class="mt-1 text-2xl font-bold text-white font-mono">{{ $resumen['total'] }}</p>
        </div>

        <div class="bg-slate-900 border border-emerald-500/40 rounded-2xl px-5 py-4 shadow-xl">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Activos</p>
            <p class="mt-1 text-2xl font-bold text-emerald-300 font-mono">{{ $resumen['activos'] }}</p>
        </div>

        <div class="bg-slate-900 border border-fuchsia-500/40 rounded-2xl px-5 py-4 shadow-xl">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Visibles en catálogo público</p>
            <p class="mt-1 text-2xl font-bold text-fuchsia-300 font-mono">{{ $resumen['publicos'] }}</p>
        </div>
    </div>

    <!-- Filtros -->
    <form method="GET" action="{{ route('promociones.combos.index') }}"
        class="bg-slate-900 border border-sky-500/40 rounded-2xl p-5 shadow-xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 items-end">
            <div class="sm:col-span-2">
                <label for="q" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Buscar</label>
                <input type="search" id="q" name="q" value="{{ $filtros['q'] ?? '' }}" maxlength="120"
                    placeholder="Código o nombre del combo…" class="{{ $campo }}">
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

        <div class="mt-4 flex justify-end gap-3">
            @if($hayFiltros)
            <a href="{{ route('promociones.combos.index') }}"
                class="px-4 py-2 rounded-xl border border-slate-700 bg-slate-950 text-slate-300 hover:text-white hover:border-sky-500/50 text-xs font-semibold transition">
                Limpiar
            </a>
            @endif
            <button type="submit"
                class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold rounded-xl shadow-md transition-all duration-150 active:scale-[0.99] cursor-pointer border border-sky-400">
                Aplicar filtros
            </button>
        </div>
    </form>

    <!-- Tabla -->
    <div class="bg-slate-900 border border-sky-500/40 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-sm text-left">
                <thead class="bg-slate-950/50 text-slate-400 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-5 text-center font-semibold">Imagen</th>
                        <th class="py-3.5 px-5 font-semibold">Código</th>
                        <th class="py-3.5 px-5 font-semibold">Nombre</th>
                        <th class="py-3.5 px-5 font-semibold">Incluye</th>
                        <th class="py-3.5 px-5 text-center font-semibold">Precio</th>
                        <th class="py-3.5 px-5 text-center font-semibold">Stock</th>
                        <th class="py-3.5 px-5 text-center font-semibold">Estado</th>
                        <th class="py-3.5 px-5 text-center font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300 text-xs">
                    @forelse($combos as $combo)
                    @php
                        $variante = $combo->variantes->first();
                    @endphp
                    <tr class="hover:bg-slate-800/40 transition-colors">
                        <td class="py-3.5 px-5 text-center">
                            @if($combo->imagenPrincipal)
                            <img src="{{ $combo->imagenPrincipal->url }}" loading="lazy" class="h-10 w-10 object-cover rounded-lg mx-auto shadow-md border border-slate-700" alt="{{ $combo->nombre }}">
                            @else
                            <div class="h-10 w-10 rounded-lg bg-slate-800 border border-slate-700/60 flex items-center justify-center mx-auto text-slate-500 text-[10px]">
                                Sin foto
                            </div>
                            @endif
                        </td>

                        <td class="py-3.5 px-5 whitespace-nowrap font-mono text-slate-400 font-medium">{{ $combo->codigo }}</td>

                        <td class="py-3.5 px-5">
                            <span class="font-semibold text-slate-100">{{ $combo->nombre }}</span>
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border bg-fuchsia-950/60 text-fuchsia-300 border-fuchsia-500/30">
                                COMBO
                            </span>
                            @if($combo->es_publico)
                            <span class="block text-[10px] text-emerald-400 mt-0.5">visible en el catálogo público</span>
                            @else
                            <span class="block text-[10px] text-slate-500 mt-0.5">no visible en el catálogo público</span>
                            @endif
                        </td>

                        <td class="py-3.5 px-5">
                            <span class="block text-[11px] text-slate-300 max-w-xs">
                                {{ $combo->componentesCombo->map(fn ($c) => ($c->componente?->nombre ?? 'Producto #'.$c->id_componente).' ×'.$c->cantidad)->implode(', ') ?: '—' }}
                            </span>
                        </td>

                        <td class="py-3.5 px-5 text-center font-mono font-semibold text-emerald-300">
                            {{ $variante?->precio_usd !== null ? 'USD '.number_format((float) $variante->precio_usd, 2) : '—' }}
                        </td>

                        <td class="py-3.5 px-5 text-center font-mono {{ (int) $combo->stock_actual <= $combo->stock_minimo ? 'text-amber-300' : 'text-slate-200' }}">
                            @if($combo->modalidad === 'INVENTARIABLE')
                            {{ (int) $combo->stock_actual }}
                            @if((int) $combo->stock_total !== (int) $combo->stock_actual)
                            <span class="block text-[10px] font-normal text-slate-500">de {{ (int) $combo->stock_total }}</span>
                            @endif
                            @else
                            <span class="text-slate-500" title="Bajo pedido">—</span>
                            @endif
                        </td>

                        <td class="py-3.5 px-5 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border
                                {{ $combo->estado === 'ACTIVO' ? 'bg-emerald-950/60 text-emerald-300 border-emerald-500/30' : 'bg-red-950/60 text-red-300 border-red-500/30' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $combo->estado === 'ACTIVO' ? 'bg-emerald-400' : 'bg-red-400' }}"></span>
                                {{ $combo->estado }}
                            </span>
                        </td>

                        <td class="py-3.5 px-5 text-center">
                            <div class="flex items-center justify-center space-x-3">
                                <a href="{{ route('promociones.combos.edit', $combo->id_producto) }}" class="text-sky-400 hover:text-sky-300 font-semibold text-xs transition-colors">Editar</a>

                                <form action="{{ route('promociones.combos.toggle', $combo->id_producto) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-amber-400 hover:text-amber-300 font-semibold text-xs transition-colors cursor-pointer">
                                        {{ $combo->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 px-6 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 8.5 12 4l7.5 4.5M4.5 8.5v7L12 20l7.5-4.5m-15-7 7.5 4.5 7.5-4.5M12 12v8" />
                                </svg>
                                <span class="text-xs font-medium text-slate-400">
                                    @if($hayFiltros)
                                    Ningún combo coincide con los filtros aplicados.
                                    @else
                                    No hay combos creados. Usa «Nuevo Combo» para crear el primero.
                                    @endif
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('promociones.combos.create') }}"
            class="fixed bottom-6 right-6 inline-flex items-center gap-2 px-5 py-3 bg-sky-600 hover:bg-sky-500 text-white text-sm font-bold rounded-2xl shadow-2xl shadow-sky-900/40 transition-all active:scale-[0.98] cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo Combo
        </a>

        {{ $combos->links('catalogo::partials.paginacion') }}
    </div>
</div>
@endsection
