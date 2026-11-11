@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- Banner: API inactiva --}}
    @if ($estadoApi['disponible'] === false)
    <div class="mb-4 rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-800 flex items-start gap-3">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01M4.93 4.93l14.14 14.14M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <p class="font-semibold">El servicio de tipo de cambio (DolarAPI) no está disponible.</p>
            <p class="mt-1">
                Estado reportado: <code class="bg-yellow-100 px-1 rounded">{{ $estadoApi['estado'] ?? 'DESCONOCIDO' }}</code>.
                @if ($ultimaSync)
                Última sincronización exitosa: {{ $ultimaSync->format('d/m/Y H:i') }}.
                @endif
                Se está usando el último valor registrado. Puedes registrar un valor manual mientras se restablece.
            </p>
        </div>
    </div>
    @endif

    {{-- Título y botón refresh --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Tipo de cambio USD → Bs</h1>
            <p class="text-sm text-gray-500">
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
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Actualizar desde API
            </button>
        </form>
        @endif
    </div>

    {{-- Tarjetas de cotización vigente --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        @foreach (['paralelo' => 'Paralelo (Binance)', 'oficial' => 'Oficial'] as $key => $label)
        @php($tc = $actuales[$key] ?? null)
        <div class="bg-white shadow rounded-lg p-5">
            <div class="flex items-start justify-between mb-2">
                <h2 class="text-lg font-semibold text-gray-700">{{ $label }}</h2>
                @if ($tc)
                <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium
                            {{ $tc->esApi() ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                    {{ $tc->origen }}
                </span>
                @endif
            </div>

            @if ($tc)
            <p class="text-3xl font-bold text-gray-900">
                Bs {{ number_format((float) $tc->valor, 2) }}
                <span class="text-sm font-normal text-gray-500">/ USD</span>
            </p>
            <dl class="mt-3 text-sm text-gray-600 space-y-1">
                <div class="flex justify-between">
                    <dt>Fecha del registro:</dt>
                    <dd class="font-medium">{{ $tc->fecha?->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>Registrado el:</dt>
                    <dd class="font-medium">{{ $tc->fecha_registro?->format('d/m/Y H:i') }}</dd>
                </div>
                @if ($tc->fecha_referencia_api)
                <div class="flex justify-between">
                    <dt>Referencia API:</dt>
                    <dd class="font-medium">{{ $tc->fecha_referencia_api->format('d/m/Y H:i') }}</dd>
                </div>
                @endif
                @if ($tc->esManual() && $tc->usuario)
                <div class="flex justify-between">
                    <dt>Registrado por:</dt>
                    <dd class="font-medium">{{ $tc->usuario->nombre_completo }}</dd>
                </div>
                @endif
            </dl>
            @else
            <p class="text-gray-500 text-sm">
                Aún no se ha registrado un tipo de cambio {{ strtolower($label) }}.
            </p>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Formulario manual (colapsable) --}}
    @if (in_array(auth()->user()?->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true))
    <details class="bg-white shadow rounded-lg mb-6">
        <summary class="px-5 py-4 cursor-pointer font-medium text-gray-700 flex items-center justify-between">
            <span>Registrar tipo de cambio manual</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </summary>
        <div class="px-5 pb-5 border-t border-gray-100">
            <form method="POST" action="{{ route('precios.tipo-cambio.store-manual') }}"
                class="grid grid-cols-1 md:grid-cols-4 gap-3 pt-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha *</label>
                    <input type="date" name="fecha" required
                        max="{{ now()->format('Y-m-d') }}"
                        value="{{ old('fecha', now()->format('Y-m-d')) }}"
                        class="mt-1 border-gray-300 rounded-md shadow-sm w-full">
                    @error('fecha') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo *</label>
                    <select name="tipo" required class="mt-1 border-gray-300 rounded-md shadow-sm w-full">
                        <option value="PARALELO" @selected(old('tipo')==='PARALELO' )>Paralelo</option>
                        <option value="OFICIAL" @selected(old('tipo')==='OFICIAL' )>Oficial</option>
                    </select>
                    @error('tipo') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Valor (Bs / USD) *</label>
                    <input type="number" name="valor" step="0.0001" min="0.0001" required
                        value="{{ old('valor') }}"
                        class="mt-1 border-gray-300 rounded-md shadow-sm w-full">
                    @error('valor') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-end">
                    <button type="submit"
                        class="w-full px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                        Registrar
                    </button>
                </div>
            </form>

            <p class="text-xs text-gray-500 mt-3">
                El registro manual se guarda con origen <code>MANUAL</code> y queda ligado a tu usuario.
                No reemplaza al registro anterior: ambos se conservan en el historial.
            </p>
        </div>
    </details>
    @endif

    {{-- Historial --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200">
            <h2 class="font-medium text-gray-700">Historial reciente ({{ $historial->count() }} registros)</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Fecha</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Tipo</th>
                    <th class="px-4 py-2 text-right font-medium text-gray-600">Valor (Bs)</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Origen</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Registrado</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Referencia API</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Usuario</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($historial as $h)
                <tr>
                    <td class="px-4 py-2">{{ $h->fecha?->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-4 py-2">{{ $h->tipo }}</td>
                    <td class="px-4 py-2 text-right font-mono">{{ number_format((float) $h->valor, 4) }}</td>
                    <td class="px-4 py-2">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                {{ $h->esApi() ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ $h->origen }}
                        </span>
                    </td>
                    <td class="px-4 py-2">{{ $h->fecha_registro?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td class="px-4 py-2 text-gray-600">
                        {{ $h->fecha_referencia_api?->format('d/m/Y H:i') ?? '—' }}
                    </td>
                    <td class="px-4 py-2">{{ $h->usuario?->nombre_completo ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                        Aún no hay registros. Usa "Actualizar desde API" o el formulario manual.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection