@extends('layouts.app')

@section('title', 'Nuevo Usuario')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 text-slate-100">

    <!-- Encabezado de la Sección -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-white">Crear Nuevo Usuario</h1>
        <p class="text-sm text-slate-300 mt-0.5">Completa los datos necesarios para registrar un nuevo acceso en el sistema</p>
    </div>

    @if (!empty($solicitud))
    <!-- Datos de la solicitud de cuenta que se está atendiendo -->
    <div class="mb-5 p-4 rounded-2xl bg-sky-950/30 border border-sky-500/30 text-sm">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-sky-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="min-w-0">
                <span class="block text-xs font-semibold uppercase tracking-wider text-sky-300">Creando cuenta desde una solicitud</span>
                <p class="mt-1 text-xs text-slate-300">
                    <span class="text-slate-400">Celular:</span> {{ $solicitud->celular }}
                    <span class="mx-1.5 text-slate-600">·</span>
                    <span class="text-slate-400">Enviada:</span> {{ $solicitud->fecha_creacion?->format('d/m/Y H:i') }}
                </p>
                <p class="mt-1 text-xs text-slate-300 break-words"><span class="text-slate-400">Motivo:</span> {{ $solicitud->motivo }}</p>
                <p class="mt-2 text-[11px] text-slate-400">Al guardar, la solicitud se marcará como aprobada. Comparte las credenciales con la persona por un medio seguro.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Contenedor Principal (Tarjeta Estilo Material Design con bordes y letras más claras) -->
    <form action="{{ route('usuarios.store') }}" method="POST"
        class="bg-slate-900 border border-slate-600 rounded-2xl p-6 shadow-xl space-y-5">
        @csrf
        @if (!empty($solicitud))
        <input type="hidden" name="id_solicitud" value="{{ $solicitud->id_solicitud }}">
        @elseif (old('id_solicitud'))
        <input type="hidden" name="id_solicitud" value="{{ old('id_solicitud') }}">
        @endif

        <!-- Inclusión del formulario parcial (mantiene lógica y variables originales) -->
        @include('auth::usuarios._form', ['usuario' => $usuario, 'modo' => 'create'])

        <!-- Acciones del Formulario -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-700">
            <a href="{{ route('usuarios.index') }}"
                class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-600 transition-colors cursor-pointer">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-150 active:scale-[0.99] cursor-pointer flex items-center gap-2 border border-blue-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Crear Usuario</span>
            </button>
        </div>
    </form>
</div>
@endsection