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
                             border border-brand-blue/20 bg-brand-blue/10 text-brand-blue">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z" />
                    </svg>
                </span>

                <h1 class="text-2xl font-bold text-ink">
                    {{ $sucursal->nombre }}
                </h1>

                @if ($sucursal->estado === 'ACTIVO')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                     border border-brand-green/20 bg-brand-green/10 text-brand-green">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-green"></span>
                    Activa
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                     border border-red-500/30 bg-red-500/10 text-red-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    Inactiva
                </span>
                @endif
            </div>

            <p class="text-sm text-muted">
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
                      rounded-lg bg-brand-blue text-white text-sm font-semibold shadow-sm hover:bg-brand-blue-dark transition">
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
    <section class="bg-surface border border-line rounded-xl shadow-soft overflow-hidden">
        <div class="px-5 py-4 border-b border-line
                    flex items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-ink">Almacenes</h2>
                <p class="text-xs text-muted mt-1">
                    {{ $sucursal->almacenes->count() }} almacén(es) en esta sucursal.
                </p>
            </div>

            @if ($puedeGestionar && $sucursal->estado === 'ACTIVO')
            <a href="{{ route('sucursales.almacenes.create', $sucursal) }}"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl
                          bg-brand-blue text-white text-xs font-semibold transition">
                + Agregar almacén
            </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[640px] w-full text-sm text-left">
                <thead class="bg-surface-2 text-xs uppercase tracking-wider text-muted">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-semibold">Almacén</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Tamaño</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Estado</th>
                        @if ($puedeGestionar)
                        <th scope="col" class="px-4 py-3 text-right font-semibold">Acciones</th>
                        @endif
                    </tr>
                </thead>

                <tbody class="divide-y divide-line">
                    @forelse ($sucursal->almacenes as $a)
                    <tr class="hover:bg-surface-2 transition-colors">
                        <td class="px-4 py-3 font-medium text-ink">
                            {{ $a->nombre }}
                        </td>
                        <td class="px-4 py-3 text-muted">
                            {{ $a->tamano === 'GRANDE' ? 'Grande' : 'Pequeño' }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($a->estado === 'ACTIVO')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                                     border border-brand-green/20 bg-brand-green/10 text-brand-green">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-green"></span>
                                Activo
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                                     border border-red-500/30 bg-red-500/10 text-red-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                Inactivo
                            </span>
                            @endif
                        </td>

                        @if ($puedeGestionar)
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('sucursales.almacenes.edit', $a) }}"
                                    class="text-sm font-semibold text-amber-600 hover:text-amber-500 dark:text-amber-400 transition">
                                    Editar
                                </a>

                                <form method="POST" action="{{ route('sucursales.almacenes.estado', $a) }}"
                                    onsubmit="return confirm('@unless($a->estado === 'ACTIVO')Reactivar@else Desactivar @endunless el almacén {{ $a->nombre }}?')">
                                    @csrf
                                    <button type="submit"
                                        class="text-sm font-semibold
                                               {{ $a->estado === 'ACTIVO'
                                                  ? 'text-red-500 hover:text-red-400'
                                                  : 'text-brand-green hover:text-brand-green-dark' }}">
                                        {{ $a->estado === 'ACTIVO' ? 'Desactivar' : 'Reactivar' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $puedeGestionar ? 4 : 3 }}" class="px-6 py-10 text-center text-sm text-muted">
                            Sin almacenes registrados en esta sucursal.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Usuarios asignados --}}
    <section class="bg-surface border border-line rounded-xl shadow-soft overflow-hidden">
        <div class="px-5 py-4 border-b border-line">
            <h2 class="font-semibold text-ink">Personal asignado</h2>
            <p class="text-xs text-muted mt-1">
                {{ $usuariosActivos->count() }} usuario(s) activo(s) de {{ $usuarios->count() }} asignado(s).
                La asignación se gestiona desde la sección Usuarios.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[560px] w-full text-sm text-left">
                <thead class="bg-surface-2 text-xs uppercase tracking-wider text-muted">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-semibold">Usuario</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Usuario de acceso</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Rol</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Estado</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-line">
                    @forelse ($usuarios as $u)
                    <tr class="hover:bg-surface-2 transition-colors">
                        <td class="px-4 py-3 font-medium text-ink">
                            {{ $u->nombre_completo }}
                        </td>
                        <td class="px-4 py-3 text-muted">
                            {{ $u->nombre_usuario }}
                        </td>
                        <td class="px-4 py-3 text-muted">
                            {{ $u->nombre_rol }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($u->estado === 'ACTIVO')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                                     border border-brand-green/20 bg-brand-green/10 text-brand-green">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-green"></span>
                                Activo
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                                     border border-red-500/30 bg-red-500/10 text-red-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                Inactivo
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-sm text-muted">
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
            class="text-sm font-semibold text-brand-blue hover:text-brand-blue-dark transition">
            ← Volver a sucursales
        </a>
    </div>
</div>
@endsection
