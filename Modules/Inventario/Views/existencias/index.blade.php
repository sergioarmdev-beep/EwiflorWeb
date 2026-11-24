@extends('layouts.app')

@section('title', 'Existencias')

@section('content')
@php $puedeGestionarInventario = in_array(auth()->user()->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true); @endphp
<div class="max-w-[1600px] mx-auto space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><h1 class="text-2xl font-bold text-ink">Existencias</h1><p class="mt-1 text-sm text-muted">Consulta y supervisa las existencias disponibles en tus almacenes.</p></div>
        @if ($puedeGestionarInventario)<a href="{{ route('inventario.movimientos.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-green px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-brand-green-dark transition">Nuevo movimiento</a>@endif
    </div>

    <section class="rounded-xl border border-line bg-surface p-4 shadow-soft sm:p-5">
        <h2 class="mb-4 text-sm font-semibold text-ink">Filtrar existencias</h2>
        <form method="GET" action="{{ route('inventario.existencias.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div><label for="buscar" class="mb-1.5 block text-xs font-semibold text-muted">Buscar producto</label><input id="buscar" type="text" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Producto, código o SKU" class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3 py-2.5 text-sm placeholder:text-muted focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20"></div>
            <div><label for="almacen" class="mb-1.5 block text-xs font-semibold text-muted">Almacén</label><select id="almacen" name="almacen" class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3 py-2.5 text-sm focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20"><option value="">Todos los almacenes</option>@foreach ($almacenes as $a)<option value="{{ $a->nombre }}" @selected(($filtros['almacen'] ?? '') === $a->nombre)>{{ $a->nombre }}</option>@endforeach</select></div>
            <div class="flex items-end"><label class="flex min-h-[42px] w-full cursor-pointer items-center gap-3 rounded-xl border border-line bg-surface-2 px-3"><input type="checkbox" name="solo_alerta" value="1" @checked((string) ($filtros['solo_alerta'] ?? '') === '1') class="h-4 w-4 rounded border border-line bg-surface-2 text-brand-green focus:ring-brand-green/50"><span class="text-sm text-muted">Mostrar solo alertas</span></label></div>
            <div class="flex items-end gap-2"><button type="submit" class="flex-1 rounded-xl bg-brand-green px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-green-dark transition">Filtrar</button><a href="{{ route('inventario.existencias.index') }}" title="Limpiar filtros" class="rounded-xl border border-line px-4 py-2.5 text-sm text-ink hover:bg-surface-2 transition">Limpiar</a></div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-line bg-surface shadow-soft">
        <div class="flex flex-col gap-2 border-b border-line px-5 py-4 sm:flex-row sm:items-center sm:justify-between"><div><h2 class="font-semibold text-ink">Inventario por almacén</h2><p class="mt-1 text-xs text-muted">Detalle de cantidades y disponibilidad.</p></div><span class="text-xs text-muted">{{ $existencias->total() }} registros</span></div>
        <div class="overflow-x-auto"><table class="min-w-[1050px] w-full text-left text-sm">
            <thead class="bg-surface-2 text-muted text-xs uppercase tracking-wider"><tr><th scope="col" class="px-4 py-4">Sucursal / Almacén</th><th scope="col" class="px-4 py-4">Producto</th><th scope="col" class="px-4 py-4">SKU</th><th scope="col" class="px-4 py-4 text-right">Total</th><th scope="col" class="px-4 py-4 text-right">Reservado</th><th scope="col" class="px-4 py-4 text-right">Disponible</th><th scope="col" class="px-4 py-4 text-right">Mínimo</th><th scope="col" class="px-4 py-4 text-center">Estado</th></tr></thead>
            <tbody class="divide-y divide-line">
                @forelse ($existencias as $e)
                    @php $necesitaReposicion = $e->alerta === 'REPONER'; @endphp
                    <tr class="transition-colors {{ $necesitaReposicion ? 'bg-red-500/10' : 'hover:bg-surface-2' }}">
                        <td class="px-4 py-3"><div class="font-medium text-ink">{{ $e->sucursal }}</div><div class="mt-0.5 text-xs text-muted">{{ $e->almacen }}</div></td>
                        <td class="px-4 py-3 font-medium text-ink">{{ $e->producto }}</td>
                        <td class="px-4 py-3"><span class="rounded bg-surface-2 px-2 py-1 font-mono text-xs text-muted">{{ $e->sku }}</span></td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums text-muted">{{ $e->cantidad_total }}</td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums text-muted">{{ $e->cantidad_reservada }}</td>
                        <td class="px-4 py-3 text-right font-mono font-bold tabular-nums {{ $necesitaReposicion ? 'text-red-500' : 'text-brand-green' }}">{{ $e->disponible }}</td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums text-muted">{{ $e->stock_minimo }}</td>
                        <td class="px-4 py-3 text-center">@if ($necesitaReposicion)<span class="inline-flex rounded-full border border-red-500/30 bg-red-500/10 px-2.5 py-1 text-xs font-semibold text-red-500"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Reponer</span>@else<span class="inline-flex rounded-full border border-brand-green/20 bg-brand-green/10 px-2.5 py-1 text-xs font-semibold text-brand-green"><span class="w-1.5 h-1.5 rounded-full bg-brand-green"></span> Correcto</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-6 py-16 text-center"><h3 class="font-semibold text-ink">No se encontraron existencias</h3><p class="mt-1 text-sm text-muted">No hay registros que coincidan con los filtros seleccionados.</p><div class="mt-5 flex flex-wrap justify-center gap-3"><a href="{{ route('inventario.existencias.index') }}" class="text-sm font-semibold text-brand-blue">Limpiar filtros</a>@if ($puedeGestionarInventario)<a href="{{ route('inventario.movimientos.create') }}" class="text-sm font-semibold text-brand-blue">Registrar movimiento</a>@endif</div></td></tr>
                @endforelse
            </tbody>
        </table></div>
        @if ($existencias->hasPages())<div class="border-t border-line px-5 py-4">{{ $existencias->withQueryString()->links() }}</div>@endif
    </section>
</div>
@endsection
