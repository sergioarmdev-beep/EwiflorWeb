@extends('layouts.app')
@section('title', "Editar cotización #{$cotizacion->id_cotizacion}")

@section('content')
<div class="p-6 max-w-5xl">
    <h1 class="text-2xl font-semibold mb-4">
        Editar cotización #{{ $cotizacion->id_cotizacion }}
    </h1>
    <form method="POST" action="{{ route('cotizaciones.update', $cotizacion) }}"
        class="bg-white p-6 rounded shadow">
        @csrf @method('PUT')
        @include('cotizaciones::_form')
    </form>
</div>
@endsection