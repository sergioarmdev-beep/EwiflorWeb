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

                    this.$nextTick(() => {
                        const map = { comercial: 'chartVentasMensuales', financiero: 'chartTipoCambio', comparativo: 'chartVentasLineas' };
                        const id = map[name];
                        if (id && window.refreshDashboardChart) window.refreshDashboardChart(id);
                    });
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
            'esTecnico' => $esTecnico,
            'meses' => $meses ?? collect(),
            'mes1' => $mes1 ?? null,
            'mes2' => $mes2 ?? null,
            'sucursales' => $sucursales ?? collect(),
            'idSucursalSeleccionada' => $idSucursalSeleccionada ?? null,
            'rol' => $rol,
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function() {
        'use strict';

        if (typeof Chart === 'undefined') return;

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const activeCharts = new Map();

        function chartTheme() {
            const isDark = document.documentElement.classList.contains('dark');
            return {
                grid: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(15,23,42,0.07)',
                tick: isDark ? '#94a3b8' : '#64748b',
                primary: '#0B6E45',
                primaryFill: isDark ? 'rgba(11,110,69,0.22)' : 'rgba(11,110,69,0.14)',
                secondary: '#a78bfa',
                secondaryFill: isDark ? 'rgba(167,139,250,0.15)' : 'rgba(167,139,250,0.10)',
                tooltipBg: isDark ? 'rgba(15,23,42,0.95)' : 'rgba(255,255,255,0.97)',
                tooltipText: isDark ? '#f1f5f9' : '#0f172a',
                tooltipBorder: isDark ? 'rgba(148,163,184,0.25)' : 'rgba(148,163,184,0.35)',
            };
        }

        const formatBs = v => 'Bs ' + v.toLocaleString('es-BO', { minimumFractionDigits: 2 });

        const initializers = {
            chartVentasMensuales(canvas) {
                const t = chartTheme();
                const data = @json((isset($stats['ventas']) ? ($stats['ventas']['ventas_por_mes'] ?? []) : []));
                const labels = data.map(d => d.mes);
                const values = data.map(d => parseFloat(d.total));

                return new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Ventas (Bs)',
                            data: values,
                            backgroundColor: t.primaryFill,
                            borderColor: t.primary,
                            borderWidth: 2,
                            borderRadius: 6,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: prefersReducedMotion ? false : { duration: 800, easing: 'easeOutQuart' },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: t.tooltipBg,
                                titleColor: t.tooltipText,
                                bodyColor: t.tooltipText,
                                borderColor: t.tooltipBorder,
                                borderWidth: 1,
                                callbacks: {
                                    label: ctx => formatBs(ctx.parsed.y)
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: t.grid },
                                ticks: { color: t.tick, font: { size: 11 }, callback: formatBs }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: t.tick, font: { size: 11 } }
                            }
                        }
                    }
                });
            },

            chartVentasLineas(canvas) {
                const t = chartTheme();
                const url = canvas.dataset.url;
                let chart = null;

                const $errorEl = () => document.getElementById('errorVentasLineas');

                const render = (datos) => {
                    const $error = $errorEl();
                    if ($error) {
                        $error.style.display = (datos && datos.error) ? 'block' : 'none';
                    }
                    if (!datos || datos.error || !datos.mes1 || !datos.mes2) {
                        if (chart) {
                            try { chart.destroy(); } catch (e) {}
                            activeCharts.delete(canvas.id);
                            chart = null;
                        }
                        return;
                    }
                    const labels = datos.mes1.dias.map(d => 'Día ' + d.dia);
                    const serie1 = datos.mes1.dias.map(d => parseFloat(d.total) || 0);
                    const serie2 = datos.mes2.dias.map(d => parseFloat(d.total) || 0);
                    const data = {
                        labels,
                        datasets: [{
                            label: datos.mes1.etiqueta,
                            data: serie1,
                            borderColor: t.primary,
                            backgroundColor: t.primaryFill,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            fill: false,
                            tension: 0.4
                        }, {
                            label: datos.mes2.etiqueta,
                            data: serie2,
                            borderColor: t.secondary,
                            backgroundColor: t.secondaryFill,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            fill: false,
                            tension: 0.4
                        }]
                    };
                    const opciones = {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: prefersReducedMotion ? false : { duration: 600, easing: 'easeOutQuart' },
                        plugins: {
                            legend: { display: true, position: 'top', labels: { color: t.tick, font: { size: 11 } } },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: t.tooltipBg,
                                titleColor: t.tooltipText,
                                bodyColor: t.tooltipText,
                                borderColor: t.tooltipBorder,
                                borderWidth: 1,
                                callbacks: {
                                    label: ctx => ctx.dataset.label + ': ' + formatBs(ctx.parsed.y)
                                }
                            }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: t.grid }, ticks: { color: t.tick, font: { size: 11 }, callback: formatBs } },
                            x: { grid: { display: false }, ticks: { color: t.tick, font: { size: 11 } } }
                        }
                    };
                    if (chart) {
                        chart.data = data;
                        chart.options = opciones;
                        chart.update();
                    } else {
                        chart = new Chart(canvas, { type: 'line', data, options: opciones });
                        activeCharts.set(canvas.id, chart);
                    }
                };

                const fetchDatos = () => {
                    const m1 = document.getElementById('mes1')?.value;
                    const m2 = document.getElementById('mes2')?.value;
                    const idS = document.getElementById('id_sucursal')?.value || null;
                    if (!m1 || !m2) return;
                    fetch(url + '?' + new URLSearchParams({ mes1: m1, mes2: m2, id_sucursal: idS ?? '' }))
                        .then(r => r.json())
                        .then(datos => {
                            console.log('[dashboard.ventas-lineas] respuesta:', datos);
                            render(datos);
                        })
                        .catch(err => { console.error('[dashboard.ventas-lineas] error de red:', err); });
                };

                // Cargar datos iniciales vía AJAX
                fetchDatos();

                document.getElementById('mes1')?.addEventListener('change', fetchDatos);
                document.getElementById('mes2')?.addEventListener('change', fetchDatos);
                document.getElementById('id_sucursal')?.addEventListener('change', fetchDatos);

                return chart;
            }
        };

        function initChart(id) {
            const el = document.getElementById(id);
            if (!el || !initializers[id]) return;
            if (activeCharts.has(id)) {
                try { activeCharts.get(id).destroy(); } catch (e) {}
                activeCharts.delete(id);
            }
            try {
                const chart = initializers[id](el);
                if (chart) activeCharts.set(id, chart);
            } catch (e) {
                console.warn('Chart init failed', id, e);
            }
            const skeleton = el.parentElement?.querySelector('[data-chart-skeleton]');
            if (skeleton) skeleton.style.display = 'none';
        }

        window.refreshDashboardChart = function(id) {
            if (initializers[id]) {
                if (activeCharts.has(id)) {
                    try { activeCharts.get(id).destroy(); } catch (e) {}
                    activeCharts.delete(id);
                }
                initChart(id);
            }
        };

        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const id = entry.target.id;
                if (!activeCharts.has(id)) initChart(id);
                io.unobserve(entry.target);
            });
        }, { threshold: 0.15, rootMargin: '120px 0px' });

        function ready(fn) {
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
            else fn();
        }

        ready(() => {
            Object.keys(initializers).forEach(id => {
                const el = document.getElementById(id);
                if (el) io.observe(el);
            });
        });

        new MutationObserver((mutations) => {
            for (const m of mutations) {
                if (m.attributeName === 'class') {
                    const ids = Array.from(activeCharts.keys());
                    if (ids.length === 0) return;
                    requestAnimationFrame(() => ids.forEach(initChart));
                    return;
                }
            }
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    })();
</script>
@endpush

@endsection