@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- Saludo --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">
            Hola, {{ auth()->user()->nombre_completo }}
        </h1>
        <p class="text-sm text-gray-500">
            {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
            · Rol: <span class="font-medium text-gray-700">{{ $stats['rol'] }}</span>
        </p>
    </div>

    {{-- Alertas del sistema (solo Gerente / Administrador) --}}
    @if (!empty($stats['alertas_sistema']))
    <div class="mb-6 space-y-3">
        @foreach ($stats['alertas_sistema'] as $alerta)
        <div class="rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3 flex items-start gap-3">
            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01M4.93 4.93l14.14 14.14M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1">
                <p class="font-semibold text-yellow-800">{{ $alerta['titulo'] }}</p>
                <p class="text-sm text-yellow-800 mt-1">{{ $alerta['mensaje'] }}</p>
            </div>
            <a href="{{ $alerta['url'] }}"
                class="text-sm font-medium text-yellow-900 underline hover:no-underline whitespace-nowrap">
                {{ $alerta['url_texto'] }} →
            </a>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Tipo de cambio actual --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        @foreach (['paralelo' => 'Paralelo (Binance)', 'oficial' => 'Oficial (BCB)'] as $key => $label)
        @php($tc = $stats['tipo_cambio'][$key] ?? null)
        <div class="bg-white shadow rounded-lg p-5">
            <div class="flex items-start justify-between mb-1">
                <p class="text-sm text-gray-500">Tipo de cambio {{ $label }}</p>
                @if ($tc)
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $tc->origen === 'API' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                    {{ $tc->origen }}
                </span>
                @endif
            </div>
            @if ($tc)
            <p class="text-3xl font-bold text-gray-900">
                Bs {{ number_format((float) $tc->valor, 2) }}
                <span class="text-sm font-normal text-gray-500">/ USD</span>
            </p>
            <p class="text-xs text-gray-500 mt-1">
                Vigente desde {{ $tc->fecha?->format('d/m/Y') ?? '—' }}
            </p>
            @else
            <p class="text-sm text-gray-500 mt-2">Sin registro todavía.</p>
            @endif
        </div>
        @endforeach
    </div>

    {{-- KPIs por rol --}}
    @if (in_array($stats['rol'], ['GERENTE', 'ADMINISTRADOR'], true))
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white shadow rounded-lg p-5">
            <p class="text-sm text-gray-500">Productos activos</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_productos'] }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <p class="text-sm text-gray-500">Productos bajo mínimo</p>
            <p class="text-3xl font-bold {{ count($stats['productos_bajo_minimo']) > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">
                {{ count($stats['productos_bajo_minimo']) }}
            </p>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <p class="text-sm text-gray-500">Cotizaciones pendientes</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['cotizaciones_pendientes'] }}</p>
        </div>
    </div>
    @endif

    @if ($stats['rol'] === 'VENDEDOR')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white shadow rounded-lg p-5">
            <p class="text-sm text-gray-500">Mis cotizaciones pendientes</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['cotizaciones_pendientes'] }}</p>
        </div>
    </div>
    @endif

    @if ($stats['rol'] === 'SERVICIO_TECNICO')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white shadow rounded-lg p-5">
            <p class="text-sm text-gray-500">Solicitudes pendientes</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['solicitudes_pendientes'] }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <p class="text-sm text-gray-500">Garantías por vencer (30 días)</p>
            <p class="text-3xl font-bold {{ $stats['garantias_por_vencer'] > 0 ? 'text-orange-600' : 'text-gray-900' }} mt-1">
                {{ $stats['garantias_por_vencer'] }}
            </p>
        </div>
    </div>
    @endif

    {{-- Tablas de detalle --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Productos bajo mínimo (solo Gerente/Admin) --}}
        @if (isset($stats['productos_bajo_minimo']))
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200">
                <h2 class="font-medium text-gray-700">Productos bajo stock mínimo</h2>
            </div>
            @if (count($stats['productos_bajo_minimo']) > 0)
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">SKU</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Producto</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600">Disponible</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600">Mín.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($stats['productos_bajo_minimo'] as $p)
                    <tr>
                        <td class="px-4 py-2 font-mono text-xs">{{ $p->sku }}</td>
                        <td class="px-4 py-2">{{ $p->producto }}</td>
                        <td class="px-4 py-2 text-right font-semibold text-red-600">{{ $p->disponible }}</td>
                        <td class="px-4 py-2 text-right text-gray-500">{{ $p->stock_minimo }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="px-5 py-8 text-center text-gray-500 text-sm">
                Todos los productos están por encima del stock mínimo.
            </p>
            @endif
        </div>
        @endif

        {{-- Últimas cotizaciones (Gerente + Vendedor) --}}
        @if (isset($stats['ultimas_cotizaciones']))
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200">
                <h2 class="font-medium text-gray-700">
                    {{ $stats['rol'] === 'VENDEDOR' ? 'Mis últimas cotizaciones' : 'Últimas cotizaciones' }}
                </h2>
            </div>
            @if (count($stats['ultimas_cotizaciones']) > 0)
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Fecha</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Cliente</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($stats['ultimas_cotizaciones'] as $c)
                    <tr>
                        <td class="px-4 py-2">
                            {{ \Carbon\Carbon::parse($c->fecha_emision)->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-2">{{ $c->cliente }}</td>
                        <td class="px-4 py-2">
                            @php($estado = $c->condicion_temporal)
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                            {{ $estado === 'VIGENTE' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $estado }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="px-5 py-8 text-center text-gray-500 text-sm">
                Aún no hay cotizaciones registradas.
            </p>
            @endif
        </div>
        @endif

        {{-- Últimas solicitudes (Técnico) --}}
        @if (isset($stats['ultimas_solicitudes']))
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200">
                <h2 class="font-medium text-gray-700">Últimas solicitudes de servicio</h2>
            </div>
            @if (count($stats['ultimas_solicitudes']) > 0)
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Fecha</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Cliente</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Falla</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($stats['ultimas_solicitudes'] as $s)
                    <tr>
                        <td class="px-4 py-2">
                            {{ \Carbon\Carbon::parse($s->fecha_solicitud)->format('d/m') }}
                        </td>
                        <td class="px-4 py-2">{{ $s->cliente }}</td>
                        <td class="px-4 py-2 text-gray-600">
                            {{ \Illuminate\Support\Str::limit($s->descripcion_falla, 40) }}
                        </td>
                        <td class="px-4 py-2">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                            {{ $s->estado === 'PENDIENTE' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $s->estado }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="px-5 py-8 text-center text-gray-500 text-sm">
                No hay solicitudes registradas.
            </p>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection