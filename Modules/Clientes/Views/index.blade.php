@extends('layouts.app')
@section('title', 'Clientes')

@section('content')
<div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Clientes</h1>
        @if (in_array(auth()->user()->nombre_rol, ['GERENTE','ADMINISTRADOR','VENDEDOR']))
        <a href="{{ route('clientes.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            + Nuevo cliente
        </a>
        @endif
    </div>

    @if (session('ok'))
    <div class="bg-green-100 text-green-800 px-4 py-2 rounded">{{ session('ok') }}</div>
    @endif

    <form method="GET" class="bg-white p-4 rounded shadow grid grid-cols-1 md:grid-cols-5 gap-3">
        <input type="text" name="buscar" value="{{ request('buscar') }}"
            placeholder="Nombre, teléfono o correo" class="border rounded px-3 py-2">
        <select name="tipo_cliente" class="border rounded px-3 py-2">
            <option value="">— Tipo —</option>
            @foreach (\Modules\Clientes\Models\Cliente::TIPOS as $t)
            <option value="{{ $t }}" @selected(request('tipo_cliente')===$t)>{{ $t }}</option>
            @endforeach
        </select>
        <select name="es_mayorista" class="border rounded px-3 py-2">
            <option value="">— Mayorista —</option>
            <option value="1" @selected(request('es_mayorista')==='1' )>Sí</option>
            <option value="0" @selected(request('es_mayorista')==='0' )>No</option>
        </select>
        <select name="estado" class="border rounded px-3 py-2">
            <option value="">— Estado —</option>
            <option value="ACTIVO" @selected(request('estado')==='ACTIVO' )>Activo</option>
            <option value="INACTIVO" @selected(request('estado')==='INACTIVO' )>Inactivo</option>
        </select>
        <div class="flex gap-2">
            <button class="bg-gray-800 text-white px-4 py-2 rounded">Filtrar</button>
            <a href="{{ route('clientes.index') }}" class="border px-4 py-2 rounded">Limpiar</a>
        </div>
    </form>

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Nombre</th>
                    <th class="px-4 py-2 text-left">Teléfono</th>
                    <th class="px-4 py-2 text-left">Correo</th>
                    <th class="px-4 py-2 text-left">Tipo</th>
                    <th class="px-4 py-2 text-left">Mayorista</th>
                    <th class="px-4 py-2 text-left">Estado</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clientes as $c)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $c->nombre }}</td>
                    <td class="px-4 py-2">{{ $c->telefono }}</td>
                    <td class="px-4 py-2">{{ $c->correo ?? '—' }}</td>
                    <td class="px-4 py-2">{{ $c->tipo_cliente ?? '—' }}</td>
                    <td class="px-4 py-2">{{ $c->es_mayorista ? 'Sí' : 'No' }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs
                                {{ $c->estado === 'ACTIVO' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $c->estado }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <a href="{{ route('clientes.show', $c) }}" class="text-blue-600">Ver</a>
                        @if (in_array(auth()->user()->nombre_rol, ['GERENTE','ADMINISTRADOR','VENDEDOR']))
                        <a href="{{ route('clientes.edit', $c) }}" class="text-amber-600">Editar</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">Sin clientes.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $clientes->links() }}</div>
</div>
@endsection