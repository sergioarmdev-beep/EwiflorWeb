@extends('layouts.app')

@section('title', 'Auditoría')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-ink">Auditoría del sistema</h1>
            <p class="text-sm text-muted">
                Registro de operaciones auditadas por disparadores de PostgreSQL.
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-brand-blue hover:underline">
            ← Volver al panel
        </a>
    </div>

    <form method="GET" class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 border border-line p-4 rounded-xl bg-surface-2/60 shadow-soft">
        <div>
            <label class="block text-xs font-semibold text-muted mb-1">Desde</label>
            <input type="date" name="desde" value="{{ request('desde') }}" class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20">
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted mb-1">Hasta</label>
            <input type="date" name="hasta" value="{{ request('hasta') }}" class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20">
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted mb-1">Usuario</label>
            <input type="text" name="usuario" value="{{ request('usuario') }}" placeholder="nombre de usuario" class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20">
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted mb-1">Módulo</label>
            <select name="modulo" class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20">
                <option value="">Todos</option>
                @foreach ($modulos as $m)
                    <option value="{{ $m }}" {{ request('modulo') === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div class="lg:col-span-2">
            <label class="block text-xs font-semibold text-muted mb-1">Operación</label>
            <select name="operacion" class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20">
                <option value="">Todas</option>
                @foreach ($operaciones as $op)
                    <option value="{{ $op }}" {{ request('operacion') === $op ? 'selected' : '' }}>{{ $op }}</option>
                @endforeach
            </select>
        </div>
        <div class="lg:col-span-2">
            <label class="block text-xs font-semibold text-muted mb-1">Tabla afectada</label>
            <input type="text" name="tabla" value="{{ request('tabla') }}" placeholder="ej. venta, cliente..." class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20">
        </div>
        <div class="lg:col-span-2 flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-brand-green text-white rounded-xl hover:bg-brand-green-dark text-sm font-medium">Filtrar</button>
            <a href="{{ route('auditoria.index') }}" class="px-4 py-2 border border-line rounded-xl text-ink hover:bg-surface-2 text-sm">Limpiar</a>
        </div>
    </form>

    @if ($auditorias->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-line bg-surface rounded-xl overflow-hidden shadow-soft">
            <thead class="bg-surface-2">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Fecha</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Usuario</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Módulo</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Operación</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Tabla</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Registro</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($auditorias as $aud)
                <tr class="transition-colors hover:bg-surface-2">
                    <td class="px-4 py-3 text-sm text-ink font-mono">{{ \Carbon\Carbon::parse($aud->fecha_hora)->format('d/m/Y H:i:s') }}</td>
                    <td class="px-4 py-3 text-sm text-ink">
                        {{ $aud->usuario?->nombre_usuario ?? $aud->usuario?->nombre_completo ?? '—' }}
                        @if ($aud->usuario)
                            <span class="text-xs text-muted ml-1">({{ $aud->usuario->nombre_rol }})</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-ink">{{ $aud->modulo }}</td>
                    <td class="px-4 py-3 text-sm font-semibold">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold
                            {{ $aud->operacion === 'CREAR' ? 'border border-brand-green/40 bg-brand-green/20 text-brand-green' : '' }}
                            {{ $aud->operacion === 'MODIFICAR' ? 'border border-brand-blue/40 bg-brand-blue/20 text-brand-blue' : '' }}
                            {{ $aud->operacion === 'ELIMINAR' ? 'border border-red-500/40 bg-red-500/20 text-red-500' : '' }}
                            {{ $aud->operacion === 'CANCELAR' ? 'border border-amber-500/40 bg-amber-500/20 text-amber-500' : '' }}
                            {{ $aud->operacion === 'INICIO_SESION' ? 'border border-brand-blue/40 bg-brand-blue/20 text-brand-blue' : '' }}">
                            {{ $aud->operacion }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-ink font-mono">{{ $aud->tabla_afectada }}</td>
                    <td class="px-4 py-3 text-sm text-muted font-mono">{{ $aud->id_registro ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex items-center justify-between">
        <p class="text-xs text-muted">
            Mostrando {{ $auditorias->firstItem() }} a {{ $auditorias->lastItem() }} de {{ $auditorias->total() }}
        </p>
        {{ $auditorias->links() }}
    </div>
    @else
    <div class="text-center py-12">
        <p class="text-sm text-muted">No se encontraron registros de auditoría con los filtros aplicados.</p>
    </div>
    @endif
</div>
@endsection
