@extends('layouts.app')

@section('title', 'Nuevo almacén')

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
                    d="M21 8v13H3V8M1 3h22v5H1zM10 12h4" />
            </svg>
        </div>

        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                Nuevo almacén
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Registra un almacén para la sucursal {{ $sucursal->nombre }}.
            </p>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="bg-white dark:bg-slate-900
                border border-slate-200 dark:border-slate-800
                rounded-2xl shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                Información del almacén
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Completa los datos necesarios para registrar el almacén.
            </p>
        </div>

        <form method="POST" action="{{ route('sucursales.almacenes.store', $sucursal) }}">
            @csrf

            <div class="p-6">
                @include('sucursales::_form_almacen', [
                    'almacen'    => $almacen,
                    'sucursales' => collect([$sucursal]),
                ])
            </div>
        </form>
    </div>
</div>
@endsection
