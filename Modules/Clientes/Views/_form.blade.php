@php
$isEdit = $cliente->exists;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium">Nombre *</label>
        <input type="text" name="nombre"
            value="{{ old('nombre', $cliente->nombre) }}"
            class="w-full border rounded px-3 py-2 @error('nombre') border-red-500 @enderror" required>
        @error('nombre') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium">Teléfono *</label>
        <input type="text" name="telefono"
            value="{{ old('telefono', $cliente->telefono) }}"
            class="w-full border rounded px-3 py-2 @error('telefono') border-red-500 @enderror" required>
        @error('telefono') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium">Correo</label>
        <input type="email" name="correo"
            value="{{ old('correo', $cliente->correo) }}"
            class="w-full border rounded px-3 py-2 @error('correo') border-red-500 @enderror">
        @error('correo') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium">Tipo de cliente</label>
        <select name="tipo_cliente" class="w-full border rounded px-3 py-2">
            <option value="">—</option>
            @foreach (\Modules\Clientes\Models\Cliente::TIPOS as $t)
            <option value="{{ $t }}" @selected(old('tipo_cliente', $cliente->tipo_cliente) === $t)>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2">
            <input type="hidden" name="es_mayorista" value="0">
            <input type="checkbox" name="es_mayorista" value="1"
                @checked(old('es_mayorista', $cliente->es_mayorista))>
            <span class="text-sm">Es mayorista</span>
        </label>
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
        {{ $isEdit ? 'Guardar cambios' : 'Crear cliente' }}
    </button>
    <a href="{{ route('clientes.index') }}" class="border px-4 py-2 rounded">Cancelar</a>
</div>