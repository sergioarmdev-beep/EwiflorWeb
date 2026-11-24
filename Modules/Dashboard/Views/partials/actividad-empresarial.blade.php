@php
$muted = 'text-muted';
$label = 'text-[11px] font-bold uppercase tracking-wider text-muted';
$title = 'text-sm font-bold text-ink';
$panel = 'bg-surface border border-line rounded-2xl shadow-soft transition-all duration-200 hover:shadow-md hover:-translate-y-0.5';
$sub = 'mt-1 text-xs text-muted';

$uv = $stats['ultimas_ventas'] ?? [];
$um = $stats['ultimos_movimientos'] ?? [];
$esGerencia = $esGerencia ?? false;
@endphp

<section class="grid grid-cols-1 lg:grid-cols-2 gap-4"
    aria-label="Actividad empresarial">

    {{-- Últimas ventas --}}
    <article class="{{ $panel }} overflow-hidden">
        <div class="px-5 py-4 border-b border-line">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="{{ $title }}">
                        {{ $esVendedor ? 'Mis ventas recientes' : 'Ventas recientes' }}
                    </h2>
                    <p class="{{ $sub }}">Últimas ventas registradas</p>
                </div>

                <span class="shrink-0 w-10 h-10 rounded-xl bg-brand-green/10 text-brand-green flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.6L22 8H6" />
                        <circle cx="10" cy="21" r="1" />
                        <circle cx="18" cy="21" r="1" />
                    </svg>
                </span>
            </div>
        </div>

        @if (count($uv) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs text-left">
                <thead class="bg-surface-2 text-[10px] uppercase tracking-wider text-muted">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-bold">Fecha</th>
                        <th scope="col" class="px-4 py-3 font-bold">Cliente</th>
                        @if($esGerencia)
                        <th scope="col" class="px-4 py-3 font-bold">Vendedor</th>
                        @endif
                        <th scope="col" class="px-4 py-3 text-right font-bold">Total</th>
                        <th scope="col" class="px-4 py-3 text-right font-bold">Pagado</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-line">
                    @foreach ($uv as $v)
                    <tr class="transition-colors hover:bg-surface-2/70">
                        <td class="px-4 py-3 whitespace-nowrap {{ $muted }}">
                            {{ \Carbon\Carbon::parse($v->fecha)->format('d/m/Y') }}
                        </td>

                        <td class="px-4 py-3 font-semibold text-ink">
                            {{ $v->cliente }}
                        </td>

                        @if($esGerencia)
                        <td class="px-4 py-3 {{ $muted }}">
                            {{ $v->vendedor }}
                        </td>
                        @endif

                        <td class="px-4 py-3 text-right whitespace-nowrap font-semibold font-mono text-ink">
                            Bs {{ number_format((float) $v->total_bs, 2, ',', '.') }}
                        </td>

                        <td class="px-4 py-3 text-right whitespace-nowrap font-mono font-semibold text-brand-green">
                            Bs {{ number_format((float) $v->pagado_bs, 2, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-10 text-center">
            <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-surface-2 text-muted">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.6L22 8H6" />
                    <circle cx="10" cy="21" r="1" />
                    <circle cx="18" cy="21" r="1" />
                </svg>
            </span>

            <p class="mt-3 text-sm font-semibold text-ink">
                Sin ventas registradas
            </p>
            <p class="mt-1 text-xs {{ $muted }}">
                Las ventas recientes aparecerán aquí
            </p>
        </div>
        @endif
    </article>

    {{-- Movimientos de inventario --}}
    @if ($esGerencia && isset($stats['ultimos_movimientos']))
    <article class="{{ $panel }} overflow-hidden">
        <div class="px-5 py-4 border-b border-line">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="{{ $title }}">Movimientos de inventario</h2>
                    <p class="{{ $sub }}">Entradas, salidas y ajustes recientes</p>
                </div>

                <span class="shrink-0 w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 7h16M4 17h16M7 4l-3 3 3 3m10 4 3 3-3 3" />
                    </svg>
                </span>
            </div>
        </div>

        @if (count($um) > 0)
        <div class="p-3 space-y-2">
            @foreach ($um as $m)
            <div class="flex items-start gap-3 p-3 rounded-xl bg-surface-2 border border-line transition-colors hover:border-brand-green/30">

                <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg
                                @if($m->tipo === 'ENTRADA')
                                    bg-brand-green/10 text-brand-green
                                @elseif($m->tipo === 'SALIDA')
                                    bg-rose-500/10 text-rose-600 dark:text-rose-400
                                @else
                                    bg-sky-500/10 text-sky-600 dark:text-sky-400
                                @endif">

                    @if ($m->tipo === 'ENTRADA')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 4v16m-8-8 8 8 8-8" />
                    </svg>
                    @elseif ($m->tipo === 'SALIDA')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 20V4m-8 8 8-8 8 8" />
                    </svg>
                    @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7 16l-4-4 4-4m10 0 4 4-4 4M3 12h18" />
                    </svg>
                    @endif
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-xs font-bold text-ink">
                            {{ str_replace('_', ' ', $m->tipo) }}
                        </p>

                        <span class="text-[10px] text-muted">
                            {{ \Carbon\Carbon::parse($m->fecha_hora)->format('d/m/Y H:i') }}
                        </span>
                    </div>

                    <p class="mt-1.5 text-xs {{ $muted }}">
                        {{ $m->motivo }}
                    </p>

                    <div class="mt-2 flex flex-wrap items-center gap-x-1.5 gap-y-1 text-[10px] text-muted">
                        <span>{{ $m->usuario }}</span>
                        <span class="text-ink/30">·</span>
                        <span>{{ $m->almacen_origen ?? '—' }}</span>
                        <svg class="w-3 h-3 shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M5 12h14m-6-6 6 6-6 6" />
                        </svg>
                        <span>{{ $m->almacen_destino ?? '—' }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-5 py-10 text-center">
            <p class="text-sm {{ $muted }}">
                Sin movimientos de inventario
            </p>
        </div>
        @endif
    </article>
    @endif

    {{-- Compras recientes 
    @if ($esGerencia)
    <article class="{{ $panel }} p-5">
    <div class="flex items-start gap-4">
        <span class="shrink-0 w-11 h-11 rounded-xl bg-violet-500/10 text-violet-600 dark:text-violet-400 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.6L22 8H6" />
                <circle cx="10" cy="21" r="1" />
                <circle cx="18" cy="21" r="1" />
            </svg>
        </span>

        <div class="min-w-0">
            <h3 class="{{ $label }}">Compras recientes</h3>
            <p class="mt-1.5 text-xs {{ $muted }}">
                Módulo de compras pendiente de implementación
            </p>
            <span class="inline-flex mt-3 px-2.5 py-1 rounded-lg bg-surface-2 border border-line text-[10px] font-semibold text-muted">
                Próximamente
            </span>
        </div>
    </div>
    </article>
    @endif
    --}}
    {{-- Usuarios responsables --}}
    @if ($esGerencia)
    <article class="{{ $panel }} overflow-hidden">
        <div class="px-5 py-4 border-b border-line">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="{{ $title }}">Usuarios responsables</h2>
                    <p class="{{ $sub }}">Últimos usuarios con actividad en el sistema</p>
                </div>

                <span class="shrink-0 w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="8" r="4" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 21v-2a8 8 0 0 1 16 0v2" />
                    </svg>
                </span>
            </div>
        </div>

        @if (count($um) > 0)
        <div class="p-3 space-y-2">
            @foreach (array_slice($um, 0, 5) as $m)
            <div class="flex items-center gap-3 p-3 rounded-xl bg-surface-2 border border-line transition-colors hover:border-brand-green/30">

                <div class="w-9 h-9 shrink-0 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">
                    {{ strtoupper(substr($m->usuario, 0, 1)) }}
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-ink truncate">
                        {{ $m->usuario }}
                    </p>

                    <p class="mt-1 text-[10px] {{ $muted }}">
                        {{ str_replace('_', ' ', $m->tipo) }}
                        ·
                        {{ \Carbon\Carbon::parse($m->fecha_hora)->format('d/m/Y H:i') }}
                    </p>
                </div>

                <span class="shrink-0 text-[10px] text-muted text-right">
                    {{ $m->almacen_origen ?? '—' }}
                </span>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-5 py-10 text-center">
            <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-surface-2 text-muted">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="8" r="4" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 21v-2a8 8 0 0 1 16 0v2" />
                </svg>
            </span>

            <p class="mt-3 text-sm font-semibold text-ink">
                Sin actividad registrada
            </p>

            <p class="mt-1 text-xs {{ $muted }}">
                No hay usuarios con actividad reciente
            </p>
        </div>
        @endif
    </article>
    @endif

</section>