@extends('layouts.app')

@section('title', 'Nuevo movimiento de inventario')

@section('content')
<div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div>
        <a href="{{ route('inventario.movimientos.index') }}" class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-sky-700 dark:text-slate-400 dark:hover:text-sky-400">← Volver a movimientos</a>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Nuevo movimiento</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Registra una operación sobre las existencias del inventario.</p>
    </div>

    <form method="POST" action="{{ route('inventario.movimientos.store') }}" class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
        @csrf
        <div class="space-y-6 p-5 sm:p-7">
            <div>
                <label for="tipo" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Tipo de movimiento <span class="text-red-500">*</span></label>
                <select name="tipo" id="tipo" required class="mt-2 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
                    @foreach (['ENTRADA', 'SALIDA', 'AJUSTE', 'TRANSFERENCIA'] as $t)<option value="{{ $t }}" @selected(old('tipo', 'ENTRADA') === $t)>{{ $t }}</option>@endforeach
                </select>
                @error('tipo')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div id="campo-origen" class="hidden">
                    <label for="id_almacen_origen" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Almacén de origen <span class="text-red-500">*</span></label>
                    <select name="id_almacen_origen" id="id_almacen_origen" class="mt-2 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"><option value="">— Seleccionar —</option>@foreach ($almacenes as $a)<option value="{{ $a->id_almacen }}" @selected(old('id_almacen_origen') == $a->id_almacen)>{{ $a->nombre }}</option>@endforeach</select>
                    @error('id_almacen_origen')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div id="campo-destino" class="hidden">
                    <label for="id_almacen_destino" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Almacén de destino <span class="text-red-500">*</span></label>
                    <select name="id_almacen_destino" id="id_almacen_destino" class="mt-2 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"><option value="">— Seleccionar —</option>@foreach ($almacenes as $a)<option value="{{ $a->id_almacen }}" @selected(old('id_almacen_destino') == $a->id_almacen)>{{ $a->nombre }}</option>@endforeach</select>
                    @error('id_almacen_destino')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="id_variante" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Producto / Variante <span class="text-red-500">*</span></label>
                    <select name="id_variante" id="id_variante" required class="mt-2 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"><option value="">— Seleccionar —</option>@foreach ($variantes as $v)<option value="{{ $v->id_variante }}" @selected(old('id_variante') == $v->id_variante)>{{ $v->sku }} — {{ $v->producto?->nombre ?? '' }}@if ($v->color) ({{ $v->color }}) @endif</option>@endforeach</select>
                    @error('id_variante')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="cantidad" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Cantidad <span class="text-red-500">*</span></label>
                    <input type="number" name="cantidad" id="cantidad" min="1" required value="{{ old('cantidad') }}" placeholder="Ej. 10" class="mt-2 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
                    @error('cantidad')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div id="campo-sentido" class="hidden">
                <label for="sentido" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Sentido del ajuste <span class="text-red-500">*</span></label>
                <select name="sentido" id="sentido" class="mt-2 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"><option value="">— Seleccionar —</option><option value="INCREMENTO" @selected(old('sentido') === 'INCREMENTO')>INCREMENTO (sumar al stock)</option><option value="DECREMENTO" @selected(old('sentido') === 'DECREMENTO')>DECREMENTO (restar del stock)</option></select>
                @error('sentido')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="motivo" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Motivo <span class="text-red-500">*</span></label>
                <input type="text" name="motivo" id="motivo" required maxlength="200" value="{{ old('motivo') }}" placeholder="Ej.: Compra a proveedor Seven" class="mt-2 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
                @error('motivo')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 px-5 py-4 sm:flex-row sm:justify-end sm:px-7">
            <a href="{{ route('inventario.existencias.index') }}" class="inline-flex justify-center rounded-lg border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Cancelar</a>
            <button type="submit" class="inline-flex justify-center rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-sky-700 dark:hover:bg-sky-500">Registrar movimiento</button>
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
