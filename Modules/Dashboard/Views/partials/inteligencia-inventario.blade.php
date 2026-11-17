@php
    $muted = 'text-slate-500 dark:text-slate-400';
    $label = 'text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400';
    $title = 'text-sm font-bold text-slate-900 dark:text-white';
    $panel = 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm';
    $sub = 'mt-0.5 text-xs text-slate-500 dark:text-slate-400';
    $epa = $stats['existencias_por_almacen'] ?? [];
    $vi = $stats['valor_inventario'] ?? 0;
    $pbm = $stats['productos_bajo_minimo'] ?? [];
@endphp

<section class="grid grid-cols-1 lg:grid-cols-3 gap-5" aria-label="Inteligencia de inventario">

    {{-- Stock disponible --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Stock disponible total</p>
                <p class="mt-3 text-2xl font-bold font-mono text-slate-900 dark:text-slate-100">
                    @php
                        $totalDisp = 0;
                        foreach ($epa as $e) { $totalDisp += (int) $e->disponible; }
                    @endphp
                    {{ number_format($totalDisp) }}
                </p>
                <p class="mt-1 text-xs {{ $muted }}">Unidades disponibles en almacenes</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-950/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold text-xs">
                📦
            </span>
        </div>
    </article>

    {{-- Inventario valorizado --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Inventario valorizado</p>
                <p class="mt-3 text-2xl font-bold font-mono text-slate-900 dark:text-slate-100">
                    Bs {{ number_format((float) $vi, 2, ',', '.') }}
                </p>
                <p class="mt-1 text-xs {{ $muted }}">Disponible × precio de lista</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-950/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xs">
                💵
            </span>
        </div>
    </article>

    {{-- Productos críticos --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Productos críticos</p>
                <p class="mt-3 text-2xl font-bold font-mono {{ count($pbm) > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-400' }}">
                    {{ count($pbm) }}
                </p>
                <p class="mt-1 text-xs {{ $muted }}">Bajo stock mínimo</p>
            </div>
            <span class="w-10 h-10 rounded-full {{ count($pbm) > 0 ? 'bg-amber-50 dark:bg-amber-950/20' : 'bg-emerald-50 dark:bg-emerald-950/20' }} flex items-center justify-center {{ count($pbm) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }} font-bold text-xs">
                {{ count($pbm) > 0 ? '⚠️' : '✓' }}
            </span>
        </div>
    </article>

    {{-- Existencias por almacén --}}
    <article class="{{ $panel }} overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800">
            <h2 class="{{ $title }}">Existencias por almacén</h2>
            <p class="{{ $sub }}">Stock disponible por ubicación</p>
        </div>
        @if (count($epa) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-slate-950/70 text-[10px] uppercase tracking-wider text-slate-500 dark:text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Almacén</th>
                        <th class="px-4 py-3 text-right font-semibold">Disponible</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/80">
                    @foreach ($epa as $e)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ $e->almacen }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">{{ (int) $e->disponible }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-10 text-center">
            <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-900 text-slate-400">🏭</span>
            <p class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-200">Sin existencias registradas</p>
        </div>
        @endif
    </article>

</section>
