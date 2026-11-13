@extends('layouts.app')
@section('title', 'Nuevo cliente')

@section('content')
<div class="p-6 max-w-3xl">
    <h1 class="text-2xl font-semibold mb-4">Nuevo cliente</h1>
    <form method="POST" action="{{ route('clientes.store') }}"
        class="bg-white p-6 rounded shadow">
        @csrf
        @include('clientes::_form', ['cliente' => $cliente])
    </form>
</div>
@endsection