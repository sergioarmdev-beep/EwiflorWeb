@extends('serviciotecnico::publico.layout')

@section('title', 'Solicitar servicio técnico')

@section('content')
<div class="max-w-md mx-auto">
    <div class="st-panel mx-auto">
        <h2 class="text-xl font-bold text-ink mb-6">Solicitar servicio técnico</h2>
        <p class="text-sm text-muted mb-6">Selecciona una compra con garantía vigente o deja sin garantía (se te cobrará el servicio según tarifa del personal técnico).</p>

        <form method="POST" action="{{ route('serviciotecnico.publico.guardar') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-muted mb-1 uppercase tracking-wider">Descripción de la falla</label>
                <textarea name="descripcion_falla" rows="4"
                    class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20"
                    placeholder="Ej: El equipo no enciende, se escucha un ruido extraño..." required></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-muted mb-1 uppercase tracking-wider">¿Está dentro de garantía?</label>
                <select name="id_garantia" class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20">
                    <option value="">No — servicio pagado (se te cobrará)</option>
                    @foreach ($compras as $compra)
                        @php
                            $garantiasVigentes = array_filter(($compra->detalles ?? []), fn($d) => $d->vigente === true);
                        @endphp
                        @foreach ($garantiasVigentes as $g)
                            <option value="{{ $g->id_garantia }}">
                                {{ $g->producto }} ({{ $g->sku }}) — vence {{ \Carbon\Carbon::parse($g->fecha_vencimiento)->format('d/m/Y') }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
                <p class="text-xs text-muted mt-1">Si seleccionas una garantía, no se te cobrará el servicio.</p>
            </div>

            <button type="submit"
                class="w-full px-4 py-2 bg-brand-green text-white rounded-xl font-semibold hover:bg-brand-green-dark transition">
                Registrar solicitud
            </button>
        </form>

        <div class="mt-4">
            <a href="{{ route('serviciotecnico.publico.panel') }}" class="text-sm text-brand-blue hover:underline">← Volver a mis compras</a>
        </div>
    </div>
</div>
@endsection
