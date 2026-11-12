@extends('layouts.app')

@section('title', 'Gestión de Marcas')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-slate-100">

    <!-- Encabezado de la Sección -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold tracking-tight text-white">Gestión de Marcas</h2>
        <p class="text-xs text-slate-300 mt-0.5">Registra, edita y administra los fabricantes y marcas del catálogo</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">

        <!-- Formulario de Creación (Tarjeta Elevada) -->
        <div class="bg-slate-900 border border-sky-500/40 rounded-2xl p-6 shadow-xl h-fit">
            <h3 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nueva Marca</span>
            </h3>

            <form action="{{ route('catalogo.marcas.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">Nombre de la Marca *</label>
                    <input type="text" name="nombre" required
                        placeholder="Ej. Bosch"
                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-sky-500/40 rounded-xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">País de Origen</label>
                    <input type="text" name="pais_origen"
                        placeholder="Ej. Alemania"
                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-sky-500/40 rounded-xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all">
                </div>

                <button type="submit"
                    class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-150 active:scale-[0.99] cursor-pointer border border-blue-400">
                    Guardar Marca
                </button>
            </form>
        </div>

        <!-- Tabla de Marcas Registradas -->
        <div class="md:col-span-2 bg-slate-900 border border-sky-500/40 rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                <h3 class="font-semibold text-sm text-white">Marcas Registradas</h3>
                <span class="text-xs text-slate-400 bg-slate-800 px-2.5 py-1 rounded-lg border border-slate-700">
                    {{ count($marcas) }} registros
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800 text-sm text-left">
                    <thead class="bg-slate-950/50 text-slate-400 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-5 font-semibold">Nombre</th>
                            <th class="py-3.5 px-5 font-semibold">País</th>
                            <th class="py-3.5 px-5 text-center font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300 text-xs">
                        @forelse($marcas as $marca)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <!-- Formulario de Actualización en Línea -->
                            <form id="update-form-{{ $marca->id_marca }}" action="{{ route('catalogo.marcas.update', $marca->id_marca) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <td class="py-3 px-5">
                                    <input type="text" name="nombre" value="{{ $marca->nombre }}" required
                                        class="w-full px-3 py-1.5 bg-slate-800 border border-slate-600 rounded-lg text-slate-100 text-xs focus:outline-none focus:border-sky-400 focus:ring-1 focus:ring-sky-500 transition-all">
                                </td>
                                <td class="py-3 px-5">
                                    <input type="text" name="pais_origen" value="{{ $marca->pais_origen }}"
                                        class="w-full px-3 py-1.5 bg-slate-800 border border-slate-600 rounded-lg text-slate-100 text-xs focus:outline-none focus:border-sky-400 focus:ring-1 focus:ring-sky-500 transition-all" placeholder="—">
                                </td>
                            </form>

                            <td class="py-3 px-5 text-center">
                                <div class="flex items-center justify-center space-x-3">
                                    <!-- Botón Actualizar (Dispara el formulario de arriba mediante form="id") -->
                                    <button type="submit" form="update-form-{{ $marca->id_marca }}"
                                        class="text-blue-400 hover:text-blue-300 font-semibold text-xs transition-colors cursor-pointer">
                                        Actualizar
                                    </button>

                                    <!-- Botón Eliminar (Fuera de formularios anidados) -->
                                    <form action="{{ route('catalogo.marcas.destroy', $marca->id_marca) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta marca?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 font-semibold text-xs transition-colors cursor-pointer">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-12 px-6 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <span class="text-xs font-medium text-slate-400">No hay marcas registradas todavía.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection