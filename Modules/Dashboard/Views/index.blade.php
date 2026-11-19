@extends('layouts.app')

@section('title', 'Panel de Control')

@php
$rol = $stats['rol'] ?? auth()->user()->nombre_rol;

$esGerencia = $esGerencia ?? false;
$esVendedor = $esVendedor ?? false;
$esTecnico = $esTecnico ?? false;

$ventas = $stats['ventas'] ?? [];
$vc = $stats['ventas_comparativo'] ?? [];
$tc = $stats['tipo_cambio'] ?? [];

// Sistema visual del layout.
$panel = 'rounded-2xl border border-line bg-surface shadow-soft transition-all duration-200';
$muted = 'text-muted';
$label = 'text-[11px] font-semibold uppercase tracking-wider text-muted';
$title = 'text-sm font-bold text-ink';
$sub = 'mt-1 text-xs text-muted';
$primaryText = 'text-brand-green';
$primaryBg = 'bg-brand-green';
$island = 'rounded-xl border border-line bg-surface-2/50';
@endphp

@section('content')
<div class="min-h-full bg-canvas text-ink">

    <div class="mx-auto max-w-[1600px] space-y-6 px-4 py-6 sm:px-6 lg:px-8">

        {{-- =========================================================
             ENCABEZADO
        ========================================================== --}}
        <header class="{{ $panel }} flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">

            <div class="min-w-0">
                <div class="mb-3 flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-brand-green"></span>

                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-brand-green">
                        EwiflorWeb
                    </p>

                    <span class="text-muted">/</span>

                    <span class="text-[11px] font-medium text-muted">
                        Panel de control
                    </span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">
                    Hola, {{ auth()->user()->nombre_completo }}
                </h1>

                <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-muted">
                    <span>
                        {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
                    </span>

                    <span class="hidden text-muted sm:inline">·</span>

                    <span class="font-semibold text-ink">
                        {{ str_replace('_', ' ', $rol) }}
                    </span>
                </div>
            </div>


        </header>


        {{-- =========================================================
             ALERTAS GLOBALES
        ========================================================== --}}
        @if ($esGerencia && !empty($stats['alertas_sistema']))

        <section class="space-y-3" aria-label="Alertas del sistema">

            <div class="flex items-center gap-2 px-1">
                <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v3m0 4h.01M10.3 3.86 2.8 17a2 2 0 0 0 1.74 3h14.92a2 2 0 0 0 1.74-3l-7.46-13.14a2 2 0 0 0-3.48 0Z" />
                </svg>

                <h2 class="text-xs font-bold uppercase tracking-wider text-ink">
                    Alertas del sistema
                </h2>

                <span class="rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-600 dark:text-amber-400">
                    {{ count($stats['alertas_sistema']) }}
                </span>
            </div>

            <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                @foreach ($stats['alertas_sistema'] as $alerta)

                <article class="group flex flex-col justify-between gap-4 rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4 transition-all duration-200 hover:border-amber-500/40 hover:bg-amber-500/10 sm:flex-row sm:items-center">

                    <div class="flex min-w-0 items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v3m0 4h.01M10.3 3.86 2.8 17a2 2 0 0 0 1.74 3h14.92a2 2 0 0 0 1.74-3l-7.46-13.14a2 2 0 0 0-3.48 0Z" />
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-ink">
                                {{ $alerta['titulo'] }}
                            </h3>

                            <p class="mt-1 text-xs leading-relaxed text-muted">
                                {{ $alerta['mensaje'] }}
                            </p>
                        </div>
                    </div>

                    <a href="{{ $alerta['url'] }}"
                        class="inline-flex shrink-0 items-center gap-2 self-start rounded-lg border border-amber-500/20 bg-amber-500/10 px-3 py-2 text-xs font-bold text-amber-700 transition-colors hover:bg-amber-500/20 dark:text-amber-300 sm:self-center">

                        {{ $alerta['url_texto'] }}

                        <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </article>

                @endforeach
            </div>
        </section>
        @endif


        {{-- =========================================================
             NAVEGACIÓN Y CONTENIDO
        ========================================================== --}}
        <div
            x-data="{
                tab: 'resumen',
                ready: { resumen: false },

                activate(name) {
                    this.tab = name;

                    if (!this.ready[name]) {
                        this.ready[name] = false;

                        this.$nextTick(() => {
                            this.ready[name] = true;
                        });
                    }
                }
            }"
            x-init="$nextTick(() => ready.resumen = true)"
            class="space-y-5">

            {{-- Pestañas como islas --}}
            <nav class="rounded-2xl border border-line bg-surface p-2 shadow-soft"
                aria-label="Secciones del panel">

                <div class="flex flex-wrap gap-2">

                    @php
                    $tabs = [
                    'resumen' => ['01', 'Resumen ejecutivo'],
                    'alertas' => ['02', 'Centro de alertas'],
                    'comercial' => ['03', 'Inteligencia comercial'],
                    'inventario' => ['04', 'Inteligencia de inventario'],
                    'financiero' => ['05', 'Resumen financiero'],
                    'acciones' => ['06', 'Acciones rápidas'],
                    'actividad' => ['07', 'Actividad empresarial'],
                    'operativo' => ['08', 'Seguimiento operativo'],
                    'comparativo' => ['09', 'Comparativa de ventas'],
                    ];
                    @endphp

                    @foreach ($tabs as $clave => $tabInfo)
                    @if (in_array($clave, $tabsPermitidas))

                    <button
                        type="button"
                        x-on:click="activate('{{ $clave }}')"
                        :aria-selected="tab === '{{ $clave }}'"
                        :class="tab === '{{ $clave }}'
                                    ? 'border-brand-green bg-brand-green text-white shadow-sm'
                                    : 'border-line bg-surface-2/50 text-muted hover:border-brand-green/40 hover:bg-brand-green/5 hover:text-brand-green'"
                        class="inline-flex min-h-10 items-center gap-2 rounded-xl border px-3 py-2 text-xs font-semibold transition-all duration-200 sm:px-4">

                        <span
                            :class="tab === '{{ $clave }}'
                                        ? 'text-white/70'
                                        : 'text-muted'"
                            class="text-[10px] font-bold tabular-nums">
                            {{ $tabInfo[0] }}
                        </span>

                        <span>{{ $tabInfo[1] }}</span>
                    </button>

                    @endif
                    @endforeach

                </div>
            </nav>


            {{-- =====================================================
                 PARTIALS
            ====================================================== --}}
            @php
            $tabsContent = [
            'resumen' => 'dashboard::partials.resumen-ejecutivo',
            'alertas' => 'dashboard::partials.centro-alertas',
            'comercial' => 'dashboard::partials.inteligencia-comercial',
            'inventario' => 'dashboard::partials.inteligencia-inventario',
            'financiero' => 'dashboard::partials.resumen-financiero',
            'acciones' => 'dashboard::partials.acciones-rapidas',
            'actividad' => 'dashboard::partials.actividad-empresarial',
            'operativo' => 'dashboard::partials.seguimiento-operativo',
            'comparativo' => 'dashboard::partials.ventas-lineas',
            ];
            @endphp

            @foreach ($tabsPermitidas as $tp)
            @php
            $partial = $tabsContent[$tp] ?? null;
            @endphp

            @if ($partial)

            <section
                x-show="tab === '{{ $tp }}'"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-init="if (tab === '{{ $tp }}') $nextTick(() => ready['{{ $tp }}'] = true)"
                class="min-w-0"
                aria-label="{{ $tabs[$tp][1] ?? $tp }}">

                {{-- Skeleton de inicialización --}}
                <div
                    x-show="!ready['{{ $tp }}']"
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3"
                    aria-hidden="true">
                    @for ($i = 0; $i < 3; $i++)
                        <div class="animate-pulse rounded-2xl border border-line bg-surface p-5">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-surface-2"></div>

                            <div class="flex-1 space-y-2">
                                <div class="h-3 w-2/5 rounded bg-surface-2"></div>
                                <div class="h-2.5 w-3/5 rounded bg-surface-2"></div>
                            </div>
                        </div>

                        <div class="mt-6 h-8 w-1/2 rounded-lg bg-surface-2"></div>
                        <div class="mt-4 h-2 w-full rounded bg-surface-2"></div>
                        <div class="mt-2 h-2 w-4/5 rounded bg-surface-2"></div>
                </div>
                @endfor
        </div>

        {{-- Contenido real --}}
        <div x-show="ready['{{ $tp }}']" x-cloak>
            @include($partial, [
            'stats' => $stats,
            'esGerencia' => $esGerencia,
            'esVendedor' => $esVendedor,
            'esTecnico' => $esTecnico
            ])
        </div>

        </section>

        @endif
        @endforeach

    </div>


    {{-- =========================================================
             PIE
        ========================================================== --}}
    <footer class="flex flex-col items-center justify-between gap-2 border-t border-line pt-5 text-[10px] text-muted sm:flex-row">
        <span class="font-semibold tracking-wide">
            EwiflorWeb · Panel de control
        </span>

        <span>
            Sistema de gestión empresarial
        </span>
    </footer>

</div>
</div>
@endsection