@extends('layouts.app')
@section('title', "Cotización #{$cotizacion->id_cotizacion}")

@section('content')
@php
$cond = $cotizacion->condicionTemporal();
$rol = auth()->user()->nombre_rol;
@endphp

<div class="p-6 max-w-5xl space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">
            Cotización #{{ $cotizacion->id_cotizacion }}
            <span class="ml-2 text-xs px-2 py-1 rounded
                {{ $cotizacion->estado === 'EMITIDA' ? 'border border-brand-blue/20 bg-brand-blue/10 text-brand-blue' : '' }}
                {{ $cotizacion->estado === 'CONVERTIDA' ? 'border border-brand-green/20 bg-brand-green/10 text-brand-green' : '' }}
                {{ $cotizacion->estado === 'ANULADA' ? 'border border-red-500/30 bg-red-500/10 text-red-500' : '' }}">
                {{ $cotizacion->estado }}
            </span>
            @if ($cond === 'VENCIDA')
            <span class="ml-1 text-xs px-2 py-1 rounded border border-red-500/30 bg-red-500/10 text-red-500">VENCIDA</span>
            @endif
        </h1>
        <div class="flex gap-2">
            @if (in_array($rol, ['GERENTE','ADMINISTRADOR']) && $cotizacion->estado === 'EMITIDA')
            <a href="{{ route('cotizaciones.edit', $cotizacion) }}"
                class="border border-line rounded-xl px-4 py-2 text-ink hover:bg-surface-2 transition">Editar</a>
            <form method="POST" action="{{ route('cotizaciones.anular', $cotizacion) }}"
                onsubmit="return confirm('¿Anular esta cotización?')">
                @csrf
                <button class="border border-red-500/30 bg-red-500/10 text-red-500 rounded-xl px-4 py-2 hover:bg-red-500/20 transition">Anular</button>
            </form>
            @endif
            <a href="{{ route('cotizaciones.index') }}" class="border border-line rounded-xl px-4 py-2 text-ink hover:bg-surface-2 transition">Volver</a>
        </div>
    </div>

    @if (session('ok'))
    <div class="mb-3 flex items-start gap-3 rounded-xl border border-brand-green/30 bg-brand-green/10 px-4 py-2 text-sm text-brand-green">
        {{ session('ok') }}
    </div>
    @endif
    @if (session('error'))
    <div class="mb-3 flex items-start gap-3 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2 text-sm text-red-500">
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-surface p-6 rounded-2xl border border-line shadow-soft grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
        <div><span class="text-muted">Cliente:</span> {{ $cotizacion->cliente?->nombre ?? '—' }}</div>
        <div><span class="text-muted">Teléfono:</span> {{ $cotizacion->cliente?->telefono ?? '—' }}</div>
        <div><span class="text-muted">Sucursal:</span> {{ $cotizacion->sucursal?->nombre ?? '—' }}</div>
        <div><span class="text-muted">Vendedor:</span> {{ $cotizacion->usuario?->nombre_completo ?? '—' }}</div>
        <div><span class="text-muted">Fecha emisión:</span> {{ $cotizacion->fecha_emision->format('d/m/Y') }}</div>
        <div><span class="text-muted">Vence:</span>
            {{ $cotizacion->fecha_emision->copy()->addDays($cotizacion->vigencia_dias)->format('d/m/Y') }}
            ({{ $cotizacion->vigencia_dias }} días)
        </div>
        <div><span class="text-muted">T/C aplicado:</span>
            {{ $cotizacion->tipoCambio?->tipo }} — {{ number_format((float) $cotizacion->tipoCambio?->valor, 4) }} Bs/USD
        </div>
        @if ($cotizacion->observacion)
        <div class="md:col-span-3"><span class="text-muted">Observación:</span> {{ $cotizacion->observacion }}</div>
        @endif
    </div>

    <div class="bg-surface rounded-2xl border border-line shadow-soft overflow-hidden overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-surface-2">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">SKU</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Producto</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">Cant.</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">P. Unit. USD</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">Desc. %</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">Garantía (meses)</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">Subtotal USD</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($cotizacion->detalles as $d)
                @php
                $sub = $d->cantidad * (float) $d->precio_unitario_usd * (1 - (float) $d->descuento_pct / 100);
                @endphp
                <tr class="hover:bg-surface-2 transition-colors">
                    <td class="px-4 py-2 text-ink">{{ $d->variante?->sku ?? '—' }}</td>
                    <td class="px-4 py-2 text-ink">
                        {{ $d->variante?->producto?->nombre ?? '—' }}
                        @if ($d->variante?->color) ({{ $d->variante->color }}) @endif
                    </td>
                    <td class="px-4 py-2 text-right text-ink">{{ $d->cantidad }}</td>
                    <td class="px-4 py-2 text-right text-ink">{{ number_format((float) $d->precio_unitario_usd, 2) }}</td>
                    <td class="px-4 py-2 text-right text-ink">{{ number_format((float) $d->descuento_pct, 2) }}</td>
                    <td class="px-4 py-2 text-right text-ink">{{ $d->garantia_meses ?? ($d->variante?->producto?->garantia_meses ?? '—') }}</td>
                    <td class="px-4 py-2 text-right text-ink">{{ number_format($sub, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-surface-2 border-t border-line font-semibold">
                <tr>
                    <td colspan="6" class="px-4 py-2 text-right text-ink">Total USD</td>
                    <td class="px-4 py-2 text-right text-ink">$ {{ number_format($cotizacion->totalUsd(), 2) }}</td>
                </tr>
                <tr>
                    <td colspan="6" class="px-4 py-2 text-right text-ink">Total Bs (al T/C congelado)</td>
                    <td class="px-4 py-2 text-right text-ink">Bs {{ number_format($cotizacion->totalBs(), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="bg-surface p-6 rounded-2xl border border-line shadow-soft">
        <h2 class="font-bold text-ink mb-2">Siguiente paso</h2>
        @if ($cotizacion->estado === 'CONVERTIDA')
        <p class="text-sm text-muted">Esta cotización ya fue convertida en venta.</p>
        @elseif ($cotizacion->estado === 'ANULADA')
        <p class="text-sm text-muted">Cotización anulada. No puede convertirse.</p>
        @elseif ($cond === 'VENCIDA')
        <p class="text-sm text-amber-500">Cotización vencida. Extiende la vigencia editándola o emite una nueva.</p>
        @elseif (Route::has('ventas.create'))
        <a href="{{ route('ventas.create', ['desde_cotizacion' => 1, 'cotizacion' => $cotizacion->id_cotizacion]) }}"
            class="bg-brand-green text-white px-4 py-2 rounded-xl font-semibold hover:bg-brand-green-dark transition-colors inline-block">
            Convertir a venta
        </a>
        @else
        <p class="text-sm text-muted">
            Módulo de Ventas pendiente. Cuando esté listo, aparecerá aquí el botón <em>Convertir a venta</em>
            (usa la función PostgreSQL <code>convertir_cotizacion_a_venta</code>).
        </p>
        @endif
    </div>
</div>
@endsection
