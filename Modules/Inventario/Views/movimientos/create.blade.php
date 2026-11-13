@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Nuevo movimiento de inventario</h1>

    <form method="POST" action="{{ route('inventario.movimientos.store') }}"
        class="bg-white shadow rounded-lg p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700">Tipo *</label>
            <select name="tipo" id="tipo" required
                class="mt-1 border-gray-300 rounded-md shadow-sm w-full">
                @foreach (['ENTRADA', 'SALIDA', 'AJUSTE', 'TRANSFERENCIA'] as $t)
                <option value="{{ $t }}" @selected(old('tipo')===$t)>{{ $t }}</option>
                @endforeach
            </select>
            @error('tipo') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div id="campo-origen" class="hidden">
                <label class="block text-sm font-medium text-gray-700">Almacén de origen *</label>
                <select name="id_almacen_origen" id="id_almacen_origen"
                    class="mt-1 border-gray-300 rounded-md shadow-sm w-full">
                    <option value="">— Seleccionar —</option>
                    @foreach ($almacenes as $a)
                    <option value="{{ $a->id_almacen }}" @selected(old('id_almacen_origen')==$a->id_almacen)>
                        {{ $a->nombre }}
                    </option>
                    @endforeach
                </select>
                @error('id_almacen_origen') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div id="campo-destino" class="hidden">
                <label class="block text-sm font-medium text-gray-700">Almacén de destino *</label>
                <select name="id_almacen_destino" id="id_almacen_destino"
                    class="mt-1 border-gray-300 rounded-md shadow-sm w-full">
                    <option value="">— Seleccionar —</option>
                    @foreach ($almacenes as $a)
                    <option value="{{ $a->id_almacen }}" @selected(old('id_almacen_destino')==$a->id_almacen)>
                        {{ $a->nombre }}
                    </option>
                    @endforeach
                </select>
                @error('id_almacen_destino') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Producto / Variante *</label>
                <select name="id_variante" required
                    class="mt-1 border-gray-300 rounded-md shadow-sm w-full">
                    <option value="">— Seleccionar —</option>
                    @foreach ($variantes as $v)
                    <option value="{{ $v->id_variante }}" @selected(old('id_variante')==$v->id_variante)>
                        {{ $v->sku }} — {{ $v->producto?->nombre ?? '' }}
                        @if ($v->color) ({{ $v->color }}) @endif
                    </option>
                    @endforeach
                </select>
                @error('id_variante') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Cantidad *</label>
                <input type="number" name="cantidad" min="1" required
                    value="{{ old('cantidad') }}"
                    class="mt-1 border-gray-300 rounded-md shadow-sm w-full">
                @error('cantidad') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div id="campo-sentido" class="hidden">
            <label class="block text-sm font-medium text-gray-700">Sentido del ajuste *</label>
            <select name="sentido" class="mt-1 border-gray-300 rounded-md shadow-sm w-full">
                <option value="">— Seleccionar —</option>
                <option value="INCREMENTO" @selected(old('sentido')==='INCREMENTO' )>
                    INCREMENTO (sumar al stock)
                </option>
                <option value="DECREMENTO" @selected(old('sentido')==='DECREMENTO' )>
                    DECREMENTO (restar del stock)
                </option>
            </select>
            @error('sentido') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Motivo *</label>
            <input type="text" name="motivo" required maxlength="200"
                value="{{ old('motivo') }}"
                placeholder="Ej.: Compra a proveedor Seven"
                class="mt-1 border-gray-300 rounded-md shadow-sm w-full">
            @error('motivo') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <a href="{{ route('inventario.existencias.index') }}"
                class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</a>
            <button type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Registrar movimiento
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tipo = document.getElementById('tipo');
        const campoOrigen = document.getElementById('campo-origen');
        const campoDestino = document.getElementById('campo-destino');
        const campoSentido = document.getElementById('campo-sentido');

        function actualizar() {
            const t = tipo.value;
            campoOrigen.classList.toggle('hidden', !['SALIDA', 'AJUSTE', 'TRANSFERENCIA'].includes(t));
            campoDestino.classList.toggle('hidden', !['ENTRADA', 'TRANSFERENCIA'].includes(t));
            campoSentido.classList.toggle('hidden', t !== 'AJUSTE');
        }

        tipo.addEventListener('change', actualizar);
        actualizar();
    });
</script>
@endsection