@extends('layouts.app')

@section('title', 'Panel de control')

@section('content')
@php
    $rol = $stats['rol'] ?? 'USUARIO';
    $esGerencia = in_array($rol, ['GERENTE', 'ADMINISTRADOR'], true);
    $esVendedor = $rol === 'VENDEDOR';
    $esTecnico = $rol === 'SERVICIO_TECNICO';
    $ventas = $stats['ventas'] ?? [];
    $panel = 'bg-slate-900 border border-slate-700/80 rounded-2xl shadow-xl shadow-black/10';
    $muted = 'text-slate-400';
    $label = 'text-[11px] font-semibold uppercase tracking-wider text-slate-400';
@endphp

<div class="min-h-full bg-slate-950 text-slate-100">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        {{-- Encabezado --}}
        <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-400">EwiflorWeb · Centro de operaciones</p>
                <h1 class="mt-2 text-2xl sm:text-3xl font-bold tracking-tight text-white">
                    Hola, {{ auth()->user()->nombre_completo }}
                </h1>
                <p class="mt-1 text-sm text-slate-400">
                    {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
                    <span class="mx-1 text-slate-600">·</span>
                    <span class="text-slate-300">{{ str_replace('_', ' ', $rol) }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,.55)]"></span>
                Panel actualizado al cargar la página
            </div>
        </header>

        {{-- Alertas del sistema --}}
        @if (!empty($stats['alertas_sistema']))
            <section class="space-y-3" aria-label="Alertas del sistema">
                @foreach ($stats['alertas_sistema'] as $alerta)
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <svg class="w-5 h-5 mt-0.5 shrink-0 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 4h.01M10.3 3.86 2.8 17a2 2 0 0 0 1.74 3h14.92a2 2 0 0 0 1.74-3l-7.46-13.14a2 2 0 0 0-3.48 0Z" />
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-amber-200">{{ $alerta['titulo'] }}</p>
                                <p class="mt-0.5 text-xs leading-relaxed text-amber-100/80">{{ $alerta['mensaje'] }}</p>
                            </div>
                        </div>
                        <a href="{{ $alerta['url'] }}" class="inline-flex items-center gap-1 text-xs font-semibold text-amber-200 hover:text-white transition sm:ml-4">
                            {{ $alerta['url_texto'] }} <span aria-hidden="true">→</span>
                        </a>
                    </div>
                @endforeach
            </section>
        @endif

        {{-- Indicadores principales --}}
        @if ($esGerencia)
            <section aria-label="Indicadores generales" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6 gap-4">
                <article class="{{ $panel }} p-5">
                    <div class="flex items-center justify-between gap-2">
                        <p class="{{ $label }}">Ventas de hoy</p>
                        <span class="text-sky-300 text-lg">↗</span>
                    </div>
                    <p class="mt-3 text-2xl font-bold font-mono text-white">Bs {{ number_format((float) ($ventas['total_hoy'] ?? 0), 2, ',', '.') }}</p>
                    <p class="mt-1 text-xs {{ $muted }}">{{ $ventas['cantidad_hoy'] ?? 0 }} ventas registradas</p>
                </article>

                <article class="{{ $panel }} p-5">
                    <div class="flex items-center justify-between gap-2">
                        <p class="{{ $label }}">Ventas del mes</p>
                        <span class="text-sky-300 text-lg">▥</span>
                    </div>
                    <p class="mt-3 text-2xl font-bold font-mono text-white">Bs {{ number_format((float) ($ventas['total_mes'] ?? 0), 2, ',', '.') }}</p>
                    <p class="mt-1 text-xs {{ $muted }}">{{ $ventas['cantidad_mes'] ?? 0 }} ventas registradas</p>
                </article>

                <article class="{{ $panel }} p-5">
                    <div class="flex items-center justify-between gap-2">
                        <p class="{{ $label }}">Cobrado este mes</p>
                        <span class="text-emerald-300 text-lg">✓</span>
                    </div>
                    <p class="mt-3 text-2xl font-bold font-mono text-emerald-300">Bs {{ number_format((float) ($ventas['pagado_mes'] ?? 0), 2, ',', '.') }}</p>
                    <p class="mt-1 text-xs {{ $muted }}">Pagos de ventas registradas</p>
                </article>

                <article class="{{ $panel }} p-5">
                    <div class="flex items-center justify-between gap-2">
                        <p class="{{ $label }}">Saldo por cobrar</p>
                        <span class="text-amber-300 text-lg">◷</span>
                    </div>
                    <p class="mt-3 text-2xl font-bold font-mono {{ ($ventas['saldo_pendiente'] ?? 0) > 0 ? 'text-amber-300' : 'text-white' }}">Bs {{ number_format((float) ($ventas['saldo_pendiente'] ?? 0), 2, ',', '.') }}</p>
                    <p class="mt-1 text-xs {{ $muted }}">Saldo de ventas registradas</p>
                </article>

                <article class="{{ $panel }} p-5">
                    <p class="{{ $label }}">Productos activos</p>
                    <p class="mt-3 text-2xl font-bold font-mono text-white">{{ number_format($stats['total_productos'] ?? 0) }}</p>
                    <p class="mt-1 text-xs {{ $muted }}">En el catálogo</p>
                </article>

                <article class="{{ $panel }} p-5 {{ ($stats['stock_bajo_total'] ?? 0) > 0 ? 'border-amber-500/40' : '' }}">
                    <p class="{{ $label }}">Existencias bajo mínimo</p>
                    <p class="mt-3 text-2xl font-bold font-mono {{ ($stats['stock_bajo_total'] ?? 0) > 0 ? 'text-amber-300' : 'text-emerald-300' }}">{{ number_format($stats['stock_bajo_total'] ?? 0) }}</p>
                    <p class="mt-1 text-xs {{ $muted }}">Variante / almacén</p>
                </article>
            </section>

            <section class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- Tipo de cambio --}}
                @foreach (['paralelo' => 'Paralelo', 'oficial' => 'Oficial'] as $key => $nombre)
                    @php($tc = $stats['tipo_cambio'][$key] ?? null)
                    <article class="{{ $panel }} p-5 flex items-center justify-between gap-4">
                        <div>
                            <p class="{{ $label }}">Tipo de cambio {{ $nombre }}</p>
                            @if ($tc)
                                <p class="mt-2 text-2xl font-bold font-mono text-white">Bs {{ number_format((float) $tc->valor, 2, ',', '.') }} <span class="text-sm font-normal text-slate-400">/ USD</span></p>
                                <p class="mt-1 text-xs {{ $muted }}">Vigente desde {{ $tc->fecha?->format('d/m/Y') ?? '—' }}</p>
                            @else
                                <p class="mt-2 text-sm text-slate-400">Sin registro disponible</p>
                            @endif
                        </div>
                        @if ($tc)
                            <span class="rounded-lg border px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider {{ $tc->origen === 'API' ? 'border-sky-500/30 bg-sky-500/10 text-sky-300' : 'border-violet-500/30 bg-violet-500/10 text-violet-300' }}">{{ $tc->origen }}</span>
                        @else
                            <span class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-[10px] font-semibold text-slate-400">SIN DATOS</span>
                        @endif
                    </article>
                @endforeach
            </section>
        @elseif ($esVendedor)
            <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4" aria-label="Indicadores de ventas">
                <article class="{{ $panel }} p-5">
                    <p class="{{ $label }}">Mis ventas de hoy</p>
                    <p class="mt-3 text-2xl font-bold font-mono text-white">Bs {{ number_format((float) ($ventas['total_hoy'] ?? 0), 2, ',', '.') }}</p>
                    <p class="mt-1 text-xs {{ $muted }}">{{ $ventas['cantidad_hoy'] ?? 0 }} ventas registradas</p>
                </article>
                <article class="{{ $panel }} p-5">
                    <p class="{{ $label }}">Mis ventas del mes</p>
                    <p class="mt-3 text-2xl font-bold font-mono text-white">Bs {{ number_format((float) ($ventas['total_mes'] ?? 0), 2, ',', '.') }}</p>
                    <p class="mt-1 text-xs {{ $muted }}">{{ $ventas['cantidad_mes'] ?? 0 }} ventas registradas</p>
                </article>
                <article class="{{ $panel }} p-5">
                    <p class="{{ $label }}">Cobrado este mes</p>
                    <p class="mt-3 text-2xl font-bold font-mono text-emerald-300">Bs {{ number_format((float) ($ventas['pagado_mes'] ?? 0), 2, ',', '.') }}</p>
                    <p class="mt-1 text-xs {{ $muted }}">Pagos de mis ventas</p>
                </article>
                <article class="{{ $panel }} p-5">
                    <p class="{{ $label }}">Cotizaciones vigentes</p>
                    <p class="mt-3 text-2xl font-bold font-mono text-sky-300">{{ $stats['cotizaciones_pendientes'] ?? 0 }}</p>
                    <p class="mt-1 text-xs {{ $muted }}">Pendientes de conversión</p>
                </article>
            </section>
        @elseif ($esTecnico)
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
        @endif

        {{-- Información operativa --}}
        <section class="grid grid-cols-1 2xl:grid-cols-2 gap-5">
            @if (isset($stats['productos_bajo_minimo']))
                <article class="{{ $panel }} overflow-hidden">
                    <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-slate-800">
                        <div>
                            <h2 class="text-sm font-bold text-white">Existencias que requieren atención</h2>
                            <p class="mt-0.5 text-xs text-slate-400">Registros por variante y almacén</p>
                        </div>
                        <a href="{{ route('catalogo.productos.index', ['stock_bajo' => 1]) }}" class="text-xs font-semibold text-sky-300 hover:text-sky-200">Ver catálogo →</a>
                    </div>
                    @if (count($stats['productos_bajo_minimo']) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs text-left">
                                <thead class="bg-slate-950/70 text-[10px] uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3 font-semibold">SKU</th>
                                        <th class="px-4 py-3 font-semibold">Producto</th>
                                        <th class="px-4 py-3 font-semibold">Almacén</th>
                                        <th class="px-4 py-3 text-right font-semibold">Disponible</th>
                                        <th class="px-4 py-3 text-right font-semibold">Mínimo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/80">
                                    @foreach ($stats['productos_bajo_minimo'] as $p)
                                        <tr class="hover:bg-slate-800/40 transition">
                                            <td class="px-4 py-3 font-mono text-slate-300">{{ $p->sku }}</td>
                                            <td class="px-4 py-3 font-medium text-slate-200">{{ $p->producto }}</td>
                                            <td class="px-4 py-3 text-slate-400">{{ $p->almacen }}</td>
                                            <td class="px-4 py-3 text-right font-bold text-amber-300">{{ $p->disponible }}</td>
                                            <td class="px-4 py-3 text-right text-slate-400">{{ $p->stock_minimo }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="px-5 py-10 text-center">
                            <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-300">✓</span>
                            <p class="mt-3 text-sm font-medium text-slate-200">Sin alertas de stock</p>
                            <p class="mt-1 text-xs text-slate-500">Las existencias están por encima del mínimo configurado.</p>
                        </div>
                    @endif
                </article>
            @endif

            @if (isset($stats['ultimas_ventas']))
                <article class="{{ $panel }} overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-800">
                        <h2 class="text-sm font-bold text-white">{{ $esVendedor ? 'Mis ventas recientes' : 'Ventas recientes' }}</h2>
                        <p class="mt-0.5 text-xs text-slate-400">Últimas ventas registradas</p>
                    </div>
                    @if (count($stats['ultimas_ventas']) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs text-left">
                                <thead class="bg-slate-950/70 text-[10px] uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3 font-semibold">Fecha</th>
                                        <th class="px-4 py-3 font-semibold">Cliente</th>
                                        @if ($esGerencia)<th class="px-4 py-3 font-semibold">Vendedor</th>@endif
                                        <th class="px-4 py-3 text-right font-semibold">Total</th>
                                        <th class="px-4 py-3 text-right font-semibold">Pagado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/80">
                                    @foreach ($stats['ultimas_ventas'] as $v)
                                        <tr class="hover:bg-slate-800/40 transition">
                                            <td class="px-4 py-3 text-slate-400">{{ \Carbon\Carbon::parse($v->fecha)->format('d/m/Y') }}</td>
                                            <td class="px-4 py-3 font-medium text-slate-200">{{ $v->cliente }}</td>
                                            @if ($esGerencia)<td class="px-4 py-3 text-slate-400">{{ $v->vendedor }}</td>@endif
                                            <td class="px-4 py-3 text-right font-semibold text-slate-200">Bs {{ number_format((float) $v->total_bs, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-right text-emerald-300">Bs {{ number_format((float) $v->pagado_bs, 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="px-5 py-10 text-center text-sm text-slate-500">Todavía no hay ventas registradas.</p>
                    @endif
                </article>
            @endif

            @if (isset($stats['ultimos_movimientos']))
                <article class="{{ $panel }} overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-800">
                        <h2 class="text-sm font-bold text-white">Actividad de inventario</h2>
                        <p class="mt-0.5 text-xs text-slate-400">Movimientos más recientes</p>
                    </div>
                    @if (count($stats['ultimos_movimientos']) > 0)
                        <div class="divide-y divide-slate-800/80">
                            @foreach ($stats['ultimos_movimientos'] as $m)
                                <div class="flex items-start gap-3 px-5 py-3.5">
                                    <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold {{ $m->tipo === 'ENTRADA' ? 'bg-emerald-500/10 text-emerald-300' : ($m->tipo === 'SALIDA' ? 'bg-rose-500/10 text-rose-300' : 'bg-sky-500/10 text-sky-300') }}">
                                        {{ $m->tipo === 'ENTRADA' ? '+' : ($m->tipo === 'SALIDA' ? '−' : '↔') }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-xs font-semibold text-slate-200">{{ str_replace('_', ' ', $m->tipo) }}</p>
                                            <span class="text-[10px] text-slate-500">{{ \Carbon\Carbon::parse($m->fecha_hora)->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-400">{{ $m->motivo }}</p>
                                        <p class="mt-1 text-[10px] text-slate-500">{{ $m->usuario }} · {{ $m->almacen_origen ?? '—' }} → {{ $m->almacen_destino ?? '—' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="px-5 py-10 text-center text-sm text-slate-500">Todavía no hay movimientos de inventario.</p>
                    @endif
                </article>
            @endif

            @if (isset($stats['ultimas_cotizaciones']))
                <article class="{{ $panel }} overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-800">
                        <h2 class="text-sm font-bold text-white">{{ $esVendedor ? 'Mis cotizaciones recientes' : 'Cotizaciones recientes' }}</h2>
                        <p class="mt-0.5 text-xs text-slate-400">Seguimiento de vigencia</p>
                    </div>
                    @if (count($stats['ultimas_cotizaciones']) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs text-left">
                                <thead class="bg-slate-950/70 text-[10px] uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3 font-semibold">Fecha</th>
                                        <th class="px-4 py-3 font-semibold">Cliente</th>
                                        <th class="px-4 py-3 font-semibold">Vencimiento</th>
                                        <th class="px-4 py-3 font-semibold">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/80">
                                    @foreach ($stats['ultimas_cotizaciones'] as $c)
                                        <tr class="hover:bg-slate-800/40 transition">
                                            <td class="px-4 py-3 text-slate-400">{{ \Carbon\Carbon::parse($c->fecha_emision)->format('d/m/Y') }}</td>
                                            <td class="px-4 py-3 font-medium text-slate-200">{{ $c->cliente }}</td>
                                            <td class="px-4 py-3 text-slate-400">{{ \Carbon\Carbon::parse($c->fecha_vencimiento)->format('d/m/Y') }}</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-bold {{ $c->condicion_temporal === 'VIGENTE' ? 'bg-emerald-500/10 text-emerald-300' : 'bg-slate-700 text-slate-300' }}">{{ $c->condicion_temporal }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="px-5 py-10 text-center text-sm text-slate-500">Todavía no hay cotizaciones registradas.</p>
                    @endif
                </article>
            @endif

            @if (isset($stats['ultimas_solicitudes']))
                <article class="{{ $panel }} overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-800">
                        <h2 class="text-sm font-bold text-white">Solicitudes de servicio</h2>
                        <p class="mt-0.5 text-xs text-slate-400">Asignadas a ti o pendientes de asignación</p>
                    </div>
                    @if (count($stats['ultimas_solicitudes']) > 0)
                        <div class="divide-y divide-slate-800/80">
                            @foreach ($stats['ultimas_solicitudes'] as $s)
                                <div class="px-5 py-3.5">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-xs font-semibold text-slate-200">{{ $s->cliente }}</p>
                                        <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-bold {{ $s->estado === 'PENDIENTE' ? 'bg-amber-500/10 text-amber-300' : 'bg-sky-500/10 text-sky-300' }}">{{ $s->estado }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($s->descripcion_falla, 100) }}</p>
                                    <p class="mt-1 text-[10px] text-slate-500">{{ \Carbon\Carbon::parse($s->fecha_solicitud)->format('d/m/Y H:i') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="px-5 py-10 text-center text-sm text-slate-500">No tienes solicitudes pendientes.</p>
                    @endif
                </article>
            @endif
        </section>

        @if (!$esGerencia && !$esVendedor && !$esTecnico)
            <section class="{{ $panel }} p-6">
                <h2 class="text-sm font-bold text-white">Bienvenido a EwiflorWeb</h2>
                <p class="mt-1 text-sm text-slate-400">Tu cuenta no tiene un panel operativo asignado. Si consideras que es un error, consulta con el administrador.</p>
            </section>
        @endif

        <footer class="border-t border-slate-800 pt-4 text-center text-[10px] text-slate-600">
            EwiflorWeb · Panel de control
        </footer>
    </div>
</div>
@endsection
