@extends('layouts.app')

@section('title', 'Promociones')

@php
    $campo = 'w-full px-3 py-2 rounded-xl bg-surface-2 border border-line text-ink text-xs focus:border-brand-green/50 focus:ring-brand-green/20';
    $hayFiltros = collect($filtros)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-ink space-y-6">

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-ink">Promociones de Variantes</h2>
            <p class="text-xs text-muted mt-0.5">Descuentos vigentes por variante; se precargan en ventas y cotizaciones</p>
        </div>

        <a href="{{ route('promociones.combos.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-surface hover:bg-surface-2 text-ink text-xs font-semibold rounded-xl border border-line transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 8.5 12 4l7.5 4.5M4.5 8.5v7L12 20l7.5-4.5m-15-7 7.5 4.5 7.5-4.5M12 12v8" />
            </svg>
            <span>Ir a Combos</span>
        </a>
    </div>

    <!-- Resumen -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('promociones.index') }}"
            class="bg-surface border border-line rounded-2xl px-5 py-4 shadow-soft hover:bg-surface-2 transition-colors">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Promociones activas</p>
            <p class="mt-1 text-2xl font-bold text-brand-blue font-mono">{{ $resumen['activas'] }}</p>
        </a>

        <a href="{{ route('promociones.index', ['vigentes' => 1]) }}"
            class="bg-surface border border-line rounded-2xl px-5 py-4 shadow-soft hover:bg-surface-2 transition-colors">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Vigentes hoy</p>
            <p class="mt-1 text-2xl font-bold text-brand-green font-mono">{{ $resumen['vigentes'] }}</p>
        </a>

        <div class="bg-surface border border-line rounded-2xl px-5 py-4 shadow-soft">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Variantes con descuento</p>
            <p class="mt-1 text-2xl font-bold text-brand-blue font-mono">{{ $resumen['variantes'] }}</p>
        </div>
    </div>

    <!-- Filtros -->
    <form method="GET" action="{{ route('promociones.index') }}"
        class="bg-surface border border-line rounded-2xl p-5 shadow-soft">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div class="sm:col-span-2">
                <label for="q" class="block text-[11px] font-semibold uppercase tracking-wider text-muted mb-1.5">Buscar</label>
                <input type="search" id="q" name="q" value="{{ $filtros['q'] ?? '' }}" maxlength="120"
                    placeholder="Nombre o descripción de la promoción…" class="{{ $campo }}">
            </div>

            <div>
                <label for="estado" class="block text-[11px] font-semibold uppercase tracking-wider text-muted mb-1.5">Estado</label>
                <select id="estado" name="estado" class="{{ $campo }}">
                    <option value="">Todos</option>
                    <option value="ACTIVA" {{ ($filtros['estado'] ?? '') === 'ACTIVA' ? 'selected' : '' }}>Activa</option>
                    <option value="INACTIVA" {{ ($filtros['estado'] ?? '') === 'INACTIVA' ? 'selected' : '' }}>Inactiva</option>
                </select>
            </div>

            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-2 text-xs text-muted cursor-pointer">
                    <input type="checkbox" name="vigentes" value="1" {{ !empty($filtros['vigentes']) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border border-line bg-surface-2 text-brand-green focus:border-brand-green/50 focus:ring-brand-green/20">
                    Solo vigentes hoy
                </label>
            </div>
        </div>

        <div class="mt-4 flex justify-end gap-3">
            @if($hayFiltros)
            <a href="{{ route('promociones.index') }}"
                class="px-4 py-2 rounded-xl border border-line bg-surface text-ink hover:bg-surface-2 hover:text-brand-green text-xs font-semibold transition">
                Limpiar
            </a>
            @endif
            <button type="submit"
                class="px-4 py-2 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-sm transition-all duration-150 active:scale-[0.99] cursor-pointer border border-brand-green/50">
                Aplicar filtros
            </button>
        </div>
    </form>

    <!-- Tabla -->
    <div class="bg-surface border border-line rounded-2xl shadow-soft overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line text-sm text-left">
                <thead class="bg-surface-2 text-muted text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-5 font-semibold">Promoción</th>
                        <th class="py-3.5 px-5 font-semibold">Descuento</th>
                        <th class="py-3.5 px-5 font-semibold">Variantes</th>
                        <th class="py-3.5 px-5 font-semibold">Vigencia</th>
                        <th class="py-3.5 px-5 text-center font-semibold">Estado</th>
                        <th class="py-3.5 px-5 text-center font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-muted text-xs">
                    @forelse($promociones as $promo)
                    @php
                        $vigente = $promo->esta_vigente;
                    @endphp
                    <tr class="hover:bg-surface-2 transition-colors">
                        <td class="py-3.5 px-5">
                            <span class="block font-semibold text-ink">{{ $promo->nombre }}</span>
                            @if($promo->descripcion)
                            <span class="block text-[11px] text-muted max-w-xs truncate" title="{{ $promo->descripcion }}">{{ $promo->descripcion }}</span>
                            @endif
                            <span class="block text-[11px] text-muted mt-0.5">Creada por {{ $promo->usuario?->nombre_usuario ?? '—' }}</span>
                        </td>

                        <td class="py-3.5 px-5">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border
                                {{ $promo->tipo_descuento === 'PCT'
                                    ? 'border border-brand-lime/40 bg-brand-lime/20 text-brand-lime'
                                    : 'border border-brand-blue/40 bg-brand-blue/20 text-brand-blue' }}">
                                {{ $promo->tipo_descuento === 'PCT' ? '−'.rtrim(rtrim(number_format((float) $promo->valor_descuento, 2, '.', ''), '0'), '.').' %' : 'USD −'.number_format((float) $promo->valor_descuento, 2) }}
                            </span>
                            <span class="block text-[10px] text-muted mt-1">por unidad</span>
                        </td>

                        <td class="py-3.5 px-5 font-mono text-ink">{{ $promo->variantes_count }} variante{{ $promo->variantes_count === 1 ? '' : 's' }}</td>

                        <td class="py-3.5 px-5 whitespace-nowrap">
                            <span class="block text-ink font-mono">{{ $promo->fecha_inicio?->format('d/m/Y') }}</span>
                            <span class="block text-[11px] text-muted">hasta {{ $promo->fecha_fin?->format('d/m/Y') ?? '— (abierta)' }}</span>
                        </td>

                        <td class="py-3.5 px-5 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border
                                {{ $promo->estado === 'ACTIVA'
                                    ? 'border border-brand-green/40 bg-brand-green/20 text-brand-green'
                                    : 'border border-line bg-surface-2 text-muted' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $promo->estado === 'ACTIVA' ? 'bg-brand-green' : 'bg-surface' }}"></span>
                                {{ $promo->estado }}
                            </span>
                            @if($promo->estado === 'ACTIVA')
                            <span class="block text-[10px] mt-1 {{ $vigente ? 'text-brand-green' : 'text-amber-500' }}">
                                {{ $vigente ? 'vigente hoy' : 'fuera de fechas' }}
                            </span>
                            @endif
                        </td>

                        <td class="py-3.5 px-5 text-center">
                            <div class="flex items-center justify-center space-x-3">
                                <a href="{{ route('promociones.edit', $promo->id_promocion) }}" class="text-brand-blue hover:text-brand-blue-dark font-semibold text-xs transition-colors">Editar</a>

                                <form action="{{ route('promociones.toggle', $promo->id_promocion) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-amber-500 hover:text-amber-400 font-semibold text-xs transition-colors cursor-pointer">
                                        {{ $promo->estado === 'ACTIVA' ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>

                                <form action="{{ route('promociones.destroy', $promo->id_promocion) }}" method="POST" class="inline"
                                    onsubmit="return confirm('¿Eliminar la promoción «{{ $promo->nombre }}»? Sus variantes dejarán de tener descuento.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-400 font-semibold text-xs transition-colors cursor-pointer">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 px-6 text-center text-muted">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-3-2.8.9.9a3.5 3.5 0 0 0 2.1.7 2.6 2.6 0 0 0 2.6-2.6 2.4 2.4 0 0 0-1.2-2.2l-2.9-1.4a2.4 2.4 0 0 1-1.2-2.2A2.6 2.6 0 0 1 11.9 6a3.5 3.5 0 0 1 2.1.7l.9.9m-3.9-3.6v1.9m3 5.9v1.9" />
                                </svg>
                                <span class="text-xs font-medium text-muted">
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
            class="fixed bottom-6 right-6 inline-flex items-center gap-2 px-5 py-3 bg-brand-green hover:bg-brand-green-dark text-white text-sm font-bold rounded-2xl shadow-soft transition-all active:scale-[0.98] cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva Promoción
        </a>

        {{ $promociones->links('catalogo::partials.paginacion') }}
    </div>
</div>
@endsection
