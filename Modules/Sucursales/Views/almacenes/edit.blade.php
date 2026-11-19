@extends('layouts.app')

@section('title', 'Editar almacén')

@section('content')
<div class="max-w-4xl mx-auto p-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex items-center gap-3">
        <div class="flex items-center justify-center w-11 h-11 rounded-xl border border-brand-blue/20 bg-brand-blue/10 text-brand-blue">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="m16.9 3.1 4 4L8 20l-5 1 1-5L16.9 3.1Z" />
            </svg>
        </div>

        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">Editar almacén</h1>
            <p class="text-sm text-muted mt-1">
                Modifica los datos del almacén {{ $almacen->nombre }}.
            </p>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="bg-surface border border-line rounded-2xl shadow-soft overflow-hidden">

        <div class="px-6 py-5 border-b border-line">
            <h2 class="text-base font-semibold text-ink">Información del almacén</h2>
            <p class="text-sm text-muted mt-1">
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
