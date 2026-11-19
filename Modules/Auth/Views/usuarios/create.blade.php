@extends('layouts.app')

@section('title', 'Nuevo Usuario')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 text-ink">

    {{-- Encabezado de la Sección --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-ink">Crear Nuevo Usuario</h1>
        <p class="text-sm text-muted mt-0.5">Completa los datos necesarios para registrar un nuevo acceso en el sistema</p>
    </div>

    @if (!empty($solicitud))
    {{-- Datos de la solicitud de cuenta que se está atendiendo --}}
    <div class="mb-5 p-4 rounded-2xl border border-brand-blue/30 bg-brand-blue/20 text-sm">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-brand-blue shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="min-w-0">
                <span class="block text-xs font-semibold uppercase tracking-wider text-brand-blue">Creando cuenta desde una solicitud</span>
                <p class="mt-1 text-xs text-muted">
                    <span class="text-muted">Celular:</span> {{ $solicitud->celular }}
                    <span class="mx-1.5 text-muted">·</span>
                    <span class="text-muted">Enviada:</span> {{ $solicitud->fecha_creacion?->format('d/m/Y H:i') }}
                </p>
                <p class="mt-1 text-xs text-muted break-words"><span class="text-muted">Motivo:</span> {{ $solicitud->motivo }}</p>
                <p class="mt-2 text-[11px] text-muted">Al guardar, la solicitud se marcará como aprobada. Comparte las credenciales con la persona por un medio seguro.</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Contenedor Principal --}}
    <form action="{{ route('usuarios.store') }}" method="POST"
        class="bg-surface border border-line rounded-2xl p-6 shadow-soft space-y-5">
        @csrf
        @if (!empty($solicitud))
        <input type="hidden" name="id_solicitud" value="{{ $solicitud->id_solicitud }}">
        @elseif (old('id_solicitud'))
        <input type="hidden" name="id_solicitud" value="{{ old('id_solicitud') }}">
        @endif

        {{-- Inclusión del formulario parcial (mantiene lógica y variables originales) --}}
        @include('auth::usuarios._form', ['usuario' => $usuario, 'modo' => 'create'])

        {{-- Acciones del Formulario --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-line">
            <a href="{{ route('usuarios.index') }}"
                class="px-4 py-2.5 rounded-xl bg-surface-2 text-ink hover:bg-surface-2 hover:text-brand-green border border-line transition-colors cursor-pointer">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl shadow-sm hover:bg-brand-green-dark transition-all duration-150 active:scale-[0.99] cursor-pointer flex items-center gap-2 border border-brand-green/50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Crear Usuario</span>
            </button>
        </div>
    </form>
</div>
@endsection
