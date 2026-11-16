@extends('layouts.app')

@section('title', "Solicitud #{$solicitud->id_solicitud}")

@section('content')
@php
    $rol = auth()->user()->nombre_rol;
    $puedeCerrar = in_array($rol, ['SERVICIO_TECNICO', 'GERENTE', 'ADMINISTRADOR'], true);
    $esMia = $solicitud->id_tecnico === auth()->id();
@endphp

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
            Solicitud #{{ $solicitud->id_solicitud }}
            <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold
                @if($solicitud->estado === 'PENDIENTE') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 @endif
                @if($solicitud->estado === 'PROGRAMADA') bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300 @endif
                @if($solicitud->estado === 'CERRADA') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 @endif">
                {{ $solicitud->estado }}
            </span>
        </h1>
        <a href="{{ url()->previous() }}" class="text-sm font-medium text-sky-600 dark:text-sky-400 hover:underline">← Volver</a>
    </div>

    @if (session('success'))
    <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:text-emerald-200">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:text-red-200">{{ session('error') }}</div>
    @endif

    <div class="bg-white dark:bg-slate-900 rounded-xl shadow p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div><span class="text-slate-500">Cliente:</span> <span class="text-slate-900 dark:text-slate-100">{{ data_get($solicitud, 'cliente.nombre', '—') }}</span></div>
        <div><span class="text-slate-500">Teléfono:</span> <span class="text-slate-900 dark:text-slate-100">{{ data_get($solicitud, 'cliente.telefono', '—') }}</span></div>
        <div><span class="text-slate-500">Registró:</span> <span class="text-slate-900 dark:text-slate-100">{{ data_get($solicitud, 'registra.nombre_completo', 'Cliente web (público)') }}</span></div>
        <div><span class="text-slate-500">Origen:</span> <span class="text-slate-900 dark:text-slate-100">{{ $solicitud->origen }}</span></div>
        <div><span class="text-slate-500">Técnico asignado:</span> <span class="text-slate-900 dark:text-slate-100">{{ data_get($solicitud, 'tecnico.nombre_completo', 'Sin asignar') }}</span></div>
        <div><span class="text-slate-500">Fecha solicitud:</span> <span class="text-slate-900 dark:text-slate-100">{{ $solicitud->fecha_solicitud?->format('d/m/Y H:i') }}</span></div>
        <div class="md:col-span-2">
            <span class="text-slate-500">Garantía:</span>
            @if ($solicitud->estaEnGarantia())
                <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Dentro de garantía (sin costo)</span>
            @else
                <span class="text-amber-600 dark:text-amber-400 font-semibold">Fuera de garantía (servicio pagado)</span>
            @endif
        </div>
        <div class="md:col-span-2">
            <span class="text-slate-500">Descripción de la falla:</span>
            <p class="mt-1 text-slate-900 dark:text-slate-100 whitespace-pre-wrap">{{ $solicitud->descripcion_falla }}</p>
        </div>
    </div>

    @if ($solicitud->estaCerrada())
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-500/30 rounded-xl p-6 text-sm">
        <h2 class="font-semibold text-emerald-700 dark:text-emerald-300 mb-2">Servicio cerrado</h2>
        <p class="text-emerald-800 dark:text-emerald-200"><span class="font-semibold">Resultado:</span> {{ \Str::ucfirst(\Str::replace('_', ' ', $solicitud->resultado)) }}</p>
        @if ($solicitud->costo_servicio_bs)
            <p class="text-emerald-800 dark:text-emerald-200"><span class="font-semibold">Costo:</span> Bs {{ number_format((float) $solicitud->costo_servicio_bs, 2) }}</p>
        @endif
        <p class="text-emerald-800 dark:text-emerald-200 mt-2"><span class="font-semibold">Diagnóstico:</span> {{ $solicitud->diagnostico }}</p>
        <p class="text-emerald-700 dark:text-emerald-300 mt-2 text-xs">Cerrada el {{ $solicitud->fecha_cierre?->format('d/m/Y H:i') }}</p>
    </div>
    @elseif ($puedeCerrar)
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Cerrar servicio</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Registra el resultado, diagnóstico y costo (si aplica).</p>
        </div>
        <form method="POST" action="{{ route('serviciotecnico.cerrar', $solicitud) }}" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Resultado</label>
                    <select name="resultado" class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white" required>
                        <option value="">— Seleccionar —</option>
                        <option value="REPARACION">Reparación</option>
                        <option value="SERVICIO_PAGADO">Servicio pagado</option>
                        <option value="REEMPLAZO">Reemplazo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Costo servicio (Bs, opcional)</label>
                    <input type="number" name="costo_servicio_bs" step="0.01" min="0"
                        class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white" placeholder="0.00">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Diagnóstico</label>
                <textarea name="diagnostico" rows="3" required
                    class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                    placeholder="Describe lo que se encontró y reparó..."></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded font-semibold transition">Cerrar solicitud</button>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
