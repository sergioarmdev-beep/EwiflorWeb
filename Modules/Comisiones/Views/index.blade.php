@extends('layouts.app')

@section('title', 'Comisiones')

@section('content')
<div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Comisiones de vendedores</h1>
        <a href="{{ route('comisiones.index') }}"
            class="border border-slate-300 dark:border-slate-600 px-4 py-2 rounded text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">Limpiar filtros</a>
    </div>

    @if (session('ok'))
        <div class="bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400 px-4 py-2 rounded">{{ session('ok') }}</div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-400 px-4 py-2 rounded">{{ session('error') }}</div>
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
        <div class="bg-white dark:bg-slate-800 p-4 rounded shadow text-sm">
            <p class="text-gray-500 dark:text-slate-400">Comisiones listadas</p>
            <p class="text-xl font-semibold text-slate-900 dark:text-white">{{ number_format($cant) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded shadow text-sm">
            <p class="text-gray-500 dark:text-slate-400">Total ventas (Bs)</p>
            <p class="text-xl font-semibold text-slate-900 dark:text-white">Bs {{ number_format($totVentas, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded shadow text-sm">
            <p class="text-gray-500 dark:text-slate-400">Total comisión (Bs)</p>
            <p class="text-xl font-semibold text-green-700 dark:text-green-400">Bs {{ number_format($totComision, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded shadow text-sm">
            <p class="text-gray-500 dark:text-slate-400">Vendedores con comisión</p>
            <p class="text-xl font-semibold text-slate-900 dark:text-white">{{ $comisiones->unique('id_usuario')->count() }}</p>
        </div>
    </div>

    <!-- Formulario de generación -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded shadow">
        <h2 class="text-lg font-semibold mb-3 text-slate-900 dark:text-white">Generar comisiones (año / mes)</h2>
        <form method="POST" action="{{ route('comisiones.generar') }}"
            class="grid grid-cols-1 md:grid-cols-5 gap-3">
            @csrf
            <div>
                <label class="block text-sm text-gray-600 dark:text-slate-300 mb-1">Año</label>
                <select name="anio" class="border border-slate-300 dark:border-slate-600 rounded px-3 py-2 w-full bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    @foreach ($anios as $a)
                        <option value="{{ $a }}" @selected($anioSel === $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-slate-300 mb-1">Mes</label>
                <select name="mes" class="border border-slate-300 dark:border-slate-600 rounded px-3 py-2 w-full bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    @foreach ($meses as $m)
                        <option value="{{ $m }}" @selected($mesSel === $m)>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            @if (auth()->user()->esGerente())
            <div>
                <label class="block text-sm text-gray-600 dark:text-slate-300 mb-1">Sucursal</label>
                <select name="id_sucursal" class="border border-slate-300 dark:border-slate-600 rounded px-3 py-2 w-full bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
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
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Generar
                </button>
            </div>
        </form>

        @if ($vendedores->isNotEmpty())
        <p class="mt-2 text-sm text-gray-500 dark:text-slate-400">
            Se calculará una fila por vendedor con rol VENDEDOR. La comisión es:
            <strong class="text-slate-900 dark:text-white">total ventas (Bs) × porcentaje_comision_pct / 100</strong>.
        </p>
        @endif
    </div>

    <!-- Filtros de listado -->
    <form method="GET" class="bg-white dark:bg-slate-800 p-4 rounded shadow grid grid-cols-1 md:grid-cols-6 gap-3">
        <div>
            <label class="block text-sm text-gray-600 dark:text-slate-300 mb-1">Año</label>
            <select name="anio" class="border border-slate-300 dark:border-slate-600 rounded px-3 py-2 w-full bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                @foreach ($anios as $a)
                <option value="{{ $a }}" @selected($anioSel === $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-600 dark:text-slate-300 mb-1">Mes</label>
            <select name="mes" class="border border-slate-300 dark:border-slate-600 rounded px-3 py-2 w-full bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                @foreach ($meses as $m)
                <option value="{{ $m }}" @selected($mesSel === $m)>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        @if (auth()->user()->esGerente())
        <div>
            <label class="block text-sm text-gray-600 dark:text-slate-300 mb-1">Sucursal</label>
            <select name="id_sucursal" class="border border-slate-300 dark:border-slate-600 rounded px-3 py-2 w-full bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
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
            <label class="block text-sm text-gray-600 dark:text-slate-300 mb-1">Estado</label>
            <select name="estado" class="border border-slate-300 dark:border-slate-600 rounded px-3 py-2 w-full bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                <option value="PENDIENTE" @selected($filtros['estado']==='PENDIENTE')>Pendiente</option>
                <option value="PAGADO" @selected($filtros['estado']==='PAGADO')>Pagado</option>
                <option value="" @selected(empty($filtros['estado']))>— Todos —</option>
            </select>
        </div>
        <div class="flex items-end">
            <button class="bg-gray-800 dark:bg-slate-700 text-white px-4 py-2 rounded hover:bg-gray-900 dark:hover:bg-slate-600">Filtrar</button>
        </div>
    </form>

    <!-- Tabla de comisiones -->
    <div class="bg-white dark:bg-slate-800 rounded shadow overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-gray-50 dark:bg-slate-900/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Vendedor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Sucursal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Año / Mes</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-slate-400 uppercase"># Ventas</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Total ventas (Bs)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">% Comisión</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Monto comisión (Bs)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Estado</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                @if ($comisiones->isEmpty())
                <tr>
                    <td colspan="9" class="px-6 py-8 text-center text-gray-500 dark:text-slate-400">
                        No se encontraron comisiones para los filtros seleccionados.
                    </td>
                </tr>
                @else
                @foreach ($comisiones as $c)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-slate-900 dark:text-white">{{ $c->vendedor->nombre_completo ?? '—' }}</div>
                        <div class="text-xs text-gray-500 dark:text-slate-400">#{{ $c->vendedor->id_usuario ?? '—' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
                        {{ $c->sucursal->nombre ?? '—' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
                        {{ $c->mes }}/{{ $c->anio }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-slate-700 dark:text-slate-300">
                        {{ number_format((int) $c->cantidad_ventas) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-slate-700 dark:text-slate-300">
                        Bs {{ number_format((float) $c->total_ventas_bs, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-slate-700 dark:text-slate-300">
                        {{ number_format((float) $c->porcentaje_comision_pct, 2) }}%
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-slate-900 dark:text-white">
                        Bs {{ number_format((float) $c->monto_comision_bs, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium
                            {{ $c->esPendiente() ? 'bg-yellow-100 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400' : 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400' }}">
                            {{ $c->esPendiente() ? 'Pendiente' : 'Pagado' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                        @if ($c->esPendiente())
                            <form method="POST" action="{{ route('comisiones.pagar', $c->id_comision) }}"
                                onsubmit="return confirm('¿Marcar esta comisión como pagada?');"
                                class="inline">
                                @csrf
                                <button class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded text-xs">
                                    Marcar pagado
                                </button>
                            </form>
                        @else
                            <span class="text-gray-400 dark:text-slate-500 text-xs">Pagada {{ $c->fecha_pago ? '('.$c->fecha_pago->format('d/m/Y').')' : '' }}</span>
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
