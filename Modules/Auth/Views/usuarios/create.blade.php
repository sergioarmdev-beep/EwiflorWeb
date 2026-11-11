@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Nuevo usuario</h1>

    <form action="{{ route('usuarios.store') }}" method="POST"
        class="bg-white shadow rounded-lg p-6 space-y-4">
        @csrf
        @include('auth::usuarios._form', ['usuario' => $usuario, 'modo' => 'create'])
        <div class="flex justify-end space-x-2">
            <a href="{{ route('usuarios.index') }}" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Crear usuario
            </button>
        </div>
    </form>
</div>
@endsection