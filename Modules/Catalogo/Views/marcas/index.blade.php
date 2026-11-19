@extends('layouts.app')

@section('title', 'Gestión de Marcas')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-ink">

    <!-- Encabezado de la Sección -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold tracking-tight text-ink">Gestión de Marcas</h2>
        <p class="text-xs text-muted mt-0.5">Registra, edita y administra los fabricantes y marcas del catálogo</p>
    </div>

    <!-- Errores de validación -->
    @if($errors->any())
    <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-xs text-red-400">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">

        <!-- Formulario de Creación (Tarjeta Elevada) -->
        <div class="bg-surface border border-line rounded-2xl p-6 shadow-soft h-fit">
            <h3 class="text-base font-semibold text-ink mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nueva Marca</span>
            </h3>

            <form action="{{ route('catalogo.marcas.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">Nombre de la Marca *</label>
                    <input type="text" name="nombre" required
                        placeholder="Ej. Bosch"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-surface-2 border border-line text-ink placeholder:text-muted text-sm focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">País de Origen</label>
                    <input type="text" name="pais_origen"
                        placeholder="Ej. Alemania"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-surface-2 border border-line text-ink placeholder:text-muted text-sm focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                </div>

                <button type="submit"
                    class="w-full py-2.5 px-4 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-semibold rounded-xl shadow-sm hover:shadow-md transition-all duration-150 active:scale-[0.99] cursor-pointer border border-brand-green/50">
                    Guardar Marca
                </button>
            </form>
        </div>

        <!-- Tabla de Marcas Registradas -->
        <div class="md:col-span-2 bg-surface border border-line rounded-2xl shadow-soft overflow-hidden">
            <div class="px-6 py-4 border-b border-line flex items-center justify-between">
                <h3 class="font-semibold text-sm text-ink">Marcas Registradas</h3>
                <span class="text-xs text-muted rounded-lg bg-surface-2 px-2.5 py-1 border border-line">
                    {{ count($marcas) }} registros
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-line text-sm text-left">
                    <thead class="bg-surface-2 text-muted text-xs uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-5 font-semibold">Nombre</th>
                            <th class="py-3.5 px-5 font-semibold">País</th>
                            <th class="py-3.5 px-5 text-center font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-muted text-xs">
                        @forelse($marcas as $marca)
                        <tr class="hover:bg-surface-2 transition-colors">
                            <td class="py-3 px-5">
                                <input type="text" name="nombre" value="{{ $marca->nombre }}" required maxlength="60"
                                    form="update-form-{{ $marca->id_marca }}"
                                    class="w-full px-3 py-1.5 rounded-lg bg-surface-2 border border-line text-ink text-xs focus:border-brand-green/50 focus:ring-1 focus:ring-brand-green/20">
                            </td>
                            <td class="py-3 px-5">
                                <input type="text" name="pais_origen" value="{{ $marca->pais_origen }}" maxlength="50"
                                    form="update-form-{{ $marca->id_marca }}"
                                    class="w-full px-3 py-1.5 rounded-lg bg-surface-2 border border-line text-ink text-xs focus:border-brand-green/50 focus:ring-1 focus:ring-brand-green/20" placeholder="—">
                            </td>

                            <td class="py-3 px-5 text-center">
                                <div class="flex items-center justify-center space-x-3">
                                    <button type="submit" form="update-form-{{ $marca->id_marca }}"
                                        class="text-brand-blue hover:text-brand-blue-dark font-semibold text-xs transition-colors cursor-pointer">
                                        Actualizar
                                    </button>

                                    <form action="{{ route('catalogo.marcas.destroy', $marca->id_marca) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta marca?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-400 font-semibold text-xs transition-colors cursor-pointer">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-12 px-6 text-center text-muted">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <span class="text-xs font-medium text-muted">No hay marcas registradas todavía.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Formularios de actualización (fuera de la tabla: un <form> no puede ir dentro de <tr>) --}}
            @foreach($marcas as $marca)
            <form id="update-form-{{ $marca->id_marca }}" action="{{ route('catalogo.marcas.update', $marca->id_marca) }}" method="POST" class="hidden">
                @csrf
                @method('PUT')
            </form>
            @endforeach
        </div>
    </div>
</div>
@endsection
