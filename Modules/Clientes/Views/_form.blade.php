@php
$isEdit = $cliente->exists;
@endphp

<div class="space-y-6">

    {{-- Datos principales --}}
    <div>
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                Datos de contacto
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Información básica para identificar y contactar al cliente.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- Nombre --}}
            <div>
                <label for="nombre"
                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Nombre <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="{{ old('nombre', $cliente->nombre) }}"
                    placeholder="Nombre completo o razón social"
                    required
                    class="w-full rounded-lg border px-3.5 py-2.5 text-sm
                           bg-white dark:bg-slate-950
                           text-slate-800 dark:text-slate-100
                           placeholder:text-slate-400 dark:placeholder:text-slate-500
                           border-slate-300 dark:border-slate-700
                           focus:outline-none focus:ring-2 focus:ring-sky-500/30
                           focus:border-sky-500
                           transition-colors
                           @error('nombre') border-red-500 dark:border-red-500 @enderror">

                @error('nombre')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- Teléfono --}}
            <div>
                <label for="telefono"
                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Teléfono <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    id="telefono"
                    name="telefono"
                    value="{{ old('telefono', $cliente->telefono) }}"
                    placeholder="Número de teléfono"
                    required
                    class="w-full rounded-lg border px-3.5 py-2.5 text-sm
                           bg-white dark:bg-slate-950
                           text-slate-800 dark:text-slate-100
                           placeholder:text-slate-400 dark:placeholder:text-slate-500
                           border-slate-300 dark:border-slate-700
                           focus:outline-none focus:ring-2 focus:ring-sky-500/30
                           focus:border-sky-500
                           transition-colors
                           @error('telefono') border-red-500 dark:border-red-500 @enderror">

                @error('telefono')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- Correo --}}
            <div>
                <label for="correo"
                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Correo electrónico
                </label>

                <input
                    type="email"
                    id="correo"
                    name="correo"
                    value="{{ old('correo', $cliente->correo) }}"
                    placeholder="ejemplo@correo.com"
                    class="w-full rounded-lg border px-3.5 py-2.5 text-sm
                           bg-white dark:bg-slate-950
                           text-slate-800 dark:text-slate-100
                           placeholder:text-slate-400 dark:placeholder:text-slate-500
                           border-slate-300 dark:border-slate-700
                           focus:outline-none focus:ring-2 focus:ring-sky-500/30
                           focus:border-sky-500
                           transition-colors
                           @error('correo') border-red-500 dark:border-red-500 @enderror">

                @error('correo')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- Tipo de cliente --}}
            <div>
                <label for="tipo_cliente"
                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Tipo de cliente
                </label>

                <select
                    id="tipo_cliente"
                    name="tipo_cliente"
                    class="w-full rounded-lg border px-3.5 py-2.5 text-sm
                           bg-white dark:bg-slate-950
                           text-slate-800 dark:text-slate-100
                           border-slate-300 dark:border-slate-700
                           focus:outline-none focus:ring-2 focus:ring-sky-500/30
                           focus:border-sky-500
                           transition-colors">
                    <option value="">Seleccionar tipo</option>

                    @foreach (\Modules\Clientes\Models\Cliente::TIPOS as $t)
                    <option value="{{ $t }}"
                        @selected(old('tipo_cliente', $cliente->tipo_cliente) === $t)>
                        {{ $t }}
                    </option>
                    @endforeach
                </select>

                @error('tipo_cliente')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                    {{ $message }}
                </p>
                @enderror
            </div>

        </div>
    </div>

    {{-- Clasificación comercial --}}
    <div class="pt-5 border-t border-slate-200 dark:border-slate-800">
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                Clasificación comercial
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Define si el cliente pertenece al segmento mayorista.
            </p>
        </div>

        <label for="es_mayorista"
            class="flex items-start gap-3 p-4 rounded-xl
                      border border-slate-200 dark:border-slate-700
                      bg-slate-50 dark:bg-slate-800/50
                      cursor-pointer hover:border-sky-400
                      dark:hover:border-sky-500 transition-colors">

            <input type="hidden" name="es_mayorista" value="0">

            <input
                type="checkbox"
                id="es_mayorista"
                name="es_mayorista"
                value="1"
                @checked(old('es_mayorista', $cliente->es_mayorista))
            class="mt-0.5 w-4 h-4 rounded border-slate-300
            text-sky-600 focus:ring-sky-500
            dark:border-slate-600 dark:bg-slate-900"
            >

            <span>
                <span class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                    Cliente mayorista
                </span>
                <span class="block text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Marca esta opción si el cliente realiza compras bajo la modalidad mayorista.
                </span>
            </span>
        </label>

        @error('es_mayorista')
        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">
            {{ $message }}
        </p>
        @enderror
    </div>

    {{-- Acciones --}}
    <div class="pt-5 border-t border-slate-200 dark:border-slate-800
                flex flex-col-reverse sm:flex-row sm:justify-end gap-3">

        <a href="{{ route('clientes.index') }}"
            class="inline-flex items-center justify-center gap-2
                  px-5 py-2.5 rounded-lg text-sm font-medium
                  border border-slate-300 dark:border-slate-700
                  text-slate-700 dark:text-slate-300
                  hover:bg-slate-100 dark:hover:bg-slate-800
                  transition-colors">
            Cancelar
        </a>

        <button
            type="submit"
            class="inline-flex items-center justify-center gap-2
                   px-5 py-2.5 rounded-lg text-sm font-semibold
                   bg-sky-600 hover:bg-sky-700
                   text-white shadow-sm
                   focus:outline-none focus:ring-2
                   focus:ring-sky-500 focus:ring-offset-2
                   dark:focus:ring-offset-slate-900
                   transition-colors">

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