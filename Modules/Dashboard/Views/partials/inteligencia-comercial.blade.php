@php
    $muted = 'text-slate-500 dark:text-slate-400';
    $label = 'text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400';
    $title = 'text-sm font-bold text-slate-900 dark:text-white';
    $panel = 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm';
    $primary = 'text-[#0B6E45] dark:text-emerald-400';
    $primaryTextH = 'text-[#0B6E45] dark:text-emerald-400 hover:text-[#095638] dark:hover:text-emerald-300';
    $pmv = $stats['productos_mas_vendidos'] ?? [];
    $cc = $stats['conversion_cotizaciones'] ?? [];
    $tp = $stats['ticket_promedio'] ?? 0;
    $tv = $stats['top_vendedores'] ?? [];
@endphp

<section class="grid grid-cols-1 lg:grid-cols-2 gap-5" aria-label="Inteligencia comercial">

    {{-- Evolución de ventas (gráfico) --}}
    <article class="{{ $panel }} overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800">
            <h2 class="{{ $title }}">Evolución de ventas</h2>
            <p class="mt-0.5 text-xs {{ $muted }}">Últimos 12 meses</p>
        </div>
        <div class="px-5 py-5">
            <div class="relative" style="height:200px;">
                <div class="chart-skeleton absolute inset-0 rounded-lg bg-slate-100 dark:bg-slate-800/40 flex items-center justify-center" data-chart-skeleton>
                    <span class="text-xs text-slate-400 dark:text-slate-500">Cargando gráfico…</span>
                </div>
                <canvas id="chartVentasMensuales" height="200" class="relative"></canvas>
            </div>
        </div>
    </article>

    {{-- Ventas por vendedor / métricas comerciales --}}
    <article class="{{ $panel }} p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="{{ $label }}">Conversión de cotizaciones</p>
                <p class="mt-2 text-2xl font-bold font-mono text-slate-900 dark:text-slate-100">
                    {{ number_format((float) ($cc['porcentaje'] ?? 0), 2, ',', '.') }}%
                </p>
                <p class="mt-1 text-xs {{ $muted }}">
                    {{ $cc['convertidas'] ?? 0 }} de {{ $cc['emitidas'] ?? 0 }} emitidas
                </p>
            </div>
            <div>
                <p class="{{ $label }}">Ticket promedio</p>
                <p class="mt-2 text-2xl font-bold font-mono text-slate-900 dark:text-slate-100">
                    Bs {{ number_format((float) $tp, 2, ',', '.') }}
                </p>
                <p class="mt-1 text-xs {{ $muted }}">Promedio por venta este mes</p>
            </div>
        </div>
    </article>

    {{-- Productos más vendidos --}}
    <article class="{{ $panel }} overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800">
            <h2 class="{{ $title }}">Productos más vendidos del mes</h2>
            <p class="{{ $sub }}">Por cantidad de unidades</p>
        </div>
        @if (count($pmv) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-slate-950/70 text-[10px] uppercase tracking-wider text-slate-500 dark:text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Producto</th>
                        <th class="px-4 py-3 font-semibold text-right">Cant.</th>
                        <th class="px-4 py-3 font-semibold text-right">Total (USD)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/80">
                    @foreach ($pmv as $p)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ $p->producto }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">{{ (int) $p->cantidad }}</td>
                        <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">USD {{ number_format((float) $p->total_usd, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-10 text-center">
            <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-900 text-slate-400">📊</span>
            <p class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-200">Sin ventas registradas este mes</p>
        </div>
        @endif
    </article>

    {{-- Top vendedores (solo gerencia) --}}
    @if ($esGerencia && count($tv) > 0)
    <article class="{{ $panel }} overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800">
            <h2 class="{{ $title }}">Vendedor que más vendió este mes</h2>
            <p class="{{ $sub }}">Ranking por monto facturado</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-slate-950/70 text-[10px] uppercase tracking-wider text-slate-500 dark:text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Vendedor</th>
                        <th class="px-4 py-3 text-right font-semibold">Ventas</th>
                        <th class="px-4 py-3 text-right font-semibold">Total (Bs)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/80">
                    @foreach ($tv as $v)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ $v->vendedor }}</td>
                        <td class="px-4 py-3 text-right font-semibold {{ $primary }}">{{ (int) $v->cantidad_ventas }}</td>
                        <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-slate-200">
                            Bs {{ number_format((float) $v->total_bs, 2, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>
    @endif

</section>
