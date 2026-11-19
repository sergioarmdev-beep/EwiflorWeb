@extends('layouts.app')
@section('title', "Editar cotización #{$cotizacion->id_cotizacion}")

@section('content')
<div class="p-6 max-w-5xl">
    <h1 class="text-2xl font-bold text-ink mb-4">
        Editar cotización #{{ $cotizacion->id_cotizacion }}
    </h1>
    <form method="POST" action="{{ route('cotizaciones.update', $cotizacion) }}"
        class="bg-surface p-6 rounded-2xl border border-line shadow-soft">
        @csrf @method('PUT')
        @include('cotizaciones::_form')
    </form>
</div>
@endsection
