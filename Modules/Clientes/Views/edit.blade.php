@extends('layouts.app')

@section('title', 'Editar cliente')

@section('content')
<div class="max-w-4xl mx-auto p-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex items-center gap-3">
        <div class="flex items-center justify-center w-11 h-11 rounded-xl
                    border border-brand-blue/20 bg-brand-blue/10 text-brand-blue">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="w-6 h-6" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="1.8"
                    d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m12-13a4 4 0 11-8 0 4 4 0 018 0zm2 4h6m-3-3v6" />
            </svg>
        </div>

        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">
                Editar cliente
            </h1>
            <p class="text-sm text-muted mt-1">
                Modifica la información del cliente registrado.
            </p>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="bg-surface border border-line rounded-2xl shadow-soft overflow-hidden">

        <div class="px-6 py-5 border-b border-line">
            <h2 class="text-base font-semibold text-ink">
                Información del cliente
            </h2>
            <p class="text-sm text-muted mt-1">
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
