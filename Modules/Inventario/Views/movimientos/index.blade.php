@extends('layouts.app')

@section('title', 'Movimientos de inventario')

@section('content')
@php
    $puedeGestionarInventario = in_array(auth()->user()->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true);
@endphp
<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-ink">Movimientos de inventario</h1>
            <p class="mt-1 text-sm text-muted">Historial de entradas, salidas, ajustes y transferencias.</p>
        </div>
        @if ($puedeGestionarInventario)
            <a href="{{ route('inventario.movimientos.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-green px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-green-dark transition">Nuevo movimiento</a>
        @endif
    </div>

    <section class="rounded-xl border border-line bg-surface p-4 shadow-soft sm:p-5">
        <form method="GET" action="{{ route('inventario.movimientos.index') }}" class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div class="w-full sm:max-w-xs">
                <label for="tipo" class="mb-1.5 block text-xs font-semibold text-muted">Tipo de movimiento</label>
                <select id="tipo" name="tipo" class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3 py-2.5 text-sm focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                    <option value="">Todos los tipos</option>
                    @foreach (['ENTRADA', 'SALIDA', 'AJUSTE', 'TRANSFERENCIA'] as $t)
                        <option value="{{ $t }}" @selected(($filtros['tipo'] ?? '') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-xl bg-brand-green px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-green-dark">Filtrar</button>
                <a href="{{ route('inventario.movimientos.index') }}" class="rounded-xl border border-line px-4 py-2.5 text-sm font-medium text-ink hover:bg-surface-2">Limpiar</a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-line bg-surface shadow-soft">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-line px-5 py-4">
            <div><h2 class="font-semibold text-ink">Historial de movimientos</h2><p class="mt-1 text-xs text-muted">Operaciones registradas sobre el inventario.</p></div>
            <span class="text-xs text-muted">{{ $movimientos->total() }} registros</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-[1100px] w-full text-left text-sm">
                <thead class="bg-surface-2 text-muted text-xs uppercase tracking-wider">
                    <tr>
                        <th scope="col" class="px-4 py-4">N.º</th><th scope="col" class="px-4 py-4">Fecha</th><th scope="col" class="px-4 py-4">Tipo</th><th scope="col" class="px-4 py-4">Producto / Variante</th><th scope="col" class="px-4 py-4 text-right">Cantidad</th><th scope="col" class="px-4 py-4">Origen → Destino</th><th scope="col" class="px-4 py-4">Motivo</th><th scope="col" class="px-4 py-4">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($movimientos as $m)
                        @php
                            $estilosTipo = match ($m->tipo) {
                                'ENTRADA' => 'border border-brand-green/20 bg-brand-green/10 text-brand-green',
                                'SALIDA' => 'border border-red-500/30 bg-red-500/10 text-red-500',
                                'AJUSTE' => 'border border-amber-500/30 bg-amber-500/10 text-amber-500',
                                'TRANSFERENCIA' => 'border border-brand-blue/20 bg-brand-blue/10 text-brand-blue',
                                default => 'border border-line bg-surface-2 text-muted',
                            };
                        @endphp
                        <tr class="transition-colors hover:bg-surface-2">
                            <td class="px-4 py-3 font-mono text-xs text-muted">#{{ $m->id_movimiento }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-muted">{{ $m->fecha_hora?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3"><span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $estilosTipo }}">{{ $m->tipo }}</span></td>
                            <td class="px-4 py-3"><div class="space-y-2">
                                @forelse ($m->detalles as $d)
                                    <div><div class="font-medium text-ink">{{ $d->variante?->producto?->nombre ?? 'Producto no disponible' }}</div><div class="mt-0.5 flex gap-2 text-xs text-muted"><span class="font-mono">{{ $d->variante?->sku ?? 'Sin SKU' }}</span>@if ($d->sentido)<span>· {{ $d->sentido }}</span>@endif</div></div>
                                @empty <span class="text-muted">Sin detalles</span> @endforelse
                            </div></td>
                            <td class="px-4 py-3 text-right font-mono font-semibold tabular-nums text-ink"><div class="space-y-2">@forelse ($m->detalles as $d)<div>{{ $d->cantidad }}</div>@empty — @endforelse</div></td>
                            <td class="min-w-48 px-4 py-3"><div class="text-muted">{{ $m->almacenOrigen?->nombre ?? '—' }}</div><div class="my-1 text-xs text-muted">↓</div><div class="text-muted">{{ $m->almacenDestino?->nombre ?? '—' }}</div></td>
                            <td class="max-w-xs px-4 py-3 text-muted">{{ $m->motivo ?: '—' }}</td>
                            <td class="px-4 py-3 text-muted">{{ $m->usuario?->nombre_completo ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-16 text-center"><h3 class="font-semibold text-ink">No hay movimientos registrados</h3><p class="mt-1 text-sm text-muted">Los movimientos que registres aparecerán aquí.</p>@if ($puedeGestionarInventario)<a href="{{ route('inventario.movimientos.create') }}" class="mt-4 inline-flex text-sm font-semibold text-brand-blue">Registrar primer movimiento</a>@endif</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($movimientos->hasPages())<div class="border-t border-line px-5 py-4">{{ $movimientos->withQueryString()->links() }}</div>@endif
    </section>
</div>
@endsection
