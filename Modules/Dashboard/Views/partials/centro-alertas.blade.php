@php
    $muted = 'text-slate-500 dark:text-slate-400';
    $label = 'text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400';
    $title = 'text-sm font-bold text-slate-900 dark:text-white';
    $panel = 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm';
    $sa = $stats['solicitudes_atrasadas'] ?? 0;
    $op = $stats['operaciones_pendientes'] ?? [];
    $pbm = $stats['productos_bajo_minimo'] ?? [];
    $uc = $stats['ultimas_cotizaciones'] ?? [];
    $cp = $stats['cotizaciones_pendientes'] ?? 0;
@endphp

<section class="grid grid-cols-1 lg:grid-cols-3 gap-5" aria-label="Centro de alertas">

    {{-- Stock crítico (solo gerencia) --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Stock crítico</p>
                <p class="mt-3 text-3xl font-bold font-mono {{ count($pbm) > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-400' }}">
                    {{ count($pbm) }}
                </p>
                <p class="mt-1 text-xs {{ $muted }}">Variantes bajo mínimo</p>
            </div>
            <span class="w-10 h-10 rounded-full {{ count($pbm) > 0 ? 'bg-amber-50 dark:bg-amber-950/20' : 'bg-emerald-50 dark:bg-emerald-950/20' }} flex items-center justify-center {{ count($pbm) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }} font-bold text-xs">
                {{ count($pbm) > 0 ? '⚠️' : '✓' }}
            </span>
        </div>
        @if (count($pbm) > 0)
        <div class="mt-4 space-y-2">
            @foreach (array_slice($pbm, 0, 4) as $p)
            <div class="flex items-center justify-between text-xs p-2 rounded bg-slate-50 dark:bg-slate-800/30">
                <span class="font-mono text-slate-600 dark:text-slate-300">{{ $p->sku }}</span>
                <span class="text-amber-600 dark:text-amber-300 font-bold">{{ $p->disponible }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="mt-6 text-xs text-center {{ $muted }}">Sin alertas de stock</p>
        @endif
    </article>
    @endif

    {{-- Cotizaciones por vencer (gerencia y vendedor) --}}
    @if ($esGerencia || $esVendedor)
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Cotizaciones por vencer</p>
                <p class="mt-3 text-3xl font-bold font-mono text-sky-600 dark:text-sky-400">{{ $cp }}</p>
                <p class="mt-1 text-xs {{ $muted }}">Vigentes pendientes de conversión</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-sky-50 dark:bg-sky-950/20 flex items-center justify-center text-sky-600 dark:text-sky-400 font-bold text-xs">
                📋
            </span>
        </div>
        @if (count($uc) > 0)
        <div class="mt-4 space-y-2">
            @foreach (array_slice($uc, 0, 4) as $c)
            <div class="flex items-center justify-between text-xs p-2 rounded bg-slate-50 dark:bg-slate-800/30">
                <span class="text-slate-600 dark:text-slate-300 truncate max-w-[120px]">{{ $c->cliente }}</span>
                <span class="text-xs font-semibold {{ $c->condicion_temporal === 'VIGENTE' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-300' }}">
                    {{ \Carbon\Carbon::parse($c->fecha_vencimiento)->format('d/m') }}
                </span>
            </div>
            @endforeach
        </div>
        @else
        <p class="mt-6 text-xs text-center {{ $muted }}">Sin cotizaciones pendientes</p>
        @endif
    </article>
    @endif

    {{-- Solicitudes atrasadas (gerencia y técnico) --}}
    @if ($esGerencia || $esTecnico)
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Solicitudes atrasadas</p>
                <p class="mt-3 text-3xl font-bold font-mono {{ $sa > 0 ? 'text-rose-600 dark:text-rose-300' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $sa }}</p>
                <p class="mt-1 text-xs {{ $muted }}">Sin atención hace +3 días</p>
            </div>
            <span class="w-10 h-10 rounded-full {{ $sa > 0 ? 'bg-rose-50 dark:bg-rose-950/20' : 'bg-emerald-50 dark:bg-emerald-950/20' }} flex items-center justify-center {{ $sa > 0 ? 'text-rose-600 dark:text-rose-300' : 'text-emerald-600 dark:text-emerald-400' }} font-bold text-xs">
                {{ $sa > 0 ? '⏰' : '✓' }}
            </span>
        </div>
        @if ($sa > 0 && $esTecnico)
        <div class="mt-3">
            <a href="{{ route('serviciotecnico.bandeja') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300">
                Ir a la Bandeja de Entrada
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
        @endif
    </article>
    @endif

    {{-- Operaciones pendientes (gerencia y vendedor) --}}
    @if ($esGerencia || $esVendedor)
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Operaciones pendientes</p>
                <p class="mt-3 text-3xl font-bold font-mono text-sky-600 dark:text-sky-400">{{ $op['total'] ?? 0 }}</p>
                <p class="mt-1 text-xs {{ $muted }}">Cotizaciones vigentes + ventas en proceso</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-950/20 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-xs">
                🔄
            </span>
        </div>
        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/50 space-y-2 text-xs">
            <div class="flex items-center justify-between">
                <span class="{{ $muted }}">Cotizaciones vigentes</span>
                <span class="font-mono text-slate-600 dark:text-slate-300">{{ $op['cotizaciones_vigentes'] ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="{{ $muted }}">Ventas en proceso</span>
                <span class="font-mono text-slate-600 dark:text-slate-300">{{ $op['ventas_en_proceso'] ?? 0 }}</span>
            </div>
        </div>
    </article>
    @endif

    {{-- Notificaciones del sistema (solo gerencia) --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Notificaciones importantes</p>
                <p class="mt-3 text-3xl font-bold font-mono text-violet-600 dark:text-violet-400">
                    @php $na = count($stats['alertas_sistema'] ?? []); @endphp {{ $na }}
                </p>
                <p class="mt-1 text-xs {{ $muted }}">Alertas del sistema activas</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-violet-50 dark:bg-violet-950/20 flex items-center justify-center text-violet-600 dark:text-violet-400 font-bold text-xs">
                🔔
            </span>
        </div>
        @if (!empty($stats['alertas_sistema']))
        <div class="mt-4 space-y-2">
            @foreach (array_slice($stats['alertas_sistema'], 0, 4) as $a)
            <div class="flex items-start gap-2 text-xs p-2 rounded bg-amber-50 dark:bg-amber-950/20">
                <span class="text-amber-600 dark:text-amber-400">⚠️</span>
                <span class="text-slate-600 dark:text-slate-300 truncate">{{ $a['titulo'] }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="mt-6 text-xs text-center {{ $muted }}">Sin notificaciones activas</p>
        @endif
    </article>
    @endif

</section>
