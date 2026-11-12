@extends('layouts.app')

@section('title', 'Tipo de Cambio')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-slate-100 space-y-6">

    {{-- Banner: API inactiva --}}
    @if ($estadoApi['disponible'] === false)
    <div class="rounded-2xl border border-amber-500/40 bg-amber-950/40 px-5 py-4 text-sm text-amber-200 flex items-start gap-3.5 shadow-lg">
        <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01M4.93 4.93l14.14 14.14M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <p class="font-semibold text-amber-300">El servicio de tipo de cambio (DolarAPI) no está disponible.</p>
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
                Última sincronización con DolarAPI: {{ $ultimaSync->format('d/m/Y H:i') }}.
                @endif
            </p>
        </div>

        @if (in_array(auth()->user()?->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true))
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
        @php($tc = $actuales[$key] ?? null)
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
                    <span class="text-xs font-normal text-slate-400">/ USD</span>
                </p>
                <dl class="mt-4 text-xs text-slate-300 space-y-2 border-t border-slate-800 pt-3">
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
    </div>

    {{-- Formulario manual (colapsable) --}}
    @if (in_array(auth()->user()?->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true))
    <details class="bg-slate-900 border border-sky-500/40 rounded-2xl shadow-xl overflow-hidden group">
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
                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-sky-500/40 rounded-xl text-slate-100 text-xs focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all cursor-pointer">
                    @error('fecha') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">Tipo *</label>
                    <select name="tipo" required class="w-full px-3.5 py-2.5 bg-slate-800 border border-sky-500/40 rounded-xl text-slate-100 text-xs focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all cursor-pointer">
                        <option value="PARALELO" @selected(old('tipo')==='PARALELO' ) class="bg-slate-900">Paralelo</option>
                        <option value="OFICIAL" @selected(old('tipo')==='OFICIAL' ) class="bg-slate-900">Oficial</option>
                    </select>
                    @error('tipo') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">Valor (Bs / USD) *</label>
                    <input type="number" name="valor" step="0.0001" min="0.0001" required
                        placeholder="0.0000"
                        value="{{ old('valor') }}"
                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-sky-500/40 rounded-xl text-slate-100 placeholder-slate-500 text-xs focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all">
                    @error('valor') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
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
                No reemplaza al registro anterior: ambos se conservan en el historial.
            </p>
        </div>
    </details>
    @endif

    {{-- Historial --}}
    <div class="bg-slate-900 border border-sky-500/40 rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
            <h2 class="font-semibold text-sm text-white">Historial reciente</h2>
            <span class="text-xs text-slate-400 bg-slate-800 px-2.5 py-1 rounded-lg border border-slate-700">
                {{ $historial->count() }} registros
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-sm text-left">
                <thead class="bg-slate-950/50 text-slate-400 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Fecha</th>
                        <th class="px-5 py-3.5 font-semibold">Tipo</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Valor (Bs)</th>
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
                        <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-xs font-medium text-slate-400">Aún no hay registros. Usa "Actualizar desde API" o el formulario manual.</span>
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