@extends('layouts.app')

@section('title', 'Promociones')

@php
    $campo = 'w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-xl text-slate-100 text-xs focus:outline-none focus:border-fuchsia-400 focus:ring-2 focus:ring-fuchsia-500/30 transition-all';
    $hayFiltros = collect($filtros)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-slate-100 space-y-6" style="--promo-acento: #e879f9; --promo-suave: rgba(232, 121, 249, 0.12);">

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-white">Promociones de Variantes</h2>
            <p class="text-xs text-slate-300 mt-0.5">Descuentos vigentes por variante; se precargan en ventas y cotizaciones</p>
        </div>

        <a href="{{ route('promociones.combos.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-600 transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 8.5 12 4l7.5 4.5M4.5 8.5v7L12 20l7.5-4.5m-15-7 7.5 4.5 7.5-4.5M12 12v8" />
            </svg>
            <span>Ir a Combos</span>
        </a>
    </div>

    <!-- Resumen -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('promociones.index') }}"
            class="bg-slate-900 border border-fuchsia-500/40 rounded-2xl px-5 py-4 shadow-xl hover:bg-slate-800/60 transition-colors">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Promociones activas</p>
            <p class="mt-1 text-2xl font-bold text-fuchsia-300 font-mono">{{ $resumen['activas'] }}</p>
        </a>

        <a href="{{ route('promociones.index', ['vigentes' => 1]) }}"
            class="bg-slate-900 border border-emerald-500/40 rounded-2xl px-5 py-4 shadow-xl hover:bg-slate-800/60 transition-colors">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Vigentes hoy</p>
            <p class="mt-1 text-2xl font-bold text-emerald-300 font-mono">{{ $resumen['vigentes'] }}</p>
        </a>

        <div class="bg-slate-900 border border-sky-500/40 rounded-2xl px-5 py-4 shadow-xl">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Variantes con descuento</p>
            <p class="mt-1 text-2xl font-bold text-sky-300 font-mono">{{ $resumen['variantes'] }}</p>
        </div>
    </div>

    <!-- Filtros -->
    <form method="GET" action="{{ route('promociones.index') }}"
        class="bg-slate-900 border border-fuchsia-500/40 rounded-2xl p-5 shadow-xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div class="sm:col-span-2">
                <label for="q" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Buscar</label>
                <input type="search" id="q" name="q" value="{{ $filtros['q'] ?? '' }}" maxlength="120"
                    placeholder="Nombre o descripción de la promoción…" class="{{ $campo }}">
            </div>

            <div>
                <label for="estado" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Estado</label>
                <select id="estado" name="estado" class="{{ $campo }}">
                    <option value="">Todos</option>
                    <option value="ACTIVA" {{ ($filtros['estado'] ?? '') === 'ACTIVA' ? 'selected' : '' }}>Activa</option>
                    <option value="INACTIVA" {{ ($filtros['estado'] ?? '') === 'INACTIVA' ? 'selected' : '' }}>Inactiva</option>
                </select>
            </div>

            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                    <input type="checkbox" name="vigentes" value="1" {{ !empty($filtros['vigentes']) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-fuchsia-500 focus:ring-fuchsia-500 focus:ring-offset-slate-900">
                    Solo vigentes hoy
                </label>
            </div>
        </div>

        <div class="mt-4 flex justify-end gap-3">
            @if($hayFiltros)
            <a href="{{ route('promociones.index') }}"
                class="px-4 py-2 rounded-xl border border-slate-700 bg-slate-950 text-slate-300 hover:text-white hover:border-fuchsia-500/50 text-xs font-semibold transition">
                Limpiar
            </a>
            @endif
            <button type="submit"
                class="px-4 py-2 bg-fuchsia-600 hover:bg-fuchsia-500 text-white text-xs font-semibold rounded-xl shadow-md transition-all duration-150 active:scale-[0.99] cursor-pointer border border-fuchsia-400">
                Aplicar filtros
            </button>
        </div>
    </form>

    <!-- Tabla -->
    <div class="bg-slate-900 border border-fuchsia-500/40 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-sm text-left">
                <thead class="bg-slate-950/50 text-slate-400 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-5 font-semibold">Promoción</th>
                        <th class="py-3.5 px-5 font-semibold">Descuento</th>
                        <th class="py-3.5 px-5 font-semibold">Variantes</th>
                        <th class="py-3.5 px-5 font-semibold">Vigencia</th>
                        <th class="py-3.5 px-5 text-center font-semibold">Estado</th>
                        <th class="py-3.5 px-5 text-center font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300 text-xs">
                    @forelse($promociones as $promo)
                    @php
                        $vigente = $promo->esta_vigente;
                    @endphp
                    <tr class="hover:bg-slate-800/40 transition-colors">
                        <td class="py-3.5 px-5">
                            <span class="block font-semibold text-slate-100">{{ $promo->nombre }}</span>
                            @if($promo->descripcion)
                            <span class="block text-[11px] text-slate-500 max-w-xs truncate" title="{{ $promo->descripcion }}">{{ $promo->descripcion }}</span>
                            @endif
                            <span class="block text-[11px] text-slate-600 mt-0.5">Creada por {{ $promo->usuario?->nombre_usuario ?? '—' }}</span>
                        </td>

                        <td class="py-3.5 px-5">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border
                                {{ $promo->tipo_descuento === 'PCT'
                                    ? 'bg-fuchsia-950/60 text-fuchsia-300 border-fuchsia-500/30'
                                    : 'bg-sky-950/60 text-sky-300 border-sky-500/30' }}">
                                {{ $promo->tipo_descuento === 'PCT' ? '−'.rtrim(rtrim(number_format((float) $promo->valor_descuento, 2, '.', ''), '0'), '.').' %' : 'USD −'.number_format((float) $promo->valor_descuento, 2) }}
                            </span>
                            <span class="block text-[10px] text-slate-500 mt-1">por unidad</span>
                        </td>

                        <td class="py-3.5 px-5 font-mono text-slate-200">{{ $promo->variantes_count }} variante{{ $promo->variantes_count === 1 ? '' : 's' }}</td>

                        <td class="py-3.5 px-5 whitespace-nowrap">
                            <span class="block text-slate-200 font-mono">{{ $promo->fecha_inicio?->format('d/m/Y') }}</span>
                            <span class="block text-[11px] text-slate-500">hasta {{ $promo->fecha_fin?->format('d/m/Y') ?? '— (abierta)' }}</span>
                        </td>

                        <td class="py-3.5 px-5 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border
                                {{ $promo->estado === 'ACTIVA'
                                    ? 'bg-emerald-950/60 text-emerald-300 border-emerald-500/30'
                                    : 'bg-slate-800 text-slate-400 border-slate-600' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $promo->estado === 'ACTIVA' ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                                {{ $promo->estado }}
                            </span>
                            @if($promo->estado === 'ACTIVA')
                            <span class="block text-[10px] mt-1 {{ $vigente ? 'text-emerald-400' : 'text-amber-400' }}">
                                {{ $vigente ? 'vigente hoy' : 'fuera de fechas' }}
                            </span>
                            @endif
                        </td>

                        <td class="py-3.5 px-5 text-center">
                            <div class="flex items-center justify-center space-x-3">
                                <a href="{{ route('promociones.edit', $promo->id_promocion) }}" class="text-sky-400 hover:text-sky-300 font-semibold text-xs transition-colors">Editar</a>

                                <form action="{{ route('promociones.toggle', $promo->id_promocion) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-amber-400 hover:text-amber-300 font-semibold text-xs transition-colors cursor-pointer">
                                        {{ $promo->estado === 'ACTIVA' ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>

                                <form action="{{ route('promociones.destroy', $promo->id_promocion) }}" method="POST" class="inline"
                                    onsubmit="return confirm('¿Eliminar la promoción «{{ $promo->nombre }}»? Sus variantes dejarán de tener descuento.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 font-semibold text-xs transition-colors cursor-pointer">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 px-6 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-3-2.8.9.9a3.5 3.5 0 0 0 2.1.7 2.6 2.6 0 0 0 2.6-2.6 2.4 2.4 0 0 0-1.2-2.2l-2.9-1.4a2.4 2.4 0 0 1-1.2-2.2A2.6 2.6 0 0 1 11.9 6a3.5 3.5 0 0 1 2.1.7l.9.9m-3.9-3.6v1.9m3 5.9v1.9" />
                                </svg>
                                <span class="text-xs font-medium text-slate-400">
                                    @if($hayFiltros)
                                    Ninguna promoción coincide con los filtros aplicados.
                                    @else
                                    No hay promociones creadas. Usa «Nueva Promoción» para crear la primera.
                                    @endif
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('promociones.create') }}"
            class="fixed bottom-6 right-6 inline-flex items-center gap-2 px-5 py-3 bg-fuchsia-600 hover:bg-fuchsia-500 text-white text-sm font-bold rounded-2xl shadow-2xl shadow-fuchsia-900/40 transition-all active:scale-[0.98] cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva Promoción
        </a>

        {{ $promociones->links('catalogo::partials.paginacion') }}
    </div>
</div>
@endsection
