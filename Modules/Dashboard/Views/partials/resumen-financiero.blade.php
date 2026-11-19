@php
$muted = 'text-muted';
$label = 'text-[11px] font-bold uppercase tracking-wider text-muted';
$panel = 'bg-surface border border-line rounded-2xl shadow-soft transition-all duration-200 hover:shadow-md hover:-translate-y-0.5';

$ingresos = $stats['ingresos_mes'] ?? 0;
$pendientes = $stats['cuentas_pendientes'] ?? 0;
$pagado = $stats['ventas']['pagado_mes'] ?? 0;
$vc = $stats['ventas_comparativo'] ?? [];

$tasa = $ingresos > 0 ? round(($pagado / $ingresos) * 100, 0) : 0;
$esPositiva = $vc['es_positiva'] ?? false;
@endphp

<section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4"
    aria-label="Resumen financiero">

    {{-- Ingresos --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Ingresos del mes</p>

                <p class="mt-3 text-2xl font-bold font-mono tracking-tight text-brand-green">
                    Bs {{ number_format((float) $ingresos, 2, ',', '.') }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Total facturado este mes
                </p>
            </div>

            <span class="shrink-0 w-11 h-11 rounded-xl bg-brand-green/10 text-brand-green flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6v12m-4-9h6a2 2 0 0 1 0 4H9a2 2 0 0 0 0 4h7M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" />
                </svg>
            </span>
        </div>
    </article>

    {{-- Costos --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Costos del mes</p>

                <p class="mt-3 text-2xl font-bold font-mono tracking-tight text-muted">
                    N/D
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Requiere módulo de compras
                </p>
            </div>

            <span class="shrink-0 w-11 h-11 rounded-xl bg-surface-2 text-muted flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8v8m-4-4h8M4 6.5 12 3l8 3.5v11L12 21l-8-3.5v-11Z" />
                </svg>
            </span>
        </div>
    </article>

    {{-- Utilidad bruta --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Utilidad bruta</p>

                <p class="mt-3 text-2xl font-bold font-mono tracking-tight text-muted">
                    N/D
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Requiere módulo de compras
                </p>
            </div>

            <span class="shrink-0 w-11 h-11 rounded-xl bg-surface-2 text-muted flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 17 9 11l4 4 8-9M15 6h6v6" />
                </svg>
            </span>
        </div>
    </article>

    {{-- Cuentas pendientes --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Cuentas pendientes</p>

                <p class="mt-3 text-2xl font-bold font-mono tracking-tight {{ $pendientes > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-brand-green' }}">
                    Bs {{ number_format((float) $pendientes, 2, ',', '.') }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Saldo por cobrar de ventas
                </p>
            </div>

            <span class="shrink-0 w-11 h-11 rounded-xl flex items-center justify-center {{ $pendientes > 0 ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-brand-green/10 text-brand-green' }}">
                @if($pendientes > 0)
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v4m0 4h.01M10.3 3.9 2.8 17a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3l-7.5-13.1a2 2 0 0 0-3.4 0Z" />
                </svg>
                @else
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m5 12 4 4L19 6" />
                </svg>
                @endif
            </span>
        </div>
    </article>

    {{-- Estado de cobros --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Cobrado este mes</p>

                <p class="mt-3 text-2xl font-bold font-mono tracking-tight text-brand-green">
                    Bs {{ number_format((float) $pagado, 2, ',', '.') }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Pagos registrados de ventas
                </p>
            </div>

            <span class="shrink-0 w-11 h-11 rounded-xl bg-brand-green/10 text-brand-green flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M5 12.5 9.5 17 19 7" />
                    <circle cx="12" cy="12" r="9" />
                </svg>
            </span>
        </div>

        <div class="mt-5 pt-4 border-t border-line">
            <div class="flex items-center justify-between gap-3 text-xs">
                <span class="{{ $muted }}">Tasa de cobro</span>

                <span class="font-mono font-bold text-ink">
                    {{ $tasa }}%
                </span>
            </div>

            <div class="mt-2 h-1.5 rounded-full bg-surface-2 overflow-hidden">
                <div class="h-full rounded-full bg-brand-green transition-all duration-500"
                    style="width: {{ min(100, max(0, $tasa)) }}%">
                </div>
            </div>
        </div>
    </article>

    {{-- Comparativa --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Variación ingresos</p>

                <p class="mt-3 text-2xl font-bold font-mono tracking-tight {{ $esPositiva ? 'text-brand-green' : 'text-amber-600 dark:text-amber-400' }}">
                    {{ $esPositiva ? '+' : '' }}{{ number_format((float) ($vc['variacion_pct'] ?? 0), 2, ',', '.') }}%
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Vs. mes anterior
                </p>
            </div>

            <span class="shrink-0 w-11 h-11 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 17 9 11l4 4 8-9M15 6h6v6" />
                </svg>
            </span>
        </div>
    </article>

</section>