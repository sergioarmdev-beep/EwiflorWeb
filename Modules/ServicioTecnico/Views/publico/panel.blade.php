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
                <h2 class="text-xl font-bold text-white">Hola, {{ $cliente }}</h2>
                <p class="text-sm text-slate-400">Tus compras y estado de garantías</p>
            </div>
            <a href="{{ route('serviciotecnico.publico.solicitar') }}"
                class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded font-semibold transition">
                + Solicitar servicio técnico
            </a>
        </div>

        <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-3">Compras registradas</h3>

        @if (count($compras ?? []) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-700 text-sm">
                <thead class="bg-slate-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400">N° Venta</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400">Fecha</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-400">Total USD</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-400">Total Bs</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-400">Pagado</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-400">Garantía</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach ($compras as $compra)
                    <tr class="hover:bg-slate-800/50 transition">
                        <td class="px-4 py-3 text-slate-200 font-mono">{{ $compra->id_venta }}</td>
                        <td class="px-4 py-3 text-slate-300">{{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right text-slate-300">$ {{ number_format((float) $compra->total_usd, 2) }}</td>
                        <td class="px-4 py-3 text-right text-slate-300">Bs {{ number_format((float) $compra->total_bs, 2) }}</td>
                        <td class="px-4 py-3 text-right text-emerald-400">{{ number_format((float) $compra->pagado_bs, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($compra->tieneGarantiaVigente)
                                <span class="st-badge st-badge-programada">Vigente</span>
                            @else
                                <span class="st-badge st-badge-cerrada">Sin garantía vigente</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8">
            <p class="text-slate-400">No se encontraron compras registradas a tu nombre.</p>
        </div>
        @endif
    </div>

    @if (count($solicitudes ?? []) > 0)
    <div class="st-panel mb-6">
        <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-3">Tus solicitudes de servicio técnico</h3>
        <div class="divide-y divide-slate-800">
            @foreach ($solicitudes as $sol)
            <div class="px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 hover:bg-slate-800/50 transition">
                <div>
                    <span class="text-sm font-medium text-slate-200">Solicitud #{{ $sol->id_solicitud }}</span>
                    <p class="text-xs text-slate-500">{{ \Str::limit($sol->descripcion_falla, 60) }}</p>
                </div>
                <div class="flex items-center gap-2 text-right">
                    <span class="st-badge @switch($sol->estado)
                        @case('PENDIENTE') st-badge-pending @break
                        @case('PROGRAMADA') st-badge-programada @break
                        @default st-badge-cerrada @endswitch">
                        {{ \Str::ucfirst(\Str::lower($sol->estado)) }}
                    </span>
                    @if ($sol->tecnico)
                        <span class="text-xs text-sky-400">Técnico: {{ $sol->tecnico }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
