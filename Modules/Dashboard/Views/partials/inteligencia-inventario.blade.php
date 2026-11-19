@php
$muted = 'text-muted';
$label = 'text-[11px] font-semibold uppercase tracking-wider text-muted';
$title = 'text-sm font-bold text-ink';
$panel = 'bg-surface border border-line rounded-2xl shadow-soft';

$epa = $stats['existencias_por_almacen'] ?? [];
$vi = $stats['valor_inventario'] ?? 0;
$pbm = $stats['productos_bajo_minimo'] ?? [];

$totalDisp = 0;
foreach ($epa as $e) {
$totalDisp += (int) $e->disponible;
}

$totalCriticos = count($pbm);
@endphp

<section
    class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4"
    aria-label="Inteligencia de inventario">

    {{-- Stock disponible --}}
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Stock disponible total</p>

                <p class="mt-3 text-2xl font-bold font-mono tracking-tight text-ink">
                    {{ number_format($totalDisp) }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Unidades disponibles en almacenes
                </p>
            </div>

            <div class="w-11 h-11 shrink-0 rounded-xl bg-brand-green/10 text-brand-green flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 7 9-4 9 4-9 4-9-4Z" />
                    <path d="m3 12 9 4 9-4M3 17l9 4 9-4" />
                </svg>
            </div>
        </div>
    </article>

    {{-- Inventario valorizado --}}
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Inventario valorizado</p>

                <p class="mt-3 text-2xl font-bold font-mono tracking-tight text-ink">
                    Bs {{ number_format((float) $vi, 2, ',', '.') }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Disponible × precio de lista
                </p>
            </div>

            <div class="w-11 h-11 shrink-0 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="5" width="20" height="14" rx="2" />
                    <path d="M2 10h20M6 15h2" />
                </svg>
            </div>
        </div>
    </article>

    {{-- Productos críticos --}}
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Productos críticos</p>

                <p class="mt-3 text-2xl font-bold font-mono tracking-tight {{ $totalCriticos > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-brand-green' }}">
                    {{ $totalCriticos }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Bajo stock mínimo
                </p>
            </div>

            <div class="w-11 h-11 shrink-0 rounded-xl flex items-center justify-center {{ $totalCriticos > 0 ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-brand-green/10 text-brand-green' }}">
                @if ($totalCriticos > 0)
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m10.3 3.9-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3.1l-8-14a2 2 0 0 0-3.4 0Z" />
                    <path d="M12 9v4M12 17h.01" />
                </svg>
                @else
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m5 12 4 4L19 6" />
                    <circle cx="12" cy="12" r="10" />
                </svg>
                @endif
            </div>
        </div>
    </article>

    {{-- Existencias por almacén --}}
    <article class="{{ $panel }} p-5 sm:col-span-2 xl:col-span-3">
        <div class="flex items-center justify-between gap-3 mb-5">
            <div>
                <h2 class="{{ $title }}">Existencias por almacén</h2>
                <p class="mt-1 text-xs {{ $muted }}">
                    Stock disponible por ubicación
                </p>
            </div>

            <div class="w-10 h-10 shrink-0 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18M5 21V7l7-4 7 4v14" />
                    <path d="M9 21v-5h6v5M9 9h.01M15 9h.01M9 12h.01M15 12h.01" />
                </svg>
            </div>
        </div>

        @if (count($epa) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach ($epa as $e)
            <div class="rounded-xl bg-surface-2 border border-line p-4 transition-colors hover:border-brand-green/30">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex items-center gap-3">
                        <div class="w-9 h-9 shrink-0 rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 21h18M5 21V7l7-4 7 4v14" />
                                <path d="M9 9h.01M15 9h.01M9 13h.01M15 13h.01" />
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-ink truncate">
                                {{ $e->almacen }}
                            </p>
                            <p class="text-[11px] {{ $muted }}">
                                Existencias disponibles
                            </p>
                        </div>
                    </div>

                    <p class="text-lg font-bold font-mono text-ink shrink-0">
                        {{ number_format((int) $e->disponible) }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="rounded-xl border border-dashed border-line bg-surface-2 px-5 py-10 text-center">
            <div class="mx-auto w-11 h-11 rounded-xl bg-surface border border-line text-muted flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18M5 21V7l7-4 7 4v14" />
                    <path d="M9 9h.01M15 9h.01" />
                </svg>
            </div>

            <p class="mt-3 text-sm font-semibold text-ink">
                Sin existencias registradas
            </p>

            <p class="mt-1 text-xs {{ $muted }}">
                No hay stock disponible en los almacenes
            </p>
        </div>
        @endif
    </article>

</section>