@php
$vc = $stats['ventas_comparativo'] ?? [];
$vi = $stats['valor_inventario'] ?? 0;
$tp = $stats['ticket_promedio'] ?? 0;

$muted = 'text-muted';
$label = 'text-[11px] font-semibold uppercase tracking-wider text-muted';
$panel = 'bg-surface border border-line rounded-2xl shadow-soft';
$value = 'text-2xl font-bold font-mono tracking-tight text-ink';
$primary = 'text-brand-green';
$amber = 'text-amber-600 dark:text-amber-400';
@endphp

<section
    class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4"
    aria-label="Resumen ejecutivo">

    {{-- Ventas del período --}}
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Ventas del período</p>

                <p class="mt-3 {{ $value }}">
                    Bs {{ number_format((float) ($stats['ventas']['total_mes'] ?? 0), 2, ',', '.') }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    {{ $stats['ventas']['cantidad_mes'] ?? 0 }} ventas este mes
                </p>
            </div>

            <div class="w-11 h-11 shrink-0 rounded-xl bg-brand-green/10 text-brand-green flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="5" width="20" height="14" rx="2" />
                    <path d="M2 10h20M6 15h2" />
                </svg>
            </div>
        </div>

        @if ($esGerencia)
        <div class="mt-5 pt-4 border-t border-line space-y-3">
            <div class="flex items-center justify-between gap-3 text-xs">
                <span class="{{ $muted }}">Mes anterior</span>
                <span class="font-mono font-semibold text-ink">
                    Bs {{ number_format((float) ($vc['total_anterior'] ?? 0), 2, ',', '.') }}
                </span>
            </div>

            <div class="flex items-center justify-between gap-3 text-xs">
                <span class="{{ $muted }}">Variación</span>
                <span class="font-semibold {{ ($vc['es_positiva'] ?? false) ? $primary : $amber }}">
                    {{ ($vc['es_positiva'] ?? false) ? '+' : '' }}{{ number_format((float) ($vc['variacion_pct'] ?? 0), 2, ',', '.') }}%
                </span>
            </div>
        </div>
        @endif
    </article>

    {{-- Ticket promedio --}}
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Ticket promedio</p>

                <p class="mt-3 {{ $value }}">
                    Bs {{ number_format((float) $tp, 2, ',', '.') }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Promedio por venta este mes
                </p>
            </div>

            <div class="w-11 h-11 shrink-0 rounded-xl bg-violet-500/10 text-violet-600 dark:text-violet-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16v16H4z" />
                    <path d="M8 9h8M8 13h5M8 17h3" />
                </svg>
            </div>
        </div>
    </article>

    {{-- Valor del inventario --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Valor del inventario</p>

                <p class="mt-3 {{ $value }}">
                    Bs {{ number_format((float) $vi, 2, ',', '.') }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Existencia disponible × precio lista
                </p>
            </div>

            <div class="w-11 h-11 shrink-0 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 7 9-4 9 4-9 4-9-4Z" />
                    <path d="m3 12 9 4 9-4M3 17l9 4 9-4" />
                </svg>
            </div>
        </div>
    </article>
    @endif

    {{-- Cuentas por cobrar --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Cuentas por cobrar</p>

                <p class="mt-3 text-2xl font-bold font-mono tracking-tight {{ ($stats['ventas']['saldo_pendiente'] ?? 0) > 0 ? $amber : $primary }}">
                    Bs {{ number_format((float) ($stats['ventas']['saldo_pendiente'] ?? 0), 2, ',', '.') }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Saldo pendiente de cobro
                </p>
            </div>

            <div class="w-11 h-11 shrink-0 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <path d="M14 2v6h6M8 13h8M8 17h5" />
                </svg>
            </div>
        </div>
    </article>
    @endif

    {{-- Comparativa con período anterior --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Comparativa mes anterior</p>

                <p class="mt-3 text-2xl font-bold font-mono tracking-tight {{ ($vc['es_positiva'] ?? false) ? $primary : $amber }}">
                    {{ ($vc['es_positiva'] ?? false) ? '+' : '' }}{{ number_format((float) ($vc['variacion_pct'] ?? 0), 2, ',', '.') }}%
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    {{ ($vc['es_positiva'] ?? false) ? 'Crecimiento' : 'Descenso' }} vs mes previo
                </p>
            </div>

            <div class="w-11 h-11 shrink-0 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 17 6-6 4 4 8-8" />
                    <path d="M15 7h6v6" />
                </svg>
            </div>
        </div>
    </article>
    @endif

    {{-- Total de productos --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Productos en catálogo</p>

                <p class="mt-3 {{ $value }}">
                    {{ number_format($stats['total_productos'] ?? 0) }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Productos activos registrados
                </p>
            </div>

            <div class="w-11 h-11 shrink-0 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.5 13.5 13 21l-9-9V4h8l8.5 8.5a1.4 1.4 0 0 1 0 2Z" />
                    <circle cx="8" cy="8" r="1" />
                </svg>
            </div>
        </div>
    </article>
    @endif

</section>