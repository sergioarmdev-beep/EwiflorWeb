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
        <h1 class="text-2xl font-bold text-ink">
            Solicitud #{{ $solicitud->id_solicitud }}
            <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold
                @if($solicitud->estado === 'PENDIENTE') border border-amber-500/30 bg-amber-500/10 text-amber-500 @endif
                @if($solicitud->estado === 'PROGRAMADA') border border-brand-blue/20 bg-brand-blue/10 text-brand-blue @endif
                @if($solicitud->estado === 'CERRADA') border border-brand-green/20 bg-brand-green/10 text-brand-green @endif
                @if($solicitud->estado === 'CANCELADA') border border-red-500/30 bg-red-500/10 text-red-500 @endif">
                {{ $solicitud->estado }}
            </span>
        </h1>
        <a href="{{ route('dashboard')  }}" class="text-sm font-medium text-brand-blue hover:underline">← Volver</a>
    </div>

    @if (session('success'))
    <div class="flex items-start gap-3 rounded-xl border border-brand-green/30 bg-brand-green/10 px-4 py-3 text-sm text-brand-green">
        {{ session('success') }}
    </div>
    @endif
    @if (session('error'))
    <div class="flex items-start gap-3 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-500">
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-surface rounded-xl shadow-soft p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div><span class="text-muted">Cliente:</span> <span class="text-ink">{{ data_get($solicitud, 'cliente.nombre', '—') }}</span></div>
        <div><span class="text-muted">Teléfono:</span> <span class="text-ink">{{ data_get($solicitud, 'cliente.telefono', '—') }}</span></div>
        <div><span class="text-muted">Registró:</span> <span class="text-ink">{{ data_get($solicitud, 'registra.nombre_completo', 'Cliente web (público)') }}</span></div>
        <div><span class="text-muted">Origen:</span> <span class="text-ink">{{ $solicitud->origen }}</span></div>
        <div><span class="text-muted">Técnico asignado:</span> <span class="text-ink">{{ data_get($solicitud, 'tecnico.nombre_completo', 'Sin asignar') }}</span></div>
        <div><span class="text-muted">Fecha solicitud:</span> <span class="text-ink">{{ $solicitud->fecha_solicitud?->format('d/m/Y H:i') }}</span></div>
        <div class="md:col-span-2">
            <span class="text-muted">Garantía:</span>
            @if ($solicitud->estaEnGarantia())
            <span class="text-brand-green font-semibold">Dentro de garantía (sin costo)</span>
            @else
            <span class="text-amber-500 font-semibold">Fuera de garantía (servicio pagado)</span>
            @endif
        </div>
        <div class="md:col-span-2">
            <span class="text-muted">Descripción de la falla:</span>
            <p class="mt-1 text-ink whitespace-pre-wrap">{{ $solicitud->descripcion_falla }}</p>
        </div>
    </div>

    @if ($solicitud->estaCerrada())
    <div class="rounded-xl border border-brand-green/30 bg-brand-green/10 p-6 text-sm">
        <h2 class="font-bold text-brand-green mb-2">Servicio cerrado</h2>
        <p class="text-brand-green"><span class="font-semibold">Resultado:</span> {{ \Str::ucfirst(\Str::replace('_', ' ', $solicitud->resultado)) }}</p>
        @if ($solicitud->costo_servicio_bs)
        <p class="text-brand-green"><span class="font-semibold">Costo:</span> Bs {{ number_format((float) $solicitud->costo_servicio_bs, 2) }}</p>
        @endif
        <p class="text-brand-green mt-2"><span class="font-semibold">Diagnóstico:</span> {{ $solicitud->diagnostico }}</p>
        <p class="text-brand-green mt-2 text-xs">Cerrada el {{ $solicitud->fecha_cierre?->format('d/m/Y H:i') }}</p>
    </div>
    @elseif ($solicitud->estaCancelada())
    <div class="rounded-xl border border-red-500/30 bg-red-500/10 p-6 text-sm text-red-500">
        <h2 class="font-bold mb-2">Solicitud cancelada</h2>
        <p><span class="font-semibold">Motivo:</span> {{ $solicitud->motivo_cancelacion ?: 'No registrado' }}</p>
    </div>
    @elseif ($puedeCerrar)
    <div class="bg-surface rounded-xl shadow-soft overflow-hidden">
        <div class="px-6 py-4 border-b border-line">
            <h2 class="text-lg font-bold text-ink">Cerrar servicio</h2>
            <p class="text-xs text-muted">Registra el resultado, diagnóstico y costo (si aplica).</p>
        </div>
        <form method="POST" action="{{ route('serviciotecnico.cerrar', $solicitud) }}" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Resultado</label>
                    <select name="resultado" class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20" required>
                        <option value="">— Seleccionar —</option>
                        <option value="REPARACION">Reparación</option>
                        <option value="SERVICIO_PAGADO">Servicio pagado</option>
                        <option value="REEMPLAZO">Reemplazo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Costo servicio (Bs, opcional)</label>
                    <input type="number" name="costo_servicio_bs" step="0.01" min="0"
                        class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20" placeholder="0.00">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Diagnóstico</label>
                <textarea name="diagnostico" rows="3" required
                    class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20"
                    placeholder="Describe lo que se encontró y reparó..."></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-brand-green text-white rounded-xl font-semibold hover:bg-brand-green-dark transition">Cerrar solicitud</button>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
