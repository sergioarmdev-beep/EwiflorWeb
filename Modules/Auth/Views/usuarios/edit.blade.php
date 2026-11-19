@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    {{-- ───────── Encabezado ───────── --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-ink">Editar Usuario</h1>
        <p class="text-sm text-muted mt-0.5">
            Modifica los datos y permisos de acceso del usuario seleccionado
        </p>
    </div>

    {{-- ───────── Formulario ───────── --}}
    <form action="{{ route('usuarios.update', $usuario) }}" method="POST"
        class="bg-surface border border-line rounded-2xl shadow-soft overflow-hidden">
        @csrf
        @method('PUT')

        <div class="p-6">
            @include('auth::usuarios._form', ['usuario' => $usuario, 'modo' => 'edit'])
        </div>

        {{-- Acciones --}}
        <div class="px-6 py-4 border-t border-line flex items-center justify-end gap-3 bg-surface-2/60">
            <a href="{{ route('usuarios.index') }}" class="px-4 py-2.5 rounded-xl bg-surface-2 text-ink hover:bg-surface-2 hover:text-brand-green border border-line transition-colors cursor-pointer">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl shadow-sm hover:bg-brand-green-dark transition-all duration-150 active:scale-[0.99] cursor-pointer flex items-center gap-2 border border-brand-green/50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Guardar Cambios</span>
            </button>
        </div>
    </form>
</div>
@endsection
