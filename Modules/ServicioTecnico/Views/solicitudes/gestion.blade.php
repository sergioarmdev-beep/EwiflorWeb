@extends('layouts.app')

@section('title', 'Gestión de servicio técnico')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Gestión de servicio técnico</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Todos los movimientos del módulo: quién aceptó cada encargo, estado y garantía.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-sky-600 dark:text-sky-400 hover:underline">← Volver al panel</a>
    </div>

    <form method="GET" action="{{ route('serviciotecnico.gestion') }}" class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 border border-slate-200 dark:border-slate-700 p-4 rounded-xl bg-white dark:bg-slate-900">
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Estado</label>
            <select name="estado" class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                <option value="">Todos</option>
                <option value="PENDIENTE" {{ $filtros['estado'] ?? '' === 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                <option value="PROGRAMADA" {{ $filtros['estado'] ?? '' === 'PROGRAMADA' ? 'selected' : '' }}>Aceptada</option>
                <option value="CERRADA" {{ $filtros['estado'] ?? '' === 'CERRADA' ? 'selected' : '' }}>Cerrada</option>
                <option value="CANCELADA" {{ $filtros['estado'] ?? '' === 'CANCELADA' ? 'selected' : '' }}>Cancelada</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Técnico</label>
            <select name="tecnico" class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                <option value="">Todos</option>
                @foreach ($tecnicos as $tec)
                <option value="{{ $tec['id_usuario'] ?? '' }}" {{ ($filtros['tecnico'] ?? '') == ($tec['id_usuario'] ?? '') ? 'selected' : '' }}>{{ $tec['nombre_completo'] ?? '' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">En garantía</label>
            <select name="en_garantia" class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                <option value="">Todos</option>
                <option value="1" {{ ($filtros['en_garantia'] ?? '') === '1' ? 'selected' : '' }}>Solo en garantía</option>
                <option value="0" {{ ($filtros['en_garantia'] ?? '') === '0' ? 'selected' : '' }}>Fuera de garantía</option>
            </select>
        </div>
        <div class="lg:col-span-2">
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Desde</label>
            <input type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}"
                class="w-full px-3 py-2 text-sm border rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
        </div>
        <div class="lg:col-span-2 flex items-end">
            <button type="submit" class="w-full px-4 py-2 bg-slate-900 dark:bg-slate-800 text-white rounded hover:bg-slate-800 dark:hover:bg-slate-700 text-sm font-medium">Filtrar</button>
        </div>
    </form>

    @if ($solicitudes->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900 rounded-xl overflow-hidden shadow">
            <thead class="bg-slate-50 dark:bg-slate-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Solicitud</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Registró</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">Técnico</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Falla</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">Estado</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">Garantía</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300">Costo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Resultado</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">Fecha</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @foreach ($solicitudes as $sol)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    <td class="px-4 py-3 text-sm font-mono text-slate-900 dark:text-slate-100">#{{ $sol->id_solicitud }}</td>
                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">{{ data_get($sol, 'cliente.nombre', '—') }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ data_get($sol, 'registra.nombre_completo', 'Cliente web') }}</td>
                    <td class="px-4 py-3 text-center text-sm text-slate-900 dark:text-slate-100">{{ data_get($sol, 'tecnico.nombre_completo', 'Sin asignar') }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ \Str::limit($sol->descripcion_falla, 30) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold
                            @if($sol->estado === 'PENDIENTE') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 @endif
                            @if($sol->estado === 'PROGRAMADA') bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300 @endif
                            @if($sol->estado === 'CERRADA') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 @endif
                            @if($sol->estado === 'CANCELADA') bg-slate-100 text-slate-700 dark:bg-slate-800/30 dark:text-slate-300 @endif">
                            {{ $sol->estado }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-sm">
                        @if ($sol->id_garantia)
                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Sí</span>
                        @else
                            <span class="text-slate-500">No</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-sm">
                        @if ($sol->costo_servicio_bs)
                            Bs {{ number_format((float) $sol->costo_servicio_bs, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                        {{ $sol->resultado ? \Str::ucfirst(\Str::replace('_', ' ', $sol->resultado)) : '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400 font-mono">{{ \Carbon\Carbon::parse($sol->fecha_solicitud)->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-center">
                        @if (in_array($sol->estado, ['PENDIENTE', 'PROGRAMADA']))
                            <button type="button"
                                class="px-3 py-1.5 bg-red-600 hover:bg-red-500 text-white text-xs rounded font-semibold transition"
                                data-sol-id="{{ $sol->id_solicitud }}"
                                data-sol-numero="#{{ $sol->id_solicitud }}"
                                onclick="abrirModalCancelar(this)">
                                Cancelar
                            </button>
                        @else
                            <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4 flex items-center justify-between">
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Mostrando {{ $solicitudes->firstItem() }} a {{ $solicitudes->lastItem() }} de {{ $solicitudes->total() }}
            </p>
            {{ $solicitudes->links() }}
        </div>
    </div>
    @else
    <div class="text-center py-12">
        <p class="text-sm text-slate-500 dark:text-slate-400">No se encontraron solicitudes con los filtros aplicados.</p>
    </div>
    @endif
</div>

    <!-- Modal: Cancelar solicitud -->
    <div id="modal-cancelar" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm hidden" aria-hidden="true">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl w-full max-w-md mx-4 transform transition scale-95" id="modal-cancelar-panel">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Cancelar solicitud</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Se cancelará la solicitud <span id="modal-sol-numero" class="font-mono font-bold"></span>. Esta acción no se puede deshacer.
                </p>
            </div>
            <form id="form-cancelar" method="POST" action="" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="id_solicitud" id="modal-sol-id">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Motivo de cancelación</label>
                    <textarea name="motivo" id="modal-sol-motivo" rows="4" required
                        class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                        placeholder="Describe por qué se cancela esta solicitud..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="cerrarModalCancelar()" class="px-4 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded font-medium transition">Cerrar</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded font-medium transition">Confirmar cancelación</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModalCancelar(btn) {
            const id = btn.getAttribute('data-sol-id');
            const numero = btn.getAttribute('data-sol-numero');
            document.getElementById('modal-sol-id').value = id;
            document.getElementById('modal-sol-numero').textContent = numero;
            document.getElementById('form-cancelar').action = "{{ route('serviciotecnico.cancelar', '__ID__') }}".replace('__ID__', id);
            document.getElementById('modal-cancelar').classList.remove('hidden');
            document.getElementById('modal-cancelar-panel').classList.remove('scale-95');
            document.getElementById('modal-cancelar-panel').classList.add('scale-100');
        }

        function cerrarModalCancelar() {
            document.getElementById('modal-cancelar-panel').classList.remove('scale-100');
            document.getElementById('modal-cancelar-panel').classList.add('scale-95');
            setTimeout(() => {
                document.getElementById('modal-cancelar').classList.add('hidden');
            }, 200);
        }

        // Cerrar al hacer clic fuera del panel
        document.getElementById('modal-cancelar').addEventListener('click', function (e) {
            if (e.target === this) {
                cerrarModalCancelar();
            }
        });
    </script>
@endsection
