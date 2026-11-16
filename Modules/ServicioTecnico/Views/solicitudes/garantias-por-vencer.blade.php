@extends('layouts.app')

@section('title', 'Garantías por vencer')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Garantías por vencer</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Garantías vigentes que expiran en los próximos {{ $dias }} días.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-sky-600 dark:text-sky-400 hover:underline">← Volver al panel</a>
    </div>

    <form method="GET" action="{{ route('serviciotecnico.garantias.por_vencer') }}" class="mb-6 flex flex-col sm:flex-row gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Días de anticipación</label>
            <input type="number" name="dias" value="{{ $dias }}" min="1" max="365"
                class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
        </div>
        <button type="submit" class="px-4 py-2 bg-slate-900 dark:bg-slate-800 text-white rounded hover:bg-slate-800 dark:hover:bg-slate-700 text-sm font-medium">Filtrar</button>
    </form>

    @if (session('success'))
    <div class="mb-4 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:text-emerald-200">
        {{ session('success') }}
    </div>
    @endif

    @if (count($garantias ?? []) > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900 rounded-xl overflow-hidden shadow">
            <thead class="bg-slate-50 dark:bg-slate-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Garantía</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Producto / SKU</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Cliente</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">Inicio</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">Vencimiento</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">Días restantes</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Venta</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @foreach ($garantias as $g)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    <td class="px-4 py-3 text-sm font-mono text-slate-900 dark:text-slate-100">#{{ $g->id_garantia }}</td>
                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">
                        {{ $g->producto }} <span class="text-xs text-slate-500 block">({{ $g->sku }})</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">{{ $g->cliente_nombre }}</td>
                    <td class="px-4 py-3 text-center text-sm text-slate-500 dark:text-slate-400 font-mono">{{ \Carbon\Carbon::parse($g->fecha_inicio)->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-center text-sm font-mono
                        @if(\Carbon\Carbon::parse($g->fecha_vencimiento)->isToday()) bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 @else text-slate-900 dark:text-slate-100 @endif">
                        {{ \Carbon\Carbon::parse($g->fecha_vencimiento)->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 text-center text-sm font-bold font-mono
                        @if(\Carbon\Carbon::parse($g->fecha_vencimiento)->isToday()) text-red-600 dark:text-red-400
                        @elseif(\Carbon\Carbon::parse($g->fecha_vencimiento)->diffInDays(now()) <= 7) text-amber-600 dark:text-amber-400
                        @else text-slate-600 dark:text-slate-400 @endif">
                        {{ \Carbon\Carbon::parse($g->fecha_vencimiento)->diffInDays(now()) }} días
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400 font-mono">#{{ $g->id_venta }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-12">
        <p class="text-sm text-slate-500 dark:text-slate-400">No hay garantías por vencer en los próximos {{ $dias }} días.</p>
    </div>
    @endif
</div>
@endsection
