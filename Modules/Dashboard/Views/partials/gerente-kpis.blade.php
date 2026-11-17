@php
$ventas = $stats['ventas'] ?? [];
$tc = $stats['tipo_cambio'] ?? [];
$tcParalelo = $tc['paralelo'] ?? null;
$tcOficial = $tc['oficial'] ?? null;

$panel = 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-[#0B6E45]/40 dark:hover:border-[#0B6E45]/60 hover:shadow-md';
$muted = 'text-slate-500 dark:text-slate-400';
$label = 'text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400';
$iconWrap = 'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg';
@endphp

<style>
    @keyframes kpiFadeUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .kpi-fade {
        animation: kpiFadeUp .45s cubic-bezier(.16, 1, .3, 1) both;
    }

    .kpi-d1 {
        animation-delay: .04s;
    }

    .kpi-d2 {
        animation-delay: .08s;
    }

    .kpi-d3 {
        animation-delay: .12s;
    }

    .kpi-d4 {
        animation-delay: .16s;
    }

    .kpi-d5 {
        animation-delay: .20s;
    }

    .kpi-d6 {
        animation-delay: .24s;
    }

    @media (prefers-reduced-motion: reduce) {
        .kpi-fade {
            animation: none !important;
        }
    }
</style>

<!-- Indicadores generales (Gerencia) -->
<section aria-label="Indicadores generales" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6 gap-4">

    {{-- Ventas de hoy --}}
    <article class="{{ $panel }} kpi-fade kpi-d1 p-5">
        <div class="flex items-center justify-between gap-2">
            <p class="{{ $label }}">Ventas de hoy</p>
            <span class="{{ $iconWrap }} bg-[#0B6E45]/10 text-[#0B6E45] dark:bg-[#0B6E45]/20 dark:text-emerald-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 17l6-6 4 4 8-8M14 7h7v7" />
                </svg>
            </span>
        </div>
        <p class="mt-3 text-2xl font-bold font-mono text-slate-900 dark:text-white">
            Bs {{ number_format((float) ($ventas['total_hoy'] ?? 0), 2, ',', '.') }}
        </p>
        <p class="mt-1 text-xs {{ $muted }}">{{ $ventas['cantidad_hoy'] ?? 0 }} ventas registradas</p>
    </article>

    {{-- Ventas del mes --}}
    <article class="{{ $panel }} kpi-fade kpi-d2 p-5">
        <div class="flex items-center justify-between gap-2">
            <p class="{{ $label }}">Ventas del mes</p>
            <span class="{{ $iconWrap }} bg-[#0B6E45]/10 text-[#0B6E45] dark:bg-[#0B6E45]/20 dark:text-emerald-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 20V10M10 20V4M16 20v-8M22 20H2" />
                </svg>
            </span>
        </div>
        <p class="mt-3 text-2xl font-bold font-mono text-slate-900 dark:text-white">
            Bs {{ number_format((float) ($ventas['total_mes'] ?? 0), 2, ',', '.') }}
        </p>
        <p class="mt-1 text-xs {{ $muted }}">{{ $ventas['cantidad_mes'] ?? 0 }} ventas registradas</p>
    </article>

    {{-- Cobrado este mes --}}
    <article class="{{ $panel }} kpi-fade kpi-d3 p-5">
        <div class="flex items-center justify-between gap-2">
            <p class="{{ $label }}">Cobrado este mes</p>
            <span class="{{ $iconWrap }} bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </span>
        </div>
        <p class="mt-3 text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400">
            Bs {{ number_format((float) ($ventas['pagado_mes'] ?? 0), 2, ',', '.') }}
        </p>
        <p class="mt-1 text-xs {{ $muted }}">Pagos de ventas registradas</p>
    </article>

    {{-- Saldo por cobrar --}}
    <article class="{{ $panel }} kpi-fade kpi-d4 p-5">
        <div class="flex items-center justify-between gap-2">
            <p class="{{ $label }}">Saldo por cobrar</p>
            <span class="{{ $iconWrap }} bg-amber-500/10 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke-width="2" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 2" />
                </svg>
            </span>
        </div>
        <p class="mt-3 text-2xl font-bold font-mono {{ ($ventas['saldo_pendiente'] ?? 0) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white' }}">
            Bs {{ number_format((float) ($ventas['saldo_pendiente'] ?? 0), 2, ',', '.') }}
        </p>
        <p class="mt-1 text-xs {{ $muted }}">Saldo de ventas registradas</p>
    </article>

    {{-- Productos activos --}}
    <article class="{{ $panel }} kpi-fade kpi-d5 p-5">
        <div class="flex items-center justify-between gap-2">
            <p class="{{ $label }}">Productos activos</p>
            <span class="{{ $iconWrap }} bg-[#0B6E45]/10 text-[#0B6E45] dark:bg-[#0B6E45]/20 dark:text-emerald-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l9 4.5v9L12 21l-9-4.5v-9L12 3z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.5 7.7L12 12l8.5-4.3M12 12v9" />
                </svg>
            </span>
        </div>
        <p class="mt-3 text-2xl font-bold font-mono text-slate-900 dark:text-white">{{ number_format($stats['total_productos'] ?? 0) }}</p>
        <p class="mt-1 text-xs {{ $muted }}">En el catálogo</p>
    </article>

    {{-- Stock bajo --}}
    @php($stockBajo = (int) ($stats['stock_bajo_total'] ?? 0))
    <article class="{{ $panel }} kpi-fade kpi-d6 p-5 {{ $stockBajo > 0 ? 'border-amber-500/50 dark:border-amber-500/40' : '' }}">
        <div class="flex items-center justify-between gap-2">
            <p class="{{ $label }}">Existencias bajo mínimo</p>
            <span class="{{ $iconWrap }} {{ $stockBajo > 0 ? 'bg-amber-500/10 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400' }}">
                @if ($stockBajo > 0)
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 4h.01M10.3 3.86 2.8 17a2 2 0 0 0 1.74 3h14.92a2 2 0 0 0 1.74-3l-7.46-13.14a2 2 0 0 0-3.48 0Z" />
                </svg>
                @else
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                @endif
            </span>
        </div>
        <p class="mt-3 text-2xl font-bold font-mono {{ $stockBajo > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ number_format($stockBajo) }}</p>
        <p class="mt-1 text-xs {{ $muted }}">Variante / almacén</p>
    </article>
</section>

<!-- Tipo de cambio -->
<section class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
    @foreach (['paralelo' => 'Paralelo', 'oficial' => 'Oficial'] as $key => $nombre)
    @php($tcItem = $tc[$key] ?? null)
    <article class="{{ $panel }} p-5 flex items-center justify-between gap-4">
        <div class="min-w-0">
            <p class="{{ $label }}">Tipo de cambio {{ $nombre }}</p>
            @if ($tcItem)
            <p class="mt-2 text-2xl font-bold font-mono text-slate-900 dark:text-white">
                Bs {{ number_format((float) $tcItem->valor, 2, ',', '.') }}
                <span class="text-sm font-normal {{ $muted }}">/ USD</span>
            </p>
            <p class="mt-1 text-xs {{ $muted }}">Vigente desde {{ $tcItem->fecha?->format('d/m/Y') ?? '—' }}</p>
            @else
            <p class="mt-2 text-sm {{ $muted }}">Sin registro disponible</p>
            @endif
        </div>
        @if ($tcItem)
        <span class="shrink-0 rounded-lg border px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider
                    @if($tcItem->origen === 'API')
                        border-[#0B6E45]/30 bg-[#0B6E45]/10 text-[#0B6E45] dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400
                    @else
                        border-violet-300 bg-violet-50 text-violet-700 dark:border-violet-500/30 dark:bg-violet-500/10 dark:text-violet-300
                    @endif">
            {{ $tcItem->origen }}
        </span>
        @else
        <span class="shrink-0 rounded-lg border border-slate-300 bg-slate-100 px-3 py-1.5 text-[10px] font-semibold text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">SIN DATOS</span>
        @endif
    </article>
    @endforeach
</section>