@extends('layouts.app')

@section('title', 'Editar almacén')

@section('content')
<div class="max-w-4xl mx-auto p-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex items-center gap-3">
        <div class="flex items-center justify-center w-11 h-11 rounded-xl
                    bg-amber-100 text-amber-700
                    dark:bg-amber-500/10 dark:text-amber-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="m16.9 3.1 4 4L8 20l-5 1 1-5L16.9 3.1Z" />
            </svg>
        </div>

        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                Editar almacén
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Modifica los datos del almacén {{ $almacen->nombre }}.
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
                El estado se administra desde la tarjeta o el detalle de la sucursal.
            </p>
        </div>

        <form method="POST" action="{{ route('sucursales.almacenes.update', $almacen) }}">
            @csrf
            @method('PUT')

            <div class="p-6">
                @include('sucursales::_form_almacen', [
                    'almacen'    => $almacen,
                    'sucursales' => $sucursales,
                ])
            </div>
        </form>
    </div>
</div>
@endsection
