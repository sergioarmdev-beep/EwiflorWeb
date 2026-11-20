@extends('layouts.app')

@php
use Modules\Auth\Models\SolicitudRestablecimiento;
$nombresEstado = [
    SolicitudRestablecimiento::PENDIENTE  => 'Pendientes',
    SolicitudRestablecimiento::ATENDIDA   => 'Atendidas',
    SolicitudRestablecimiento::RECHAZADA  => 'Descartadas',
];
$colores = [
    SolicitudRestablecimiento::PENDIENTE  => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
    SolicitudRestablecimiento::ATENDIDA   => 'bg-brand-green/10 text-brand-green border-brand-green/20',
    SolicitudRestablecimiento::RECHAZADA  => 'bg-red-500/10 text-red-500 border-red-500/20',
];
$tarjeta = 'p-4 rounded-2xl border border-line bg-surface shadow-soft hover:bg-surface-2 transition-colors';
$total = 0;
foreach ($conteos as $estado => $c) {
    $total += (int) $c;
}
@endphp

@section('title', 'Claves olvidadas')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-ink">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">Solicitudes de restablecimiento</h1>
            <p class="text-xs text-muted mt-0.5">Usuarios que olvidaron su contraseña y verificaron correo + celular.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('usuarios.index') }}"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-surface-2 border border-line text-ink text-sm font-semibold rounded-xl hover:bg-surface-2 transition-colors cursor-pointer">
                <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 8v6m3-3h-6" />
                </svg>
                <span>Usuarios</span>
            </a>
        </div>
    </div>

    {{-- Contraseña temporal (se muestra una sola vez) --}}
    @include('auth::partials.password-temporal')

    {{-- Tarjetas de conteo por estado --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 mb-6">
        @php
            $estadoPendiente = \Modules\Auth\Models\SolicitudRestablecimiento::PENDIENTE;
            $estadoAtendida = \Modules\Auth\Models\SolicitudRestablecimiento::ATENDIDA;
            $svgPendiente = 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z';
            $svgAtendida = 'M5 13l4 4L19 7';
            $svgRechazada = 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z';
        @endphp
        @foreach ($nombresEstado as $estado => $nombre)
        <a href="{{ route('usuarios.solicitudes-contrasena.index', ['estado' => $estado]) }}"
            class="{{ $tarjeta }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">{{ $nombre }}</span>
                <span class="w-8 h-8 rounded-lg {{ $colores[$estado] }} flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="{{ $estado === $estadoPendiente ? $svgPendiente : ($estado === $estadoAtendida ? $svgAtendida : $svgRechazada) }}" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold tracking-tight text-ink">{{ (int) ($conteos[$estado] ?? 0) }}</p>
            <p class="mt-1 text-[11px] text-muted">{{ $estado === $estadoPendiente ? 'Esperan tu atención' : 'Procesadas' }}</p>
        </a>
        @endforeach
        <a href="{{ route('usuarios.solicitudes-contrasena.index', ['estado' => 'TODAS']) }}"
            class="{{ $tarjeta }} col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">Total solicitudes</span>
                <span class="w-8 h-8 rounded-lg bg-brand-blue/20 text-brand-blue flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold tracking-tight text-ink">{{ $total }}</p>
            <p class="mt-1 text-[11px] text-muted">Todas las solicitudes</p>
        </a>
    </div>

    {{-- Filtro de estado --}}
    <form method="GET" action="{{ route('usuarios.solicitudes-contrasena.index') }}"
        class="mb-6 p-4 rounded-2xl border border-line bg-surface-2/60 shadow-soft flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5">
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-muted">Estado:</span>
            @foreach (array_merge($nombresEstado, ['TODAS' => 'Todas']) as $valor => $etiqueta)
            <a href="{{ route('usuarios.solicitudes-contrasena.index', ['estado' => $valor]) }}"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $estado === $valor ? 'bg-brand-green/15 text-brand-green border border-brand-green/25' : 'bg-surface-2 text-muted border border-line hover:bg-surface-2 hover:text-ink' }} border">
                {{ $etiqueta }}
            </a>
            @endforeach
        </div>
        <div class="flex items-end gap-2">
            <button type="submit"
                class="flex-1 sm:flex-initial py-2 px-4 bg-brand-green text-white text-xs font-bold rounded-xl border border-brand-green/50 hover:bg-brand-green-dark transition-colors cursor-pointer flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>Filtrar</span>
            </button>
        </div>
    </form>

    {{-- Tabla de solicitudes --}}
    <div class="bg-surface rounded-2xl border border-line shadow-soft overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line text-sm text-left">
                <thead class="bg-surface-2 text-muted text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Usuario</th>
                        <th class="px-5 py-3.5 font-semibold">Correo</th>
                        <th class="px-5 py-3.5 font-semibold">Celular</th>
                        <th class="px-5 py-3.5 font-semibold">Sucursal</th>
                        <th class="px-5 py-3.5 font-semibold text-center">Estado</th>
                        <th class="px-5 py-3.5 font-semibold">Creada</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-muted">
                    @forelse ($solicitudes as $s)
                    <tr class="hover:bg-surface-2 transition-colors">
                        <td class="px-5 py-4 font-medium text-ink">
                            {{ $s->usuario?->nombre_completo ?? '—' }}
                            <br>
                            <span class="text-[10px] text-muted">{{ $s->usuario?->nombre_usuario ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-4 text-muted">{{ $s->correo_solicitado }}</td>
                        <td class="px-5 py-4 text-muted">{{ $s->celular_solicitado }}</td>
                        <td class="px-5 py-4 text-muted">{{ $s->usuario?->sucursal?->nombre ?? '—' }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium border
                                {{ $s->estado === $estadoPendiente ? 'border-amber-500/30 bg-amber-500/20 text-amber-500' : ($s->estado === $estadoAtendida ? 'border-brand-green/20 bg-brand-green/10 text-brand-green' : 'border-red-500/30 bg-red-500/10 text-red-500') }}">
                                {{ $nombresEstado[$s->estado] ?? $s->estado }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-muted">{{ $s->fecha_creacion?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-5 py-4 text-right space-x-3 whitespace-nowrap">
                            @if ($s->estaPendiente())
                            <form action="{{ route('usuarios.solicitudes-contrasena.atender', $s) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-brand-green hover:text-brand-green-dark transition-colors cursor-pointer"
                                    title="Generar clave temporal para este usuario">
                                    Atender
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('usuarios.solicitudes-contrasena.descartar', $s) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-red-500 hover:text-red-400 transition-colors cursor-pointer"
                                    title="Descartar sin restablecer">
                                    Descartar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-muted">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span class="text-xs font-medium text-muted">No hay solicitudes que coincidan con el filtro.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginación --}}
    <div class="mt-6">
        {{ $solicitudes->links() }}
    </div>
</div>
@endsection
