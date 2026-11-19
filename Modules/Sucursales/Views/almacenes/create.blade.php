@extends('layouts.app')

@section('title', 'Nuevo almacén')

@section('content')
<div class="max-w-4xl mx-auto p-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex items-center gap-3">
        <div class="flex items-center justify-center w-11 h-11 rounded-xl border border-brand-blue/20 bg-brand-blue/10 text-brand-blue">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M21 8v13H3V8M1 3h22v5H1zM10 12h4" />
            </svg>
        </div>

        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">Nuevo almacén</h1>
            <p class="text-sm text-muted mt-1">
                Registra un almacén para la sucursal {{ $sucursal->nombre }}.
            </p>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="bg-surface border border-line rounded-2xl shadow-soft overflow-hidden">

        <div class="px-6 py-5 border-b border-line">
            <h2 class="text-base font-semibold text-ink">Información del almacén</h2>
            <p class="text-sm text-muted mt-1">
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
