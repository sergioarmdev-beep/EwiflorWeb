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
                       text-slate-500 hover:text-sky-700
                       dark:text-slate-400 dark:hover:text-sky-400 transition mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Volver a clientes
            </a>

            <div class="flex items-center gap-3">
                <span class="w-10 h-10 flex items-center justify-center rounded-xl
                             bg-sky-100 text-sky-700
                             dark:bg-sky-500/10 dark:text-sky-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M17 20h5v-2a4 4 0 0 0-5.5-3.7M9 20H2v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2H9Zm0-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-1a3 3 0 1 0 0-6" />
                    </svg>
                </span>

                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                        {{ $cliente->nombre }}
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Detalle e historial comercial del cliente
                    </p>
                </div>
            </div>
        </div>

        @if ($puedeGestionarClientes)
        <a href="{{ route('clientes.edit', $cliente) }}"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5
                       rounded-lg bg-sky-600 hover:bg-sky-700
                       dark:bg-sky-600 dark:hover:bg-sky-500
                       text-white text-sm font-semibold shadow-sm transition">
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
    <section class="bg-white dark:bg-slate-900
                    border border-slate-200 dark:border-slate-800
                    rounded-xl shadow-sm overflow-hidden">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between
                    gap-3 px-5 py-4
                    border-b border-slate-200 dark:border-slate-800">

            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-slate-500 dark:text-slate-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m16-10a4 4 0 0 0 0-8m4 18v-2a4 4 0 0 0-3-3.87M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
                </svg>

                <h2 class="font-semibold text-slate-900 dark:text-slate-100">
                    Información del cliente
                </h2>
            </div>

            <span class="text-xs text-slate-500 dark:text-slate-400">
                Código: #{{ $cliente->id_cliente }}
            </span>
        </div>

        <div class="p-5">
            <div class="flex flex-wrap items-center gap-2 mb-5">

                @if ($cliente->estado === 'ACTIVO')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1
                                 rounded-full text-xs font-semibold
                                 bg-emerald-100 text-emerald-800
                                 border border-emerald-200
                                 dark:bg-emerald-500/10 dark:text-emerald-400
                                 dark:border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Activo
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1
                                 rounded-full text-xs font-semibold
                                 bg-red-100 text-red-800
                                 border border-red-200
                                 dark:bg-red-500/10 dark:text-red-400
                                 dark:border-red-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    Inactivo
                </span>
                @endif

                @if ($cliente->es_mayorista)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                 text-xs font-semibold
                                 bg-violet-100 text-violet-800
                                 border border-violet-200
                                 dark:bg-violet-500/10 dark:text-violet-400
                                 dark:border-violet-500/20">
                    Mayorista
                </span>
                @else
                <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                 text-xs font-semibold
                                 bg-slate-100 text-slate-700
                                 border border-slate-200
                                 dark:bg-slate-700/50 dark:text-slate-300
                                 dark:border-slate-600">
                    Minorista
                </span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider
                              text-slate-500 dark:text-slate-400 mb-1.5">
                        Nombre completo
                    </p>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                        {{ $cliente->nombre }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider
                              text-slate-500 dark:text-slate-400 mb-1.5">
                        Teléfono
                    </p>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                        {{ $cliente->telefono ?: 'No registrado' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider
                              text-slate-500 dark:text-slate-400 mb-1.5">
                        Correo electrónico
                    </p>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300 break-all">
                        {{ $cliente->correo ?: 'No registrado' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider
                              text-slate-500 dark:text-slate-400 mb-1.5">
                        Tipo de cliente
                    </p>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                        {{ str_replace('_', ' ', $cliente->tipo_cliente) }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider
                              text-slate-500 dark:text-slate-400 mb-1.5">
                        Fecha de registro
                    </p>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                        {{ $cliente->fecha_registro?->format('d/m/Y') ?? 'No disponible' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Resumen comercial --}}
    <section class="space-y-4">

        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-slate-500 dark:text-slate-400"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="1.8"
                    d="M3 3v18h18M18 17V9m-5 8V5m-5 12v-5" />
            </svg>

            <div>
                <h2 class="font-semibold text-slate-900 dark:text-slate-100">
                    Resumen comercial
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Indicadores acumulados de la actividad del cliente
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            {{-- Cotizaciones emitidas --}}
            <div class="bg-white dark:bg-slate-900
                        border border-slate-200 dark:border-slate-800
                        rounded-xl p-5 shadow-sm">

                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                        Cotizaciones emitidas
                    </p>

                    <span class="w-9 h-9 flex items-center justify-center rounded-lg
                                 bg-sky-100 text-sky-700
                                 dark:bg-sky-500/10 dark:text-sky-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M8 7h8m-8 4h8m-8 4h5M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                        </svg>
                    </span>
                </div>

                <p class="text-3xl font-bold text-slate-900 dark:text-slate-100 mt-3 tabular-nums">
                    {{ $resumenCotizaciones->emitidas ?? 0 }}
                </p>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                    Total: {{ $resumenCotizaciones->total ?? 0 }}
                </p>
            </div>

            {{-- Cotizaciones convertidas --}}
            <div class="bg-white dark:bg-slate-900
                        border border-slate-200 dark:border-slate-800
                        rounded-xl p-5 shadow-sm">

                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                        Cotizaciones convertidas
                    </p>

                    <span class="w-9 h-9 flex items-center justify-center rounded-lg
                                 bg-emerald-100 text-emerald-700
                                 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="1.8"
                                d="m5 12 4 4L19 6" />
                        </svg>
                    </span>
                </div>

                <p class="text-3xl font-bold text-slate-900 dark:text-slate-100 mt-3 tabular-nums">
                    {{ $resumenCotizaciones->convertidas ?? 0 }}
                </p>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                    Anuladas: {{ $resumenCotizaciones->anuladas ?? 0 }}
                </p>
            </div>

            {{-- Ventas registradas --}}
            <div class="bg-white dark:bg-slate-900
                        border border-slate-200 dark:border-slate-800
                        rounded-xl p-5 shadow-sm">

                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                        Ventas registradas
                    </p>

                    <span class="w-9 h-9 flex items-center justify-center rounded-lg
                                 bg-violet-100 text-violet-700
                                 dark:bg-violet-500/10 dark:text-violet-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M3 3h2l2.4 12.5a2 2 0 0 0 2 1.5h8.8a2 2 0 0 0 2-1.6L22 8H6m4 13a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
                        </svg>
                    </span>
                </div>

                <p class="text-3xl font-bold text-slate-900 dark:text-slate-100 mt-3 tabular-nums">
                    {{ $resumenVentas->total ?? 0 }}
                </p>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                    Operaciones efectivas
                </p>
            </div>

            {{-- Saldo pendiente --}}
            <div class="bg-white dark:bg-slate-900
                        border border-slate-200 dark:border-slate-800
                        rounded-xl p-5 shadow-sm">

                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                        Saldo pendiente
                    </p>

                    <span class="w-9 h-9 flex items-center justify-center rounded-lg
                                 bg-amber-100 text-amber-700
                                 dark:bg-amber-500/10 dark:text-amber-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>
                </div>

                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-3 tabular-nums">
                    Bs {{ number_format((float) ($resumenVentas->saldo_bs ?? 0), 2, ',', '.') }}
                </p>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                    Por cobrar
                </p>
            </div>
        </div>

        {{-- Resumen financiero --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="bg-white dark:bg-slate-900
                        border border-slate-200 dark:border-slate-800
                        rounded-xl p-5 shadow-sm">

                <p class="text-xs font-semibold uppercase tracking-wider
                          text-slate-500 dark:text-slate-400">
                    Importe total vendido
                </p>

                <p class="text-xl font-bold text-slate-900 dark:text-slate-100 mt-2 tabular-nums">
                    Bs {{ number_format((float) ($resumenVentas->total_bs ?? 0), 2, ',', '.') }}
                </p>
            </div>

            <div class="bg-white dark:bg-slate-900
                        border border-slate-200 dark:border-slate-800
                        rounded-xl p-5 shadow-sm">

                <p class="text-xs font-semibold uppercase tracking-wider
                          text-slate-500 dark:text-slate-400">
                    Importe pagado
                </p>

                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-2 tabular-nums">
                    Bs {{ number_format((float) ($resumenVentas->pagado_bs ?? 0), 2, ',', '.') }}
                </p>
            </div>
        </div>
    </section>

    {{-- Historial de cotizaciones --}}
    <section class="bg-white dark:bg-slate-900
                    border border-slate-200 dark:border-slate-800
                    rounded-xl shadow-sm overflow-hidden">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between
                    gap-3 px-5 py-4
                    border-b border-slate-200 dark:border-slate-800">

            <div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-500 dark:text-slate-400"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M8 7h8m-8 4h8m-8 4h5M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                    </svg>

                    <h2 class="font-semibold text-slate-900 dark:text-slate-100">
                        Historial de cotizaciones
                    </h2>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Últimas 10 cotizaciones del cliente
                </p>
            </div>

            <a href="{{ route('cotizaciones.index', ['id_cliente' => $cliente->id_cliente]) }}"
                class="text-sm font-semibold text-sky-700 hover:text-sky-900
                       dark:text-sky-400 dark:hover:text-sky-300 transition">
                Ver todas las cotizaciones →
            </a>
        </div>

        @if ($cotizaciones->isEmpty())
        <div class="px-6 py-14 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-2xl
                            bg-slate-100 dark:bg-slate-800
                            text-slate-500 dark:text-slate-400
                            flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="1.6"
                        d="M8 7h8m-8 4h8m-8 4h5M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                </svg>
            </div>

            <h3 class="font-semibold text-slate-800 dark:text-slate-200">
                No hay cotizaciones registradas
            </h3>

            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Las cotizaciones de este cliente aparecerán aquí.
            </p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-[900px] w-full text-sm text-left">
                <thead class="bg-slate-100 dark:bg-slate-800/80
                                  text-xs uppercase tracking-wider
                                  text-slate-600 dark:text-slate-400">
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

                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach ($cotizaciones as $cotizacion)
                    @php
                    $condicion = $cotizacion->condicionTemporal();

                    $condicionClases = match ($condicion) {
                    'VIGENTE' => 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
                    'VENCIDA' => 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
                    default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-700/50 dark:text-slate-300 dark:border-slate-600',
                    };

                    $estadoClases = match ($cotizacion->estado) {
                    'EMITIDA' => 'bg-sky-100 text-sky-800 border-sky-200 dark:bg-sky-500/10 dark:text-sky-400 dark:border-sky-500/20',
                    'CONVERTIDA' => 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
                    'ANULADA' => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20',
                    default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-700/50 dark:text-slate-300 dark:border-slate-600',
                    };
                    @endphp

                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">
                            #{{ $cotizacion->id_cotizacion }}
                        </td>

                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300 whitespace-nowrap">
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

                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 tabular-nums whitespace-nowrap">
                            $ {{ number_format($cotizacion->totalUsd(), 2, ',', '.') }}
                        </td>

                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 tabular-nums whitespace-nowrap">
                            Bs {{ number_format($cotizacion->totalBs(), 2, ',', '.') }}
                        </td>

                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('cotizaciones.show', $cotizacion) }}"
                                class="text-sm font-semibold text-sky-700 hover:text-sky-900
                                               dark:text-sky-400 dark:hover:text-sky-300 transition">
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
    <section class="bg-white dark:bg-slate-900
                    border border-slate-200 dark:border-slate-800
                    rounded-xl shadow-sm overflow-hidden">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between
                    gap-3 px-5 py-4
                    border-b border-slate-200 dark:border-slate-800">

            <div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-500 dark:text-slate-400"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M3 3h2l2.4 12.5a2 2 0 0 0 2 1.5h8.8a2 2 0 0 0 2-1.6L22 8H6m4 13a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
                    </svg>

                    <h2 class="font-semibold text-slate-900 dark:text-slate-100">
                        Historial de ventas
                    </h2>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Últimas 10 operaciones comerciales
                </p>
            </div>

            <a href="{{ route('ventas.index', ['buscar' => $cliente->nombre]) }}"
                class="text-sm font-semibold text-sky-700 hover:text-sky-900
                       dark:text-sky-400 dark:hover:text-sky-300 transition">
                Ver todas las ventas →
            </a>
        </div>

        @if ($ventas->isEmpty())
        <div class="px-6 py-14 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-2xl
                            bg-slate-100 dark:bg-slate-800
                            text-slate-500 dark:text-slate-400
                            flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="1.6"
                        d="M3 3h2l2.4 12.5a2 2 0 0 0 2 1.5h8.8a2 2 0 0 0 2-1.6L22 8" />
                </svg>
            </div>

            <h3 class="font-semibold text-slate-800 dark:text-slate-200">
                No hay ventas registradas
            </h3>

            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Las operaciones comerciales de este cliente aparecerán aquí.
            </p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-[950px] w-full text-sm text-left">
                <thead class="bg-slate-100 dark:bg-slate-800/80
                                  text-xs uppercase tracking-wider
                                  text-slate-600 dark:text-slate-400">
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

                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach ($ventas as $venta)
                    @php
                    $estadoVentaClases = match ($venta->estado) {
                    'REGISTRADA' => 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
                    'EN_PROCESO' => 'bg-sky-100 text-sky-800 border-sky-200 dark:bg-sky-500/10 dark:text-sky-400 dark:border-sky-500/20',
                    'CANCELADA' => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20',
                    default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-700/50 dark:text-slate-300 dark:border-slate-600',
                    };

                    $totalVenta = (float) ($venta->total_bs ?? 0);
                    $pagadoVenta = (float) ($venta->total_pagado_bs ?? 0);
                    $saldoVenta = $totalVenta - $pagadoVenta;
                    @endphp

                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">
                            #{{ $venta->id_venta }}
                        </td>

                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300 whitespace-nowrap">
                            {{ $venta->fecha_hora?->format('d/m/Y H:i') ?? '—' }}
                        </td>

                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                                 text-xs font-semibold border {{ $estadoVentaClases }}">
                                {{ $venta->estado }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 tabular-nums whitespace-nowrap">
                            Bs {{ number_format($totalVenta, 2, ',', '.') }}
                        </td>

                        <td class="px-4 py-3 text-right text-emerald-700 dark:text-emerald-400 tabular-nums whitespace-nowrap">
                            Bs {{ number_format($pagadoVenta, 2, ',', '.') }}
                        </td>

                        <td class="px-4 py-3 text-right font-semibold tabular-nums whitespace-nowrap
                                    {{ $saldoVenta > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-700 dark:text-slate-300' }}">
                            Bs {{ number_format($saldoVenta, 2, ',', '.') }}
                        </td>

                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('ventas.show', $venta) }}"
                                class="text-sm font-semibold text-sky-700 hover:text-sky-900
                                               dark:text-sky-400 dark:hover:text-sky-300 transition">
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

</div>
@endsection