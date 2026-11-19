@extends('layouts.app')

@section('title', 'Panel de control')

@section('content')
@php
    $rol = $stats['rol'] ?? 'USUARIO';
    $esGerencia = in_array($rol, ['GERENTE', 'ADMINISTRADOR'], true);
    $esVendedor = $rol === 'VENDEDOR';
    $esTecnico = $rol === 'SERVICIO_TECNICO';
    $ventas = $stats['ventas'] ?? [];
    $panel = 'bg-surface border border-line rounded-2xl shadow-soft';
    $muted = 'text-muted';
    $label = 'text-[11px] font-semibold uppercase tracking-wider text-muted';
@endphp

<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- Encabezado --}}
    <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-blue">EwiflorWeb · Centro de operaciones</p>
            <h1 class="mt-2 text-2xl sm:text-3xl font-bold tracking-tight text-ink">
                Hola, {{ auth()->user()->nombre_completo }}
            </h1>
            <p class="mt-1 text-sm text-muted">
                {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
                <span class="mx-1 text-muted">·</span>
                <span class="text-muted">{{ str_replace('_', ' ', $rol) }}</span>
            </p>
        </header>
        <div class="flex items-center gap-2 text-xs text-muted">
            <span class="h-2 w-2 rounded-full bg-brand-green shadow-[0_0_10px_rgba(42,152,50,.55)]"></span>
            Panel actualizado al cargar la página
        </div>
    </header>

    {{-- Alertas del sistema --}}
    @if (!empty($stats['alertas_sistema']))
        <section class="space-y-3" aria-label="Alertas del sistema">
            @foreach ($stats['alertas_sistema'] as $alerta)
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 rounded-xl border border-amber-500/30 bg-amber-500/20 px-4 py-3">
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        <svg class="w-5 h-5 mt-0.5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 4h.01M10.3 3.86 2.8 17a2 2 0 0 0 1.74 3h14.92a2 2 0 0 0 1.74-3l-7.46-13.14a2 2 0 0 0-3.48 0Z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-amber-500">{{ $alerta['titulo'] }}</p>
                            <p class="mt-0.5 text-xs leading-relaxed text-amber-400">{{ $alerta['mensaje'] }}</p>
                        </div>
                    </div>
                    <a href="{{ $alerta['url'] }}" class="inline-flex items-center gap-1 text-xs font-semibold text-amber-500 hover:text-brand-green-dark transition sm:ml-4">
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
                    <span class="text-brand-blue text-lg">↗</span>
                </div>
                <p class="mt-3 text-2xl font-bold font-mono text-ink">Bs {{ number_format((float) ($ventas['total_hoy'] ?? 0), 2, ',', '.') }}</p>
                <p class="mt-1 text-xs {{ $muted }}">{{ $ventas['cantidad_hoy'] ?? 0 }} ventas registradas</p>
            </article>

            <article class="{{ $panel }} p-5">
                <div class="flex items-center justify-between gap-2">
                    <p class="{{ $label }}">Ventas del mes</p>
                    <span class="text-brand-blue text-lg">▥</span>
                </div>
                <p class="mt-3 text-2xl font-bold font-mono text-ink">Bs {{ number_format((float) ($ventas['total_mes'] ?? 0), 2, ',', '.') }}</p>
                <p class="mt-1 text-xs {{ $muted }}">{{ $ventas['cantidad_mes'] ?? 0 }} ventas registradas</p>
            </article>

            <article class="{{ $panel }} p-5">
                <div class="flex items-center justify-between gap-2">
                    <p class="{{ $label }}">Cobrado este mes</p>
                    <span class="text-brand-green text-lg">✓</span>
                </div>
                <p class="mt-3 text-2xl font-bold font-mono text-brand-green">Bs {{ number_format((float) ($ventas['pagado_mes'] ?? 0), 2, ',', '.') }}</p>
                <p class="mt-1 text-xs {{ $muted }}">Pagos de ventas registradas</p>
            </article>

            <article class="{{ $panel }} p-5">
                <div class="flex items-center justify-between gap-2">
                    <p class="{{ $label }}">Saldo por cobrar</p>
                    <span class="text-amber-500 text-lg">◷</span>
                </div>
                <p class="mt-3 text-2xl font-bold font-mono {{ ($ventas['saldo_pendiente'] ?? 0) > 0 ? 'text-amber-500' : 'text-ink' }}">Bs {{ number_format((float) ($ventas['saldo_pendiente'] ?? 0), 2, ',', '.') }}</p>
                <p class="mt-1 text-xs {{ $muted }}">Saldo de ventas registradas</p>
            </article>

            <article class="{{ $panel }} p-5">
                <p class="{{ $label }}">Productos activos</p>
                <p class="mt-3 text-2xl font-bold font-mono text-ink">{{ number_format($stats['total_productos'] ?? 0) }}</p>
                <p class="mt-1 text-xs {{ $muted }}">En el catálogo</p>
            </article>

            <article class="{{ $panel }} p-5 {{ ($stats['stock_bajo_total'] ?? 0) > 0 ? 'border-amber-500/40' : '' }}">
                <p class="{{ $label }}">Existencias bajo mínimo</p>
                <p class="mt-3 text-2xl font-bold font-mono {{ ($stats['stock_bajo_total'] ?? 0) > 0 ? 'text-amber-500' : 'text-brand-green' }}">{{ number_format($stats['stock_bajo_total'] ?? 0) }}</p>
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
                            <p class="mt-2 text-2xl font-bold font-mono text-ink">Bs {{ number_format((float) $tc->valor, 2, ',', '.') }} <span class="text-sm font-normal text-muted">/ USD</span></p>
                            <p class="mt-1 text-xs {{ $muted }}">Vigente desde {{ $tc->fecha?->format('d/m/Y') ?? '—' }}</p>
                        @else
                            <p class="mt-2 text-sm text-muted">Sin registro disponible</p>
                        @endif
                    </div>
                    @if ($tc)
                        <span class="rounded-lg border px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider {{ $tc->origen === 'API' ? 'border-brand-blue/40 bg-brand-blue/20 text-brand-blue' : 'border border-line bg-surface-2 text-muted' }}">{{ $tc->origen }}</span>
                    @else
                        <span class="rounded-lg border border-line bg-surface-2 px-3 py-1.5 text-[10px] font-semibold text-muted">SIN DATOS</span>
                    @endif
                </article>
            @endforeach
        </section>
    @elseif ($esVendedor)
        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4" aria-label="Indicadores de ventas">
            <article class="{{ $panel }} p-5">
                <p class="{{ $label }}">Mis ventas de hoy</p>
                <p class="mt-3 text-2xl font-bold font-mono text-ink">Bs {{ number_format((float) ($ventas['total_hoy'] ?? 0), 2, ',', '.') }}</p>
                <p class="mt-1 text-xs {{ $muted }}">{{ $ventas['cantidad_hoy'] ?? 0 }} ventas registradas</p>
            </article>
            <article class="{{ $panel }} p-5">
                <p class="{{ $label }}">Mis ventas del mes</p>
                <p class="mt-3 text-2xl font-bold font-mono text-ink">Bs {{ number_format((float) ($ventas['total_mes'] ?? 0), 2, ',', '.') }}</p>
                <p class="mt-1 text-xs {{ $muted }}">{{ $ventas['cantidad_mes'] ?? 0 }} ventas registradas</p>
            </article>
            <article class="{{ $panel }} p-5">
                <p class="{{ $label }}">Cobrado este mes</p>
                <p class="mt-3 text-2xl font-bold font-mono text-brand-green">Bs {{ number_format((float) ($ventas['pagado_mes'] ?? 0), 2, ',', '.') }}</p>
                <p class="mt-1 text-xs {{ $muted }}">Pagos de mis ventas</p>
            </article>
            <article class="{{ $panel }} p-5">
                <p class="{{ $label }}">Cotizaciones vigentes</p>
                <p class="mt-3 text-2xl font-bold font-mono text-brand-blue">{{ $stats['cotizaciones_pendientes'] ?? 0 }}</p>
                <p class="mt-1 text-xs {{ $muted }}">Pendientes de conversión</p>
            </article>
        </section>
    @elseif ($esTecnico)
        <section class="grid grid-cols-1 sm:grid-cols-2 gap-4" aria-label="Indicadores de servicio técnico">
            <article class="{{ $panel }} p-5">
                <p class="{{ $label }}">Solicitudes por atender</p>
                <p class="mt-3 text-3xl font-bold font-mono text-brand-blue">{{ $stats['solicitudes_pendientes'] ?? 0 }}</p>
                <p class="mt-1 text-xs {{ $muted }}">Asignadas a ti o sin asignar</p>
            </article>
            <article class="{{ $panel }} p-5">
                <p class="{{ $label }}">Garantías por vencer</p>
                <p class="mt-3 text-3xl font-bold font-mono {{ ($stats['garantias_por_vencer'] ?? 0) > 0 ? 'text-amber-500' : 'text-brand-green' }}">{{ $stats['garantias_por_vencer'] ?? 0 }}</p>
                <p class="mt-1 text-xs {{ $muted }}">Durante los próximos 30 días</p>
            </article>
        </section>

        {{-- Flujo de solicitud activa del técnico --}}
        <section class="{{ $panel }} overflow-hidden">
            <div class="px-5 py-4 border-b border-line">
                <h2 class="text-sm font-bold text-ink">Mi solicitud activa</h2>
                <p class="mt-0.5 text-xs text-muted">Solo puedes tener una solicitud activa a la vez.</p>
            </div>
            @if ($stats['solicitud_activa'])
                <div class="px-5 py-5 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold text-muted">{{ $stats['solicitud_activa']->cliente }}</p>
                            <p class="mt-0.5 text-xs text-muted font-mono">#{{ $stats['solicitud_activa']->id_solicitud }}</p>
                        </div>
                        <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-bold bg-brand-blue/20 text-brand-blue">
                            Asignada a ti
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-muted">{{ \Illuminate\Support\Str::limit($stats['solicitud_activa']->descripcion_falla, 120) }}</p>
                    <p class="mt-1 text-[10px] text-muted">
                        {{ \Carbon\Carbon::parse($stats['solicitud_activa']->fecha_solicitud)->format('d/m/Y H:i') }}
                        @if ($stats['solicitud_activa']->id_garantia)
                            <span class="ml-2 text-brand-green">· Dentro de garantía</span>
                        @else
                            <span class="ml-2 text-amber-500">· Fuera de garantía</span>
                        @endif
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <a href="{{ route('serviciotecnico.show', $stats['solicitud_activa']->id_solicitud) }}"
                           class="w-full md:w-auto px-4 py-2 bg-brand-green text-white rounded font-medium hover:bg-brand-green-dark transition">
                            Finalizar servicio
                        </a>
                        <a href="{{ route('serviciotecnico.bandeja') }}"
                           class="w-full md:w-auto px-4 py-2 text-muted hover:text-ink hover:bg-surface-2 rounded font-medium transition text-sm">
                            Ver bandeja
                        </a>
                    </div>
                </div>
            @else
                <div class="px-5 py-10 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-blue/20 text-brand-blue">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <p class="mt-3 text-sm font-medium text-ink">No tienes ninguna solicitud activa</p>
                    <p class="mt-1 text-xs text-muted">Ve a la bandeja para aceptar un encargo pendiente.</p>
                    <a href="{{ route('serviciotecnico.bandeja') }}"
                       class="mt-4 inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-brand-blue hover:text-brand-blue-dark">
                        Ir a la bandeja <span aria-hidden="true">→</span>
                    </a>
                </div>
            @endif
        </section>
    @endif

    {{-- Información operativa --}}
    <section class="grid grid-cols-1 2xl:grid-cols-2 gap-5">
        @if (isset($stats['productos_bajo_minimo']))
            <article class="{{ $panel }} overflow-hidden">
                <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-line">
                    <div>
                        <h2 class="text-sm font-bold text-ink">Existencias que requieren atención</h2>
                        <p class="mt-0.5 text-xs text-muted">Registros por variante y almacén</p>
                    </div>
                    <a href="{{ route('catalogo.productos.index', ['stock_bajo' => 1]) }}" class="text-xs font-semibold text-brand-blue hover:text-brand-blue-dark">Ver catálogo →</a>
                </div>
                @if (count($stats['productos_bajo_minimo']) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs text-left">
                            <thead class="bg-surface-2 text-[10px] uppercase tracking-wider text-muted">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">SKU</th>
                                    <th class="px-4 py-3 font-semibold">Producto</th>
                                    <th class="px-4 py-3 font-semibold">Almacén</th>
                                    <th class="px-4 py-3 text-right font-semibold">Disponible</th>
                                    <th class="px-4 py-3 text-right font-semibold">Mínimo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($stats['productos_bajo_minimo'] as $p)
                                    <tr class="transition-colors hover:bg-surface-2">
                                        <td class="px-4 py-3 font-mono text-muted">{{ $p->sku }}</td>
                                        <td class="px-4 py-3 font-medium text-ink">{{ $p->producto }}</td>
                                        <td class="px-4 py-3 text-muted">{{ $p->almacen }}</td>
                                        <td class="px-4 py-3 text-right font-bold text-amber-500">{{ $p->disponible }}</td>
                                        <td class="px-4 py-3 text-right text-muted">{{ $p->stock_minimo }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-5 py-10 text-center">
                        <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-brand-green/20 text-brand-green">✓</span>
                        <p class="mt-3 text-sm font-medium text-ink">Sin alertas de stock</p>
                        <p class="mt-1 text-xs text-muted">Las existencias están por encima del mínimo configurado.</p>
                    </div>
                @endif
            </article>
        @endif

        @if (isset($stats['ultimas_ventas']))
            <article class="{{ $panel }} overflow-hidden">
                <div class="px-5 py-4 border-b border-line">
                    <h2 class="text-sm font-bold text-ink">{{ $esVendedor ? 'Mis ventas recientes' : 'Ventas recientes' }}</h2>
                    <p class="mt-0.5 text-xs text-muted">Últimas ventas registradas</p>
                </div>
                @if (count($stats['ultimas_ventas']) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs text-left">
                            <thead class="bg-surface-2 text-[10px] uppercase tracking-wider text-muted">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Fecha</th>
                                    <th class="px-4 py-3 font-semibold">Cliente</th>
                                    @if ($esGerencia)<th class="px-4 py-3 font-semibold">Vendedor</th>@endif
                                    <th class="px-4 py-3 text-right font-semibold">Total</th>
                                    <th class="px-4 py-3 text-right font-semibold">Pagado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($stats['ultimas_ventas'] as $v)
                                    <tr class="transition-colors hover:bg-surface-2">
                                        <td class="px-4 py-3 text-muted">{{ \Carbon\Carbon::parse($v->fecha)->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 font-medium text-ink">{{ $v->cliente }}</td>
                                        @if ($esGerencia)<td class="px-4 py-3 text-muted">{{ $v->vendedor }}</td>@endif
                                        <td class="px-4 py-3 text-right font-semibold text-ink">Bs {{ number_format((float) $v->total_bs, 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-right text-brand-green">Bs {{ number_format((float) $v->pagado_bs, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="px-5 py-10 text-center text-sm text-muted">Todavía no hay ventas registradas.</p>
                @endif
            </article>
        @endif

        @if (isset($stats['ultimos_movimientos']))
            <article class="{{ $panel }} overflow-hidden">
                <div class="px-5 py-4 border-b border-line">
                    <h2 class="text-sm font-bold text-ink">Actividad de inventario</h2>
                    <p class="mt-0.5 text-xs text-muted">Movimientos más recientes</p>
                </div>
                @if (count($stats['ultimos_movimientos']) > 0)
                    <div class="divide-y divide-line">
                        @foreach ($stats['ultimos_movimientos'] as $m)
                            <div class="flex items-start gap-3 px-5 py-3.5">
                                <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold {{ $m->tipo === 'ENTRADA' ? 'bg-brand-green/20 text-brand-green' : ($m->tipo === 'SALIDA' ? 'bg-red-500/20 text-red-500' : 'bg-brand-blue/20 text-brand-blue') }}">
                                    {{ $m->tipo === 'ENTRADA' ? '+' : ($m->tipo === 'SALIDA' ? '−' : '↔') }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-xs font-semibold text-ink">{{ str_replace('_', ' ', $m->tipo) }}</p>
                                        <span class="text-[10px] text-muted">{{ \Carbon\Carbon::parse($m->fecha_hora)->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-muted">{{ $m->motivo }}</p>
                                    <p class="mt-1 text-[10px] text-muted">{{ $m->usuario }} · {{ $m->almacen_origen ?? '—' }} → {{ $m->almacen_destino ?? '—' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="px-5 py-10 text-center text-sm text-muted">Todavía no hay movimientos de inventario.</p>
                @endif
            </article>
        @endif

        @if (isset($stats['ultimas_cotizaciones']))
            <article class="{{ $panel }} overflow-hidden">
                <div class="px-5 py-4 border-b border-line">
                    <h2 class="text-sm font-bold text-ink">{{ $esVendedor ? 'Mis cotizaciones recientes' : 'Cotizaciones recientes' }}</h2>
                    <p class="mt-0.5 text-xs text-muted">Seguimiento de vigencia</p>
                </div>
                @if (count($stats['ultimas_cotizaciones']) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs text-left">
                            <thead class="bg-surface-2 text-[10px] uppercase tracking-wider text-muted">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Fecha</th>
                                    <th class="px-4 py-3 font-semibold">Cliente</th>
                                    <th class="px-4 py-3 font-semibold">Vencimiento</th>
                                    <th class="px-4 py-3 font-semibold">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($stats['ultimas_cotizaciones'] as $c)
                                    <tr class="transition-colors hover:bg-surface-2">
                                        <td class="px-4 py-3 text-muted">{{ \Carbon\Carbon::parse($c->fecha_emision)->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 font-medium text-ink">{{ $c->cliente }}</td>
                                        <td class="px-4 py-3 text-muted">{{ \Carbon\Carbon::parse($c->fecha_vencimiento)->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-bold {{ $c->condicion_temporal === 'VIGENTE' ? 'bg-brand-green/20 text-brand-green' : 'bg-surface-2 text-muted' }}">{{ $c->condicion_temporal }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="px-5 py-10 text-center text-sm text-muted">Todavía no hay cotizaciones registradas.</p>
                @endif
            </article>
        @endif

        @if (isset($stats['ultimas_solicitudes']))
            <article class="{{ $panel }} overflow-hidden">
                <div class="px-5 py-4 border-b border-line">
                    <h2 class="text-sm font-bold text-ink">Solicitudes de servicio</h2>
                    <p class="mt-0.5 text-xs text-muted">Asignadas a ti o pendientes de asignación</p>
                </div>
                @if (count($stats['ultimas_solicitudes']) > 0)
                    <div class="divide-y divide-line">
                        @foreach ($stats['ultimas_solicitudes'] as $s)
                            <div class="px-5 py-3.5">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="text-xs font-semibold text-ink">{{ $s->cliente }}</p>
                                    <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-bold {{ $s->estado === 'PENDIENTE' ? 'bg-amber-500/20 text-amber-500' : 'bg-brand-blue/20 text-brand-blue' }}">{{ $s->estado }}</span>
                                </div>
                                <p class="mt-1 text-xs text-muted">{{ \Illuminate\Support\Str::limit($s->descripcion_falla, 100) }}</p>
                                <p class="mt-1 text-[10px] text-muted">{{ \Carbon\Carbon::parse($s->fecha_solicitud)->format('d/m/Y H:i') }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="px-5 py-10 text-center text-sm text-muted">No tienes solicitudes pendientes.</p>
                @endif
            </article>
        @endif
    </section>

    @if (!$esGerencia && !$esVendedor && !$esTecnico)
        <section class="{{ $panel }} p-6">
            <h2 class="text-sm font-bold text-ink">Bienvenido a EwiflorWeb</h2>
            <p class="mt-1 text-sm text-muted">Tu cuenta no tiene un panel operativo asignado. Si consideras que es un error, consulta con el administrador.</p>
        </section>
    @endif

    <footer class="border-t border-line pt-4 text-center text-[10px] text-muted">
        EwiflorWeb · Panel de control
    </footer>
</div>
@endsection
