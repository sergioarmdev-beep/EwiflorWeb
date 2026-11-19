@extends('serviciotecnico::publico.layout')

@section('title', 'Solicitud #{{ $solicitud->id_solicitud }}')

@section('content')
<div class="max-w-md mx-auto">
    <div class="st-panel mx-auto">
        <h2 class="text-xl font-bold text-ink mb-6">Solicitud #{{ $solicitud->id_solicitud }}</h2>

        <div class="space-y-4">
            <div class="bg-surface-2 rounded-xl p-4">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Estado actual</p>
                <span class="st-badge
                    @switch($solicitud->estado)
                        @case('PENDIENTE') border border-amber-500/30 bg-amber-500/10 text-amber-500 @break
                        @case('PROGRAMADA') border border-brand-blue/20 bg-brand-blue/10 text-brand-blue @break
                        @default border border-brand-green/20 bg-brand-green/10 text-brand-green @endswitch">
                    @switch($solicitud->estado)
                        @case('PENDIENTE') Pendiente de asignación @break
                        @case('PROGRAMADA') Aceptada por técnico @break
                        @default Cerrada @endswitch
                    </span>
                    @if ($solicitud->tecnico)
                        <span class="ml-2 text-xs text-muted">Técnico asignado: {{ $solicitud->tecnico->nombre_completo }}</span>
                    @endif
                </div>

                <div class="bg-surface-2 rounded-xl p-4">
                    <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Descripción de la falla</p>
                    <p class="text-sm text-ink whitespace-pre-wrap">{{ $solicitud->descripcion_falla }}</p>
                </div>

                <div class="bg-surface-2 rounded-xl p-4">
                    <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Garantía</p>
                    @if ($solicitud->estaEnGarantia())
                        <span class="text-brand-green font-medium">Dentro de garantía — sin costo de servicio</span>
                    @else
                        <span class="text-amber-500 font-medium">Fuera de garantía — se te cobrará según tarifa del personal técnico</span>
                    @endif
                </div>

                @if ($solicitud->estaCerrada())
                <div class="bg-brand-green/10 border border-brand-green/30 rounded-xl p-4">
                    <p class="text-xs font-semibold text-brand-green uppercase tracking-wider mb-1">Resultado</p>
                    <p class="text-sm text-brand-green">{{ \Str::ucfirst(\Str::replace('_', ' ', $solicitud->resultado)) }}</p>
                    @if ($solicitud->costo_servicio_bs)
                        <p class="text-sm text-brand-green mt-1">Costo del servicio: Bs {{ number_format((float) $solicitud->costo_servicio_bs, 2) }}</p>
                    @endif
                    <p class="text-xs text-muted mt-2">Diagnóstico: {{ \Str::limit($solicitud->diagnostico, 120) }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('serviciotecnico.publico.panel') }}" class="w-full px-4 py-2 border border-line rounded-xl text-center text-ink hover:bg-surface-2 transition">
                Volver a mis solicitudes
            </a>
        </div>
    </div>
</div>
@endsection
