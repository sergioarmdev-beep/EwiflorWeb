@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 text-slate-100">

    <!-- Encabezado de la Sección -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-100">Gestión de Usuarios</h1>
            <p class="text-xs text-slate-400 mt-0.5">Administra los accesos, roles y sucursales del personal</p>
        </div>
        <a href="{{ route('usuarios.create') }}"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-150 active:scale-[0.99] cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Nuevo Usuario</span>
        </a>
    </div>

    <!-- Barra de Filtros (Diseño Tarjeta Elevada) -->
    <form method="GET" action="{{ route('usuarios.index') }}"
        class="mb-6 p-4 bg-slate-900 border border-slate-800/80 rounded-2xl shadow-xl grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 items-center">

        <!-- Input de Búsqueda -->
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                placeholder="Nombre, usuario o correo..."
                class="w-full pl-9 pr-3 py-2 bg-slate-800 border border-slate-700/70 rounded-xl text-slate-100 placeholder-slate-500 text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
        </div>

        <!-- Select Rol -->
        <div>
            <select name="rol" class="w-full px-3 py-2 bg-slate-800 border border-slate-700/70 rounded-xl text-slate-100 text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                <option value="">Todos los roles</option>
                @foreach (['GERENTE','ADMINISTRADOR','VENDEDOR','SERVICIO_TECNICO'] as $r)
                <option value="{{ $r }}" @selected(request('rol')===$r)>{{ $r }}</option>
                @endforeach
            </select>
        </div>

        <!-- Select Sucursal -->
        <div>
            <select name="id_sucursal" class="w-full px-3 py-2 bg-slate-800 border border-slate-700/70 rounded-xl text-slate-100 text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                <option value="">Todas las sucursales</option>
                @foreach ($sucursales as $s)
                <option value="{{ $s->id_sucursal }}"
                    @selected((string) request('id_sucursal')===(string) $s->id_sucursal)>
                    {{ $s->nombre }}
                </option>
                @endforeach
            </select>
        </div>

        <!-- Select Estado -->
        <div>
            <select name="estado" class="w-full px-3 py-2 bg-slate-800 border border-slate-700/70 rounded-xl text-slate-100 text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                <option value="">Todos los estados</option>
                <option value="ACTIVO" @selected(request('estado')==='ACTIVO' )>Activo</option>
                <option value="INACTIVO" @selected(request('estado')==='INACTIVO' )>Inactivo</option>
            </select>
        </div>

        <!-- Botón Filtrar -->
        <div class="flex gap-2">
            <button type="submit"
                class="flex-1 py-2 px-4 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700/80 transition-colors cursor-pointer flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>Filtrar</span>
            </button>
            @if(request()->anyFilled(['buscar', 'rol', 'id_sucursal', 'estado']))
            <a href="{{ route('usuarios.index') }}"
                class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white rounded-xl border border-slate-700/80 transition-colors flex items-center justify-center" title="Limpiar filtros">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
            @endif
        </div>
    </form>

    <!-- Contenedor de la Tabla -->
    <div class="bg-slate-900 border border-slate-800/80 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-sm text-left">
                <thead class="bg-slate-950/50 text-slate-400 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Nombre</th>
                        <th class="px-5 py-3.5 font-semibold">Usuario</th>
                        <th class="px-5 py-3.5 font-semibold">Correo</th>
                        <th class="px-5 py-3.5 font-semibold">Rol</th>
                        <th class="px-5 py-3.5 font-semibold">Sucursal</th>
                        <th class="px-5 py-3.5 font-semibold">Comisión</th>
                        <th class="px-5 py-3.5 font-semibold">Estado</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse ($usuarios as $u)
                    <tr class="hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-4 font-medium text-slate-100">{{ $u->nombre_completo }}</td>
                        <td class="px-5 py-4 font-mono text-xs text-slate-400">{{ $u->nombre_usuario }}</td>
                        <td class="px-5 py-4 text-slate-400">{{ $u->correo ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 border border-slate-700/50 text-slate-300">
                                {{ $u->nombre_rol }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-400">{{ $u->sucursal?->nombre ?? '—' }}</td>
                        <td class="px-5 py-4 font-medium text-slate-200">{{ number_format((float) $u->porcentaje_comision_pct, 2) }}%</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $u->estado === 'ACTIVO' ? 'bg-emerald-950/60 text-emerald-300 border border-emerald-500/30' : 'bg-red-950/60 text-red-300 border border-red-500/30' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $u->estado === 'ACTIVO' ? 'bg-emerald-400' : 'bg-red-400' }}"></span>
                                {{ $u->estado }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right space-x-3 whitespace-nowrap">
                            <a href="{{ route('usuarios.edit', $u) }}"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-blue-400 hover:text-blue-300 transition-colors">
                                Editar
                            </a>

                            @if ($u->id_usuario !== auth()->id())
                            <form action="{{ route('usuarios.toggleEstado', $u) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="inline-flex items-center gap-1 text-xs font-semibold {{ $u->estado === 'ACTIVO' ? 'text-red-400 hover:text-red-300' : 'text-emerald-400 hover:text-emerald-300' }} transition-colors cursor-pointer">
                                    {{ $u->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="text-xs font-medium text-slate-400">No hay usuarios registrados que coincidan con la búsqueda.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    <div class="mt-6">
        {{ $usuarios->links() }}
    </div>
</div>
@endsection