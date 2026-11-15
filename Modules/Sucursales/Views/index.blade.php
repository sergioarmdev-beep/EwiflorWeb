@extends('layouts.app')

@section('title', 'Sucursales')

@section('content')
@php
$puedeGestionar = $esGestor;
@endphp

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 flex items-center justify-center rounded-xl
                             bg-sky-100 text-sky-700
                             dark:bg-sky-500/10 dark:text-sky-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z" />
                    </svg>
                </span>

                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                    Sucursales
                </h1>
            </div>

            <p class="text-sm text-slate-600 dark:text-slate-400">
                @if ($puedeGestionar)
                    Gestiona las sucursales, sus almacenes y el personal asignado.
                @else
                    Tu sucursal asignada y sus almacenes.
                @endif
            </p>
        </div>

        @if ($puedeGestionar)
        <a href="{{ route('sucursales.create') }}"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5
                      rounded-lg bg-sky-600 hover:bg-sky-700
                      dark:bg-sky-600 dark:hover:bg-sky-500
                      text-white text-sm font-semibold shadow-sm transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva sucursal
        </a>
        @endif
    </div>

    {{-- Filtros (solo gestores: los demás solo ven su sucursal) --}}
    @if ($puedeGestionar)
    <section class="bg-white dark:bg-slate-900
                    border border-slate-200 dark:border-slate-800
                    rounded-xl p-4 sm:p-5 shadow-sm">
        <form method="GET" action="{{ route('sucursales.index') }}"
            class="grid grid-cols-1 sm:grid-cols-3 gap-3">

            <div>
                <label for="buscar" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                    Buscar sucursal
                </label>
                <input type="text" id="buscar" name="buscar"
                    value="{{ request('buscar') }}"
                    placeholder="Nombre o dirección"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                              bg-white dark:bg-slate-800
                              text-slate-900 dark:text-slate-100
                              placeholder-slate-400 dark:placeholder-slate-500
                              px-3 py-2.5 text-sm outline-none
                              focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
            </div>

            <div>
                <label for="estado" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                    Estado
                </label>
                <select name="estado" id="estado"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700
                               bg-white dark:bg-slate-800
                               text-slate-900 dark:text-slate-100
                               px-3 py-2.5 text-sm outline-none
                               focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
                    <option value="">Todos los estados</option>
                    <option value="ACTIVO" @selected(request('estado') === 'ACTIVO')>Activo</option>
                    <option value="INACTIVO" @selected(request('estado') === 'INACTIVO')>Inactivo</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 px-4 py-2.5 rounded-lg
                               bg-slate-800 hover:bg-slate-700
                               dark:bg-slate-700 dark:hover:bg-slate-600
                               text-white text-sm font-semibold transition">
                    Filtrar
                </button>
                <a href="{{ route('sucursales.index') }}" title="Limpiar filtros"
                    class="inline-flex items-center justify-center w-10 h-[42px] rounded-lg
                          border border-slate-300 dark:border-slate-700
                          text-slate-600 dark:text-slate-400
                          hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </a>
            </div>
        </form>
    </section>
    @endif

    {{-- Listado de sucursales (tarjetas) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        @forelse ($sucursales as $s)
        <section class="bg-white dark:bg-slate-900
                        border border-slate-200 dark:border-slate-800
                        rounded-xl shadow-sm overflow-hidden
                        {{ $s->estado === 'INACTIVO' ? 'opacity-75' : '' }}">

            {{-- Cabecera de la tarjeta --}}
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800
                        flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="font-semibold text-slate-900 dark:text-slate-100 truncate">
                            {{ $s->nombre }}
                        </h2>
                        @if ($s->estado === 'ACTIVO')
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold
                                             bg-emerald-100 text-emerald-800 border border-emerald-200
                                             dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Activa
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold
                                             bg-red-100 text-red-800 border border-red-200
                                             dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                            Inactiva
                        </span>
                        @endif
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        {{ \Illuminate\Support\Str::of($s->departamento)->replace('_', ' ')->title() }}
                        @if ($s->direccion)
                            · {{ $s->direccion }}
                        @endif
                        @if ($s->telefono)
                            · {{ $s->telefono }}
                        @endif
                    </p>
                </div>

                <div class="shrink-0 text-right">
                    <span class="block text-lg font-bold text-slate-800 dark:text-slate-100">
                        {{ $s->usuarios_activos }}
                    </span>
                    <span class="block text-[10px] uppercase tracking-wider text-slate-400">
                        usuario{{ $s->usuarios_activos === 1 ? '' : 's' }} activo{{ $s->usuarios_activos === 1 ? '' : 's' }}
                    </span>
                </div>
            </div>

            {{-- Almacenes --}}
            <div class="px-5 py-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        Almacenes ({{ $s->almacenes->count() }})
                    </h3>

                    @if ($puedeGestionar && $s->estado === 'ACTIVO')
                    <a href="{{ route('sucursales.almacenes.create', $s) }}"
                        class="text-xs font-semibold text-sky-700 hover:text-sky-900
                                  dark:text-sky-400 dark:hover:text-sky-300 transition">
                        + Agregar almacén
                    </a>
                    @endif
                </div>

                @forelse ($s->almacenes as $a)
                <div class="flex items-center justify-between gap-3 py-2
                            {{ !$loop->first ? 'border-t border-slate-100 dark:border-slate-800' : '' }}">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate">
                                {{ $a->nombre }}
                            </span>
                            @if ($a->estado === 'ACTIVO')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                                 bg-emerald-100 text-emerald-800 border border-emerald-200
                                                 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                Activo
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                                 bg-red-100 text-red-800 border border-red-200
                                                 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20">
                                Inactivo
                            </span>
                            @endif
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $a->tamano === 'GRANDE' ? 'Grande' : 'Pequeño' }}
                        </span>
                    </div>

                    @if ($puedeGestionar)
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('sucursales.almacenes.edit', $a) }}"
                            class="text-xs font-semibold text-amber-700 hover:text-amber-900
                                      dark:text-amber-400 dark:hover:text-amber-300 transition">
                            Editar
                        </a>

                        <form method="POST" action="{{ route('sucursales.almacenes.estado', $a) }}"
                            onsubmit="return confirm('@unless($a->estado === 'ACTIVO')Reactivar@else Desactivar @endunless el almacén {{ $a->nombre }}?')">
                            @csrf
                            <button type="submit"
                                class="text-xs font-semibold
                                       {{ $a->estado === 'ACTIVO'
                                          ? 'text-red-700 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300'
                                          : 'text-emerald-700 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300' }}">
                                {{ $a->estado === 'ACTIVO' ? 'Desactivar' : 'Reactivar' }}
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                @empty
                <p class="text-sm text-slate-500 dark:text-slate-400 italic py-2">
                    Sin almacenes registrados.
                </p>
                @endforelse
            </div>

            {{-- Acciones --}}
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800
                        flex items-center justify-between gap-3 bg-slate-50 dark:bg-slate-800/40">
                <a href="{{ route('sucursales.show', $s) }}"
                    class="text-sm font-semibold text-sky-700 hover:text-sky-900
                              dark:text-sky-400 dark:hover:text-sky-300 transition">
                    Ver detalle
                </a>

                @if ($puedeGestionar)
                <div class="flex items-center gap-4">
                    <a href="{{ route('sucursales.edit', $s) }}"
                        class="text-sm font-semibold text-amber-700 hover:text-amber-900
                                  dark:text-amber-400 dark:hover:text-amber-300 transition">
                        Editar
                    </a>

                    <form method="POST" action="{{ route('sucursales.estado', $s) }}"
                        onsubmit="return confirm('@unless($s->estado === 'ACTIVO')Reactivar@else Desactivar @endunless la sucursal {{ $s->nombre }}?')">
                        @csrf
                        <button type="submit"
                            class="text-sm font-semibold
                                   {{ $s->estado === 'ACTIVO'
                                      ? 'text-red-700 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300'
                                      : 'text-emerald-700 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300' }}">
                            {{ $s->estado === 'ACTIVO' ? 'Desactivar' : 'Reactivar' }}
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </section>

        @empty
        <div class="lg:col-span-2 bg-white dark:bg-slate-900
                    border border-slate-200 dark:border-slate-800
                    rounded-xl shadow-sm px-6 py-16 text-center">
            <div class="max-w-md mx-auto">
                <div class="w-14 h-14 mx-auto mb-4 rounded-2xl
                            bg-slate-100 dark:bg-slate-800
                            text-slate-500 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                            d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z" />
                    </svg>
                </div>

                <h3 class="font-semibold text-slate-800 dark:text-slate-200">
                    No tienes sucursal asignada
                </h3>

                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    @if ($puedeGestionar)
                        No hay sucursales que coincidan con los filtros.
                    @else
                        Contacta a un gerente o administrador para que te asigne a una sucursal.
                    @endif
                </p>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
