@extends('layouts.app')

@section('title', 'Bandeja de servicio técnico')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Bandeja de servicio técnico</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Encargos pendientes y asignados a ti</p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-sky-600 dark:text-sky-400 hover:underline">← Volver al panel</a>
    </div>

    @if (session('success'))
    <div class="mb-4 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:text-emerald-200">
        {{ session('success') }}
    </div>
    @endif

    @if (count($solicitudes ?? []) > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900 rounded-xl overflow-hidden shadow">
            <thead class="bg-slate-50 dark:bg-slate-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Solicitud</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Falla</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">Estado</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">Fecha</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @foreach ($solicitudes as $sol)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    <td class="px-4 py-3 text-sm font-mono text-slate-900 dark:text-slate-100">#{{ $sol->id_solicitud }}</td>
                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">{{ $sol->cliente?->nombre ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ \Str::limit($sol->descripcion_falla, 40) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold
                            @if($sol->estado === 'PENDIENTE') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 @endif
                            @if($sol->estado === 'PROGRAMADA') bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300 @endif">
                            {{ $sol->estado }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400 font-mono">{{ \Carbon\Carbon::parse($sol->fecha_solicitud)->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-right">
                        @if ($sol->estado === 'PENDIENTE' && is_null($sol->id_tecnico))
                            <form method="POST" action="{{ route('serviciotecnico.aceptar', $sol) }}" style="display:inline;">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-1.5 bg-sky-600 hover:bg-sky-500 text-white text-xs rounded font-semibold transition">
                                    Aceptar encargo
                                </button>
                            </form>
                        @elseif ($sol->id_tecnico === auth()->id())
                            <span class="text-xs text-slate-500">Tu encargo</span>
                        @else
                            <span class="text-xs text-slate-400">Asignado</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-12">
        <p class="text-sm text-slate-500 dark:text-slate-400">No tienes encargos pendientes ni asignados.</p>
    </div>
    @endif
</div>
@endsection
