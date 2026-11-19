@extends('layouts.app')

@section('title', 'Bandeja de servicio técnico')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-ink">Bandeja de servicio técnico</h1>
            <p class="text-sm text-muted">Encargos pendientes y asignados a ti</p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-brand-blue hover:underline">← Volver al panel</a>
    </div>

    @if (session('success'))
    <div class="mb-4 flex items-start gap-3 rounded-xl border border-brand-green/30 bg-brand-green/10 px-4 py-3 text-sm text-brand-green">
        {{ session('success') }}
    </div>
    @endif

    @if (count($solicitudes ?? []) > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-line bg-surface rounded-xl overflow-hidden shadow-soft">
            <thead class="bg-surface-2">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Solicitud</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Falla</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Estado</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Fecha</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted">Acción</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Detalle</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($solicitudes as $sol)
                <tr class="transition-colors hover:bg-surface-2">
                    <td class="px-4 py-3 text-sm font-mono text-ink">#{{ $sol->id_solicitud }}</td>
                    <td class="px-4 py-3 text-sm text-ink">{{ $sol->cliente?->nombre ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-muted">{{ \Str::limit($sol->descripcion_falla, 40) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold
                            @if($sol->estado === 'PENDIENTE') border border-amber-500/30 bg-amber-500/10 text-amber-500 @endif
                            @if($sol->estado === 'PROGRAMADA') border border-brand-blue/20 bg-brand-blue/10 text-brand-blue @endif">
                            {{ $sol->estado }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-muted font-mono">{{ \Carbon\Carbon::parse($sol->fecha_solicitud)->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-right">
                        @if ($sol->estado === 'PENDIENTE' && is_null($sol->id_tecnico))
                            <form method="POST" action="{{ route('serviciotecnico.aceptar', $sol) }}" style="display:inline;">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-1.5 bg-brand-blue text-white text-xs rounded-xl font-semibold hover:bg-brand-blue-dark transition">
                                    Aceptar encargo
                                </button>
                            </form>
                        @elseif ($sol->id_tecnico === auth()->id())
                            <span class="text-xs text-muted">Tu encargo</span>
                        @else
                            <span class="text-xs text-muted">Asignado</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('serviciotecnico.show', $sol) }}"
                            class="text-xs text-brand-blue hover:text-brand-blue-dark font-medium underline">
                            Ver detalle
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-12">
        <p class="text-sm text-muted">No tienes encargos pendientes ni asignados.</p>
    </div>
    @endif
</div>
@endsection
