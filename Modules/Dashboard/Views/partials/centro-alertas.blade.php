@php
$muted = 'text-muted';
$label = 'text-[11px] font-semibold uppercase tracking-wider text-muted';
$panel = 'bg-surface border border-line rounded-2xl shadow-soft';

$sa = $stats['solicitudes_atrasadas'] ?? 0;
$op = $stats['operaciones_pendientes'] ?? [];
$pbm = $stats['productos_bajo_minimo'] ?? [];
$uc = $stats['ultimas_cotizaciones'] ?? [];
$cp = $stats['cotizaciones_pendientes'] ?? 0;
$alertas = $stats['alertas_sistema'] ?? [];
$na = count($alertas);
@endphp

<section
    class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4"
    aria-label="Centro de alertas">

    {{-- Stock crítico --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Stock crítico</p>

                <p class="mt-3 text-3xl font-bold font-mono tracking-tight {{ count($pbm) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-brand-green' }}">
                    {{ count($pbm) }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Variantes bajo mínimo
                </p>
            </div>

            <div class="w-11 h-11 shrink-0 rounded-xl flex items-center justify-center {{ count($pbm) > 0 ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-brand-green/10 text-brand-green' }}">
                @if (count($pbm) > 0)
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

        @if (count($pbm) > 0)
        <div class="mt-5 pt-4 border-t border-line space-y-2">
            @foreach (array_slice($pbm, 0, 4) as $p)
            <div class="flex items-center justify-between gap-3 rounded-xl bg-surface-2 border border-line px-3 py-2.5">
                <span class="font-mono text-xs text-ink truncate">
                    {{ $p->sku }}
                </span>

                <span class="text-xs font-bold text-amber-600 dark:text-amber-400 shrink-0">
                    {{ $p->disponible }}
                </span>
            </div>
            @endforeach
        </div>
        @else
        <div class="mt-5 pt-4 border-t border-line">
            <p class="text-xs text-center text-brand-green font-medium">
                Sin alertas de stock
            </p>
        </div>
        @endif
    </article>
    @endif

    {{-- Cotizaciones por vencer --}}
    @if ($esGerencia || $esVendedor)
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Cotizaciones por vencer</p>

                <p class="mt-3 text-3xl font-bold font-mono tracking-tight text-sky-600 dark:text-sky-400">
                    {{ $cp }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Vigentes pendientes de conversión
                </p>
            </div>

            <div class="w-11 h-11 shrink-0 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 2v4M16 2v4M3 10h18" />
                    <rect x="3" y="4" width="18" height="18" rx="2" />
                    <path d="m9 16 2 2 4-4" />
                </svg>
            </div>
        </div>

        @if (count($uc) > 0)
        <div class="mt-5 pt-4 border-t border-line space-y-2">
            @foreach (array_slice($uc, 0, 4) as $c)
            <div class="flex items-center justify-between gap-3 rounded-xl bg-surface-2 border border-line px-3 py-2.5">
                <span class="text-xs text-ink truncate min-w-0">
                    {{ $c->cliente }}
                </span>

                <span class="text-xs font-semibold shrink-0 {{ $c->condicion_temporal === 'VIGENTE' ? 'text-brand-green' : 'text-muted' }}">
                    {{ \Carbon\Carbon::parse($c->fecha_vencimiento)->format('d/m') }}
                </span>
            </div>
            @endforeach
        </div>
        @else
        <div class="mt-5 pt-4 border-t border-line">
            <p class="text-xs text-center {{ $muted }}">
                Sin cotizaciones pendientes
            </p>
        </div>
        @endif
    </article>
    @endif

    {{-- Solicitudes atrasadas --}}
    @if ($esGerencia || $esTecnico)
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Solicitudes atrasadas</p>

                <p class="mt-3 text-3xl font-bold font-mono tracking-tight {{ $sa > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-brand-green' }}">
                    {{ $sa }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Sin atención hace +3 días
                </p>
            </div>

            <div class="w-11 h-11 shrink-0 rounded-xl flex items-center justify-center {{ $sa > 0 ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400' : 'bg-brand-green/10 text-brand-green' }}">
                @if ($sa > 0)
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 6v6l4 2" />
                </svg>
                @else
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m5 12 4 4L19 6" />
                    <circle cx="12" cy="12" r="10" />
                </svg>
                @endif
            </div>
        </div>

        @if ($sa > 0 && $esTecnico)
        <div class="mt-5 pt-4 border-t border-line">
            <a
                href="{{ route('serviciotecnico.bandeja') }}"
                class="inline-flex items-center gap-2 text-xs font-semibold text-brand-green hover:opacity-75 transition-opacity">
                Ir a la Bandeja de Entrada

                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
            </a>
        </div>
        @else
        <div class="mt-5 pt-4 border-t border-line">
            <p class="text-xs text-center {{ $muted }}">
                {{ $sa > 0 ? 'Requiere seguimiento' : 'Sin solicitudes atrasadas' }}
            </p>
        </div>
        @endif
    </article>
    @endif

    {{-- Operaciones pendientes --}}
    @if ($esGerencia || $esVendedor)
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Operaciones pendientes</p>

                <p class="mt-3 text-3xl font-bold font-mono tracking-tight text-sky-600 dark:text-sky-400">
                    {{ $op['total'] ?? 0 }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Cotizaciones vigentes + ventas en proceso
                </p>
            </div>

            <div class="w-11 h-11 shrink-0 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 7h-9M14 3l-4 4 4 4" />
                    <path d="M4 17h9M10 13l4 4-4 4" />
                </svg>
            </div>
        </div>

        <div class="mt-5 pt-4 border-t border-line space-y-3 text-xs">
            <div class="flex items-center justify-between gap-3">
                <span class="{{ $muted }}">Cotizaciones vigentes</span>
                <span class="font-mono font-semibold text-ink">
                    {{ $op['cotizaciones_vigentes'] ?? 0 }}
                </span>
            </div>

            <div class="flex items-center justify-between gap-3">
                <span class="{{ $muted }}">Ventas en proceso</span>
                <span class="font-mono font-semibold text-ink">
                    {{ $op['ventas_en_proceso'] ?? 0 }}
                </span>
            </div>
        </div>
    </article>
    @endif

    {{-- Notificaciones del sistema --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Notificaciones importantes</p>

                <p class="mt-3 text-3xl font-bold font-mono tracking-tight text-violet-600 dark:text-violet-400">
                    {{ $na }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Alertas del sistema activas
                </p>
            </div>

            <div class="w-11 h-11 shrink-0 rounded-xl bg-violet-500/10 text-violet-600 dark:text-violet-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                    <path d="M10 21h4" />
                </svg>
            </div>
        </div>

        @if (!empty($alertas))
        <div class="mt-5 pt-4 border-t border-line space-y-2">
            @foreach (array_slice($alertas, 0, 4) as $a)
            <div class="flex items-start gap-2.5 rounded-xl bg-amber-500/10 border border-amber-500/15 px-3 py-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m10.3 3.9-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3.1l-8-14a2 2 0 0 0-3.4 0Z" />
                    <path d="M12 9v4M12 17h.01" />
                </svg>

                <span class="text-xs text-ink truncate min-w-0">
                    {{ $a['titulo'] }}
                </span>
            </div>
            @endforeach
        </div>
        @else
        <div class="mt-5 pt-4 border-t border-line">
            <p class="text-xs text-center {{ $muted }}">
                Sin notificaciones activas
            </p>
        </div>
        @endif
    </article>
    @endif

</section>