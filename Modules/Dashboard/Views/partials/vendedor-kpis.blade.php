@php
    $ventas = $stats['ventas'] ?? [];
    $tc = $stats['tipo_cambio'] ?? [];
    $panel = 'bg-slate-900 border border-slate-700/80 rounded-2xl shadow-xl shadow-black/10';
    $muted = 'text-slate-400';
    $label = 'text-[11px] font-semibold uppercase tracking-wider text-slate-400';
@endphp

<!-- Indicadores de ventas (Vendedor) -->
<section aria-label="Indicadores de ventas" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    <article class="{{ $panel }} p-5">
        <p class="{{ $label }}">Mis ventas de hoy</p>
        <p class="mt-3 text-2xl font-bold font-mono text-white">Bs {{ number_format((float) ($ventas['total_hoy'] ?? 0), 2, ',', '.') }}</p>
        <p class="mt-1 text-xs {{ $muted }}">{{ $ventas['cantidad_hoy'] ?? 0 }} ventas registradas</p>
    </article>
    <article class="{{ $panel }} p-5">
        <p class="{{ $label }}">Mis ventas del mes</p>
        <p class="mt-3 text-2xl font-bold font-mono text-white">Bs {{ number_format((float) ($ventas['total_mes'] ?? 0), 2, ',', '.') }}</p>
        <p class="mt-1 text-xs {{ $muted }}">{{ $ventas['cantidad_mes'] ?? 0 }} ventas registradas</p>
    </article>
    <article class="{{ $panel }} p-5">
        <p class="{{ $label }}">Cobrado este mes</p>
        <p class="mt-3 text-2xl font-bold font-mono text-emerald-300">Bs {{ number_format((float) ($ventas['pagado_mes'] ?? 0), 2, ',', '.') }}</p>
        <p class="mt-1 text-xs {{ $muted }}">Pagos de mis ventas</p>
    </article>
    <article class="{{ $panel }} p-5">
        <p class="{{ $label }}">Cotizaciones vigentes</p>
        <p class="mt-3 text-2xl font-bold font-mono text-sky-300">{{ $stats['cotizaciones_pendientes'] ?? 0 }}</p>
        <p class="mt-1 text-xs {{ $muted }}">Pendientes de conversión</p>
    </article>
</section>
