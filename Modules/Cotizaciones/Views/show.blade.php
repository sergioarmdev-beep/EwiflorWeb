@extends('layouts.app')
@section('title', "Cotización #{$cotizacion->id_cotizacion}")

@section('content')
@php
$cond = $cotizacion->condicionTemporal();
$rol = auth()->user()->nombre_rol;
@endphp

<div class="p-6 max-w-5xl space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">
            Cotización #{{ $cotizacion->id_cotizacion }}
            <span class="ml-2 text-xs px-2 py-1 rounded
                {{ $cotizacion->estado === 'EMITIDA' ? 'bg-blue-100 text-blue-800' : '' }}
                {{ $cotizacion->estado === 'CONVERTIDA' ? 'bg-green-100 text-green-800' : '' }}
                {{ $cotizacion->estado === 'ANULADA' ? 'bg-red-100 text-red-800' : '' }}">
                {{ $cotizacion->estado }}
            </span>
            @if ($cond === 'VENCIDA')
            <span class="ml-1 text-xs px-2 py-1 rounded bg-red-100 text-red-800">VENCIDA</span>
            @endif
        </h1>
        <div class="flex gap-2">
            @if (in_array($rol, ['GERENTE','ADMINISTRADOR']) && $cotizacion->estado === 'EMITIDA')
            <a href="{{ route('cotizaciones.edit', $cotizacion) }}"
                class="border px-4 py-2 rounded">Editar</a>
            <form method="POST" action="{{ route('cotizaciones.anular', $cotizacion) }}"
                onsubmit="return confirm('¿Anular esta cotización?')">
                @csrf
                <button class="border border-red-500 text-red-600 px-4 py-2 rounded">Anular</button>
            </form>
            @endif
            <a href="{{ route('cotizaciones.index') }}" class="border px-4 py-2 rounded">Volver</a>
        </div>
    </div>

    @if (session('ok'))
    <div class="bg-green-100 text-green-800 px-4 py-2 rounded">{{ session('ok') }}</div>
    @endif
    @if (session('error'))
    <div class="bg-red-100 text-red-800 px-4 py-2 rounded">{{ session('error') }}</div>
    @endif

    <div class="bg-white p-6 rounded shadow grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
        <div><span class="text-gray-500">Cliente:</span> {{ $cotizacion->cliente?->nombre ?? '—' }}</div>
        <div><span class="text-gray-500">Teléfono:</span> {{ $cotizacion->cliente?->telefono ?? '—' }}</div>
        <div><span class="text-gray-500">Sucursal:</span> {{ $cotizacion->sucursal?->nombre ?? '—' }}</div>
        <div><span class="text-gray-500">Vendedor:</span> {{ $cotizacion->usuario?->nombre_completo ?? '—' }}</div>
        <div><span class="text-gray-500">Fecha emisión:</span> {{ $cotizacion->fecha_emision->format('d/m/Y') }}</div>
        <div><span class="text-gray-500">Vence:</span>
            {{ $cotizacion->fecha_emision->copy()->addDays($cotizacion->vigencia_dias)->format('d/m/Y') }}
            ({{ $cotizacion->vigencia_dias }} días)
        </div>
        <div><span class="text-gray-500">T/C aplicado:</span>
            {{ $cotizacion->tipoCambio?->tipo }} — {{ number_format((float) $cotizacion->tipoCambio?->valor, 4) }} Bs/USD
        </div>
        @if ($cotizacion->observacion)
        <div class="md:col-span-3"><span class="text-gray-500">Observación:</span> {{ $cotizacion->observacion }}</div>
        @endif
    </div>

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">SKU</th>
                    <th class="px-4 py-2 text-left">Producto</th>
                    <th class="px-4 py-2 text-right">Cant.</th>
                    <th class="px-4 py-2 text-right">P. Unit. USD</th>
                    <th class="px-4 py-2 text-right">Desc. %</th>
                    <th class="px-4 py-2 text-right">Subtotal USD</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cotizacion->detalles as $d)
                @php
                $sub = $d->cantidad * (float) $d->precio_unitario_usd * (1 - (float) $d->descuento_pct / 100);
                @endphp
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $d->variante?->sku ?? '—' }}</td>
                    <td class="px-4 py-2">
                        {{ $d->variante?->producto?->nombre ?? '—' }}
                        @if ($d->variante?->color) ({{ $d->variante->color }}) @endif
                    </td>
                    <td class="px-4 py-2 text-right">{{ $d->cantidad }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format((float) $d->precio_unitario_usd, 2) }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format((float) $d->descuento_pct, 2) }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($sub, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 border-t font-semibold">
                <tr>
                    <td colspan="5" class="px-4 py-2 text-right">Total USD</td>
                    <td class="px-4 py-2 text-right">$ {{ number_format($cotizacion->totalUsd(), 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="px-4 py-2 text-right">Total Bs (al T/C congelado)</td>
                    <td class="px-4 py-2 text-right">Bs {{ number_format($cotizacion->totalBs(), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <h2 class="font-semibold mb-2">Siguiente paso</h2>
        @if ($cotizacion->estado === 'CONVERTIDA')
        <p class="text-sm text-gray-600">Esta cotización ya fue convertida en venta.</p>
        @elseif ($cotizacion->estado === 'ANULADA')
        <p class="text-sm text-gray-600">Cotización anulada. No puede convertirse.</p>
        @elseif ($cond === 'VENCIDA')
        <p class="text-sm text-red-700">Cotización vencida. Extiende la vigencia editándola o emite una nueva.</p>
        @elseif (Route::has('ventas.create'))
        <a href="{{ route('ventas.create', ['cotizacion' => $cotizacion->id_cotizacion]) }}"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded inline-block">
            Convertir a venta
        </a>
        @else
        <p class="text-sm text-gray-500">
            Módulo de Ventas pendiente. Cuando esté listo, aparecerá aquí el botón <em>Convertir a venta</em>
            (usa la función PostgreSQL <code>convertir_cotizacion_a_venta</code>).
        </p>
        @endif
    </div>
</div>
@endsection