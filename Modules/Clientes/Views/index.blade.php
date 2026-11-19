@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
@php
$puedeGestionarClientes = in_array(
auth()->user()->nombre_rol,
['GERENTE', 'ADMINISTRADOR', 'VENDEDOR'],
true
);
@endphp

<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 flex items-center justify-center rounded-xl
                             border border-brand-blue/20 bg-brand-blue/10 text-brand-blue">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M17 20h5v-2a4 4 0 0 0-5.5-3.7M9 20H2v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2H9Zm0-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-1a3 3 0 1 0 0-6" />
                    </svg>
                </span>

                <h1 class="text-2xl font-bold text-ink">
                    Clientes
                </h1>
            </div>

            <p class="text-sm text-muted">
                Administra la información y clasificación de tus clientes.
            </p>
        </div>

        @if ($puedeGestionarClientes)
        <a href="{{ route('clientes.create') }}"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5
                      rounded-lg bg-brand-green text-white text-sm font-semibold shadow-sm hover:bg-brand-green-dark transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo cliente
        </a>
        @endif
    </div>

    {{-- Mensaje de éxito --}}
    @if (session('ok'))
    <div class="flex items-center gap-3 p-4 rounded-lg
                border border-brand-green/30 bg-brand-green/10
                text-brand-green">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2" d="m5 12 4 4L19 6" />
        </svg>
        <p class="text-sm font-medium">{{ session('ok') }}</p>
    </div>
    @endif

    {{-- Filtros --}}
    <section class="bg-surface border border-line rounded-xl p-4 sm:p-5 shadow-soft">

        <div class="flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-muted"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="1.8"
                    d="M3 4h18M6 9h12m-9 5h6" />
            </svg>

            <h2 class="text-sm font-semibold text-ink">
                Buscar y filtrar clientes
            </h2>
        </div>

        <form method="GET" action="{{ route('clientes.index') }}"
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3">

            {{-- Búsqueda --}}
            <div>
                <label for="buscar"
                    class="block text-xs font-semibold text-muted mb-1.5">
                    Buscar cliente
                </label>

                <input type="text" id="buscar" name="buscar"
                    value="{{ request('buscar') }}"
                    placeholder="Nombre, teléfono o correo"
                    class="w-full rounded-xl border border-line bg-surface-2 text-ink placeholder:text-muted px-3 py-2.5 text-sm outline-none focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
            </div>

            {{-- Tipo --}}
            <div>
                <label for="tipo_cliente"
                    class="block text-xs font-semibold text-muted mb-1.5">
                    Tipo de cliente
                </label>

                <select name="tipo_cliente" id="tipo_cliente"
                    class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3 py-2.5 text-sm outline-none focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                    <option value="">Todos los tipos</option>

                    @foreach (\Modules\Clientes\Models\Cliente::TIPOS as $t)
                    <option value="{{ $t }}"
                        @selected(request('tipo_cliente')===$t)>
                        {{ $t }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Mayorista --}}
            <div>
                <label for="es_mayorista"
                    class="block text-xs font-semibold text-muted mb-1.5">
                    Clasificación
                </label>

                <select name="es_mayorista" id="es_mayorista"
                    class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3 py-2.5 text-sm outline-none focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                    <option value="">Todos</option>
                    <option value="1" @selected(request('es_mayorista')==='1' )>
                        Mayorista
                    </option>
                    <option value="0" @selected(request('es_mayorista')==='0' )>
                        Minorista
                    </option>
                </select>
            </div>

            {{-- Estado --}}
            <div>
                <label for="estado"
                    class="block text-xs font-semibold text-muted mb-1.5">
                    Estado
                </label>

                <select name="estado" id="estado"
                    class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3 py-2.5 text-sm outline-none focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                    <option value="">Todos los estados</option>
                    <option value="ACTIVO" @selected(request('estado')==='ACTIVO' )>
                        Activo
                    </option>
                    <option value="INACTIVO" @selected(request('estado')==='INACTIVO' )>
                        Inactivo
                    </option>
                </select>
            </div>

            {{-- Acciones --}}
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 px-4 py-2.5 rounded-xl
                               bg-brand-green text-white text-sm font-semibold hover:bg-brand-green-dark transition">
                    Filtrar
                </button>

                <a href="{{ route('clientes.index') }}"
                    title="Limpiar filtros"
                    class="inline-flex items-center justify-center
                          w-10 h-[42px] rounded-xl
                          border border-line text-ink hover:bg-surface-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </a>
            </div>
        </form>
    </section>

    {{-- Tabla de clientes --}}
    <section class="bg-surface border border-line rounded-xl shadow-soft overflow-hidden">

        <div class="flex flex-col sm:flex-row sm:items-center
                    sm:justify-between gap-2 px-5 py-4
                    border-b border-line">

            <div>
                <h2 class="font-semibold text-ink">
                    Directorio de clientes
                </h2>

                <p class="text-xs text-muted mt-1">
                    Información general y clasificación comercial.
                </p>
            </div>

            <span class="text-xs text-muted">
                {{ $clientes->total() }} registros
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[950px] w-full text-sm text-left">

                <thead class="bg-surface-2 text-xs uppercase tracking-wider text-muted">
                    <tr>
                        <th class="px-4 py-4 font-semibold">Cliente</th>
                        <th class="px-4 py-4 font-semibold">Teléfono</th>
                        <th class="px-4 py-4 font-semibold">Correo</th>
                        <th class="px-4 py-4 font-semibold">Tipo</th>
                        <th class="px-4 py-4 font-semibold">Clasificación</th>
                        <th class="px-4 py-4 font-semibold">Estado</th>
                        <th class="px-4 py-4 text-right font-semibold">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-line">

                    @forelse ($clientes as $c)
                    <tr class="hover:bg-surface-2 transition-colors">

                        {{-- Nombre --}}
                        <td class="px-4 py-3">
                            <div class="font-semibold text-ink">
                                {{ $c->nombre }}
                            </div>
                        </td>

                        {{-- Teléfono --}}
                        <td class="px-4 py-3 text-muted">
                            {{ $c->telefono ?: '—' }}
                        </td>

                        {{-- Correo --}}
                        <td class="px-4 py-3 text-muted">
                            {{ $c->correo ?? '—' }}
                        </td>

                        {{-- Tipo --}}
                        <td class="px-4 py-3">
                            <span class="text-muted">
                                {{ $c->tipo_cliente ?? '—' }}
                            </span>
                        </td>

                        {{-- Clasificación --}}
                        <td class="px-4 py-3">
                            @if ($c->es_mayorista)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                         text-xs font-semibold
                                         border border-brand-blue/20 bg-brand-blue/10 text-brand-blue">
                                Mayorista
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                         text-xs font-semibold
                                         border border-line bg-surface-2 text-muted">
                                Minorista
                            </span>
                            @endif
                        </td>

                        {{-- Estado --}}
                        <td class="px-4 py-3">
                            @if ($c->estado === 'ACTIVO')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1
                                         rounded-full text-xs font-semibold
                                         border border-brand-green/20 bg-brand-green/10 text-brand-green">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-green"></span>
                                Activo
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1
                                         rounded-full text-xs font-semibold
                                         border border-red-500/30 bg-red-500/10 text-red-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                Inactivo
                            </span>
                            @endif
                        </td>

                        {{-- Acciones --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('clientes.show', $c) }}"
                                    class="text-sm font-semibold
                                          text-brand-blue hover:text-brand-blue-dark
                                          transition">
                                    Ver
                                </a>

                                @if ($puedeGestionarClientes)
                                <a href="{{ route('clientes.edit', $c) }}"
                                    class="text-sm font-semibold
                                          text-amber-600 hover:text-amber-500 dark:text-amber-400 transition">
                                    Editar
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="max-w-md mx-auto">

                                <div class="w-14 h-14 mx-auto mb-4 rounded-2xl
                                                bg-surface-2 text-muted flex items-center justify-center">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="1.6"
                                            d="M17 20h5v-2a4 4 0 0 0-5.5-3.7M9 20H2v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2H9Zm0-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
                                    </svg>
                                </div>

                                <h3 class="font-semibold text-ink">
                                    No se encontraron clientes
                                </h3>

                                <p class="text-sm text-muted mt-1">
                                    No hay clientes que coincidan con los filtros seleccionados.
                                </p>

                                <div class="flex flex-wrap justify-center items-center gap-3 mt-5">
                                    <a href="{{ route('clientes.index') }}"
                                        class="text-sm font-semibold
                                                  text-brand-blue hover:text-brand-blue-dark">
                                        Limpiar filtros
                                    </a>

                                    @if ($puedeGestionarClientes)
                                    <span class="text-muted">|</span>

                                    <a href="{{ route('clientes.create') }}"
                                        class="text-sm font-semibold
                                                  text-brand-blue hover:text-brand-blue-dark">
                                        Registrar cliente
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if ($clientes->hasPages())
        <div class="px-5 py-4 border-t border-line">
            {{ $clientes->withQueryString()->links() }}
        </div>
        @endif

    </section>
</div>
@endsection
