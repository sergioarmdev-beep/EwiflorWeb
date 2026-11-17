@php
    $panel = 'bg-slate-900 border border-slate-700/80 rounded-2xl shadow-xl shadow-black/10';
    $muted = 'text-slate-400';
    $label = 'text-[11px] font-semibold uppercase tracking-wider text-slate-400';
@endphp

<!-- Indicadores de servicio técnico -->
<section class="grid grid-cols-1 sm:grid-cols-2 gap-4" aria-label="Indicadores de servicio técnico">
    <article class="{{ $panel }} p-5">
        <p class="{{ $label }}">Solicitudes por atender</p>
        <p class="mt-3 text-3xl font-bold font-mono text-sky-300">{{ $stats['solicitudes_pendientes'] ?? 0 }}</p>
        <p class="mt-1 text-xs {{ $muted }}">Asignadas a ti o sin asignar</p>
    </article>
    <article class="{{ $panel }} p-5">
        <p class="{{ $label }}">Garantías por vencer</p>
        <p class="mt-3 text-3xl font-bold font-mono {{ ($stats['garantias_por_vencer'] ?? 0) > 0 ? 'text-amber-300' : 'text-emerald-300' }}">{{ $stats['garantias_por_vencer'] ?? 0 }}</p>
        <p class="mt-1 text-xs {{ $muted }}">Durante los próximos 30 días</p>
    </article>
</section>

<!-- Flujo de solicitud activa del técnico -->
<section class="{{ $panel }} overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <h2 class="text-sm font-bold text-white">Mi solicitud activa</h2>
        <p class="mt-0.5 text-xs text-slate-400">Solo puedes tener una solicitud activa a la vez.</p>
    </div>
    @if ($stats['solicitud_activa'])
    <div class="px-5 py-5 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="text-xs font-semibold text-slate-200">{{ $stats['solicitud_activa']->cliente }}</p>
                <p class="mt-0.5 text-xs text-slate-400 font-mono">#{{ $stats['solicitud_activa']->id_solicitud }}</p>
            </div>
            <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-bold bg-sky-500/10 text-sky-300">
                Asignada a ti
            </span>
        </div>
        <p class="mt-1 text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($stats['solicitud_activa']->descripcion_falla, 120) }}</p>
        <p class="mt-1 text-[10px] text-slate-500">
            {{ \Carbon\Carbon::parse($stats['solicitud_activa']->fecha_solicitud)->format('d/m/Y H:i') }}
            @if ($stats['solicitud_activa']->id_garantia)
                <span class="ml-2 text-emerald-400">· Dentro de garantía</span>
            @else
                <span class="ml-2 text-amber-400">· Fuera de garantía</span>
            @endif
        </p>
        <div class="flex items-center gap-3 pt-2">
            <a href="{{ route('serviciotecnico.show', $stats['solicitud_activa']->id_solicitud) }}"
               class="w-full md:w-auto px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded font-medium transition">
                Finalizar servicio
            </a>
            <a href="{{ route('serviciotecnico.bandeja') }}"
               class="w-full md:w-auto px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded font-medium transition text-sm">
                Ver bandeja
            </a>
        </div>
    </div>
    @else
    <div class="px-5 py-10 text-center">
        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-sky-500/10 text-sky-300">🔧</span>
        <p class="mt-3 text-sm font-medium text-slate-200">No tienes ninguna solicitud activa</p>
        <p class="mt-1 text-xs text-slate-500">Ve a la bandeja para aceptar un encargo pendiente.</p>
        <a href="{{ route('serviciotecnico.bandeja') }}"
           class="mt-4 inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-sky-300 hover:text-sky-200">
            Ir a la bandeja <span aria-hidden="true">→</span>
        </a>
    </div>
    @endif
</section>
