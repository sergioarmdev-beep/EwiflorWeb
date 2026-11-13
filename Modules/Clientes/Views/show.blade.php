@extends('layouts.app')
@section('title', $cliente->nombre)

@section('content')
<div class="p-6 max-w-4xl space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">{{ $cliente->nombre }}</h1>
        <div class="flex gap-2">
            @if (in_array(auth()->user()->nombre_rol, ['GERENTE','ADMINISTRADOR','VENDEDOR']))
            <a href="{{ route('clientes.edit', $cliente) }}"
                class="border px-4 py-2 rounded">Editar</a>
            @endif
            @if (in_array(auth()->user()->nombre_rol, ['GERENTE','ADMINISTRADOR']))
            <form method="POST" action="{{ route('clientes.toggleEstado', $cliente) }}">
                @csrf @method('PATCH')
                <button class="border px-4 py-2 rounded">
                    {{ $cliente->estado === 'ACTIVO' ? 'Desactivar' : 'Reactivar' }}
                </button>
            </form>
            @endif
            <a href="{{ route('clientes.index') }}" class="border px-4 py-2 rounded">Volver</a>
        </div>
    </div>

    @if (session('ok'))
    <div class="bg-green-100 text-green-800 px-4 py-2 rounded">{{ session('ok') }}</div>
    @endif

    <div class="bg-white p-6 rounded shadow grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div><span class="text-gray-500">Teléfono:</span> {{ $cliente->telefono }}</div>
        <div><span class="text-gray-500">Correo:</span> {{ $cliente->correo ?? '—' }}</div>
        <div><span class="text-gray-500">Tipo:</span> {{ $cliente->tipo_cliente ?? '—' }}</div>
        <div><span class="text-gray-500">Mayorista:</span> {{ $cliente->es_mayorista ? 'Sí' : 'No' }}</div>
        <div><span class="text-gray-500">Estado:</span> {{ $cliente->estado }}</div>
        <div><span class="text-gray-500">Registrado:</span> {{ $cliente->fecha_registro?->format('d/m/Y H:i') ?? '—' }}</div>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <h2 class="font-semibold mb-2">Historial</h2>
        <p class="text-sm text-gray-500">
            Aquí aparecerán cotizaciones, ventas y garantías cuando esos módulos estén implementados.
        </p>
    </div>
</div>
@endsection