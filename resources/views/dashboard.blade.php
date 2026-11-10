@extends('layouts.app')

@section('title', 'Panel de Control')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-gray-500 text-sm font-medium">Bienvenido</h3>
        <p class="text-2xl font-bold text-gray-800">{{ Auth::user()->nombre_completo }}</p>
        <p class="text-sm text-gray-500 mt-2">Rol: {{ Auth::user()->nombre_rol }}</p>
    </div>
</div>
@endsection