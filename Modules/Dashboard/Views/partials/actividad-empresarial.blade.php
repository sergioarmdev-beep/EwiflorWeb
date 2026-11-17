@php
    $muted = 'text-slate-500 dark:text-slate-400';
    $label = 'text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400';
    $title = 'text-sm font-bold text-slate-900 dark:text-white';
    $panel = 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm';
    $sub = 'mt-0.5 text-xs text-slate-500 dark:text-slate-400';
    $uv = $stats['ultimas_ventas'] ?? [];
    $um = $stats['ultimos_movimientos'] ?? [];
    $esGerencia = $esGerencia ?? false;
@endphp

<section class="grid grid-cols-1 lg:grid-cols-2 gap-5" aria-label="Actividad empresarial">

    {{-- Últimas ventas --}}
    <article class="{{ $panel }} overflow-hidden">
        <div class="px-5 py-4 {{ $headBar ?? 'border-b border-slate-200 dark:border-slate-800' }}">
            <h2 class="{{ $title }}">{{ $esVendedor ? 'Mis ventas recientes' : 'Ventas recientes' }}</h2>
            <p class="{{ $sub }}">Últimas ventas registradas</p>
        </div>
        @if (count($uv) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-slate-950/70 text-[10px] uppercase tracking-wider text-slate-500 dark:text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Fecha</th>
                        <th class="px-4 py-3 font-semibold">Cliente</th>
                        @if($esGerencia)<th class="px-4 py-3 font-semibold">Vendedor</th>@endif
                        <th class="px-4 py-3 text-right font-semibold">Total</th>
                        <th class="px-4 py-3 text-right font-semibold">Pagado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/80">
                    @foreach ($uv as $v)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-4 py-3 {{ $muted }}">{{ \Carbon\Carbon::parse($v->fecha)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ $v->cliente }}</td>
                        @if($esGerencia)<td class="px-4 py-3 {{ $muted }}">{{ $v->vendedor }}</td>@endif
                        <td class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-slate-200">
                            Bs {{ number_format((float) $v->total_bs, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400">
                            Bs {{ number_format((float) $v->pagado_bs, 2, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-10 text-center">
            <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-900 text-slate-400">🛒</span>
            <p class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-200">Sin ventas registradas</p>
        </div>
        @endif
    </article>

    {{-- Movimientos de inventario (solo gerencia) --}}
    @if ($esGerencia && isset($stats['ultimos_movimientos']))
    <article class="{{ $panel }} overflow-hidden">
        <div class="px-5 py-4 {{ $headBar ?? 'border-b border-slate-200 dark:border-slate-800' }}">
            <h2 class="{{ $title }}">Movimientos de inventario</h2>
            <p class="{{ $sub }}">Entradas, salidas y ajustes recientes</p>
        </div>
        @if (count($um) > 0)
        <div class="divide-y divide-slate-200 dark:divide-slate-800/80">
            @foreach ($um as $m)
            <div class="flex items-start gap-3 px-5 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                    @if($m->tipo === 'ENTRADA') bg-emerald-50 text-[#0B6E45] dark:bg-[#0B6E45]/15 dark:text-emerald-400
                    @elseif($m->tipo === 'SALIDA') bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400
                    @else bg-sky-50 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400 @endif">
                    @if ($m->tipo === 'ENTRADA')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m-8-8l8 8 8-8" />
                    </svg>
                    @elseif ($m->tipo === 'SALIDA')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 20V4m-8 8l8-8 8 8" />
                    </svg>
                    @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4 4-4m10 0l4 4-4 4M3 12h18" />
                    </svg>
                    @endif
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ str_replace('_', ' ', $m->tipo) }}</p>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500">{{ \Carbon\Carbon::parse($m->fecha_hora)->format('d/m/Y H:i') }}</span>
                    </div>
                    <p class="mt-1 text-xs {{ $muted }}">{{ $m->motivo }}</p>
                    <p class="mt-1 text-[10px] text-slate-400 dark:text-slate-500">{{ $m->usuario }} · {{ $m->almacen_origen ?? '—' }} → {{ $m->almacen_destino ?? '—' }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="px-5 py-10 text-center text-sm {{ $muted }}">Sin movimientos de inventario</p>
        @endif
    </article>
    @endif

    {{-- Compras recientes (solo gerencia) --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-6">
        <div class="flex flex-col items-center justify-center text-center">
            <span class="text-slate-400 dark:text-slate-600 text-2xl mb-2">🛒</span>
            <h3 class="{{ $label }}">Compras recientes</h3>
            <p class="mt-1 text-xs {{ $muted }}">Módulo de compras pendiente de implementación</p>
        </div>
    </article>
    @endif

    {{-- Usuarios responsables / cambios importantes (solo gerencia) --}}
    @if ($esGerencia)
    <article class="{{ $panel }} overflow-hidden">
        <div class="px-5 py-4 {{ $headBar ?? 'border-b border-slate-200 dark:border-slate-800' }}">
            <h2 class="{{ $title }}">Usuarios responsables</h2>
            <p class="{{ $sub }}">Últimos usuarios con actividad en el sistema</p>
        </div>
        @if (count($um) > 0)
        <div class="divide-y divide-slate-200 dark:divide-slate-800/80">
            @foreach (array_slice($um, 0, 5) as $m)
            <div class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 text-xs font-bold">
                    {{ strtoupper(substr($m->usuario, 0, 1)) }}
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-800 dark:text-slate-200">{{ $m->usuario }}</p>
                    <p class="text-[10px] {{ $muted }}">{{ str_replace('_', ' ', $m->tipo) }} · {{ \Carbon\Carbon::parse($m->fecha_hora)->format('d/m/Y H:i') }}</p>
                </div>
                <span class="ml-auto text-[10px] {{ $muted }}">{{ $m->almacen_origen ?? '—' }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="px-5 py-10 text-center text-sm {{ $muted }}">Sin actividad de usuarios registrada</p>
        @endif
    </article>
    @endif

</section>
