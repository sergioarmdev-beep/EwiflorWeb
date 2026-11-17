@php
    $muted = 'text-slate-500 dark:text-slate-400';
    $label = 'text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400';
    $title = 'text-sm font-bold text-slate-900 dark:text-white';
    $panel = 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm';
    $sub = 'mt-0.5 text-xs text-slate-500 dark:text-slate-400';
    $ingresos = $stats['ingresos_mes'] ?? 0;
    $pendientes = $stats['cuentas_pendientes'] ?? 0;
    $pagado = $stats['ventas']['pagado_mes'] ?? 0;
    $vc = $stats['ventas_comparativo'] ?? [];
@endphp

<section class="grid grid-cols-1 lg:grid-cols-3 gap-5" aria-label="Resumen financiero">

    {{-- Ingresos --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Ingresos del mes</p>
                <p class="mt-3 text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400">
                    Bs {{ number_format((float) $ingresos, 2, ',', '.') }}
                </p>
                <p class="mt-1 text-xs {{ $muted }}">Total facturado este mes</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-950/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold text-xs">
                💵
            </span>
        </div>
    </article>

    {{-- Costos (no disponible) --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Costos del mes</p>
                <p class="mt-3 text-2xl font-bold font-mono text-slate-400 dark:text-slate-500">
                    N/D
                </p>
                <p class="mt-1 text-xs {{ $muted }}">Requiere módulo de compras</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-600 font-bold text-xs">
                —
            </span>
        </div>
    </article>

    {{-- Utilidad bruta (no disponible) --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Utilidad bruta</p>
                <p class="mt-3 text-2xl font-bold font-mono text-slate-400 dark:text-slate-500">
                    N/D
                </p>
                <p class="mt-1 text-xs {{ $muted }}">Requiere módulo de compras</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-600 font-bold text-xs">
                —
            </span>
        </div>
    </article>

    {{-- Cuentas pendientes --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Cuentas pendientes</p>
                <p class="mt-3 text-2xl font-bold font-mono {{ $pendientes > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-400' }}">
                    Bs {{ number_format((float) $pendientes, 2, ',', '.') }}
                </p>
                <p class="mt-1 text-xs {{ $muted }}">Saldo por cobrar de ventas</p>
            </div>
            <span class="w-10 h-10 rounded-full {{ $pendientes > 0 ? 'bg-amber-50 dark:bg-amber-950/20' : 'bg-emerald-50 dark:bg-emerald-950/20' }} flex items-center justify-center {{ $pendientes > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-400' }} font-bold text-xs">
                {{ $pendientes > 0 ? '⚠️' : '✓' }}
            </span>
        </div>
    </article>

    {{-- Estado de cobros --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Cobrado este mes</p>
                <p class="mt-3 text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400">
                    Bs {{ number_format((float) $pagado, 2, ',', '.') }}
                </p>
                <p class="mt-1 text-xs {{ $muted }}">Pagos registrados de ventas</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-950/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold text-xs">
                ✓
            </span>
        </div>
        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/50">
            <div class="flex items-center justify-between text-xs">
                <span class="{{ $muted }}">Tasa de cobro</span>
                <span class="font-mono text-slate-600 dark:text-slate-300">
                    @php
                        $tasa = $ingresos > 0 ? round(($pagado / $ingresos) * 100, 0) : 0;
                    @endphp
                    {{ $tasa }}%
                </span>
            </div>
        </div>
    </article>

    {{-- Comparativa --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Variación ingresos</p>
                <p class="mt-3 text-2xl font-bold font-mono {{ $vc['es_positiva'] ?? false ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-300' }}">
                    {{ ($vc['es_positiva'] ?? false) ? '+' : '' }}{{ number_format((float) ($vc['variacion_pct'] ?? 0), 2, ',', '.') }}%
                </p>
                <p class="mt-1 text-xs {{ $muted }}">Vs mes anterior</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-950/20 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-xs">
                📊
            </span>
        </div>
    </article>

</section>
