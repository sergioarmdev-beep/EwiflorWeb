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
    $panel = 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm transition-all duration-300 ease-out hover:border-[#0B6E45]/50 dark:hover:border-[#0B6E45]/60';
    $muted = 'text-slate-500 dark:text-slate-400';
    $label = 'text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400';
    $title = 'text-sm font-bold text-slate-900 dark:text-white';
    $sub = 'mt-0.5 text-xs text-slate-500 dark:text-slate-400';
    $headBar = 'border-b border-slate-200 dark:border-slate-800';
    $primaryText = 'text-[#0B6E45] dark:text-emerald-400';
    $primaryTextH = 'text-[#0B6E45] dark:text-emerald-400 hover:text-[#095638] dark:hover:text-emerald-300';
@endphp

@section('content')
<div class="min-h-full text-slate-900 dark:text-slate-100">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        {{-- Encabezado --}}
        <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] {{ $primaryText }}">EwiflorWeb · Panel de control</p>
                <h1 class="mt-2 text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Hola, {{ auth()->user()->nombre_completo }}
                </h1>
                <p class="mt-1 text-sm {{ $muted }}">
                    {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
                    <span class="mx-1 text-slate-400 dark:text-slate-600">·</span>
                    <span class="font-medium text-slate-700 dark:text-slate-200">{{ str_replace('_', ' ', $rol) }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2 text-xs {{ $muted }}">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#0B6E45] opacity-60"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-[#0B6E45]"></span>
                </span>
                <span>Panel actualizado</span>
            </div>
        </header>

        {{-- Alertas globales (fuera de pestañas) --}}
        @if ($esGerencia && !empty($stats['alertas_sistema']))
        <section class="space-y-2" aria-label="Alertas del sistema">
            @foreach ($stats['alertas_sistema'] as $alerta)
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 rounded-lg border-l-4 border-amber-500 bg-amber-50 dark:bg-amber-500/5 px-4 py-3 transition-colors hover:bg-amber-100 dark:hover:bg-amber-500/10">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <svg class="w-5 h-5 mt-0.5 shrink-0 text-amber-600 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 4h.01M10.3 3.86 2.8 17a2 2 0 0 0 1.74 3h14.92a2 2 0 0 0 1.74-3l-7.46-13.14a2 2 0 0 0-3.48 0Z" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-amber-800 dark:text-amber-200">{{ $alerta['titulo'] }}</p>
                        <p class="mt-0.5 text-xs leading-relaxed text-amber-700 dark:text-amber-100/80">{{ $alerta['mensaje'] }}</p>
                    </div>
                </div>
                <a href="{{ $alerta['url'] }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 hover:text-amber-900 dark:text-amber-200 dark:hover:text-white transition-colors sm:ml-4">
                    {{ $alerta['url_texto'] }}
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
            @endforeach
        </section>
        @endif

        {{-- Barra de pestañas --}}
        <div x-data="{ tab: 'resumen' }" class="flex flex-col gap-1">
            <nav class="flex flex-wrap gap-1 border-b border-slate-200 dark:border-slate-800 -mb-px" aria-label="Secciones del panel">
@foreach (['resumen' => '01. Resumen Ejecutivo', 'alertas' => '02. Centro de Alertas', 'comercial' => '03. Inteligencia Comercial', 'inventario' => '04. Inteligencia de Inventario', 'financiero' => '05. Resumen Financiero', 'acciones' => '06. Acciones Rápidas', 'actividad' => '07. Actividad Empresarial', 'comparativo' => '09. Comparativa de Ventas', 'operativo' => '08. Seguimiento Operativo'] as $clave => $etiqueta)
    @php $mostrar = in_array($clave, $tabsPermitidas); @endphp
    @if($mostrar)
    <button x-on:click="tab = '{{ $clave }}'"
        :class="tab === '{{ $clave }}' ? 'border-[#0B6E45] dark:border-emerald-400 text-[#0B6E45] dark:text-emerald-400 border-b-2 pb-1' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 border-transparent pb-1'"
        class="px-4 py-3 text-xs font-semibold transition-colors border-b-2">
        {{ $etiqueta }}
    </button>
    @endif
@endforeach
            </nav>

            {{-- Contenido de pestañas --}}
            @php $tabsContent = [
                'resumen' => 'dashboard::partials.resumen-ejecutivo',
                'alertas' => 'dashboard::partials.centro-alertas',
                'comercial' => 'dashboard::partials.inteligencia-comercial',
                'inventario' => 'dashboard::partials.inteligencia-inventario',
                'financiero' => 'dashboard::partials.resumen-financiero',
                'acciones' => 'dashboard::partials.acciones-rapidas',
                'actividad' => 'dashboard::partials.actividad-empresarial',
                'comparativo' => 'dashboard::partials.ventas-lineas',
                'operativo' => 'dashboard::partials.seguimiento-operativo',
            ]; @endphp
            @foreach ($tabsPermitidas as $tp)
            @php $partial = $tabsContent[$tp] ?? null; @endphp
            @if($partial)
            <div x-show="tab === '{{ $tp }}'" x-transition>
                @include($partial, ['stats' => $stats, 'esGerencia' => $esGerencia, 'esVendedor' => $esVendedor, 'esTecnico' => $esTecnico])
            </div>
            @endif
            @endforeach
        </div>

        <footer class="border-t border-slate-200 dark:border-slate-800 pt-4 text-center text-[10px] text-slate-400 dark:text-slate-600">
            EwiflorWeb · Panel de control
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
                const data = @json($stats['ventas']['ventas_por_mes'] ?? []);
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

            chartTipoCambio(canvas) {
                const t = chartTheme();
                const data = @json($stats['tipo_cambio_historico'] ?? []);
                const paralelo = data.paralelo || [];
                const oficial = data.oficial || [];
                const allDates = [...new Set([...paralelo.map(d => d.fecha), ...oficial.map(d => d.fecha)])].sort();
                const byDate = {};
                [...paralelo, ...oficial].forEach(d => {
                    byDate[d.fecha] = byDate[d.fecha] || {};
                    byDate[d.fecha][d.tipo] = parseFloat(d.valor);
                });

                return new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: allDates,
                        datasets: [{
                            label: 'Dólar paralelo (Bs)',
                            data: allDates.map(f => byDate[f]?.PARALELO ?? null),
                            borderColor: t.secondary,
                            backgroundColor: t.secondaryFill,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            fill: false,
                            tension: 0.4
                        },
                        {
                            label: 'Dólar oficial (Bs)',
                            data: allDates.map(f => byDate[f]?.OFICIAL ?? null),
                            borderColor: t.primary,
                            backgroundColor: t.primaryFill,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            fill: false,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: prefersReducedMotion ? false : { duration: 800, easing: 'easeOutQuart' },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: { color: t.tick, font: { size: 11 } }
                            },
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
                            y: {
                                beginAtZero: false,
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
            }
        },

        chartVentasLineas(canvas) {
            const t = chartTheme();
            const url = canvas.dataset.url;
            const inicial = @json($stats['ventas_lineas'] ?? []);
            const $error = document.getElementById('errorVentasLineas');
            let chart = null;

            const render = (datos) => {
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

            render(inicial);

            const onChange = () => {
                const mes1 = document.getElementById('mes1')?.value;
                const mes2 = document.getElementById('mes2')?.value;
                const idSucursal = document.getElementById('id_sucursal')?.value || null;
                if (!mes1 || !mes2) return;
                fetch(url + '?' + new URLSearchParams({ mes1, mes2, id_sucursal: idSucursal ?? '' }))
                    .then(r => r.json())
                    .then(datos => render(datos))
                    .catch(() => { if ($error) $error.style.display = 'block'; });
            };
            document.getElementById('mes1')?.addEventListener('change', onChange);
            document.getElementById('mes2')?.addEventListener('change', onChange);
            document.getElementById('id_sucursal')?.addEventListener('change', onChange);

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
