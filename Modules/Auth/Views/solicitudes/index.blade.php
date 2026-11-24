@extends('layouts.app')

@section('title', 'Solicitudes de cuenta')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-ink">

    {{-- Encabezado de la Sección --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">Solicitudes de cuenta</h1>
            <p class="text-xs text-muted mt-0.5">Peticiones enviadas desde la pantalla de inicio de sesión</p>
        </div>
        <a href="{{ route('usuarios.index') }}"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-surface-2 border border-line text-ink hover:bg-surface-2 hover:text-brand-green text-sm font-semibold transition-colors cursor-pointer">
            <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Volver a usuarios</span>
        </a>
    </div>

    {{-- Filtros por estado --}}
    @php
        $tabs = [
            'PENDIENTE' => ['Pendientes', $conteos['PENDIENTE'] ?? 0],
            'APROBADA'  => ['Aprobadas',  $conteos['APROBADA'] ?? 0],
            'RECHAZADA' => ['Rechazadas', $conteos['RECHAZADA'] ?? 0],
            'TODAS'     => ['Todas',      collect($conteos)->sum()],
        ];
    @endphp
    <div class="mb-6 flex flex-wrap gap-2">
        @foreach ($tabs as $clave => [$titulo, $total])
        <a href="{{ route('usuarios.solicitudes.index', ['estado' => $clave]) }}"
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold border transition-colors
                {{ $estado === $clave ? 'bg-brand-green text-white border-brand-green shadow-sm' : 'border-line bg-surface-2 text-muted hover:bg-surface-2' }}">
            <span>{{ $titulo }}</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] leading-none {{ $estado === $clave ? 'bg-white text-ink' : 'bg-surface-2 text-muted' }}">{{ $total }}</span>
        </a>
        @endforeach
    </div>

    {{-- Tabla --}}
    <div class="bg-surface border border-line rounded-2xl shadow-soft overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line text-sm text-left">
                <thead class="bg-surface-2 text-muted text-xs uppercase tracking-wider">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Fecha</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Solicitante</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Contacto</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Motivo</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Estado</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-muted">
                    @forelse ($solicitudes as $sol)
                    <tr class="hover:bg-surface-2 transition-colors align-top">
                        <td class="px-5 py-4 text-xs text-muted whitespace-nowrap">{{ $sol->fecha_creacion?->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-4 font-medium text-ink">{{ $sol->nombre_completo }}</td>
                        <td class="px-5 py-4 text-xs text-muted">
                            <div>{{ $sol->correo }}</div>
                            <div class="mt-0.5 font-mono">{{ $sol->celular }}</div>
                        </td>
                        <td class="px-5 py-4 text-xs text-ink max-w-xs">
                            <p class="break-words" title="{{ $sol->motivo }}">{{ \Illuminate\Support\Str::limit($sol->motivo, 140) }}</p>
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $badge = match ($sol->estado) {
                                    'APROBADA'  => ['border border-brand-green/20 bg-brand-green/20 text-brand-green', 'bg-brand-green', 'Aprobada'],
                                    'RECHAZADA' => ['border border-red-500/30 bg-red-500/20 text-red-500', 'bg-red-500', 'Rechazada'],
                                    default     => ['border border-amber-500/30 bg-amber-500/20 text-amber-500', 'bg-amber-500', 'Pendiente'],
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $badge[0] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $badge[1] }}"></span>
                                {{ $badge[2] }}
                            </span>
                            @unless ($sol->estaPendiente())
                            <p class="mt-1.5 text-[11px] text-muted">
                                {{ $sol->revisor?->nombre_completo ?? '—' }} · {{ $sol->fecha_revision?->format('d/m/Y') }}
                            </p>
                            @if ($sol->nota_revision)
                            <p class="mt-0.5 text-[11px] text-muted break-words max-w-[14rem]">«{{ $sol->nota_revision }}»</p>
                            @endif
                            @endunless
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            @if ($sol->estaPendiente())
                            <div class="flex flex-col items-end gap-2">
                                <a href="{{ route('usuarios.create', ['solicitud' => $sol->id_solicitud]) }}"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-brand-green hover:text-brand-green-dark transition-colors">
                                    Crear usuario
                                </a>

                                {{-- Rechazo con nota opcional (sin JavaScript) --}}
                                <details class="group text-right">
                                    <summary class="list-none cursor-pointer text-xs font-bold text-red-500 hover:text-red-400 transition-colors select-none">Rechazar</summary>
                                    <form action="{{ route('usuarios.solicitudes.rechazar', $sol) }}" method="POST"
                                        class="mt-2 w-56 p-3 rounded-xl border border-line bg-surface space-y-2 text-left">
                                        @csrf
                                        @method('PATCH')
                                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-muted">Nota (opcional)</label>
                                        <input type="text" name="nota_revision" maxlength="255" placeholder="Motivo del rechazo"
                                            class="w-full px-3 py-2 rounded-lg border border-line bg-surface-2 text-ink placeholder:text-muted text-xs focus:border-brand-green/50 focus:ring-brand-green/20">
                                        <button type="submit"
                                            class="w-full py-2 px-3 bg-red-500 text-white font-medium text-xs rounded-xl border border-red-500/50 hover:bg-red-400 transition-colors cursor-pointer">
                                            Confirmar rechazo
                                        </button>
                                    </form>
                                </details>
                            </div>
                            @elseif ($sol->usuarioCreado)
                            <span class="text-xs text-muted">Usuario: <span class="font-mono text-muted">{{ $sol->usuarioCreado->nombre_completo }}</span></span>
                            @else
                            <span class="text-xs text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-muted">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <span class="text-xs font-medium text-muted">No hay solicitudes en esta categoría.</span>
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
