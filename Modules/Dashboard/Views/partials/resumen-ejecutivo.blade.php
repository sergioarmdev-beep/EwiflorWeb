@php
    $vc = $stats['ventas_comparativo'] ?? [];
    $vi = $stats['valor_inventario'] ?? 0;
    $tp = $stats['ticket_promedio'] ?? 0;
    $muted = 'text-slate-500 dark:text-slate-400';
    $label = 'text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400';
    $title = 'text-sm font-bold text-slate-900 dark:text-white';
    $panel = 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm';
    $primary = 'text-[#0B6E45] dark:text-emerald-400';
    $amber = 'text-amber-600 dark:text-amber-300';
    $sky = 'text-sky-600 dark:text-sky-400';
@endphp

<section class="grid grid-cols-1 lg:grid-cols-3 gap-5" aria-label="Resumen ejecutivo">

    {{-- Ventas del período (todos los roles) --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Ventas del período</p>
                <p class="mt-3 text-2xl font-bold font-mono text-slate-900 dark:text-slate-100">
                    Bs {{ number_format((float) ($stats['ventas']['total_mes'] ?? 0), 2, ',', '.') }}
                </p>
                <p class="mt-1 text-xs {{ $muted }}">{{ ($stats['ventas']['cantidad_mes'] ?? 0) }} ventas este mes</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-950/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold text-xs">
                💰
            </span>
        </div>
        @if ($esGerencia)
        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/50 space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="{{ $muted }}">Mes anterior</span>
                <span class="font-mono text-slate-600 dark:text-slate-300">Bs {{ number_format((float) ($vc['total_anterior'] ?? 0), 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="{{ $muted }}">Variación</span>
                <span class="font-semibold {{ ($vc['es_positiva'] ?? false) ? $primary : $amber }} text-xs">
                    {{ ($vc['es_positiva'] ?? false) ? '+' : '' }}{{ number_format((float) ($vc['variacion_pct'] ?? 0), 2, ',', '.') }}%
                </span>
            </div>
        </div>
        @endif
    </article>

    {{-- Ticket promedio (todos los roles) --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Ticket promedio</p>
                <p class="mt-3 text-2xl font-bold font-mono text-slate-900 dark:text-slate-100">
                    Bs {{ number_format((float) $tp, 2, ',', '.') }}
                </p>
                <p class="mt-1 text-xs {{ $muted }}">Promedio por venta este mes</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-violet-50 dark:bg-violet-950/20 flex items-center justify-center text-violet-600 dark:text-violet-400 font-bold text-xs">
                🎫
            </span>
        </div>
    </article>

    {{-- Valor del inventario (solo gerencia) --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Valor del inventario</p>
                <p class="mt-3 text-2xl font-bold font-mono text-slate-900 dark:text-slate-100">
                    Bs {{ number_format((float) $vi, 2, ',', '.') }}
                </p>
                <p class="mt-1 text-xs {{ $muted }}">Existencia disponible × precio lista</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-sky-50 dark:bg-sky-950/20 flex items-center justify-center text-sky-600 dark:text-sky-400 font-bold text-xs">
                📦
            </span>
        </div>
    </article>
    @endif

    {{-- Cuentas por cobrar (solo gerencia) --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Cuentas por cobrar</p>
                <p class="mt-3 text-2xl font-bold font-mono {{ ($stats['ventas']['saldo_pendiente'] ?? 0) > 0 ? $amber : $primary }}>
                    Bs {{ number_format((float) ($stats['ventas']['saldo_pendiente'] ?? 0), 2, ',', '.') }}
                </p>
                <p class="mt-1 text-xs {{ $muted }}">Saldo pendiente de cobro</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-amber-50 dark:bg-amber-950/20 flex items-center justify-center text-amber-600 dark:text-amber-400 font-bold text-xs">
                🧾
            </span>
        </div>
    </article>
    @endif

    {{-- Comparativa con período anterior (solo gerencia) --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Comparativa mes anterior</p>
                <p class="mt-3 text-2xl font-bold font-mono {{ ($vc['es_positiva'] ?? false) ? $primary : $amber }}">
                    {{ ($vc['es_positiva'] ?? false) ? '+' : '' }}{{ number_format((float) ($vc['variacion_pct'] ?? 0), 2, ',', '.') }}%
                </p>
                <p class="mt-1 text-xs {{ $muted }}">
                    {{ ($vc['es_positiva'] ?? false) ? 'Crecimiento' : 'Descenso' }} vs mes previo
                </p>
            </div>
            <span class="w-10 h-10 rounded-full bg-rose-50 dark:bg-rose-950/20 flex items-center justify-center text-rose-600 dark:text-rose-400 font-bold text-xs">
                📈
            </span>
        </div>
    </article>
    @endif

    {{-- Total productos (solo gerencia) --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Productos en catálogo</p>
                <p class="mt-3 text-2xl font-bold font-mono text-slate-900 dark:text-slate-100">
                    {{ number_format($stats['total_productos'] ?? 0) }}
                </p>
                <p class="mt-1 text-xs {{ $muted }}">Productos activos registrados</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-950/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xs">
                🏷️
            </span>
        </div>
    </article>
    @endif

</section>
