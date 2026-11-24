@extends('layouts.app')

@section('title', 'Gestión de servicio técnico')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-ink">Gestión de servicio técnico</h1>
            <p class="text-sm text-muted">Todos los movimientos del módulo: quién aceptó cada encargo, estado y garantía.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-brand-blue hover:underline">← Volver al panel</a>
    </div>

    <form method="GET" action="{{ route('serviciotecnico.gestion') }}" class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 border border-line p-4 rounded-xl bg-surface shadow-soft">
        <div>
            <label class="block text-xs font-semibold text-muted mb-1">Estado</label>
            <select name="estado" class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20">
                <option value="">Todos</option>
                <option value="PENDIENTE" {{ $filtros['estado'] ?? '' === 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                <option value="PROGRAMADA" {{ $filtros['estado'] ?? '' === 'PROGRAMADA' ? 'selected' : '' }}>Aceptada</option>
                <option value="CERRADA" {{ $filtros['estado'] ?? '' === 'CERRADA' ? 'selected' : '' }}>Cerrada</option>
                <option value="CANCELADA" {{ $filtros['estado'] ?? '' === 'CANCELADA' ? 'selected' : '' }}>Cancelada</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted mb-1">Técnico</label>
            <select name="tecnico" class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20">
                <option value="">Todos</option>
                @foreach ($tecnicos as $tec)
                <option value="{{ $tec['id_usuario'] ?? '' }}" {{ ($filtros['tecnico'] ?? '') == ($tec['id_usuario'] ?? '') ? 'selected' : '' }}>{{ $tec['nombre_completo'] ?? '' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted mb-1">En garantía</label>
            <select name="en_garantia" class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20">
                <option value="">Todos</option>
                <option value="1" {{ ($filtros['en_garantia'] ?? '') === '1' ? 'selected' : '' }}>Solo en garantía</option>
                <option value="0" {{ ($filtros['en_garantia'] ?? '') === '0' ? 'selected' : '' }}>Fuera de garantía</option>
            </select>
        </div>
        <div class="lg:col-span-2">
            <label class="block text-xs font-semibold text-muted mb-1">Desde</label>
            <input type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}"
                class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20">
        </div>
        <div class="lg:col-span-2 flex items-end">
            <button type="submit" class="w-full px-4 py-2 bg-brand-green text-white rounded-xl hover:bg-brand-green-dark text-sm font-medium">Filtrar</button>
        </div>
    </form>

    @if ($solicitudes->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-line bg-surface rounded-xl overflow-hidden shadow-soft">
            <thead class="bg-surface-2">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Solicitud</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Cliente</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Registró</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Técnico</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Falla</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Estado</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Garantía</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted">Costo</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Resultado</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Fecha</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($solicitudes as $sol)
                <tr class="transition-colors hover:bg-surface-2">
                    <td class="px-4 py-3 text-sm font-mono text-ink">#{{ $sol->id_solicitud }}</td>
                    <td class="px-4 py-3 text-sm text-ink">{{ data_get($sol, 'cliente.nombre', '—') }}</td>
                    <td class="px-4 py-3 text-sm text-muted">{{ data_get($sol, 'registra.nombre_completo', 'Cliente web') }}</td>
                    <td class="px-4 py-3 text-center text-sm text-ink">{{ data_get($sol, 'tecnico.nombre_completo', 'Sin asignar') }}</td>
                    <td class="px-4 py-3 text-sm text-muted">{{ \Str::limit($sol->descripcion_falla, 30) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold
                            @if($sol->estado === 'PENDIENTE') border border-amber-500/30 bg-amber-500/10 text-amber-500 @endif
                            @if($sol->estado === 'PROGRAMADA') border border-brand-blue/20 bg-brand-blue/10 text-brand-blue @endif
                            @if($sol->estado === 'CERRADA') border border-brand-green/20 bg-brand-green/10 text-brand-green @endif
                            @if($sol->estado === 'CANCELADA') border border-line bg-surface-2 text-muted @endif">
                            {{ $sol->estado }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-sm">
                        @if ($sol->id_garantia)
                            <span class="text-brand-green font-semibold">Sí</span>
                        @else
                            <span class="text-muted">No</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-sm">
                        @if ($sol->costo_servicio_bs)
                            Bs {{ number_format((float) $sol->costo_servicio_bs, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-muted">
                        {{ $sol->resultado ? \Str::ucfirst(\Str::replace('_', ' ', $sol->resultado)) : '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-muted font-mono">{{ \Carbon\Carbon::parse($sol->fecha_solicitud)->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-center">
                        @if (in_array($sol->estado, ['PENDIENTE', 'PROGRAMADA']))
                            <button type="button"
                                class="px-3 py-1.5 bg-red-500 text-white text-xs rounded-xl font-semibold hover:bg-red-400 transition"
                                data-sol-id="{{ $sol->id_solicitud }}"
                                data-sol-numero="#{{ $sol->id_solicitud }}"
                                onclick="abrirModalCancelar(this)">
                                Cancelar
                            </button>
                        @else
                            <span class="text-xs text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4 flex items-center justify-between">
            <p class="text-xs text-muted">
                Mostrando {{ $solicitudes->firstItem() }} a {{ $solicitudes->lastItem() }} de {{ $solicitudes->total() }}
            </p>
            {{ $solicitudes->links() }}
        </div>
    </div>
    @else
    <div class="text-center py-12">
        <p class="text-sm text-muted">No se encontraron solicitudes con los filtros aplicados.</p>
    </div>
    @endif
</div>

    <!-- Modal: Cancelar solicitud -->
    <div id="modal-cancelar" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm hidden" aria-hidden="true">
        <div class="bg-surface rounded-xl shadow-modal w-full max-w-md mx-4 transform transition scale-95" id="modal-cancelar-panel">
            <div class="p-6 border-b border-line">
                <h3 class="text-lg font-bold text-ink">Cancelar solicitud</h3>
                <p class="text-sm text-muted mt-1">
                    Se cancelará la solicitud <span id="modal-sol-numero" class="font-mono font-bold"></span>. Esta acción no se puede deshacer.
                </p>
            </div>
            <form id="form-cancelar" method="POST" action="" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="id_solicitud" id="modal-sol-id">
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Motivo de cancelación</label>
                    <textarea name="motivo" id="modal-sol-motivo" rows="4" required
                        class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:ring-brand-green/50 focus:border-brand-green/50"
                        placeholder="Describe por qué se cancela esta solicitud..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="cerrarModalCancelar()" class="px-4 py-2 text-muted hover:bg-surface-2 rounded-xl font-medium transition">Cerrar</button>
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-xl font-medium hover:bg-red-400 transition">Confirmar cancelación</button>
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
