@extends('layouts.app')

@section('title', 'Notificaciones')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 text-ink">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">Notificaciones</h1>
            <p class="text-xs text-muted mt-0.5">Avisos del sistema: solicitudes de restablecimiento y bloqueos de cuenta.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('usuarios.index') }}"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-surface-2 border border-line text-ink text-sm font-semibold rounded-xl hover:bg-surface-2 transition-colors cursor-pointer">
                <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 8v6m3-3h-6" />
                </svg>
                <span>Usuarios</span>
            </a>
            <form method="POST" action="{{ route('notificaciones.leer-todas') }}"
                class="inline-block">
                @csrf
                <button type="submit"
                    class="px-4 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl shadow-sm hover:bg-brand-green-dark transition-all duration-150 active:scale-[0.99] cursor-pointer"
                    {{ $noLeidas === 0 ? 'disabled opacity-50' : '' }}>
                    Marcar todas como leídas
                </button>
            </form>
        </div>
    </div>

    @if ($noLeidas > 0)
    <div class="mb-6 p-3 rounded-xl border border-amber-500/30 bg-amber-500/10 text-sm">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-amber-500">Tienes {{ $noLeidas }} notificación{{ $noLeidas > 1 ? 'es' : '' }} sin leer</span>
                <p class="mt-0.5 text-xs text-amber-400/90">Haz clic en una para ver su detalle o marca todas como leídas.</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Lista de notificaciones --}}
    <div class="bg-surface rounded-2xl border border-line shadow-soft overflow-hidden">
        <table class="min-w-full divide-y divide-line text-sm text-left">
            <thead class="bg-surface-2 text-muted text-xs uppercase tracking-wider">
                <tr>
                    <th scope="col" class="px-5 py-3.5 font-semibold">Tipo</th>
                    <th scope="col" class="px-5 py-3.5 font-semibold">Mensaje</th>
                    <th scope="col" class="px-5 py-3.5 font-semibold">Origen</th>
                    <th scope="col" class="px-5 py-3.5 font-semibold text-center">Fecha</th>
                    <th scope="col" class="px-5 py-3.5 font-semibold text-right">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line text-muted">
                @forelse ($notificaciones as $n)
                @php
                    $tipoSolicitud = \Modules\Auth\Models\Notificacion::TIPO_SOLICITUD_CONTRASENA;
                    $url = $n->url;
                @endphp
                <tr class="hover:bg-surface-2 transition-colors {{ $n->marcadaLeida() ? 'opacity-60' : '' }}"
                    @if ($url) onclick="window.location.href='{{ $url }}'" @endif
                    style="cursor: {{ $url ? 'pointer' : 'default' }};">
                    <td class="px-5 py-4 font-medium text-ink">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-semibold uppercase tracking-wider border
                            {{ $n->tipo === $tipoSolicitud ? 'border-brand-blue/30 bg-brand-blue/10 text-brand-blue' : 'border-red-500/30 bg-red-500/10 text-red-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full
                                {{ $n->tipo === $tipoSolicitud ? 'bg-brand-blue' : 'bg-red-500' }}"></span>
                            {{ $n->tipo === $tipoSolicitud ? 'Clave olvidada' : 'Bloqueo de cuenta' }}
                        </span>
                    </td>
                    <td class="px-5 py-4">{{ $n->titulo }}</td>
                    <td class="px-5 py-4">
                        {{ $n->origen?->nombre_completo ?? 'Sistema' }}
                        <br>
                        <span class="text-[10px] text-muted">{{ $n->origen?->nombre_rol ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-4">{{ $n->fecha_creacion?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td class="px-5 py-4 text-right">
                        @if ($n->marcadaLeida())
                        <span class="text-[10px] text-muted">Leída</span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold border border-amber-500/30 bg-amber-500/20 text-amber-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Sin leer
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center text-muted">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <svg class="w-8 h-8 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs font-medium text-muted">No tienes notificaciones.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="mt-6">
        {{ $notificaciones->links() }}
    </div>
</div>
@endsection
