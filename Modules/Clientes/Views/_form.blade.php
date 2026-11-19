@php
$isEdit = $cliente->exists;
@endphp

<div class="space-y-6">

    {{-- Datos principales --}}
    <div>
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-ink">
                Datos de contacto
            </h3>
            <p class="text-xs text-muted mt-1">
                Información básica para identificar y contactar al cliente.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- Nombre --}}
            <div>
                <label for="nombre"
                    class="block text-sm font-medium text-muted mb-1.5">
                    Nombre <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="{{ old('nombre', $cliente->nombre) }}"
                    placeholder="Nombre completo o razón social"
                    required
                    class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3.5 py-2.5 text-sm placeholder:text-muted focus:border-brand-green/50 focus:ring-brand-green/20 @error('nombre') border-red-500 @enderror">

                @error('nombre')
                <p class="mt-1.5 text-xs text-red-500">
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- Teléfono --}}
            <div>
                <label for="telefono"
                    class="block text-sm font-medium text-muted mb-1.5">
                    Teléfono <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    id="telefono"
                    name="telefono"
                    value="{{ old('telefono', $cliente->telefono) }}"
                    placeholder="Número de teléfono"
                    required
                    class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3.5 py-2.5 text-sm placeholder:text-muted focus:border-brand-green/50 focus:ring-brand-green/20 @error('telefono') border-red-500 @enderror">

                @error('telefono')
                <p class="mt-1.5 text-xs text-red-500">
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- Correo --}}
            <div>
                <label for="correo"
                    class="block text-sm font-medium text-muted mb-1.5">
                    Correo electrónico
                </label>

                <input
                    type="email"
                    id="correo"
                    name="correo"
                    value="{{ old('correo', $cliente->correo) }}"
                    placeholder="ejemplo@correo.com"
                    class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3.5 py-2.5 text-sm placeholder:text-muted focus:border-brand-green/50 focus:ring-brand-green/20 @error('correo') border-red-500 @enderror">

                @error('correo')
                <p class="mt-1.5 text-xs text-red-500">
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- Tipo de cliente --}}
            <div>
                <label for="tipo_cliente"
                    class="block text-sm font-medium text-muted mb-1.5">
                    Tipo de cliente
                </label>

                <select
                    id="tipo_cliente"
                    name="tipo_cliente"
                    class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3.5 py-2.5 text-sm cursor-pointer focus:border-brand-green/50 focus:ring-brand-green/20">
                    <option value="">Seleccionar tipo</option>

                    @foreach (\Modules\Clientes\Models\Cliente::TIPOS as $t)
                    <option value="{{ $t }}"
                        @selected(old('tipo_cliente', $cliente->tipo_cliente) === $t)>
                        {{ $t }}
                    </option>
                    @endforeach
                </select>

                @error('tipo_cliente')
                <p class="mt-1.5 text-xs text-red-500">
                    {{ $message }}
                </p>
                @enderror
            </div>

        </div>
    </div>

    {{-- Clasificación comercial --}}
    <div class="pt-5 border-t border-line">
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-ink">
                Clasificación comercial
            </h3>
            <p class="text-xs text-muted mt-1">
                Define si el cliente pertenece al segmento mayorista.
            </p>
        </div>

        <label for="es_mayorista"
            class="flex items-start gap-3 p-4 rounded-xl border border-line bg-surface-2/60 cursor-pointer hover:border-brand-blue/50 transition-colors">

            <input type="hidden" name="es_mayorista" value="0">

            <input
                type="checkbox"
                id="es_mayorista"
                name="es_mayorista"
                value="1"
                @checked(old('es_mayorista', $cliente->es_mayorista))
            class="mt-0.5 w-4 h-4 rounded border border-line bg-surface-2 text-brand-green focus:ring-brand-green/50 focus:ring-offset-surface">

            <span>
                <span class="block text-sm font-medium text-ink">
                    Cliente mayorista
                </span>
                <span class="block text-xs text-muted mt-1">
                    Marca esta opción si el cliente realiza compras bajo la modalidad mayorista.
                </span>
            </span>
        </label>

        @error('es_mayorista')
        <p class="mt-1.5 text-xs text-red-500">
            {{ $message }}
        </p>
        @enderror
    </div>

    {{-- Acciones --}}
    <div class="pt-5 border-t border-line flex flex-col-reverse sm:flex-row sm:justify-end gap-3">

        <a href="{{ route('clientes.index') }}"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium border border-line text-ink hover:bg-surface-2 hover:text-brand-green transition-colors">
            Cancelar
        </a>

        <button
            type="submit"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-brand-green text-white shadow-sm hover:bg-brand-green-dark focus:ring-brand-green/40 focus:ring-offset-surface transition-colors">

            <svg xmlns="http://www.w3.org/2000/svg"
                class="w-4 h-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 13l4 4L19 7" />
            </svg>

            {{ $isEdit ? 'Guardar cambios' : 'Crear cliente' }}
        </button>

    </div>

</div>
