@extends('layouts.app')

@section('title', 'Nuevo movimiento de inventario')

@section('content')
<div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div>
        <a href="{{ route('inventario.movimientos.index') }}" class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-muted hover:text-brand-blue">← Volver a movimientos</a>
        <h1 class="text-2xl font-bold text-ink">Nuevo movimiento</h1>
        <p class="mt-1 text-sm text-muted">Registra una operación sobre las existencias del inventario.</p>
    </div>

    <form method="POST" action="{{ route('inventario.movimientos.store') }}" class="overflow-hidden rounded-xl border border-line bg-surface shadow-soft">
        @csrf
        <div class="space-y-6 p-5 sm:p-7">
            <div>
                <label for="tipo" class="block text-sm font-semibold text-muted">Tipo de movimiento <span class="text-red-500">*</span></label>
                <select name="tipo" id="tipo" required class="mt-2 w-full rounded-xl border border-line bg-surface-2 text-ink px-3 py-2.5 text-sm focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                    @foreach (['ENTRADA', 'SALIDA', 'AJUSTE', 'TRANSFERENCIA'] as $t)<option value="{{ $t }}" @selected(old('tipo', 'ENTRADA') === $t)>{{ $t }}</option>@endforeach
                </select>
                @error('tipo')<p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div id="campo-origen" class="hidden">
                    <label for="id_almacen_origen" class="block text-sm font-semibold text-muted">Almacén de origen <span class="text-red-500">*</span></label>
                    <select name="id_almacen_origen" id="id_almacen_origen" class="mt-2 w-full rounded-xl border border-line bg-surface-2 text-ink px-3 py-2.5 text-sm focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20"><option value="">— Seleccionar —</option>@foreach ($almacenes as $a)<option value="{{ $a->id_almacen }}" @selected(old('id_almacen_origen') == $a->id_almacen)>{{ $a->nombre }}</option>@endforeach</select>
                    @error('id_almacen_origen')<p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div id="campo-destino" class="hidden">
                    <label for="id_almacen_destino" class="block text-sm font-semibold text-muted">Almacén de destino <span class="text-red-500">*</span></label>
                    <select name="id_almacen_destino" id="id_almacen_destino" class="mt-2 w-full rounded-xl border border-line bg-surface-2 text-ink px-3 py-2.5 text-sm focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20"><option value="">— Seleccionar —</option>@foreach ($almacenes as $a)<option value="{{ $a->id_almacen }}" @selected(old('id_almacen_destino') == $a->id_almacen)>{{ $a->nombre }}</option>@endforeach</select>
                    @error('id_almacen_destino')<p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="id_variante" class="block text-sm font-semibold text-muted">Producto / Variante <span class="text-red-500">*</span></label>
                    <select name="id_variante" id="id_variante" required class="mt-2 w-full rounded-xl border border-line bg-surface-2 text-ink px-3 py-2.5 text-sm focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20"><option value="">— Seleccionar —</option>@foreach ($variantes as $v)<option value="{{ $v->id_variante }}" @selected(old('id_variante') == $v->id_variante)>{{ $v->sku }} — {{ $v->producto?->nombre ?? '' }}@if ($v->color) ({{ $v->color }}) @endif</option>@endforeach</select>
                    @error('id_variante')<p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="cantidad" class="block text-sm font-semibold text-muted">Cantidad <span class="text-red-500">*</span></label>
                    <input type="number" name="cantidad" id="cantidad" min="1" required value="{{ old('cantidad') }}" placeholder="Ej. 10" class="mt-2 w-full rounded-xl border border-line bg-surface-2 text-ink px-3 py-2.5 text-sm placeholder:text-muted focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                    @error('cantidad')<p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div id="campo-sentido" class="hidden">
                <label for="sentido" class="block text-sm font-semibold text-muted">Sentido del ajuste <span class="text-red-500">*</span></label>
                <select name="sentido" id="sentido" class="mt-2 w-full rounded-xl border border-line bg-surface-2 text-ink px-3 py-2.5 text-sm focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20"><option value="">— Seleccionar —</option><option value="INCREMENTO" @selected(old('sentido') === 'INCREMENTO')>INCREMENTO (sumar al stock)</option><option value="DECREMENTO" @selected(old('sentido') === 'DECREMENTO')>DECREMENTO (restar del stock)</option></select>
                @error('sentido')<p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="motivo" class="block text-sm font-semibold text-muted">Motivo <span class="text-red-500">*</span></label>
                <input type="text" name="motivo" id="motivo" required maxlength="200" value="{{ old('motivo') }}" placeholder="Ej.: Compra a proveedor Seven" class="mt-2 w-full rounded-xl border border-line bg-surface-2 text-ink px-3 py-2.5 text-sm placeholder:text-muted focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
                @error('motivo')<p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-line bg-surface-2/60 px-5 py-4 sm:flex-row sm:justify-end sm:px-7">
            <a href="{{ route('inventario.existencias.index') }}" class="inline-flex justify-center rounded-xl border border-line px-4 py-2.5 text-sm font-semibold text-ink hover:bg-surface-2">Cancelar</a>
            <button type="submit" class="inline-flex justify-center rounded-xl bg-brand-green px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-green-dark">Registrar movimiento</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tipo = document.getElementById('tipo');
    const campoOrigen = document.getElementById('campo-origen');
    const campoDestino = document.getElementById('campo-destino');
    const campoSentido = document.getElementById('campo-sentido');
    if (!tipo || !campoOrigen || !campoDestino || !campoSentido) return;

    function actualizarCampos() {
        const valor = tipo.value;
        campoOrigen.classList.toggle('hidden', !['SALIDA', 'AJUSTE', 'TRANSFERENCIA'].includes(valor));
        campoDestino.classList.toggle('hidden', !['ENTRADA', 'TRANSFERENCIA'].includes(valor));
        campoSentido.classList.toggle('hidden', valor !== 'AJUSTE');
    }
    tipo.addEventListener('change', actualizarCampos);
    actualizarCampos();
});
</script>
@endpush
@endsection
