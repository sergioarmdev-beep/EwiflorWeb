@php
    $muted = 'text-slate-500 dark:text-slate-400';
    $label = 'text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400';
    $title = 'text-sm font-bold text-slate-900 dark:text-white';
    $panel = 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm';
    $sub = 'mt-0.5 text-xs text-slate-500 dark:text-slate-400';
    $primaryBg = 'bg-[#0B6E45] hover:bg-[#095638] dark:bg-emerald-600 dark:hover:bg-emerald-700';
    $primaryText = 'text-white';
    $sa = $stats['solicitudes_atrasadas'] ?? 0;
    $sp = $stats['solicitudes_servicio_pendientes'] ?? 0;
    $uss = $stats['ultimas_solicitudes_servicio'] ?? [];
    $cp = $stats['cotizaciones_pendientes'] ?? 0;
    $op = $stats['operaciones_pendientes'] ?? [];
@endphp

<section class="grid grid-cols-1 lg:grid-cols-3 gap-5" aria-label="Seguimiento operativo">

    {{-- Solicitudes técnicas --}}
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Solicitudes técnicas</p>
                <p class="mt-3 text-2xl font-bold font-mono text-sky-600 dark:text-sky-400">{{ $sp }}</p>
                <p class="mt-1 text-xs {{ $muted }}">Pendientes de atención</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-sky-50 dark:bg-sky-950/20 flex items-center justify-center text-sky-600 dark:text-sky-400 font-bold text-xs">
                🔧
            </span>
        </div>
        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/50 space-y-2 text-xs">
            <div class="flex items-center justify-between">
                <span class="{{ $muted }}">Atrasadas (>3 días)</span>
                <span class="font-mono text-slate-600 dark:text-slate-300">{{ $sa }}</span>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/50">
            <a href="{{ route('serviciotecnico.bandeja') }}"
                class="flex items-center gap-2 px-4 py-3 {{ $primaryBg }} {{ $primaryText }} rounded-xl font-medium transition-colors">
                <span class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-emerald-200">
                    📥
                </span>
                <div>
                    <p class="text-sm font-semibold leading-tight">Ir a la Bandeja de Entrada</p>
                    <p class="text-[10px] text-emerald-100">Ver todas tus solicitudes asignadas y pendientes</p>
                </div>
            </a>
        </div>
    </article>

    {{-- Cotizaciones pendientes (solo gerencia) --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Cotizaciones pendientes</p>
                <p class="mt-3 text-2xl font-bold font-mono text-amber-600 dark:text-amber-300">{{ $cp }}</p>
                <p class="mt-1 text-xs {{ $muted }}">Vigentes, sin convertir</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-amber-50 dark:bg-amber-950/20 flex items-center justify-center text-amber-600 dark:text-amber-300 font-bold text-xs">
                📋
            </span>
        </div>
    </article>
    @endif

    {{-- Operaciones sin finalizar (solo gerencia) --}}
    @if ($esGerencia)
    <article class="{{ $panel }} p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="{{ $label }}">Operaciones sin finalizar</p>
                <p class="mt-3 text-2xl font-bold font-mono text-blue-600 dark:text-blue-400">{{ $op['ventas_en_proceso'] ?? 0 }}</p>
                <p class="mt-1 text-xs {{ $muted }}">Ventas en estado EN_PROCESO</p>
            </div>
            <span class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-950/20 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-xs">
                ⏳
            </span>
        </div>
    </article>
    @endif

    {{-- Estado de las tareas (solicitudes recientes) --}}
    <article class="{{ $panel }} overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800">
            <h2 class="{{ $title }}">Últimas solicitudes de servicio</h2>
            <p class="{{ $sub }}">Historial de encargos técnicos</p>
        </div>
        @if (count($uss) > 0)
        <div class="divide-y divide-slate-200 dark:divide-slate-800/80">
            @foreach ($uss as $s)
            <div class="flex items-start gap-3 px-5 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $s->cliente }}</p>
                        <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-bold
                            @if($s->estado === 'PENDIENTE')
                                bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300
                            @elseif($s->estado === 'PROGRAMADA')
                                bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-400
                            @else
                                bg-emerald-50 text-[#0B6E45] dark:bg-emerald-500/15 dark:text-emerald-400
                            @endif">
                            {{ $s->estado }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs {{ $muted }}">{{ \Illuminate\Support\Str::limit($s->descripcion_falla, 80) }}</p>
                    <p class="mt-1 text-[10px] text-slate-400 dark:text-slate-500">{{ \Carbon\Carbon::parse($s->fecha_solicitud)->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-5 py-10 text-center">
            <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-400">✓</span>
            <p class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-200">Sin solicitudes recientes</p>
        </div>
        @endif
    </article>

</section>
