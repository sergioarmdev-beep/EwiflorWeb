@extends('layouts.app')

@section('title', 'Detalle de sucursal')

@section('content')
@php
$puedeGestionar = $esGestor;
$usuariosActivos = $usuarios->where('estado', 'ACTIVO');
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1 flex-wrap">
                <span class="w-10 h-10 flex items-center justify-center rounded-xl
                             bg-sky-100 text-sky-700
                             dark:bg-sky-500/10 dark:text-sky-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z" />
                    </svg>
                </span>

                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                    {{ $sucursal->nombre }}
                </h1>

                @if ($sucursal->estado === 'ACTIVO')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                     bg-emerald-100 text-emerald-800 border border-emerald-200
                                     dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Activa
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                     bg-red-100 text-red-800 border border-red-200
                                     dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    Inactiva
                </span>
                @endif
            </div>

            <p class="text-sm text-slate-600 dark:text-slate-400">
                {{ \Illuminate\Support\Str::of($sucursal->departamento)->replace('_', ' ')->title() }}
                @if ($sucursal->direccion)
                    · {{ $sucursal->direccion }}
                @endif
                @if ($sucursal->telefono)
                    · Tel. {{ $sucursal->telefono }}
                @endif
            </p>
        </div>

        @if ($puedeGestionar)
        <a href="{{ route('sucursales.edit', $sucursal) }}"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5
                      rounded-lg bg-amber-600 hover:bg-amber-700
                      text-white text-sm font-semibold shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m16.9 3.1 4 4L8 20l-5 1 1-5L16.9 3.1Z" />
            </svg>
            Editar sucursal
        </a>
        @endif
    </div>

    {{-- Mapa de la sucursal --}}
    @include('sucursales::partials.mapa-sucursales', ['sucursales' => collect([$sucursal]), 'altura' => 'h-72'])

    {{-- Almacenes --}}
    <section class="bg-white dark:bg-slate-900
                    border border-slate-200 dark:border-slate-800
                    rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800
                    flex items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-slate-900 dark:text-slate-100">Almacenes</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    {{ $sucursal->almacenes->count() }} almacén(es) en esta sucursal.
                </p>
            </div>

            @if ($puedeGestionar && $sucursal->estado === 'ACTIVO')
            <a href="{{ route('sucursales.almacenes.create', $sucursal) }}"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg
                          bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold transition">
                + Agregar almacén
            </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[640px] w-full text-sm text-left">
                <thead class="bg-slate-100 dark:bg-slate-800/80
                              text-xs uppercase tracking-wider
                              text-slate-600 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Almacén</th>
                        <th class="px-4 py-3 font-semibold">Tamaño</th>
                        <th class="px-4 py-3 font-semibold">Estado</th>
                        @if ($puedeGestionar)
                        <th class="px-4 py-3 text-right font-semibold">Acciones</th>
                        @endif
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($sucursal->almacenes as $a)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">
                            {{ $a->nombre }}
                        </td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                            {{ $a->tamano === 'GRANDE' ? 'Grande' : 'Pequeño' }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($a->estado === 'ACTIVO')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                                 bg-emerald-100 text-emerald-800 border border-emerald-200
                                                 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Activo
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                                 bg-red-100 text-red-800 border border-red-200
                                                 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                Inactivo
                            </span>
                            @endif
                        </td>

                        @if ($puedeGestionar)
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('sucursales.almacenes.edit', $a) }}"
                                    class="text-sm font-semibold text-amber-700 hover:text-amber-900
                                              dark:text-amber-400 dark:hover:text-amber-300 transition">
                                    Editar
                                </a>

                                <form method="POST" action="{{ route('sucursales.almacenes.estado', $a) }}"
                                    onsubmit="return confirm('@unless($a->estado === 'ACTIVO')Reactivar@else Desactivar @endunless el almacén {{ $a->nombre }}?')">
                                    @csrf
                                    <button type="submit"
                                        class="text-sm font-semibold
                                               {{ $a->estado === 'ACTIVO'
                                                  ? 'text-red-700 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300'
                                                  : 'text-emerald-700 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300' }}">
                                        {{ $a->estado === 'ACTIVO' ? 'Desactivar' : 'Reactivar' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $puedeGestionar ? 4 : 3 }}" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                            Sin almacenes registrados en esta sucursal.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Usuarios asignados --}}
    <section class="bg-white dark:bg-slate-900
                    border border-slate-200 dark:border-slate-800
                    rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800">
            <h2 class="font-semibold text-slate-900 dark:text-slate-100">Personal asignado</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ $usuariosActivos->count() }} usuario(s) activo(s) de {{ $usuarios->count() }} asignado(s).
                La asignación se gestiona desde la sección Usuarios.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[560px] w-full text-sm text-left">
                <thead class="bg-slate-100 dark:bg-slate-800/80
                              text-xs uppercase tracking-wider
                              text-slate-600 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Usuario</th>
                        <th class="px-4 py-3 font-semibold">Usuario de acceso</th>
                        <th class="px-4 py-3 font-semibold">Rol</th>
                        <th class="px-4 py-3 font-semibold">Estado</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($usuarios as $u)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">
                            {{ $u->nombre_completo }}
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                            {{ $u->nombre_usuario }}
                        </td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                            {{ $u->nombre_rol }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($u->estado === 'ACTIVO')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                                 bg-emerald-100 text-emerald-800 border border-emerald-200
                                                 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Activo
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                                 bg-red-100 text-red-800 border border-red-200
                                                 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                Inactivo
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                            Ningún usuario está asignado a esta sucursal.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div>
        <a href="{{ route('sucursales.index') }}"
            class="text-sm font-semibold text-sky-700 hover:text-sky-900
                      dark:text-sky-400 dark:hover:text-sky-300 transition">
            ← Volver a sucursales
        </a>
    </div>
</div>
@endsection
