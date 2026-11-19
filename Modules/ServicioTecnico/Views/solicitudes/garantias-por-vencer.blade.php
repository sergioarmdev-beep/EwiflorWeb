@extends('layouts.app')

@section('title', 'Garantías por vencer')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-ink">Garantías por vencer</h1>
            <p class="text-sm text-muted">Garantías vigentes que expiran en los próximos {{ $dias }} días.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-brand-blue hover:underline">← Volver al panel</a>
    </div>

    <form method="GET" action="{{ route('serviciotecnico.garantias.por_vencer') }}" class="mb-6 flex flex-col sm:flex-row gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-muted mb-1">Días de anticipación</label>
            <input type="number" name="dias" value="{{ $dias }}" min="1" max="365"
                class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20">
        </div>
        <button type="submit" class="px-4 py-2 bg-brand-green text-white rounded-xl hover:bg-brand-green-dark text-sm font-medium">Filtrar</button>
    </form>

    @if (session('success'))
    <div class="mb-4 flex items-start gap-3 rounded-xl border border-brand-green/30 bg-brand-green/10 px-4 py-3 text-sm text-brand-green">
        {{ session('success') }}
    </div>
    @endif

    @if (count($garantias ?? []) > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-line bg-surface rounded-xl overflow-hidden shadow-soft">
            <thead class="bg-surface-2">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Garantía</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Producto / SKU</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Cliente</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Inicio</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Vencimiento</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Días restantes</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Venta</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($garantias as $g)
                <tr class="transition-colors hover:bg-surface-2">
                    <td class="px-4 py-3 text-sm font-mono text-ink">#{{ $g->id_garantia }}</td>
                    <td class="px-4 py-3 text-sm text-ink">
                        {{ $g->producto }} <span class="text-xs text-muted block">({{ $g->sku }})</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-ink">{{ $g->cliente_nombre }}</td>
                    <td class="px-4 py-3 text-center text-sm text-muted font-mono">{{ \Carbon\Carbon::parse($g->fecha_inicio)->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-center text-sm font-mono
                        @if(\Carbon\Carbon::parse($g->fecha_vencimiento)->isToday()) text-amber-500 @else text-ink @endif">
                        {{ \Carbon\Carbon::parse($g->fecha_vencimiento)->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 text-center text-sm font-bold font-mono
                        @if(\Carbon\Carbon::parse($g->fecha_vencimiento)->isToday()) text-amber-500
                        @elseif(\Carbon\Carbon::parse($g->fecha_vencimiento)->diffInDays(now()) <= 7) text-amber-500
                        @else text-muted @endif">
                        {{ \Carbon\Carbon::parse($g->fecha_vencimiento)->diffInDays(now()) }} días
                    </td>
                    <td class="px-4 py-3 text-sm text-muted font-mono">#{{ $g->id_venta }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-12">
        <p class="text-sm text-muted">No hay garantías por vencer en los próximos {{ $dias }} días.</p>
    </div>
    @endif
</div>
@endsection
