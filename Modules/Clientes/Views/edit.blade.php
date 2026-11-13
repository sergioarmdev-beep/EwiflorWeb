@extends('layouts.app')

@section('title', 'Editar cliente')

@section('content')
<div class="max-w-4xl mx-auto p-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex items-center gap-3">
        <div class="flex items-center justify-center w-11 h-11 rounded-xl
                    bg-sky-100 text-sky-700
                    dark:bg-sky-500/10 dark:text-sky-400">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="w-6 h-6" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="1.8"
                    d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m12-13a4 4 0 11-8 0 4 4 0 018 0zm2 4h6m-3-3v6" />
            </svg>
        </div>

        <div>
            <h1 class="text-2xl font-bold tracking-tight
                       text-slate-800 dark:text-slate-100">
                Editar cliente
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Modifica la información del cliente registrado.
            </p>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="bg-white dark:bg-slate-900
                border border-slate-200 dark:border-slate-800
                rounded-2xl shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                Información del cliente
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Actualiza los campos necesarios y guarda los cambios.
            </p>
        </div>

        <form method="POST" action="{{ route('clientes.update', $cliente) }}">
            @csrf
            @method('PUT')

            <div class="p-6">
                @include('clientes::_form', ['cliente' => $cliente])
            </div>
        </form>
    </div>

</div>
@endsection