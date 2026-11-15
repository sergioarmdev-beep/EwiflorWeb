@extends('layouts.app')

@section('title', 'Nueva sucursal')

@section('content')
<div class="max-w-4xl mx-auto p-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex items-center gap-3">
        <div class="flex items-center justify-center w-11 h-11 rounded-xl
                    bg-sky-100 text-sky-700
                    dark:bg-sky-500/10 dark:text-sky-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z" />
            </svg>
        </div>

        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                Nueva sucursal
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Registra una nueva sucursal en el sistema.
            </p>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="bg-white dark:bg-slate-900
                border border-slate-200 dark:border-slate-800
                rounded-2xl shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                Información de la sucursal
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Completa los datos necesarios para registrar la sucursal.
            </p>
        </div>

        <form method="POST" action="{{ route('sucursales.store') }}">
            @csrf

            <div class="p-6">
                @include('sucursales::_form', ['sucursal' => $sucursal])
            </div>
        </form>
    </div>
</div>
@endsection
