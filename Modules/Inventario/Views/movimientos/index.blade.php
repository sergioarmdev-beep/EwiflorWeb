@extends('layouts.app')

@section('title', 'Movimientos de inventario')

@section('content')
@php
    $puedeGestionarInventario = in_array(auth()->user()->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true);
@endphp
<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Movimientos de inventario</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Historial de entradas, salidas, ajustes y transferencias.</p>
        </div>
        @if ($puedeGestionarInventario)
            <a href="{{ route('inventario.movimientos.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-sky-700 dark:hover:bg-sky-500 transition">＋ Nuevo movimiento</a>
        @endif
    </div>

    <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-5 shadow-sm">
        <form method="GET" action="{{ route('inventario.movimientos.index') }}" class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div class="w-full sm:max-w-xs">
                <label for="tipo" class="mb-1.5 block text-xs font-semibold text-slate-600 dark:text-slate-400">Tipo de movimiento</label>
                <select id="tipo" name="tipo" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
                    <option value="">Todos los tipos</option>
                    @foreach (['ENTRADA', 'SALIDA', 'AJUSTE', 'TRANSFERENCIA'] as $t)
                        <option value="{{ $t }}" @selected(($filtros['tipo'] ?? '') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600">Filtrar</button>
                <a href="{{ route('inventario.movimientos.index') }}" class="rounded-lg border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">Limpiar</a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-200 dark:border-slate-800 px-5 py-4">
            <div><h2 class="font-semibold text-slate-900 dark:text-slate-100">Historial de movimientos</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Operaciones registradas sobre el inventario.</p></div>
            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $movimientos->total() }} registros</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-[1100px] w-full text-left text-sm">
                <thead class="bg-slate-100 dark:bg-slate-800/80 text-xs uppercase tracking-wider text-slate-600 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-4">N.º</th><th class="px-4 py-4">Fecha</th><th class="px-4 py-4">Tipo</th><th class="px-4 py-4">Producto / Variante</th><th class="px-4 py-4 text-right">Cantidad</th><th class="px-4 py-4">Origen → Destino</th><th class="px-4 py-4">Motivo</th><th class="px-4 py-4">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($movimientos as $m)
                        @php
                            $estilosTipo = match ($m->tipo) {
                                'ENTRADA' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20',
                                'SALIDA' => 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-400 border-red-200 dark:border-red-500/20',
                                'AJUSTE' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-400 border-amber-200 dark:border-amber-500/20',
                                'TRANSFERENCIA' => 'bg-sky-100 text-sky-800 dark:bg-sky-500/10 dark:text-sky-400 border-sky-200 dark:border-sky-500/20',
                                default => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600',
                            };
                        @endphp
                        <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-4 py-3 font-mono text-xs text-slate-500 dark:text-slate-400">#{{ $m->id_movimiento }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-300">{{ $m->fecha_hora?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3"><span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $estilosTipo }}">{{ $m->tipo }}</span></td>
                            <td class="px-4 py-3"><div class="space-y-2">
                                @forelse ($m->detalles as $d)
                                    <div><div class="font-medium text-slate-800 dark:text-slate-200">{{ $d->variante?->producto?->nombre ?? 'Producto no disponible' }}</div><div class="mt-0.5 flex gap-2 text-xs text-slate-500 dark:text-slate-400"><span class="font-mono">{{ $d->variante?->sku ?? 'Sin SKU' }}</span>@if ($d->sentido)<span>· {{ $d->sentido }}</span>@endif</div></div>
                                @empty <span class="text-slate-500 dark:text-slate-400">Sin detalles</span> @endforelse
                            </div></td>
                            <td class="px-4 py-3 text-right font-mono font-semibold tabular-nums text-slate-800 dark:text-slate-200"><div class="space-y-2">@forelse ($m->detalles as $d)<div>{{ $d->cantidad }}</div>@empty — @endforelse</div></td>
                            <td class="min-w-48 px-4 py-3"><div class="text-slate-700 dark:text-slate-300">{{ $m->almacenOrigen?->nombre ?? '—' }}</div><div class="my-1 text-xs text-slate-400">↓</div><div class="text-slate-700 dark:text-slate-300">{{ $m->almacenDestino?->nombre ?? '—' }}</div></td>
                            <td class="max-w-xs px-4 py-3 text-slate-600 dark:text-slate-400">{{ $m->motivo ?: '—' }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $m->usuario?->nombre_completo ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-16 text-center"><h3 class="font-semibold text-slate-800 dark:text-slate-200">No hay movimientos registrados</h3><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Los movimientos que registres aparecerán aquí.</p>@if ($puedeGestionarInventario)<a href="{{ route('inventario.movimientos.create') }}" class="mt-4 inline-flex text-sm font-semibold text-sky-700 dark:text-sky-400">Registrar primer movimiento</a>@endif</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($movimientos->hasPages())<div class="border-t border-slate-200 dark:border-slate-800 px-5 py-4">{{ $movimientos->withQueryString()->links() }}</div>@endif
    </section>
</div>
@endsection
