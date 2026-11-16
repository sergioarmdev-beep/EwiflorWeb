@extends('serviciotecnico::publico.layout')

@section('title', 'Solicitud #{{ $solicitud->id_solicitud }}')

@section('content')
<div class="max-w-md mx-auto">
    <div class="st-panel mx-auto">
        <h2 class="text-xl font-bold text-white mb-6">Solicitud #{{ $solicitud->id_solicitud }}</h2>

        <div class="space-y-4">
            <div class="bg-slate-800/50 rounded-xl p-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Estado actual</p>
                <span class="st-badge @switch($solicitud->estado)
                    @case('PENDIENTE') st-badge-pending @break
                    @case('PROGRAMADA') st-badge-programada @break
                    @default st-badge-cerrada @endswitch">
                    @switch($solicitud->estado)
                        @case('PENDIENTE') Pendiente de asignación @break
                        @case('PROGRAMADA') Aceptada por técnico @break
                        @default Cerrada @endswitch
                    </span>
                    @if ($solicitud->tecnico)
                        <span class="ml-2 text-xs text-slate-400">Técnico asignado: {{ $solicitud->tecnico->nombre_completo }}</span>
                    @endif
                </div>

                <div class="bg-slate-800/50 rounded-xl p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Descripción de la falla</p>
                    <p class="text-sm text-slate-300 whitespace-pre-wrap">{{ $solicitud->descripcion_falla }}</p>
                </div>

                <div class="bg-slate-800/50 rounded-xl p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Garantía</p>
                    @if ($solicitud->estaEnGarantia())
                        <span class="text-emerald-400 font-medium">Dentro de garantía — sin costo de servicio</span>
                    @else
                        <span class="text-amber-400 font-medium">Fuera de garantía — se te cobrará según tarifa del personal técnico</span>
                    @endif
                </div>

                @if ($solicitud->estaCerrada())
                <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-4">
                    <p class="text-xs font-semibold text-emerald-300 uppercase tracking-wider mb-1">Resultado</p>
                    <p class="text-sm text-emerald-200">{{ \Str::ucfirst(\Str::replace('_', ' ', $solicitud->resultado)) }}</p>
                    @if ($solicitud->costo_servicio_bs)
                        <p class="text-sm text-emerald-200 mt-1">Costo del servicio: Bs {{ number_format((float) $solicitud->costo_servicio_bs, 2) }}</p>
                    @endif
                    <p class="text-xs text-slate-400 mt-2">Diagnóstico: {{ \Str::limit($solicitud->diagnostico, 120) }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('serviciotecnico.publico.panel') }}" class="w-full px-4 py-2 border border-slate-600 text-slate-300 rounded text-center hover:bg-slate-800 transition">
                Volver a mis solicitudes
            </a>
        </div>
    </div>
</div>
@endsection
