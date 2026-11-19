@extends('layouts.app')

@section('title', 'Detalle del cliente')

@section('content')
@php
$puedeGestionarClientes = in_array(
auth()->user()->nombre_rol,
['GERENTE', 'ADMINISTRADOR', 'VENDEDOR'],
true
);
@endphp

<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('clientes.index') }}"
                class="inline-flex items-center gap-2 text-sm font-medium
                       text-muted hover:text-brand-blue transition mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Volver a clientes
            </a>

            <div class="flex items-center gap-3">
                <span class="w-10 h-10 flex items-center justify-center rounded-xl
                             border border-brand-blue/20 bg-brand-blue/10 text-brand-blue">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M17 20h5v-2a4 4 0 0 0-5.5-3.7M9 20H2v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2H9Zm0-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-1a3 3 0 1 0 0-6" />
                    </svg>
                </span>

                <div>
                    <h1 class="text-2xl font-bold text-ink">
                        {{ $cliente->nombre }}
                    </h1>
                    <p class="text-sm text-muted">
                        Detalle e historial comercial del cliente
                    </p>
                </div>
            </div>
        </div>

        @if ($puedeGestionarClientes)
        <a href="{{ route('clientes.edit', $cliente) }}"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5
                       rounded-lg bg-brand-green text-white text-sm font-semibold shadow-sm hover:bg-brand-green-dark transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="2"
                    d="m16.862 4.487 1.687-1.688a2.121 2.121 0 0 1 3 3l-1.688 1.687m-3-3L7.5 13.85a4 4 0 0 0-1 1.7L6 18l2.45-.5a4 4 0 0 0 1.7-1l9.712-9.713" />
            </svg>
            Editar cliente
        </a>
        @endif
    </div>

    {{-- Información general --}}
    <section class="bg-surface border border-line rounded-xl shadow-soft overflow-hidden">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between
                    gap-3 px-5 py-4
                    border-b border-line">

            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-muted"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m16-10a4 4 0 0 0 0-8m4 18v-2a4 4 0 0 0-3-3.87M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
                </svg>

                <h2 class="font-semibold text-ink">
                    Información del cliente
                </h2>
            </div>

            <span class="text-xs text-muted">
                Código: #{{ $cliente->id_cliente }}
            </span>
        </div>

        <div class="p-5">
            <div class="flex flex-wrap items-center gap-2 mb-5">

                @if ($cliente->estado === 'ACTIVO')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1
                                 rounded-full text-xs font-semibold
                                 border border-brand-green/20 bg-brand-green/10 text-brand-green">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-green"></span>
                    Activo
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1
                                 rounded-full text-xs font-semibold
                                 border border-red-500/30 bg-red-500/10 text-red-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    Inactivo
                </span>
                @endif

                @if ($cliente->es_mayorista)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                 text-xs font-semibold
                                 border border-brand-blue/20 bg-brand-blue/10 text-brand-blue">
                    Mayorista
                </span>
                @else
                <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                 text-xs font-semibold
                                 border border-line bg-surface-2 text-muted">
                    Minorista
                </span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider
                              text-muted mb-1.5">
                        Nombre completo
                    </p>
                    <p class="text-sm font-semibold text-ink">
                        {{ $cliente->nombre }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider
                              text-muted mb-1.5">
                        Teléfono
                    </p>
                    <p class="text-sm font-medium text-muted">
                        {{ $cliente->telefono ?: 'No registrado' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider
                              text-muted mb-1.5">
                        Correo electrónico
                    </p>
                    <p class="text-sm font-medium text-muted break-all">
                        {{ $cliente->correo ?: 'No registrado' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider
                              text-muted mb-1.5">
                        Tipo de cliente
                    </p>
                    <p class="text-sm font-medium text-muted">
                        {{ str_replace('_', ' ', $cliente->tipo_cliente) }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider
                              text-muted mb-1.5">
                        Fecha de registro
                    </p>
                    <p class="text-sm font-medium text-muted">
                        {{ $cliente->fecha_registro?->format('d/m/Y') ?? 'No disponible' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Aviso de alcance: el vendedor solo ve sus propias operaciones --}}
    @if ($soloDocumentosPropios ?? false)
    <div class="flex items-start gap-3 p-4 rounded-xl
                border border-brand-blue/30 bg-brand-blue/10 text-brand-blue">
        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                stroke-width="1.8"
                d="M13 16h-1v-4h-1m1-4h.01M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z" />
        </svg>

        <p class="text-sm font-medium">
            Este resumen muestra solo tus cotizaciones y ventas con este cliente.
            Un gerente o administrador ve el historial completo.
        </p>
    </div>
    @endif

    {{-- Resumen comercial --}}
    <section class="space-y-4">

        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-muted"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="1.8"
                    d="M3 3v18h18M18 17V9m-5 8V5m-5 12v-5" />
            </svg>

            <div>
                <h2 class="font-semibold text-ink">
                    Resumen comercial
                </h2>
                <p class="text-xs text-muted">
                    Indicadores acumulados de la actividad del cliente
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            {{-- Cotizaciones emitidas --}}
            <div class="bg-surface border border-line rounded-xl p-5 shadow-soft">

                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-muted">
                        Cotizaciones emitidas
                    </p>

                    <span class="w-9 h-9 flex items-center justify-center rounded-lg
                                 border border-brand-blue/20 bg-brand-blue/10 text-brand-blue">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M8 7h8m-8 4h8m-8 4h5M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                        </svg>
                    </span>
                </div>

                <p class="text-3xl font-bold text-ink mt-3 tabular-nums">
                    {{ $resumenCotizaciones->emitidas ?? 0 }}
                </p>

                <p class="text-xs text-muted mt-2">
                    Total: {{ $resumenCotizaciones->total ?? 0 }}
                </p>
            </div>

            {{-- Cotizaciones convertidas --}}
            <div class="bg-surface border border-line rounded-xl p-5 shadow-soft">

                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-muted">
                        Cotizaciones convertidas
                    </p>

                    <span class="w-9 h-9 flex items-center justify-center rounded-lg
                                 border border-brand-green/20 bg-brand-green/10 text-brand-green">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="1.8"
                                d="m5 12 4 4L19 6" />
                        </svg>
                    </span>
                </div>

                <p class="text-3xl font-bold text-ink mt-3 tabular-nums">
                    {{ $resumenCotizaciones->convertidas ?? 0 }}
                </p>

                <p class="text-xs text-muted mt-2">
                    Anuladas: {{ $resumenCotizaciones->anuladas ?? 0 }}
                </p>
            </div>

            {{-- Ventas registradas --}}
            <div class="bg-surface border border-line rounded-xl p-5 shadow-soft">

                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-muted">
                        Ventas registradas
                    </p>

                    <span class="w-9 h-9 flex items-center justify-center rounded-lg
                                 border border-brand-blue/20 bg-brand-blue/10 text-brand-blue">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M3 3h2l2.4 12.5a2 2 0 0 0 2 1.5h8.8a2 2 0 0 0 2-1.6L22 8H6m4 13a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
                        </svg>
                    </span>
                </div>

                <p class="text-3xl font-bold text-ink mt-3 tabular-nums">
                    {{ $resumenVentas->total ?? 0 }}
                </p>

                <p class="text-xs text-muted mt-2">
                    Operaciones efectivas
                </p>
            </div>

            {{-- Saldo pendiente --}}
            <div class="bg-surface border border-line rounded-xl p-5 shadow-soft">

                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-muted">
                        Saldo pendiente
                    </p>

                    <span class="w-9 h-9 flex items-center justify-center rounded-lg
                                 border border-amber-500/30 bg-amber-500/10 text-amber-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>
                </div>

                <p class="text-2xl font-bold text-amber-500 mt-3 tabular-nums">
                    Bs {{ number_format((float) ($resumenVentas->saldo_bs ?? 0), 2, ',', '.') }}
                </p>

                <p class="text-xs text-muted mt-2">
                    Por cobrar
                </p>
            </div>

            {{-- Garantías por vencer --}}
            <div class="bg-surface border border-line rounded-xl p-5 shadow-soft">

                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-muted">
                        Garantías por vencer
                    </p>

                    <span class="w-9 h-9 flex items-center justify-center rounded-lg
                                 border border-amber-500/30 bg-amber-500/10 text-amber-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>

                <p class="text-3xl font-bold text-amber-500 mt-3 tabular-nums">
                    {{ $garantiasPorVencer ?? 0 }}
                </p>

                <p class="text-xs text-muted mt-2">
                    Próximos 30 días
                </p>
            </div>
        </div>

        {{-- Resumen financiero --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="bg-surface border border-line rounded-xl p-5 shadow-soft">

                <p class="text-xs font-semibold uppercase tracking-wider
                          text-muted">
                    Importe total vendido
                </p>

                <p class="text-xl font-bold text-ink mt-2 tabular-nums">
                    Bs {{ number_format((float) ($resumenVentas->total_bs ?? 0), 2, ',', '.') }}
                </p>
            </div>

            <div class="bg-surface border border-line rounded-xl p-5 shadow-soft">

                <p class="text-xs font-semibold uppercase tracking-wider
                          text-muted">
                    Importe pagado
                </p>

                <p class="text-xl font-bold text-brand-green mt-2 tabular-nums">
                    Bs {{ number_format((float) ($resumenVentas->pagado_bs ?? 0), 2, ',', '.') }}
                </p>
            </div>
        </div>
    </section>

    {{-- Historial de cotizaciones --}}
    <section class="bg-surface border border-line rounded-xl shadow-soft overflow-hidden">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between
                    gap-3 px-5 py-4
                    border-b border-line">

            <div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-muted"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M8 7h8m-8 4h8m-8 4h5M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                    </svg>

                    <h2 class="font-semibold text-ink">
                        Historial de cotizaciones
                    </h2>
                </div>

                <p class="text-xs text-muted mt-1">
                    Últimas 10 cotizaciones del cliente
                </p>
            </div>

            <a href="{{ route('cotizaciones.index', ['id_cliente' => $cliente->id_cliente]) }}"
                class="text-sm font-semibold text-brand-blue hover:text-brand-blue-dark transition">
                Ver todas las cotizaciones →
            </a>
        </div>

        @if ($cotizaciones->isEmpty())
        <div class="px-6 py-14 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-2xl
                        bg-surface-2 text-muted
                        flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="1.6"
                        d="M8 7h8m-8 4h8m-8 4h5M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                </svg>
            </div>

            <h3 class="font-semibold text-ink">
                No hay cotizaciones registradas
            </h3>

            <p class="text-sm text-muted mt-1">
                Las cotizaciones de este cliente aparecerán aquí.
            </p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-[900px] w-full text-sm text-left">
                <thead class="bg-surface-2 text-muted text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-4 font-semibold">N.º</th>
                        <th class="px-4 py-4 font-semibold">Fecha</th>
                        <th class="px-4 py-4 font-semibold">Estado</th>
                        <th class="px-4 py-4 font-semibold">Vigencia</th>
                        <th class="px-4 py-4 text-right font-semibold">Total USD</th>
                        <th class="px-4 py-4 text-right font-semibold">Total Bs</th>
                        <th class="px-4 py-4 text-right font-semibold">Acción</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-line">
                    @foreach ($cotizaciones as $cotizacion)
                    @php
                    $condicion = $cotizacion->condicionTemporal();

                    $condicionClases = match ($condicion) {
                    'VIGENTE' => 'border border-brand-green/20 bg-brand-green/10 text-brand-green',
                    'VENCIDA' => 'border border-amber-500/30 bg-amber-500/10 text-amber-500',
                    default => 'border border-line bg-surface-2 text-muted',
                    };

                    $estadoClases = match ($cotizacion->estado) {
                    'EMITIDA' => 'border border-brand-blue/20 bg-brand-blue/10 text-brand-blue',
                    'CONVERTIDA' => 'border border-brand-green/20 bg-brand-green/10 text-brand-green',
                    'ANULADA' => 'border border-red-500/30 bg-red-500/10 text-red-500',
                    default => 'border border-line bg-surface-2 text-muted',
                    };
                    @endphp

                    <tr class="hover:bg-surface-2 transition-colors">
                        <td class="px-4 py-3 font-semibold text-ink">
                            #{{ $cotizacion->id_cotizacion }}
                        </td>

                        <td class="px-4 py-3 text-muted whitespace-nowrap">
                            {{ $cotizacion->fecha_emision?->format('d/m/Y') ?? '—' }}
                        </td>

                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                                     text-xs font-semibold border {{ $estadoClases }}">
                                {{ $cotizacion->estado }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                                     text-xs font-semibold border {{ $condicionClases }}">
                                {{ $condicion }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right text-muted tabular-nums whitespace-nowrap">
                            $ {{ number_format($cotizacion->totalUsd(), 2, ',', '.') }}
                        </td>

                        <td class="px-4 py-3 text-right text-muted tabular-nums whitespace-nowrap">
                            Bs {{ number_format($cotizacion->totalBs(), 2, ',', '.') }}
                        </td>

                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('cotizaciones.show', $cotizacion) }}"
                                class="text-sm font-semibold text-brand-blue hover:text-brand-blue-dark transition">
                                Ver
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </section>

    {{-- Historial de ventas --}}
    <section class="bg-surface border border-line rounded-xl shadow-soft overflow-hidden">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between
                    gap-3 px-5 py-4
                    border-b border-line">

            <div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-muted"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M3 3h2l2.4 12.5a2 2 0 0 0 2 1.5h8.8a2 2 0 0 0 2-1.6L22 8H6m4 13a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
                    </svg>

                    <h2 class="font-semibold text-ink">
                        Historial de ventas
                    </h2>
                </div>

                <p class="text-xs text-muted mt-1">
                    Últimas 10 operaciones comerciales
                </p>
            </div>

            <a href="{{ route('ventas.index', ['buscar' => $cliente->nombre]) }}"
                class="text-sm font-semibold text-brand-blue hover:text-brand-blue-dark transition">
                Ver todas las ventas →
            </a>
        </div>

        @if ($ventas->isEmpty())
        <div class="px-6 py-14 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-2xl
                        bg-surface-2 text-muted
                        flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="1.6"
                        d="M3 3h2l2.4 12.5a2 2 0 0 0 2 1.5h8.8a2 2 0 0 0 2-1.6L22 8" />
                </svg>
            </div>

            <h3 class="font-semibold text-ink">
                No hay ventas registradas
            </h3>

            <p class="text-sm text-muted mt-1">
                Las operaciones comerciales de este cliente aparecerán aquí.
            </p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-[950px] w-full text-sm text-left">
                <thead class="bg-surface-2 text-muted text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-4 font-semibold">N.º</th>
                        <th class="px-4 py-4 font-semibold">Fecha</th>
                        <th class="px-4 py-4 font-semibold">Estado</th>
                        <th class="px-4 py-4 text-right font-semibold">Total Bs</th>
                        <th class="px-4 py-4 text-right font-semibold">Pagado Bs</th>
                        <th class="px-4 py-4 text-right font-semibold">Saldo Bs</th>
                        <th class="px-4 py-4 text-right font-semibold">Acción</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-line">
                    @foreach ($ventas as $venta)
                    @php
                    $estadoVentaClases = match ($venta->estado) {
                    'REGISTRADA' => 'border border-brand-green/20 bg-brand-green/10 text-brand-green',
                    'EN_PROCESO' => 'border border-brand-blue/20 bg-brand-blue/10 text-brand-blue',
                    'CANCELADA' => 'border border-red-500/30 bg-red-500/10 text-red-500',
                    default => 'border border-line bg-surface-2 text-muted',
                    };

                    $totalVenta = (float) ($venta->total_bs ?? 0);
                    $pagadoVenta = (float) ($venta->total_pagado_bs ?? 0);
                    $saldoVenta = $totalVenta - $pagadoVenta;
                    @endphp

                    <tr class="hover:bg-surface-2 transition-colors">
                        <td class="px-4 py-3 font-semibold text-ink">
                            #{{ $venta->id_venta }}
                        </td>

                        <td class="px-4 py-3 text-muted whitespace-nowrap">
                            {{ $venta->fecha_hora?->format('d/m/Y H:i') ?? '—' }}
                        </td>

                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                                     text-xs font-semibold border {{ $estadoVentaClases }}">
                                {{ $venta->estado }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right text-muted tabular-nums whitespace-nowrap">
                            Bs {{ number_format($totalVenta, 2, ',', '.') }}
                        </td>

                        <td class="px-4 py-3 text-right text-brand-green tabular-nums whitespace-nowrap">
                            Bs {{ number_format($pagadoVenta, 2, ',', '.') }}
                        </td>

                        <td class="px-4 py-3 text-right font-semibold tabular-nums whitespace-nowrap
                                    {{ $saldoVenta > 0 ? 'text-amber-500' : 'text-muted' }}">
                            Bs {{ number_format($saldoVenta, 2, ',', '.') }}
                        </td>

                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('ventas.show', $venta) }}"
                                class="text-sm font-semibold text-brand-blue hover:text-brand-blue-dark transition">
                                Ver
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </section>

    {{-- Garantías activas --}}
    <section class="bg-surface border border-line rounded-xl shadow-soft overflow-hidden">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between
                    gap-3 px-5 py-4
                    border-b border-line">

            <div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-muted"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>

                    <h2 class="font-semibold text-ink">
                        Garantías activas
                    </h2>
                </div>

                <p class="text-xs text-muted mt-1">
                    Garantías vigentes y por vencer en los próximos 30 días
                </p>
            </div>

            @if ($garantiasActivas->count() > 0)
            <a href="{{ route('ventas.index', ['buscar' => $cliente->nombre]) }}"
                class="text-sm font-semibold text-brand-blue hover:text-brand-blue-dark transition">
                Ver todas las ventas →
            </a>
            @endif
        </div>

        @if ($garantiasActivas->isEmpty())
        <div class="px-6 py-14 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-2xl
                        bg-surface-2 text-muted
                        flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="1.6"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h3 class="font-semibold text-ink">
                Sin garantías activas
            </h3>

            <p class="text-sm text-muted mt-1">
                Este cliente no tiene garantías vigentes ni por vencer.
            </p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-[950px] w-full text-sm text-left">
                <thead class="bg-surface-2 text-muted text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-4 font-semibold">Garantía</th>
                        <th class="px-4 py-4 font-semibold">Producto / SKU</th>
                        <th class="px-4 py-4 font-semibold">Fecha inicio</th>
                        <th class="px-4 py-4 font-semibold">Vencimiento</th>
                        <th class="px-4 py-4 font-semibold text-center">Días restantes</th>
                        <th class="px-4 py-4 font-semibold">Venta</th>
                        <th class="px-4 py-4 font-semibold">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($garantiasActivas as $g)
                    @php
                    $diasRestantes = \Carbon\Carbon::parse($g->fecha_vencimiento)->diffInDays(now());
                    $estadoClase = $diasRestantes <= 7
                        ? 'border border-amber-500/30 bg-amber-500/10 text-amber-500'
                        : 'border border-brand-green/20 bg-brand-green/10 text-brand-green';
                    $estadoTexto = $diasRestantes <= 7 ? 'Por vencer' : 'Vigente';
                    @endphp

                    <tr class="hover:bg-surface-2 transition-colors">
                        <td class="px-4 py-3 font-semibold text-ink">
                            #{{ $g->id_garantia }}
                        </td>

                        <td class="px-4 py-3 text-muted">
                            {{ $g->producto }}
                            <span class="text-xs text-muted block">({{ $g->sku }})</span>
                        </td>

                        <td class="px-4 py-3 text-muted whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($g->fecha_inicio)->format('d/m/Y') }}
                        </td>

                        <td class="px-4 py-3 text-muted whitespace-nowrap
                                {{ $diasRestantes <= 7 ? 'font-bold text-amber-500' : '' }}">
                            {{ \Carbon\Carbon::parse($g->fecha_vencimiento)->format('d/m/Y') }}
                        </td>

                        <td class="px-4 py-3 text-center text-sm font-mono
                                {{ $diasRestantes <= 7 ? 'text-amber-500 font-bold' : 'text-muted' }}">
                            {{ $diasRestantes }} días
                        </td>

                        <td class="px-4 py-3 text-muted font-mono">
                            #{{ $g->id_venta }}
                        </td>

                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                                     text-xs font-semibold border {{ $estadoClase }}">
                                {{ $estadoTexto }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </section>

    {{-- Solicitudes de servicio técnico --}}
    <section class="bg-surface border border-line rounded-xl shadow-soft overflow-hidden">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between
                    gap-3 px-5 py-4
                    border-b border-line">

            <div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-muted"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>

                    <h2 class="font-semibold text-ink">
                        Solicitudes de servicio técnico
                    </h2>
                </div>

                <p class="text-xs text-muted mt-1">
                    Historial de solicitudes de servicio asociadas a este cliente
                </p>
            </div>

            <a href="{{ route('serviciotecnico.publico.panel') }}"
                class="text-sm font-semibold text-brand-blue hover:text-brand-blue-dark transition">
                Gestión de servicio →
            </a>
        </div>

        @if ($solicitudesServicio->isEmpty())
        <div class="px-6 py-14 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-2xl
                        bg-surface-2 text-muted
                        flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="1.6"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h3 class="font-semibold text-ink">
                Sin solicitudes de servicio
            </h3>

            <p class="text-sm text-muted mt-1">
                Este cliente no tiene solicitudes de servicio técnico registradas.
            </p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-[950px] w-full text-sm text-left">
                <thead class="bg-surface-2 text-muted text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-4 font-semibold">Solicitud</th>
                        <th class="px-4 py-4 font-semibold">Fecha</th>
                        <th class="px-4 py-4 font-semibold">Falla</th>
                        <th class="px-4 py-4 font-semibold text-center">Estado</th>
                        <th class="px-4 py-4 font-semibold">Técnico</th>
                        <th class="px-4 py-4 font-semibold">Resultado</th>
                        <th class="px-4 py-4 font-semibold text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($solicitudesServicio as $s)
                    @php
                    $estadoClase = match ($s->estado) {
                        'PENDIENTE' => 'border border-amber-500/30 bg-amber-500/10 text-amber-500',
                        'PROGRAMADA' => 'border border-brand-blue/20 bg-brand-blue/10 text-brand-blue',
                        'CERRADA' => 'border border-brand-green/20 bg-brand-green/10 text-brand-green',
                        'CANCELADA' => 'border border-line bg-surface-2 text-muted',
                        default => 'border border-line bg-surface-2 text-muted',
                    };
                    $resultadoTexto = $s->resultado
                        ? \Str::ucfirst(\Str::replace('_', ' ', $s->resultado))
                        : '—';
                    @endphp

                    <tr class="hover:bg-surface-2 transition-colors">
                        <td class="px-4 py-3 font-semibold text-ink font-mono">
                            #{{ $s->id_solicitud }}
                        </td>

                        <td class="px-4 py-3 text-muted whitespace-nowrap font-mono">
                            {{ \Carbon\Carbon::parse($s->fecha_solicitud)->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-4 py-3 text-muted">
                            {{ \Illuminate\Support\Str::limit($s->descripcion_falla, 60) }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                                     text-xs font-semibold border {{ $estadoClase }}">
                                {{ $s->estado }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-muted">
                            {{ data_get($s, 'tecnico.nombre_completo', 'Sin asignar') }}
                        </td>

                        <td class="px-4 py-3 text-muted text-sm">
                            {{ $resultadoTexto }}
                        </td>

                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('serviciotecnico.show', $s->id_solicitud) }}"
                                class="text-sm font-semibold text-brand-blue hover:text-brand-blue-dark transition">
                                Ver detalle
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </section>

</div>
@endsection
