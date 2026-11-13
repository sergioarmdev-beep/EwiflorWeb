@extends('layouts.app')

@section('title', 'Existencias')

@section('content')
@php $puedeGestionarInventario = in_array(auth()->user()->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true); @endphp
<div class="max-w-[1600px] mx-auto space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Existencias</h1><p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Consulta y supervisa las existencias disponibles en tus almacenes.</p></div>
        @if ($puedeGestionarInventario)<a href="{{ route('inventario.movimientos.create') }}" class="rounded-lg bg-sky-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-sky-700 dark:hover:bg-sky-500">＋ Nuevo movimiento</a>@endif
    </div>

    <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm sm:p-5">
        <h2 class="mb-4 text-sm font-semibold text-slate-800 dark:text-slate-200">Filtrar existencias</h2>
        <form method="GET" action="{{ route('inventario.existencias.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div><label for="buscar" class="mb-1.5 block text-xs font-semibold text-slate-600 dark:text-slate-400">Buscar producto</label><input id="buscar" type="text" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Producto, código o SKU" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"></div>
            <div><label for="almacen" class="mb-1.5 block text-xs font-semibold text-slate-600 dark:text-slate-400">Almacén</label><select id="almacen" name="almacen" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"><option value="">Todos los almacenes</option>@foreach ($almacenes as $a)<option value="{{ $a->nombre }}" @selected(($filtros['almacen'] ?? '') === $a->nombre)>{{ $a->nombre }}</option>@endforeach</select></div>
            <div class="flex items-end"><label class="flex min-h-[42px] w-full cursor-pointer items-center gap-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3"><input type="checkbox" name="solo_alerta" value="1" @checked((string) ($filtros['solo_alerta'] ?? '') === '1') class="h-4 w-4 rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-sky-600 focus:ring-sky-500"><span class="text-sm text-slate-700 dark:text-slate-300">Mostrar solo alertas</span></label></div>
            <div class="flex items-end gap-2"><button type="submit" class="flex-1 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600">Filtrar</button><a href="{{ route('inventario.existencias.index') }}" title="Limpiar filtros" class="rounded-lg border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">Limpiar</a></div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
        <div class="flex flex-col gap-2 border-b border-slate-200 dark:border-slate-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"><div><h2 class="font-semibold text-slate-900 dark:text-slate-100">Inventario por almacén</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Detalle de cantidades y disponibilidad.</p></div><span class="text-xs text-slate-500 dark:text-slate-400">{{ $existencias->total() }} registros</span></div>
        <div class="overflow-x-auto"><table class="min-w-[1050px] w-full text-left text-sm">
            <thead class="bg-slate-100 dark:bg-slate-800/80 text-xs uppercase tracking-wider text-slate-600 dark:text-slate-400"><tr><th class="px-4 py-4">Sucursal / Almacén</th><th class="px-4 py-4">Producto</th><th class="px-4 py-4">SKU</th><th class="px-4 py-4 text-right">Total</th><th class="px-4 py-4 text-right">Reservado</th><th class="px-4 py-4 text-right">Disponible</th><th class="px-4 py-4 text-right">Mínimo</th><th class="px-4 py-4 text-center">Estado</th></tr></thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($existencias as $e)
                    @php $necesitaReposicion = $e->alerta === 'REPONER'; @endphp
                    <tr class="transition-colors {{ $necesitaReposicion ? 'bg-red-50 hover:bg-red-100 dark:bg-red-950/20 dark:hover:bg-red-950/30' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
                        <td class="px-4 py-3"><div class="font-medium text-slate-800 dark:text-slate-200">{{ $e->sucursal }}</div><div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $e->almacen }}</div></td>
                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ $e->producto }}</td>
                        <td class="px-4 py-3"><span class="rounded bg-slate-100 dark:bg-slate-800 px-2 py-1 font-mono text-xs text-slate-600 dark:text-slate-400">{{ $e->sku }}</span></td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums text-slate-700 dark:text-slate-300">{{ $e->cantidad_total }}</td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums text-slate-500 dark:text-slate-400">{{ $e->cantidad_reservada }}</td>
                        <td class="px-4 py-3 text-right font-mono font-bold tabular-nums {{ $necesitaReposicion ? 'text-red-600 dark:text-red-400' : 'text-emerald-700 dark:text-emerald-400' }}">{{ $e->disponible }}</td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums text-slate-500 dark:text-slate-400">{{ $e->stock_minimo }}</td>
                        <td class="px-4 py-3 text-center">@if ($necesitaReposicion)<span class="inline-flex rounded-full border border-red-200 bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-800 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">● Reponer</span>@else<span class="inline-flex rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400">● Correcto</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-6 py-16 text-center"><h3 class="font-semibold text-slate-800 dark:text-slate-200">No se encontraron existencias</h3><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">No hay registros que coincidan con los filtros seleccionados.</p><div class="mt-5 flex flex-wrap justify-center gap-3"><a href="{{ route('inventario.existencias.index') }}" class="text-sm font-semibold text-sky-700 dark:text-sky-400">Limpiar filtros</a>@if ($puedeGestionarInventario)<a href="{{ route('inventario.movimientos.create') }}" class="text-sm font-semibold text-sky-700 dark:text-sky-400">Registrar movimiento</a>@endif</div></td></tr>
                @endforelse
            </tbody>
        </table></div>
        @if ($existencias->hasPages())<div class="border-t border-slate-200 dark:border-slate-800 px-5 py-4">{{ $existencias->withQueryString()->links() }}</div>@endif
    </section>
</div>
@endsection
