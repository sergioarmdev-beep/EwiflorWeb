@extends('layouts.app')

@section('title', 'Comisiones')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight text-ink">Comisiones de vendedores</h1>
        <a href="{{ route('comisiones.index') }}"
            class="border border-line rounded-xl px-4 py-2 text-sm text-ink hover:bg-surface-2 hover:text-brand-green transition">Limpiar filtros</a>
    </div>

    @if (session('ok'))
        <div class="mb-3 flex items-start gap-3 rounded-xl border border-brand-green/30 bg-brand-green/10 px-4 py-2 text-sm text-brand-green" role="status">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
            </svg>
            <span>{{ session('ok') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-3 flex items-start gap-3 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2 text-sm text-red-400" role="alert">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="9" />
                <path stroke-linecap="round" d="M12 8v5m0 3h.01" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Resumen -->
    @php
        $cant = (int) ($resumen['cantidad'] ?? 0);
        $totVentas = (float) ($resumen['total_ventas_bs'] ?? 0);
        $totComision = (float) ($resumen['monto_comision_bs'] ?? 0);

        // Valores seleccionados en los <select>: casteados a int para que la
        // comparación estricta con los valores de range() funcione siempre.
        // old() tiene prioridad tras un redirect con withInput().
        $anioSel = (int) (old('anio') ?? $filtros['anio']);
        $mesSel = (int) (old('mes') ?? $filtros['mes']);
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-surface p-4 rounded-2xl border border-line shadow-soft text-sm">
            <p class="text-muted">Comisiones listadas</p>
            <p class="text-xl font-bold text-ink">{{ number_format($cant) }}</p>
        </div>
        <div class="bg-surface p-4 rounded-2xl border border-line shadow-soft text-sm">
            <p class="text-muted">Total ventas (Bs)</p>
            <p class="text-xl font-bold text-ink">Bs {{ number_format($totVentas, 2) }}</p>
        </div>
        <div class="bg-surface p-4 rounded-2xl border border-line shadow-soft text-sm">
            <p class="text-muted">Total comisión (Bs)</p>
            <p class="text-xl font-bold text-brand-green">Bs {{ number_format($totComision, 2) }}</p>
        </div>
        <div class="bg-surface p-4 rounded-2xl border border-line shadow-soft text-sm">
            <p class="text-muted">Vendedores con comisión</p>
            <p class="text-xl font-bold text-ink">{{ $comisiones->unique('id_usuario')->count() }}</p>
        </div>
    </div>

    <!-- Formulario de generación -->
    <div class="bg-surface p-4 rounded-2xl border border-line shadow-soft">
        <h2 class="text-lg font-bold mb-3 text-ink">Generar comisiones (año / mes)</h2>
        <form method="POST" action="{{ route('comisiones.generar') }}"
            class="grid grid-cols-1 md:grid-cols-5 gap-3">
            @csrf
            <div>
                <label class="block text-sm text-muted mb-1">Año</label>
                <select name="anio" class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 w-full focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                    @foreach ($anios as $a)
                        <option value="{{ $a }}" @selected($anioSel === $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-muted mb-1">Mes</label>
                <select name="mes" class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 w-full focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                    @foreach ($meses as $m)
                        <option value="{{ $m }}" @selected($mesSel === $m)>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            @if (auth()->user()->esGerente())
            <div>
                <label class="block text-sm text-muted mb-1">Sucursal</label>
                <select name="id_sucursal" class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 w-full focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                    <option value="">— Todas las sucursales —</option>
                    @foreach ($sucursales as $s)
                        <option value="{{ $s->id_sucursal }}" @selected((string) $filtros['id_sucursal']===(string) $s->id_sucursal)>
                            {{ $s->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="flex items-end">
                <button class="bg-brand-green text-white px-4 py-2 rounded-xl font-semibold hover:bg-brand-green-dark transition-colors">
                    Generar
                </button>
            </div>
        </form>

        @if ($vendedores->isNotEmpty())
        <p class="mt-2 text-sm text-muted">
            Se calculará una fila por vendedor con rol VENDEDOR. La comisión es:
            <strong class="text-ink">total ventas (Bs) × porcentaje_comision_pct / 100</strong>.
        </p>
        @endif
    </div>

    <!-- Filtros de listado -->
    <form method="GET" class="bg-surface p-4 rounded-2xl border border-line shadow-soft grid grid-cols-1 md:grid-cols-6 gap-3">
        <div>
            <label class="block text-sm text-muted mb-1">Año</label>
            <select name="anio" class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 w-full focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                @foreach ($anios as $a)
                <option value="{{ $a }}" @selected($anioSel === $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm text-muted mb-1">Mes</label>
            <select name="mes" class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 w-full focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                @foreach ($meses as $m)
                <option value="{{ $m }}" @selected($mesSel === $m)>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        @if (auth()->user()->esGerente())
        <div>
            <label class="block text-sm text-muted mb-1">Sucursal</label>
            <select name="id_sucursal" class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 w-full focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                <option value="">— Todas —</option>
                @foreach ($sucursales as $s)
                <option value="{{ $s->id_sucursal }}" @selected((string) $filtros['id_sucursal']===(string) $s->id_sucursal)>
                    {{ $s->nombre }}
                </option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label class="block text-sm text-muted mb-1">Estado</label>
            <select name="estado" class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 w-full focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                <option value="PENDIENTE" @selected($filtros['estado']==='PENDIENTE')>Pendiente</option>
                <option value="PAGADO" @selected($filtros['estado']==='PAGADO')>Pagado</option>
                <option value="" @selected(empty($filtros['estado']))>— Todos —</option>
            </select>
        </div>
        <div class="flex items-end">
            <button class="bg-brand-green text-white px-4 py-2 rounded-xl font-semibold hover:bg-brand-green-dark transition-colors">Filtrar</button>
        </div>
    </form>

    <!-- Tabla de comisiones -->
    <div class="bg-surface rounded-2xl border border-line shadow-soft overflow-hidden">
        <table class="min-w-full divide-y divide-line">
            <thead class="bg-surface-2">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Vendedor</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Sucursal</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Año / Mes</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted"># Ventas</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted">Total ventas (Bs)</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted">% Comisión</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted">Monto comisión (Bs)</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Estado</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @if ($comisiones->isEmpty())
                <tr>
                    <td colspan="9" class="px-6 py-8 text-center text-muted">
                        No se encontraron comisiones para los filtros seleccionados.
                    </td>
                </tr>
                @else
                @foreach ($comisiones as $c)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-ink">{{ $c->vendedor->nombre_completo ?? '—' }}</div>
                        <div class="text-xs text-muted">#{{ $c->vendedor->id_usuario ?? '—' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-muted">
                        {{ $c->sucursal->nombre ?? '—' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-muted">
                        {{ $c->mes }}/{{ $c->anio }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-muted">
                        {{ number_format((int) $c->cantidad_ventas) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-muted">
                        Bs {{ number_format((float) $c->total_ventas_bs, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-muted">
                        {{ number_format((float) $c->porcentaje_comision_pct, 2) }}%
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-ink">
                        Bs {{ number_format((float) $c->monto_comision_bs, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium
                            {{ $c->esPendiente() ? 'border border-amber-500/30 bg-amber-500/10 text-amber-500' : 'border border-brand-green/20 bg-brand-green/10 text-brand-green' }}">
                            {{ $c->esPendiente() ? 'Pendiente' : 'Pagado' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                        @if ($c->esPendiente())
                            <form method="POST" action="{{ route('comisiones.pagar', $c->id_comision) }}"
                                onsubmit="return confirm('¿Marcar esta comisión como pagada?');"
                                class="inline">
                                @csrf
                                <button class="bg-brand-green text-white px-3 py-1.5 rounded text-xs font-semibold hover:bg-brand-green-dark transition-colors">
                                    Marcar pagado
                                </button>
                            </form>
                        @else
                            <span class="text-muted text-xs">Pagada {{ $c->fecha_pago ? '('.$c->fecha_pago->format('d/m/Y').')' : '' }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
