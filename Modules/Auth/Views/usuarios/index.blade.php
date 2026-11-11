@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold text-gray-800">Usuarios</h1>
        <a href="{{ route('usuarios.create') }}"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
            + Nuevo usuario
        </a>
    </div>

    <form method="GET" action="{{ route('usuarios.index') }}"
        class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-3">
        <input type="text" name="buscar" value="{{ request('buscar') }}"
            placeholder="Nombre, usuario o correo"
            class="border-gray-300 rounded-md shadow-sm w-full">

        <select name="rol" class="border-gray-300 rounded-md shadow-sm w-full">
            <option value="">Todos los roles</option>
            @foreach (['GERENTE','ADMINISTRADOR','VENDEDOR','SERVICIO_TECNICO'] as $r)
            <option value="{{ $r }}" @selected(request('rol')===$r)>{{ $r }}</option>
            @endforeach
        </select>

        <select name="id_sucursal" class="border-gray-300 rounded-md shadow-sm w-full">
            <option value="">Todas las sucursales</option>
            @foreach ($sucursales as $s)
            <option value="{{ $s->id_sucursal }}"
                @selected((string) request('id_sucursal')===(string) $s->id_sucursal)>
                {{ $s->nombre }}
            </option>
            @endforeach
        </select>

        <select name="estado" class="border-gray-300 rounded-md shadow-sm w-full">
            <option value="">Todos los estados</option>
            <option value="ACTIVO" @selected(request('estado')==='ACTIVO' )>Activo</option>
            <option value="INACTIVO" @selected(request('estado')==='INACTIVO' )>Inactivo</option>
        </select>

        <button type="submit"
            class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-800">
            Filtrar
        </button>
    </form>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Nombre</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Usuario</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Correo</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Rol</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Sucursal</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Comisión</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Estado</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($usuarios as $u)
                <tr>
                    <td class="px-4 py-3">{{ $u->nombre_completo }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $u->nombre_usuario }}</td>
                    <td class="px-4 py-3">{{ $u->correo ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $u->nombre_rol }}</td>
                    <td class="px-4 py-3">{{ $u->sucursal?->nombre ?? '—' }}</td>
                    <td class="px-4 py-3">{{ number_format((float) $u->porcentaje_comision_pct, 2) }}%</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium
                                {{ $u->estado === 'ACTIVO' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $u->estado }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('usuarios.edit', $u) }}"
                            class="text-indigo-600 hover:underline text-sm">Editar</a>

                        @if ($u->id_usuario !== auth()->id())
                        <form action="{{ route('usuarios.toggleEstado', $u) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="text-sm {{ $u->estado === 'ACTIVO' ? 'text-red-600 hover:underline' : 'text-green-600 hover:underline' }}">
                                {{ $u->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">No hay usuarios registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $usuarios->links() }}
    </div>
</div>
@endsection