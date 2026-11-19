@php
$muted = 'text-muted';
$label = 'text-[11px] font-bold uppercase tracking-wider text-muted';
$title = 'text-sm font-bold text-ink';
$panel = 'bg-surface border border-line rounded-2xl shadow-soft transition-all duration-200 hover:shadow-md hover:-translate-y-0.5';

$primaryBg = 'bg-brand-green hover:bg-brand-green/90';
$primaryText = 'text-white';

$sa = $stats['solicitudes_atrasadas'] ?? 0;
$sp = $stats['solicitudes_servicio_pendientes'] ?? 0;
$uss = $stats['ultimas_solicitudes_servicio'] ?? [];
$cp = $stats['cotizaciones_pendientes'] ?? 0;
$op = $stats['operaciones_pendientes'] ?? [];
@endphp

<section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4"
    aria-label="Seguimiento operativo">

    {{-- Solicitudes técnicas --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Solicitudes técnicas</p>

                <p class="mt-3 text-2xl font-bold font-mono tracking-tight text-sky-600 dark:text-sky-400">
                    {{ $sp }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Pendientes de atención
                </p>
            </div>

            <span class="shrink-0 w-11 h-11 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14.7 6.3a4 4 0 0 0-5.6 5.6l-5.6 5.6a2 2 0 0 0 2.8 2.8l5.6-5.6a4 4 0 0 0 5.6-5.6L15 11l-2-2 1.7-2.7Z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m17 3 4 4" />
                </svg>
            </span>
        </div>

        <div class="mt-5 pt-4 border-t border-line">
            <div class="flex items-center justify-between gap-3 text-xs">
                <span class="{{ $muted }}">Atrasadas (>3 días)</span>

                <span class="font-mono font-bold {{ $sa > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-ink' }}">
                    {{ $sa }}
                </span>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('serviciotecnico.bandeja') }}"
                class="group flex items-center gap-3 p-3.5 {{ $primaryBg }} {{ $primaryText }} rounded-xl transition-all duration-200 hover:shadow-md">

                <span class="shrink-0 w-10 h-10 rounded-lg bg-white/15 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 7h18v13H3zM3 7l3-4h12l3 4M9 12h6m-6 4h6" />
                    </svg>
                </span>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold leading-tight">
                        Ir a la Bandeja de Entrada
                    </p>
                    <p class="mt-1 text-[10px] text-white/75">
                        Ver solicitudes asignadas y pendientes
                    </p>
                </div>

                <svg class="w-4 h-4 shrink-0 transition-transform group-hover:translate-x-1"
                    fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M5 12h14m-6-6 6 6-6 6" />
                </svg>
            </a>
        </div>
    </article>

    {{-- Cotizaciones pendientes --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Cotizaciones pendientes</p>

                <p class="mt-3 text-2xl font-bold font-mono tracking-tight text-amber-600 dark:text-amber-400">
                    {{ $cp }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Vigentes, sin convertir
                </p>
            </div>

            <span class="shrink-0 w-11 h-11 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14 3v5h5M9 13h6m-6 4h6" />
                </svg>
            </span>
        </div>
    </article>
    @endif

    {{-- Operaciones sin finalizar --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="{{ $label }}">Operaciones sin finalizar</p>

                <p class="mt-3 text-2xl font-bold font-mono tracking-tight text-blue-600 dark:text-blue-400">
                    {{ $op['ventas_en_proceso'] ?? 0 }}
                </p>

                <p class="mt-1.5 text-xs {{ $muted }}">
                    Ventas en estado EN_PROCESO
                </p>
            </div>

            <span class="shrink-0 w-11 h-11 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="9" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 7v5l3 2" />
                </svg>
            </span>
        </div>
    </article>
    @endif

    {{-- Últimas solicitudes de servicio --}}
    <article class="{{ $panel }} overflow-hidden">
        <div class="px-5 py-4 border-b border-line">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="{{ $title }}">Últimas solicitudes de servicio</h2>
                    <p class="mt-1 text-xs {{ $muted }}">
                        Historial de encargos técnicos
                    </p>
                </div>

                <span class="shrink-0 w-9 h-9 rounded-lg bg-surface-2 text-muted flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                    </svg>
                </span>
            </div>
        </div>

        @if (count($uss) > 0)
        <div class="p-3 space-y-2">
            @foreach ($uss as $s)
            <div class="flex items-start gap-3 p-3 rounded-xl bg-surface-2 border border-line transition-colors hover:border-brand-green/30">

                <div class="shrink-0 w-9 h-9 rounded-lg bg-surface text-muted flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20 7 12 3 4 7v10l8 4 8-4V7Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m4 7 8 4 8-4M12 11v10" />
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-xs font-semibold text-ink">
                            {{ $s->cliente }}
                        </p>

                        <span class="inline-flex rounded-lg px-2 py-1 text-[10px] font-bold
                                    @if($s->estado === 'PENDIENTE')
                                        bg-amber-500/10 text-amber-700 dark:text-amber-400
                                    @elseif($s->estado === 'PROGRAMADA')
                                        bg-sky-500/10 text-sky-700 dark:text-sky-400
                                    @else
                                        bg-brand-green/10 text-brand-green
                                    @endif">
                            {{ $s->estado }}
                        </span>
                    </div>

                    <p class="mt-1.5 text-xs {{ $muted }} leading-relaxed">
                        {{ \Illuminate\Support\Str::limit($s->descripcion_falla, 80) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-[10px] text-muted">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 7v5l3 2" />
                        </svg>

                        {{ \Carbon\Carbon::parse($s->fecha_solicitud)->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-5 py-10 text-center">
            <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-brand-green/10 text-brand-green">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m5 12 4 4L19 6" />
                </svg>
            </span>

            <p class="mt-3 text-sm font-semibold text-ink">
                Sin solicitudes recientes
            </p>

            <p class="mt-1 text-xs {{ $muted }}">
                No hay encargos técnicos para mostrar
            </p>
        </div>
        @endif
    </article>

</section>