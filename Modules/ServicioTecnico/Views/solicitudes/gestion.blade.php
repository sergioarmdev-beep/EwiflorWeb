@extends('layouts.app')

@section('title', 'Gestión de servicio técnico')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Gestión de servicio técnico</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Todos los movimientos del módulo: quién aceptó cada encargo, estado y garantía.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-sky-600 dark:text-sky-400 hover:underline">← Volver al panel</a>
    </div>

    <form method="GET" action="{{ route('serviciotecnico.gestion') }}" class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 border border-slate-200 dark:border-slate-700 p-4 rounded-xl bg-white dark:bg-slate-900">
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Estado</label>
            <select name="estado" class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                <option value="">Todos</option>
                <option value="PENDIENTE" {{ $filtros['estado'] ?? '' === 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                <option value="PROGRAMADA" {{ $filtros['estado'] ?? '' === 'PROGRAMADA' ? 'selected' : '' }}>Aceptada</option>
                <option value="CERRADA" {{ $filtros['estado'] ?? '' === 'CERRADA' ? 'selected' : '' }}>Cerrada</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Técnico</label>
            <select name="tecnico" class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                <option value="">Todos</option>
                @foreach ($tecnicos as $tec)
                <option value="{{ $tec['id_usuario'] ?? '' }}" {{ ($filtros['tecnico'] ?? '') == ($tec['id_usuario'] ?? '') ? 'selected' : '' }}>{{ $tec['nombre_completo'] ?? '' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">En garantía</label>
            <select name="en_garantia" class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                <option value="">Todos</option>
                <option value="1" {{ ($filtros['en_garantia'] ?? '') === '1' ? 'selected' : '' }}>Solo en garantía</option>
                <option value="0" {{ ($filtros['en_garantia'] ?? '') === '0' ? 'selected' : '' }}>Fuera de garantía</option>
            </select>
        </div>
        <div class="lg:col-span-2">
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Desde</label>
            <input type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}"
                class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
        </div>
        <div class="lg:col-span-2 flex items-end">
            <button type="submit" class="w-full px-4 py-2 bg-slate-900 dark:bg-slate-800 text-white rounded hover:bg-slate-800 dark:hover:bg-slate-700 text-sm font-medium">Filtrar</button>
        </div>
    </form>

    @if ($solicitudes->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900 rounded-xl overflow-hidden shadow">
            <thead class="bg-slate-50 dark:bg-slate-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Solicitud</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Registró</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">Técnico</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Falla</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">Estado</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">Garantía</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300">Costo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Resultado</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @foreach ($solicitudes as $sol)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    <td class="px-4 py-3 text-sm font-mono text-slate-900 dark:text-slate-100">#{{ $sol->id_solicitud }}</td>
                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">{{ data_get($sol, 'cliente.nombre', '—') }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ data_get($sol, 'registra.nombre_completo', 'Cliente web') }}</td>
                    <td class="px-4 py-3 text-center text-sm text-slate-900 dark:text-slate-100">{{ data_get($sol, 'tecnico.nombre_completo', 'Sin asignar') }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ \Str::limit($sol->descripcion_falla, 30) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold
                            @if($sol->estado === 'PENDIENTE') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 @endif
                            @if($sol->estado === 'PROGRAMADA') bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300 @endif
                            @if($sol->estado === 'CERRADA') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 @endif">
                            {{ $sol->estado }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-sm">
                        @if ($sol->id_garantia)
                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Sí</span>
                        @else
                            <span class="text-slate-500">No</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-sm">
                        @if ($sol->costo_servicio_bs)
                            Bs {{ number_format((float) $sol->costo_servicio_bs, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                        {{ $sol->resultado ? \Str::ucfirst(\Str::replace('_', ' ', $sol->resultado)) : '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400 font-mono">{{ \Carbon\Carbon::parse($sol->fecha_solicitud)->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4 flex items-center justify-between">
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Mostrando {{ $solicitudes->firstItem() }} a {{ $solicitudes->lastItem() }} de {{ $solicitudes->total() }}
            </p>
            {{ $solicitudes->links() }}
        </div>
    </div>
    @else
    <div class="text-center py-12">
        <p class="text-sm text-slate-500 dark:text-slate-400">No se encontraron solicitudes con los filtros aplicados.</p>
    </div>
    @endif
</div>
@endsection
