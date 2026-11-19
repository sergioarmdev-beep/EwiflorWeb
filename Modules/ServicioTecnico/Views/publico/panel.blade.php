@extends('serviciotecnico::publico.layout')

@section('title', 'Mis compras y garantías')

@section('content')
@php
    $cliente = session('serviciotecnico.cliente_nombre', 'Cliente');
@endphp

<div class="max-w-5xl mx-auto">
    <div class="st-panel mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-ink">Hola, {{ $cliente }}</h2>
                <p class="text-sm text-muted">Tus compras y estado de garantías</p>
            </div>
            <a href="{{ route('serviciotecnico.publico.solicitar') }}"
                class="px-4 py-2 bg-brand-green text-white rounded font-semibold hover:bg-brand-green-dark transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Solicitar servicio técnico
            </a>
        </div>

        <h3 class="text-sm font-semibold text-muted uppercase tracking-wider mb-3">Compras registradas</h3>

        @if (count($compras ?? []) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line text-sm">
                <thead class="bg-surface-2">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">N° Venta</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Fecha</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted">Total USD</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted">Total Bs</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted">Pagado</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Garantía</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($compras as $compra)
                    <tr class="hover:bg-surface-2 transition">
                        <td class="px-4 py-3 text-ink font-mono">{{ $compra->id_venta }}</td>
                        <td class="px-4 py-3 text-muted">{{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right text-muted">$ {{ number_format((float) $compra->total_usd, 2) }}</td>
                        <td class="px-4 py-3 text-right text-muted">Bs {{ number_format((float) $compra->total_bs, 2) }}</td>
                        <td class="px-4 py-3 text-right text-brand-green">{{ number_format((float) $compra->pagado_bs, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($compra->tieneGarantiaVigente)
                                <span class="st-badge border border-brand-blue/40 bg-brand-blue/20 text-brand-blue">Vigente</span>
                            @else
                                <span class="st-badge border border-line bg-surface-2 text-muted">Sin garantía vigente</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8">
            <p class="text-muted">No se encontraron compras registradas a tu nombre.</p>
        </div>
        @endif
    </div>

    @if (count($solicitudes ?? []) > 0)
    <div class="st-panel mb-6">
        <h3 class="text-sm font-semibold text-muted uppercase tracking-wider mb-3">Tus solicitudes de servicio técnico</h3>
        <div class="divide-y divide-line">
            @foreach ($solicitudes as $sol)
            <div class="px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 hover:bg-surface-2 transition">
                <div>
                    <span class="text-sm font-medium text-ink">Solicitud #{{ $sol->id_solicitud }}</span>
                    <p class="text-xs text-muted">{{ \Str::limit($sol->descripcion_falla, 60) }}</p>
                </div>
                <div class="flex items-center gap-2 text-right">
                    <span class="st-badge
                        @switch($sol->estado)
                            @case('PENDIENTE') border border-amber-500/30 bg-amber-500/10 text-amber-500 @break
                            @case('PROGRAMADA') border border-brand-blue/20 bg-brand-blue/10 text-brand-blue @break
                            @default border border-brand-green/20 bg-brand-green/10 text-brand-green @endswitch">
                        {{ \Str::ucfirst(\Str::lower($sol->estado)) }}
                    </span>
                    @if ($sol->tecnico)
                        <span class="text-xs text-brand-blue">Técnico: {{ $sol->tecnico }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
