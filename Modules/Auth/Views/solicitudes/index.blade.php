@extends('layouts.app')

@section('title', 'Solicitudes de cuenta')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 text-slate-100">

    <!-- Encabezado de la Sección -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-100">Solicitudes de cuenta</h1>
            <p class="text-xs text-slate-400 mt-0.5">Peticiones enviadas desde la pantalla de inicio de sesión</p>
        </div>
        <a href="{{ route('usuarios.index') }}"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold rounded-xl border border-slate-700/80 transition-colors cursor-pointer">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Volver a usuarios</span>
        </a>
    </div>

    <!-- Filtros por estado -->
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
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold border transition-colors
                {{ $estado === $clave ? 'bg-blue-600 border-blue-400 text-white shadow-md' : 'bg-slate-900 border-slate-800/80 text-slate-300 hover:bg-slate-800' }}">
            <span>{{ $titulo }}</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] leading-none {{ $estado === $clave ? 'bg-white/20' : 'bg-slate-800 text-slate-400' }}">{{ $total }}</span>
        </a>
        @endforeach
    </div>

    <!-- Tabla -->
    <div class="bg-slate-900 border border-slate-800/80 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-sm text-left">
                <thead class="bg-slate-950/50 text-slate-400 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Fecha</th>
                        <th class="px-5 py-3.5 font-semibold">Solicitante</th>
                        <th class="px-5 py-3.5 font-semibold">Contacto</th>
                        <th class="px-5 py-3.5 font-semibold">Motivo</th>
                        <th class="px-5 py-3.5 font-semibold">Estado</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse ($solicitudes as $sol)
                    <tr class="hover:bg-slate-800/40 transition-colors align-top">
                        <td class="px-5 py-4 text-xs text-slate-400 whitespace-nowrap">{{ $sol->fecha_creacion?->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-4 font-medium text-slate-100">{{ $sol->nombre_completo }}</td>
                        <td class="px-5 py-4 text-xs text-slate-400">
                            <div>{{ $sol->correo }}</div>
                            <div class="mt-0.5 font-mono">{{ $sol->celular }}</div>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-300 max-w-xs">
                            <p class="break-words" title="{{ $sol->motivo }}">{{ \Illuminate\Support\Str::limit($sol->motivo, 140) }}</p>
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $badge = match ($sol->estado) {
                                    'APROBADA'  => ['bg-emerald-950/60 text-emerald-300 border border-emerald-500/30', 'bg-emerald-400', 'Aprobada'],
                                    'RECHAZADA' => ['bg-red-950/60 text-red-300 border border-red-500/30', 'bg-red-400', 'Rechazada'],
                                    default     => ['bg-amber-950/60 text-amber-300 border border-amber-500/30', 'bg-amber-400', 'Pendiente'],
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $badge[0] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $badge[1] }}"></span>
                                {{ $badge[2] }}
                            </span>
                            @unless ($sol->estaPendiente())
                            <p class="mt-1.5 text-[11px] text-slate-500">
                                {{ $sol->revisor?->nombre_completo ?? '—' }} · {{ $sol->fecha_revision?->format('d/m/Y') }}
                            </p>
                            @if ($sol->nota_revision)
                            <p class="mt-0.5 text-[11px] text-slate-400 break-words max-w-[14rem]">«{{ $sol->nota_revision }}»</p>
                            @endif
                            @endunless
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            @if ($sol->estaPendiente())
                            <div class="flex flex-col items-end gap-2">
                                <a href="{{ route('usuarios.create', ['solicitud' => $sol->id_solicitud]) }}"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-400 hover:text-emerald-300 transition-colors">
                                    Crear usuario
                                </a>

                                <!-- Rechazo con nota opcional (sin JavaScript) -->
                                <details class="group text-right">
                                    <summary class="list-none cursor-pointer text-xs font-semibold text-red-400 hover:text-red-300 transition-colors select-none">Rechazar</summary>
                                    <form action="{{ route('usuarios.solicitudes.rechazar', $sol) }}" method="POST"
                                        class="mt-2 w-56 p-3 bg-slate-950/60 border border-slate-800 rounded-xl space-y-2 text-left">
                                        @csrf
                                        @method('PATCH')
                                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400">Nota (opcional)</label>
                                        <input type="text" name="nota_revision" maxlength="255" placeholder="Motivo del rechazo"
                                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700/60 rounded-lg text-slate-100 placeholder-slate-600 text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        <button type="submit"
                                            class="w-full py-2 px-3 bg-red-950/60 hover:bg-red-900/60 text-red-200 font-medium text-xs rounded-lg border border-red-500/30 transition-colors cursor-pointer">
                                            Confirmar rechazo
                                        </button>
                                    </form>
                                </details>
                            </div>
                            @elseif ($sol->usuarioCreado)
                            <span class="text-xs text-slate-500">Usuario: <span class="font-mono text-slate-400">{{ $sol->usuarioCreado->nombre_completo }}</span></span>
                            @else
                            <span class="text-xs text-slate-600">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <span class="text-xs font-medium text-slate-400">No hay solicitudes en esta categoría.</span>
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
        {{ $solicitudes->links() }}
    </div>
</div>
@endsection
