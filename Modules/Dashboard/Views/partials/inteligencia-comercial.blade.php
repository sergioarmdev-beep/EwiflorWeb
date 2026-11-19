@php
$muted = 'text-muted';
$label = 'text-[11px] font-semibold uppercase tracking-wider text-muted';
$title = 'text-sm font-bold text-ink';
$panel = 'bg-surface border border-line rounded-2xl shadow-soft';
$primary = 'text-brand-green';

$pmv = $stats['productos_mas_vendidos'] ?? [];
$cc = $stats['conversion_cotizaciones'] ?? [];
$tp = $stats['ticket_promedio'] ?? 0;
$tv = $stats['top_vendedores'] ?? [];
@endphp

<section
    class="grid grid-cols-1 lg:grid-cols-2 gap-4"
    aria-label="Inteligencia comercial">

    {{-- Evolución de ventas --}}
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="{{ $title }}">Evolución de ventas</h2>
                <p class="mt-1 text-xs {{ $muted }}">Últimos 12 meses</p>
            </div>

            <div class="w-10 h-10 shrink-0 rounded-xl bg-brand-green/10 text-brand-green flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 3v18h18" />
                    <path d="m7 14 4-4 4 4 6-7" />
                    <path d="M17 7h4v4" />
                </svg>
            </div>
        </div>

        <div class="mt-5 pt-4 border-t border-line">
            <div class="relative" style="height: 200px;">
                <div
                    class="chart-skeleton absolute inset-0 rounded-xl bg-surface-2 border border-line flex items-center justify-center"
                    data-chart-skeleton>
                    <span class="text-xs {{ $muted }}">
                        Cargando gráfico…
                    </span>
                </div>

                <canvas
                    id="chartVentasMensuales"
                    height="200"
                    class="relative"></canvas>
            </div>
        </div>
    </article>

    {{-- Métricas comerciales --}}
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md">
        <div class="flex items-center justify-between gap-3 mb-5">
            <div>
                <h2 class="{{ $title }}">Rendimiento comercial</h2>
                <p class="mt-1 text-xs {{ $muted }}">Indicadores de conversión y venta</p>
            </div>

            <div class="w-10 h-10 shrink-0 rounded-xl bg-violet-500/10 text-violet-600 dark:text-violet-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 3v18h18" />
                    <path d="M18 17V9M13 17V5M8 17v-3" />
                </svg>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            {{-- Conversión --}}
            <div class="rounded-xl bg-surface-2 border border-line p-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 12h16M12 4v16" />
                            <path d="m5 7 7-4 7 4M5 17l7 4 7-4" />
                        </svg>
                    </div>
                    <p class="{{ $label }}">Conversión de cotizaciones</p>
                </div>

                <p class="mt-4 text-2xl font-bold font-mono tracking-tight text-ink">
                    {{ number_format((float) ($cc['porcentaje'] ?? 0), 2, ',', '.') }}%
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    {{ $cc['convertidas'] ?? 0 }} de {{ $cc['emitidas'] ?? 0 }} emitidas
                </p>
            </div>

            {{-- Ticket promedio --}}
            <div class="rounded-xl bg-surface-2 border border-line p-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-brand-green/10 text-brand-green flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="5" width="20" height="14" rx="2" />
                            <path d="M2 10h20M6 15h2" />
                        </svg>
                    </div>
                    <p class="{{ $label }}">Ticket promedio</p>
                </div>

                <p class="mt-4 text-2xl font-bold font-mono tracking-tight text-ink">
                    Bs {{ number_format((float) $tp, 2, ',', '.') }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Promedio por venta este mes
                </p>
            </div>
        </div>
    </article>

    {{-- Productos más vendidos --}}
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md">
        <div class="flex items-center justify-between gap-3 mb-5">
            <div>
                <h2 class="{{ $title }}">Productos más vendidos del mes</h2>
                <p class="mt-1 text-xs {{ $muted }}">Por cantidad de unidades</p>
            </div>

            <div class="w-10 h-10 shrink-0 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 7 9-4 9 4-9 4-9-4Z" />
                    <path d="m3 12 9 4 9-4M3 17l9 4 9-4" />
                </svg>
            </div>
        </div>

        @if (count($pmv) > 0)
        <div class="space-y-2">
            @foreach ($pmv as $p)
            <div class="rounded-xl bg-surface-2 border border-line p-3 transition-colors hover:border-brand-green/30">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-ink truncate">
                            {{ $p->producto }}
                        </p>

                        <p class="mt-1 text-[11px] {{ $muted }}">
                            Total vendido: USD {{ number_format((float) $p->total_usd, 2) }}
                        </p>
                    </div>

                    <div class="shrink-0 text-right">
                        <p class="text-lg font-bold font-mono text-brand-green">
                            {{ (int) $p->cantidad }}
                        </p>
                        <p class="text-[10px] uppercase tracking-wider {{ $muted }}">
                            Unidades
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="rounded-xl border border-dashed border-line bg-surface-2 px-5 py-10 text-center">
            <div class="mx-auto w-11 h-11 rounded-xl bg-surface border border-line text-muted flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 3v18h18" />
                    <path d="m7 14 4-4 4 4 6-7" />
                </svg>
            </div>

            <p class="mt-3 text-sm font-semibold text-ink">
                Sin ventas registradas este mes
            </p>

            <p class="mt-1 text-xs {{ $muted }}">
                Los productos vendidos aparecerán aquí
            </p>
        </div>
        @endif
    </article>

    {{-- Top vendedores --}}
    @if ($esGerencia && count($tv) > 0)
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md">
        <div class="flex items-center justify-between gap-3 mb-5">
            <div>
                <h2 class="{{ $title }}">Vendedor que más vendió este mes</h2>
                <p class="mt-1 text-xs {{ $muted }}">Ranking por monto facturado</p>
            </div>

            <div class="w-10 h-10 shrink-0 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m8 21 4-7 4 7M12 14V3" />
                    <path d="M5 3h14l-1 7H6L5 3Z" />
                    <path d="M8 10v2a4 4 0 0 0 8 0v-2" />
                </svg>
            </div>
        </div>

        <div class="space-y-2">
            @foreach ($tv as $v)
            <div class="rounded-xl bg-surface-2 border border-line p-3 transition-colors hover:border-brand-green/30">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 shrink-0 rounded-lg bg-brand-green/10 text-brand-green flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M5 21v-2a7 7 0 0 1 14 0v2" />
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-ink truncate">
                                {{ $v->vendedor }}
                            </p>

                            <p class="mt-1 text-[11px] {{ $muted }}">
                                {{ (int) $v->cantidad_ventas }} ventas realizadas
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0 text-right">
                        <p class="text-sm font-bold font-mono text-ink">
                            Bs {{ number_format((float) $v->total_bs, 2, ',', '.') }}
                        </p>
                        <p class="text-[10px] uppercase tracking-wider {{ $muted }}">
                            Facturado
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </article>
    @endif

</section>