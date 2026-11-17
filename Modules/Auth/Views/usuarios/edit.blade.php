@extends('layouts.app')

@section('title', 'Editar Usuario')

@php
$card = 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden transition-colors';
$secondary = 'inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 transition-colors cursor-pointer';
$primary = 'inline-flex items-center gap-2 px-5 py-2.5 bg-[#0B6E45] hover:bg-[#095638] text-white text-xs font-semibold rounded-xl shadow-sm hover:shadow-md transition-all duration-150 active:scale-[0.99] cursor-pointer';
@endphp

@section('content')

{{-- Fallback: garantiza modo oscuro y paleta correcta incluso si el _form tiene colores hardcodeados --}}
<style>
    /* Wrapper general en modo oscuro */
    html.dark [data-usuarios-form] {
        color: #f1f5f9;
    }

    /* Inputs, selects y textareas dentro del form incluido */
    html.dark [data-usuarios-form] input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]),
    html.dark [data-usuarios-form] select,
    html.dark [data-usuarios-form] textarea {
        background-color: #1e293b !important;
        color: #f1f5f9 !important;
        border-color: rgba(14, 165, 233, 0.4) !important;
    }

    html.dark [data-usuarios-form] input::placeholder,
    html.dark [data-usuarios-form] textarea::placeholder {
        color: #64748b !important;
    }

    html.dark [data-usuarios-form] input:focus,
    html.dark [data-usuarios-form] select:focus,
    html.dark [data-usuarios-form] textarea:focus {
        border-color: #38bdf8 !important;
        box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.3) !important;
        outline: none !important;
    }

    /* Labels */
    html.dark [data-usuarios-form] label {
        color: #cbd5e1 !important;
    }

    /* Separadores y subpaneles */
    html.dark [data-usuarios-form] .border-slate-700,
    html.dark [data-usuarios-form] .border-slate-800,
    html.dark [data-usuarios-form] .border-slate-200 {
        border-color: #1e293b !important;
    }

    html.dark [data-usuarios-form] .bg-slate-50,
    html.dark [data-usuarios-form] .bg-slate-100 {
        background-color: rgba(15, 23, 42, 0.5) !important;
    }

    /* Textos que suelen venir oscuros */
    html.dark [data-usuarios-form] .text-slate-900,
    html.dark [data-usuarios-form] .text-slate-800,
    html.dark [data-usuarios-form] .text-slate-700 {
        color: #e2e8f0 !important;
    }

    html.dark [data-usuarios-form] .text-slate-600,
    html.dark [data-usuarios-form] .text-slate-500 {
        color: #94a3b8 !important;
    }

    /* Checkboxes y radios (acento verde) */
    html.dark [data-usuarios-form] input[type="checkbox"],
    html.dark [data-usuarios-form] input[type="radio"] {
        accent-color: #0B6E45;
    }
</style>

<div class="max-w-2xl mx-auto space-y-5">

    {{-- ───────── Encabezado ───────── --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Editar Usuario</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Modifica los datos y permisos de acceso del usuario seleccionado
        </p>
    </div>

    {{-- ───────── Formulario ───────── --}}
    <form action="{{ route('usuarios.update', $usuario) }}" method="POST"
        class="{{ $card }}">
        @csrf
        @method('PUT')

        <div class="p-6" data-usuarios-form>
            @include('auth::usuarios._form', ['usuario' => $usuario, 'modo' => 'edit'])
        </div>

        {{-- Acciones --}}
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3 bg-slate-50 dark:bg-slate-950/40">
            <a href="{{ route('usuarios.index') }}" class="{{ $secondary }}">
                Cancelar
            </a>
            <button type="submit" class="{{ $primary }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Guardar Cambios</span>
            </button>
        </div>
    </form>
</div>
@endsection