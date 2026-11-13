@extends('layouts.app')
@section('title', 'Nueva cotización')

@section('content')
<div class="p-6 max-w-5xl">
    <h1 class="text-2xl font-semibold mb-4">Nueva cotización</h1>
    <form method="POST" action="{{ route('cotizaciones.store') }}"
        class="bg-white p-6 rounded shadow">
        @csrf
        @include('cotizaciones::_form')
    </form>
</div>
@endsection