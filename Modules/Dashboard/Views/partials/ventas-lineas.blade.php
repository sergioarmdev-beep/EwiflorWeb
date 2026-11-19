@php
$vl = $stats['ventas_lineas'] ?? [];
$tieneError = !empty($vl['error']);
$muted = 'text-slate-500 dark:text-slate-400';
$label = 'text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400';
$panel = 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm';
$select = 'mt-1 block w-full rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500';
@endphp

@if ($esGerencia)
{{-- Filtros superiores: meses y (para Gerente) sucursal --}}
<section class="{{ $panel }} p-5 mb-5">
    <div class="flex flex-col sm:flex-row gap-4 items-end">
        <div class="flex-1 min-w-0">
            <p class="{{ $label }}">Mes 1</p>
            <select id="mes1" name="mes1" class="{{ $select }}">
                @foreach ($meses as $mes)
                <option value="{{ $mes['valor'] }}" {{ $mes['valor'] === $mes1 ? 'selected' : '' }}>{{ $mes['etiqueta'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 min-w-0">
            <p class="{{ $label }}">Mes 2</p>
            <select id="mes2" name="mes2" class="{{ $select }}">
                @foreach ($meses as $mes)
                <option value="{{ $mes['valor'] }}" {{ $mes['valor'] === $mes2 ? 'selected' : '' }}>{{ $mes['etiqueta'] }}</option>
                @endforeach
            </select>
        </div>

        @if ($rol === 'GERENTE')
        <div class="flex-1 min-w-0">
            <p class="{{ $label }}">Sucursal</p>
            <select id="id_sucursal" name="id_sucursal" class="{{ $select }}">
                <option value="">Todas las sucursales</option>
                @foreach ($sucursales as $s)
                <option value="{{ $s->id_sucursal }}" {{ $idSucursalSeleccionada == $s->id_sucursal ? 'selected' : '' }}>{{ $s->nombre }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>
</section>
@endif



<div id="errorVentasLineas" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200 hidden" role="alert">
    No se pudo cargar la comparativa de ventas. Inténtalo de nuevo más tarde.
</div>



<div class="{{ $panel }} p-5">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Comparativa de ventas diarias</h3>
        <p class="text-xs {{ $muted }}">{{ ($vl['mes1']['etiqueta'] ?? '') }} vs {{ ($vl['mes2']['etiqueta'] ?? '') }}</p>
    </div>

    <div class="h-[320px] w-full">
        <canvas id="chartVentasLineas" data-url="{{ route('dashboard.ventas-lineas') }}"></canvas>
    </div>
</div>

@if (!$tieneError && empty(($vl['mes1']['dias'] ?? [])) && empty(($vl['mes2']['dias'] ?? [])))
<p class="mt-3 text-xs {{ $muted }} text-center">No hay ventas registradas en los períodos seleccionados.</p>
@endif