@php
$ventas = $stats['ventas'] ?? [];
$tc = $stats['tipo_cambio'] ?? [];
$tcParalelo = $tc['paralelo'] ?? null;
$tcOficial = $tc['oficial'] ?? null;
$panel = 'bg-slate-900 border border-slate-700/80 rounded-2xl shadow-xl shadow-black/10';
$muted = 'text-slate-400';
$label = 'text-[11px] font-semibold uppercase tracking-wider text-slate-400';
@endphp

<!-- Indicadores generales (Administración) -->
<section aria-label="Indicadores generales" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6 gap-4">
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-2">
            <p class="{{ $label }}">Ventas de hoy</p>
            <span class="text-sky-300 text-lg">↗</span>
        </div>
        <p class="mt-3 text-2xl font-bold font-mono text-white">Bs {{ number_format((float) ($ventas['total_hoy'] ?? 0), 2, ',', '.') }}</p>
        <p class="mt-1 text-xs {{ $muted }}">{{ $ventas['cantidad_hoy'] ?? 0 }} ventas registradas</p>
    </article>

    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-2">
            <p class="{{ $label }}">Ventas del mes</p>
            <span class="text-sky-300 text-lg">▥</span>
        </div>
        <p class="mt-3 text-2xl font-bold font-mono text-white">Bs {{ number_format((float) ($ventas['total_mes'] ?? 0), 2, ',', '.') }}</p>
        <p class="mt-1 text-xs {{ $muted }}">{{ $ventas['cantidad_mes'] ?? 0 }} ventas registradas</p>
    </article>

    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-2">
            <p class="{{ $label }}">Cobrado este mes</p>
            <span class="text-emerald-300 text-lg">✓</span>
        </div>
        <p class="mt-3 text-2xl font-bold font-mono text-emerald-300">Bs {{ number_format((float) ($ventas['pagado_mes'] ?? 0), 2, ',', '.') }}</p>
        <p class="mt-1 text-xs {{ $muted }}">Pagos de ventas registradas</p>
    </article>

    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-2">
            <p class="{{ $label }}">Saldo por cobrar</p>
            <span class="text-amber-300 text-lg">◷</span>
        </div>
        <p class="mt-3 text-2xl font-bold font-mono {{ ($ventas['saldo_pendiente'] ?? 0) > 0 ? 'text-amber-300' : 'text-white' }}">Bs {{ number_format((float) ($ventas['saldo_pendiente'] ?? 0), 2, ',', '.') }}</p>
        <p class="mt-1 text-xs {{ $muted }}">Saldo de ventas registradas</p>
    </article>

    <article class="{{ $panel }} p-5">
        <p class="{{ $label }}">Productos activos</p>
        <p class="mt-3 text-2xl font-bold font-mono text-white">{{ number_format($stats['total_productos'] ?? 0) }}</p>
        <p class="mt-1 text-xs {{ $muted }}">En el catálogo</p>
    </article>

    <article class="{{ $panel }} p-5 {{ ($stats['stock_bajo_total'] ?? 0) > 0 ? 'border-amber-500/40' : '' }}">
        <p class="{{ $label }}">Existencias bajo mínimo</p>
        <p class="mt-3 text-2xl font-bold font-mono {{ ($stats['stock_bajo_total'] ?? 0) > 0 ? 'text-amber-300' : 'text-emerald-300' }}">{{ number_format($stats['stock_bajo_total'] ?? 0) }}</p>
        <p class="mt-1 text-xs {{ $muted }}">Variante / almacén</p>
    </article>
</section>

<!-- Tipo de cambio -->
<section class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    @foreach (['paralelo' => 'Paralelo', 'oficial' => 'Oficial'] as $key => $nombre)
    @php($tcItem = $tc[$key] ?? null)
    <article class="{{ $panel }} p-5 flex items-center justify-between gap-4">
        <div>
            <p class="{{ $label }}">Tipo de cambio {{ $nombre }}</p>
            @if ($tcItem)
            <p class="mt-2 text-2xl font-bold font-mono text-white">Bs {{ number_format((float) $tcItem->valor, 2, ',', '.') }} <span class="text-sm font-normal text-slate-400">/ USD</span></p>
            <p class="mt-1 text-xs {{ $muted }}">Vigente desde {{ $tcItem->fecha?->format('d/m/Y') ?? '—' }}</p>
            @else
            <p class="mt-2 text-sm text-slate-400">Sin registro disponible</p>
            @endif
        </div>
        @if ($tcItem)
        <span class="rounded-lg border px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider {{ $tcItem->origen === 'API' ? 'border-sky-500/30 bg-sky-500/10 text-sky-300' : 'border-violet-500/30 bg-violet-500/10 text-violet-300' }}">{{ $tcItem->origen }}</span>
        @else
        <span class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-[10px] font-semibold text-slate-400">SIN DATOS</span>
        @endif
    </article>
    @endforeach
</section>