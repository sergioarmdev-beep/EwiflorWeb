@extends('layouts.app')

@section('title', 'Tipo de Cambio')

@section('content')
@php
    $esGestor   = in_array(auth()->user()?->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true);
    $hayFiltros = !empty($filtros);
    $inputCls   = 'w-full px-3.5 py-2.5 bg-slate-800 border border-sky-500/40 rounded-xl text-slate-100 text-xs focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all';
@endphp
<div class="max-w-7xl mx-auto px-4 py-6 text-slate-100 space-y-6">

    {{-- Banner: API inactiva --}}
    @if ($estadoApi['disponible'] === false)
    <div class="rounded-2xl border border-amber-500/40 bg-amber-950/40 px-5 py-4 text-sm text-amber-200 flex items-start gap-3.5 shadow-lg">
        <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01M4.93 4.93l14.14 14.14M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <p class="font-semibold text-amber-300">Uno o más servicios de tipo de cambio no están disponibles.</p>
            <p class="mt-1 text-xs text-amber-200/90 leading-relaxed">
                Estado reportado: <code class="bg-amber-900/60 text-amber-200 px-1.5 py-0.5 rounded border border-amber-500/30">{{ $estadoApi['estado'] ?? 'DESCONOCIDO' }}</code>.
                @if ($ultimaSync)
                Última sincronización exitosa: {{ $ultimaSync->format('d/m/Y H:i') }}.
                @endif
                Se está usando el último valor registrado. Puedes registrar un valor manual mientras se restablece.
            </p>
        </div>
    </div>
    @endif

    {{-- Título y botón refresh --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Tipo de cambio USD → Bs</h1>
            <p class="text-xs text-slate-300 mt-0.5">
                Cotizaciones vigentes y historial de registros.
                @if ($ultimaSync)
                Última sincronización automática: {{ $ultimaSync->format('d/m/Y H:i') }}.
                @endif
            </p>
        </div>

        @if ($esGestor)
        <form method="POST" action="{{ route('precios.tipo-cambio.refresh') }}">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-150 active:scale-[0.99] cursor-pointer border border-blue-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Actualizar desde API</span>
            </button>
        </form>
        @endif
    </div>

    {{-- Tarjetas de cotización vigente --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach (['paralelo' => 'Paralelo (Binance)', 'oficial' => 'Oficial'] as $key => $label)
        @php
            $tc = $actuales[$key] ?? null;
            $v  = $variaciones[$key] ?? null;
        @endphp
        <div class="bg-slate-900 border border-sky-500/40 rounded-2xl p-6 shadow-xl flex flex-col justify-between">
            <div class="flex items-start justify-between mb-3">
                <h2 class="text-base font-semibold text-white tracking-wide">{{ $label }}</h2>
                @if ($tc)
                <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium border
                    {{ $tc->esApi() ? 'bg-blue-950/60 text-blue-300 border-blue-500/30' : 'bg-purple-950/60 text-purple-300 border-purple-500/30' }}">
                    {{ $tc->origen }}
                </span>
                @endif
            </div>

            @if ($tc)
            <div>
                <p class="text-3xl font-extrabold text-white tracking-tight">
                    Bs {{ number_format((float) $tc->valor, 2) }}
                    <span class="text-xs font-normal text-slate-400">/ USD (venta)</span>
                </p>

                @if ($v)
                @php
                    $sube = $v['diferencia'] > 0;
                    $baja = $v['diferencia'] < 0;
                    $cls  = $sube ? 'text-emerald-400' : ($baja ? 'text-rose-400' : 'text-slate-400');
                @endphp
                <p class="mt-1.5 text-xs font-semibold {{ $cls }}">
                    {{ $sube ? '▲' : ($baja ? '▼' : '■') }}
                    {{ number_format(abs($v['diferencia']), 4) }}
                    @if ($v['porcentaje'] !== null)
                    ({{ number_format(abs($v['porcentaje']), 2) }}%)
                    @endif
                    <span class="font-normal text-slate-400">vs registro anterior (Bs {{ number_format($v['anterior'], 4) }})</span>
                </p>
                @endif

                @if ($tc->esManual())
                <p class="mt-2 text-xs text-purple-300/90 leading-relaxed">
                    Valor manual vigente: la sincronización con la API sigue guardando historial, pero no lo reemplaza hasta que se registre otro manual.
                </p>
                @endif

                <dl class="mt-4 text-xs text-slate-300 space-y-2 border-t border-slate-800 pt-3">
                    @if ($tc->valor_compra !== null)
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Compra:</dt>
                        <dd class="font-medium text-slate-200">Bs {{ number_format((float) $tc->valor_compra, 4) }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Fecha del registro:</dt>
                        <dd class="font-medium text-slate-200">{{ $tc->fecha?->format('d/m/Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Registrado el:</dt>
                        <dd class="font-medium text-slate-200">{{ $tc->fecha_registro?->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if ($tc->fecha_referencia_api)
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Referencia API:</dt>
                        <dd class="font-medium text-slate-200">{{ $tc->fecha_referencia_api->format('d/m/Y H:i') }}</dd>
                    </div>
                    @endif
                    @if ($tc->esManual() && $tc->usuario)
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Registrado por:</dt>
                        <dd class="font-medium text-slate-200">{{ $tc->usuario->nombre_completo }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            @else
            <p class="text-slate-400 text-xs py-4">
                Aún no se ha registrado un tipo de cambio {{ strtolower($label) }}.
            </p>
            @endif
        </div>
        @endforeach

        {{-- Brecha paralelo / oficial --}}
        @if ($brecha)
        <div class="md:col-span-2 bg-slate-900 border border-sky-500/40 rounded-2xl px-6 py-4 shadow-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-sm font-semibold text-white tracking-wide">Brecha paralelo / oficial</h2>
                <p class="text-xs text-slate-400 mt-0.5">Diferencia entre el valor vigente paralelo y el oficial.</p>
            </div>
            <p class="text-xl font-extrabold text-white tracking-tight">
                Bs {{ number_format($brecha['diferencia'], 2) }}
                @if ($brecha['porcentaje'] !== null)
                <span class="text-sm font-semibold text-sky-300">({{ number_format($brecha['porcentaje'], 2) }}%)</span>
                @endif
            </p>
        </div>
        @endif
    </div>

    {{-- Formulario manual (colapsable) --}}
    @if ($esGestor)
    <details class="bg-slate-900 border border-sky-500/40 rounded-2xl shadow-xl overflow-hidden group" @if ($errors->manual->any()) open @endif>
        <summary class="px-6 py-4 cursor-pointer font-semibold text-sm text-white flex items-center justify-between hover:bg-slate-800/50 transition-colors list-none select-none">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Registrar tipo de cambio manual</span>
            </div>
            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </summary>
        <div class="px-6 pb-6 pt-2 border-t border-slate-800">
            <form method="POST" action="{{ route('precios.tipo-cambio.store-manual') }}"
                class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-2">
                @csrf

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">Fecha *</label>
                    <input type="date" name="fecha" required
                        max="{{ now()->format('Y-m-d') }}"
                        value="{{ old('fecha', now()->format('Y-m-d')) }}"
                        class="{{ $inputCls }} cursor-pointer">
                    @error('fecha', 'manual') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">Tipo *</label>
                    <select name="tipo" required class="{{ $inputCls }} cursor-pointer">
                        <option value="PARALELO" @selected(old('tipo')==='PARALELO' ) class="bg-slate-900">Paralelo</option>
                        <option value="OFICIAL" @selected(old('tipo')==='OFICIAL' ) class="bg-slate-900">Oficial</option>
                    </select>
                    @error('tipo', 'manual') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">Valor (Bs / USD) *</label>
                    <input type="number" name="valor" step="0.0001" min="0.0001" required
                        placeholder="0.0000"
                        value="{{ old('valor') }}"
                        class="{{ $inputCls }} placeholder-slate-500">
                    @error('valor', 'manual') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-end">
                    <button type="submit"
                        class="w-full py-2.5 px-4 bg-purple-600 hover:bg-purple-500 text-white text-xs font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-150 active:scale-[0.99] cursor-pointer border border-purple-400">
                        Registrar
                    </button>
                </div>
            </form>

            <p class="text-xs text-slate-400 mt-4 leading-relaxed">
                El registro manual se guarda con origen <code class="bg-slate-800 text-purple-300 px-1.5 py-0.5 rounded border border-slate-700">MANUAL</code> y queda ligado a tu usuario.
                Ambos registros se conservan en el historial. Este valor queda vigente hasta que registres otro manual del mismo tipo: la API sigue guardando historial, pero no lo reemplaza.
            </p>
        </div>
    </details>
    @endif

    {{-- Historial --}}
    <div class="bg-slate-900 border border-sky-500/40 rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between gap-3">
            <h2 class="font-semibold text-sm text-white">Historial</h2>
            <span class="text-xs text-slate-400 bg-slate-800 px-2.5 py-1 rounded-lg border border-slate-700">
                {{ $historial->total() }} {{ $historial->total() === 1 ? 'registro' : 'registros' }}
            </span>
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('precios.tipo-cambio.index') }}"
            class="px-6 py-4 border-b border-slate-800 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Tipo</label>
                <select name="tipo" class="{{ $inputCls }} cursor-pointer">
                    <option value="" class="bg-slate-900">Todos</option>
                    <option value="PARALELO" @selected(($filtros['tipo'] ?? '')==='PARALELO' ) class="bg-slate-900">Paralelo</option>
                    <option value="OFICIAL" @selected(($filtros['tipo'] ?? '')==='OFICIAL' ) class="bg-slate-900">Oficial</option>
                </select>
                @error('tipo') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Origen</label>
                <select name="origen" class="{{ $inputCls }} cursor-pointer">
                    <option value="" class="bg-slate-900">Todos</option>
                    <option value="API" @selected(($filtros['origen'] ?? '')==='API' ) class="bg-slate-900">API</option>
                    <option value="MANUAL" @selected(($filtros['origen'] ?? '')==='MANUAL' ) class="bg-slate-900">Manual</option>
                </select>
                @error('origen') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Desde</label>
                <input type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}" class="{{ $inputCls }} cursor-pointer">
                @error('desde') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Hasta</label>
                <input type="date" name="hasta" value="{{ $filtros['hasta'] ?? '' }}" class="{{ $inputCls }} cursor-pointer">
                @error('hasta') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2 lg:col-span-2">
                <button type="submit"
                    class="flex-1 py-2.5 px-4 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md transition-all duration-150 active:scale-[0.99] cursor-pointer border border-blue-400">
                    Filtrar
                </button>
                @if ($hayFiltros)
                <a href="{{ route('precios.tipo-cambio.index') }}"
                    class="py-2.5 px-4 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-600 transition-colors text-center">
                    Limpiar
                </a>
                @endif
                <a href="{{ route('precios.tipo-cambio.export', $filtros) }}"
                    class="inline-flex items-center gap-1.5 py-2.5 px-4 bg-slate-800 hover:bg-slate-700 text-sky-300 text-xs font-semibold rounded-xl border border-sky-500/40 transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                    </svg>
                    CSV
                </a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-sm text-left">
                <thead class="bg-slate-950/50 text-slate-400 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Fecha</th>
                        <th class="px-5 py-3.5 font-semibold">Tipo</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Venta (Bs)</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Compra (Bs)</th>
                        <th class="px-5 py-3.5 font-semibold">Origen</th>
                        <th class="px-5 py-3.5 font-semibold">Registrado</th>
                        <th class="px-5 py-3.5 font-semibold">Referencia API</th>
                        <th class="px-5 py-3.5 font-semibold">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300 text-xs">
                    @forelse ($historial as $h)
                    <tr class="hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5 font-medium text-slate-200">{{ $h->fecha?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-5 py-3.5">{{ $h->tipo }}</td>
                        <td class="px-5 py-3.5 text-right font-mono font-medium text-slate-100">{{ number_format((float) $h->valor, 4) }}</td>
                        <td class="px-5 py-3.5 text-right font-mono text-slate-300">{{ $h->valor_compra !== null ? number_format((float) $h->valor_compra, 4) : '—' }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium border
                                {{ $h->esApi() ? 'bg-blue-950/60 text-blue-300 border-blue-500/30' : 'bg-purple-950/60 text-purple-300 border-purple-500/30' }}">
                                {{ $h->origen }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-400">{{ $h->fecha_registro?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-slate-400">
                            {{ $h->fecha_referencia_api?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-slate-300">{{ $h->usuario?->nombre_completo ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-xs font-medium text-slate-400">
                                    @if ($hayFiltros)
                                    No hay registros con esos filtros.
                                    @else
                                    Aún no hay registros. Usa "Actualizar desde API" o el formulario manual.
                                    @endif
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if ($historial->hasPages())
        <div class="px-6 py-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
            <span>Página {{ $historial->currentPage() }} de {{ $historial->lastPage() }}</span>
            <div class="flex gap-2">
                @if ($historial->onFirstPage())
                <span class="px-3.5 py-2 rounded-xl border border-slate-800 text-slate-600 cursor-not-allowed">Anterior</span>
                @else
                <a href="{{ $historial->previousPageUrl() }}" class="px-3.5 py-2 rounded-xl border border-sky-500/40 bg-slate-800 hover:bg-slate-700 text-slate-200 transition-colors">Anterior</a>
                @endif

                @if ($historial->hasMorePages())
                <a href="{{ $historial->nextPageUrl() }}" class="px-3.5 py-2 rounded-xl border border-sky-500/40 bg-slate-800 hover:bg-slate-700 text-slate-200 transition-colors">Siguiente</a>
                @else
                <span class="px-3.5 py-2 rounded-xl border border-slate-800 text-slate-600 cursor-not-allowed">Siguiente</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
