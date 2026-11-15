@extends('layouts.app')

@section('title', 'Auditoría')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Auditoría del sistema</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Registro de operaciones auditadas por disparadores de PostgreSQL.
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-sky-600 dark:text-sky-400 hover:underline">
            ← Volver al panel
        </a>
    </div>

    <form method="GET" class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 border border-slate-200 dark:border-slate-700 p-4 rounded-xl bg-slate-50 dark:bg-slate-900">
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Desde</label>
            <input type="date" name="desde" value="{{ request('desde') }}" class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Hasta</label>
            <input type="date" name="hasta" value="{{ request('hasta') }}" class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Usuario</label>
            <input type="text" name="usuario" value="{{ request('usuario') }}" placeholder="nombre de usuario" class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Módulo</label>
            <select name="modulo" class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                <option value="">Todos</option>
                @foreach ($modulos as $m)
                    <option value="{{ $m }}" {{ request('modulo') === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div class="lg:col-span-2">
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Operación</label>
            <select name="operacion" class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                <option value="">Todas</option>
                @foreach ($operaciones as $op)
                    <option value="{{ $op }}" {{ request('operacion') === $op ? 'selected' : '' }}>{{ $op }}</option>
                @endforeach
            </select>
        </div>
        <div class="lg:col-span-2">
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tabla afectada</label>
            <input type="text" name="tabla" value="{{ request('tabla') }}" placeholder="ej. venta, cliente..." class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
        </div>
        <div class="lg:col-span-2 flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-slate-900 dark:bg-slate-800 text-white rounded hover:bg-slate-800 dark:hover:bg-slate-700 text-sm font-medium">Filtrar</button>
            <a href="{{ route('auditoria.index') }}" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-sm">Limpiar</a>
        </div>
    </form>

    @if ($auditorias->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Usuario</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Módulo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Operación</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Tabla</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Registro</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-700">
                @foreach ($auditorias as $aud)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100 font-mono">{{ \Carbon\Carbon::parse($aud->fecha_hora)->format('d/m/Y H:i:s') }}</td>
                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">
                        {{ $aud->usuario?->nombre_usuario ?? $aud->usuario?->nombre_completo ?? '—' }}
                        @if ($aud->usuario)
                            <span class="text-xs text-slate-500 dark:text-slate-500 ml-1">({{ $aud->usuario->nombre_rol }})</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">{{ $aud->modulo }}</td>
                    <td class="px-4 py-3 text-sm font-semibold">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold
                            {{ $aud->operacion === 'CREAR' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : '' }}
                            {{ $aud->operacion === 'MODIFICAR' ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300' : '' }}
                            {{ $aud->operacion === 'ELIMINAR' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' : '' }}
                            {{ $aud->operacion === 'CANCELAR' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : '' }}
                            {{ $aud->operacion === 'INICIO_SESION' ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300' : '' }}">
                            {{ $aud->operacion }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100 font-mono">{{ $aud->tabla_afectada }}</td>
                    <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400 font-mono">{{ $aud->id_registro ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex items-center justify-between">
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Mostrando {{ $auditorias->firstItem() }} a {{ $auditorias->lastItem() }} de {{ $auditorias->total() }}
        </p>
        {{ $auditorias->links() }}
    </div>
    @else
    <div class="text-center py-12">
        <p class="text-sm text-slate-500 dark:text-slate-400">No se encontraron registros de auditoría con los filtros aplicados.</p>
    </div>
    @endif
</div>
@endsection
