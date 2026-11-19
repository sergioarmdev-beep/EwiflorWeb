@extends('layouts.app')

@section('title', 'Registrar Nuevo Producto')

@section('content')

<div class="min-h-screen py-10 px-4 sm:px-6 lg:px-8">

    <div class="max-w-5xl mx-auto">

        {{-- ENCABEZADO --}}
        <div class="mb-8">
            <div class="flex items-center gap-2 text-sm text-muted mb-3">
                <span>Catálogo</span>
                <span>/</span>
                <span class="text-brand-blue">Nuevo producto</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-11 h-11 rounded-xl border border-brand-blue/20 bg-brand-blue/10 flex items-center justify-center text-brand-blue">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                        </div>

                        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-ink">
                            Registrar producto
                        </h2>
                    </div>

                    <p class="text-sm text-muted max-w-xl">
                        Complete la información general, clasificación y características
                        del nuevo producto para incorporarlo al catálogo.
                    </p>
                </div>

                <a href="{{ route('catalogo.productos.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-line bg-surface text-ink hover:text-brand-green hover:border-brand-green/50 transition duration-200 text-sm font-semibold">

                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7 7-7M3 12h18" />
                    </svg>

                    Volver al catálogo
                </a>

            </div>
        </div>


        {{-- FORMULARIO --}}
        <div class="bg-surface border border-line rounded-2xl shadow-soft overflow-hidden">

            {{-- CABECERA DEL FORMULARIO --}}
            <div class="px-6 sm:px-8 py-5 border-b border-line bg-surface-2/60">
                <div class="flex items-center gap-3">

                    <div class="w-9 h-9 rounded-lg bg-brand-blue/10 flex items-center justify-center text-brand-blue">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-base font-bold text-ink">
                            Información del producto
                        </h3>
                        <p class="text-xs text-muted mt-0.5">
                            Los campos marcados con * son obligatorios.
                        </p>
                    </div>

                </div>
            </div>


            <form action="{{ route('catalogo.productos.store') }}" method="POST" enctype="multipart/form-data">

                @csrf

                <div class="p-6 sm:p-8 space-y-10">

                    {{-- SECCIÓN 1: IDENTIFICACIÓN --}}
                    <section>

                        <div class="flex items-center gap-3 mb-6">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-brand-blue/10 text-brand-blue text-xs font-bold">
                                01
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-ink">
                                    Identificación
                                </h4>
                                <p class="text-xs text-muted">
                                    Datos principales para reconocer el producto.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Código --}}
                            <div>
                                <label for="codigo" class="block text-sm font-medium text-muted mb-2">
                                    Código <span class="text-red-500">*</span>
                                </label>

                                <input type="text" name="codigo" id="codigo"
                                    value="{{ old('codigo') }}" required
                                    placeholder="Ej. EQ-001"
                                    class="form-input uppercase">

                                @error('codigo')
                                <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>


                            {{-- Nombre --}}
                            <div>
                                <label for="nombre" class="block text-sm font-medium text-muted mb-2">
                                    Nombre del producto <span class="text-red-500">*</span>
                                </label>

                                <input type="text" name="nombre" id="nombre"
                                    value="{{ old('nombre') }}" required
                                    placeholder="Nombre del producto"
                                    class="form-input">

                                @error('nombre')
                                <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>


                            {{-- Marca --}}
                            <div>
                                <label for="id_marca" class="block text-sm font-medium text-muted mb-2">
                                    Marca <span class="text-red-500">*</span>
                                </label>

                                <select name="id_marca" id="id_marca" required class="form-select">
                                    <option value="">Seleccione una marca</option>

                                    @foreach($marcas as $marca)
                                    <option value="{{ $marca->id_marca }}"
                                        {{ old('id_marca') == $marca->id_marca ? 'selected' : '' }}>
                                        {{ $marca->nombre }}
                                    </option>
                                    @endforeach
                                </select>

                                @error('id_marca')
                                <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>


                            {{-- Modelo --}}
                            <div>
                                <label for="modelo" class="block text-sm font-medium text-muted mb-2">
                                    Modelo
                                </label>

                                <input type="text" name="modelo" id="modelo"
                                    value="{{ old('modelo') }}"
                                    placeholder="Modelo del fabricante"
                                    class="form-input">
                            </div>

                        </div>
                    </section>


                    {{-- SECCIÓN 2: CLASIFICACIÓN --}}
                    <section class="pt-8 border-t border-line">

                        <div class="flex items-center gap-3 mb-6">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-brand-blue/10 text-brand-blue text-xs font-bold">
                                02
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-ink">
                                    Clasificación del producto
                                </h4>
                                <p class="text-xs text-muted">
                                    Defina cómo se organiza y comercializa el producto.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Categoría --}}
                            <div>
                                <label for="categoria" class="block text-sm font-medium text-muted mb-2">
                                    Categoría <span class="text-red-500">*</span>
                                </label>

                                <select name="categoria" id="categoria" required class="form-select">
                                    <option value="">Seleccione categoría</option>

                                    @foreach($categorias as $cat)
                                    <option value="{{ $cat }}"
                                        {{ old('categoria') == $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                    @endforeach
                                </select>

                                @error('categoria')
                                <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>


                            {{-- Tipo de producto --}}
                            <div>
                                <label for="tipo_producto" class="block text-sm font-medium text-muted mb-2">
                                    Tipo de producto <span class="text-red-500">*</span>
                                </label>

                                <select name="tipo_producto" id="tipo_producto" required class="form-select">
                                    <option value="">Seleccione tipo</option>

                                    @foreach($tipos as $tipo)
                                    <option value="{{ $tipo }}"
                                        {{ old('tipo_producto') == $tipo ? 'selected' : '' }}>
                                        {{ $tipo }}
                                    </option>
                                    @endforeach
                                </select>

                                @error('tipo_producto')
                                <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>


                            {{-- Modalidad --}}
                            <div class="md:col-span-2">

                                <label for="modalidad" class="block text-sm font-medium text-muted mb-2">
                                    Modalidad <span class="text-red-500">*</span>
                                </label>

                                <select name="modalidad" id="modalidad" required class="form-select">
                                    <option value="INVENTARIABLE"
                                        {{ old('modalidad') == 'INVENTARIABLE' ? 'selected' : '' }}>
                                        INVENTARIABLE
                                    </option>

                                    <option value="BAJO_PEDIDO"
                                        {{ old('modalidad') == 'BAJO_PEDIDO' ? 'selected' : '' }}>
                                        BAJO PEDIDO
                                    </option>
                                </select>

                                @error('modalidad')
                                <span class="form-error">{{ $message }}</span>
                                @enderror

                                <p class="text-xs text-muted mt-2">
                                    Seleccione si el producto se gestiona mediante inventario
                                    o se comercializa bajo pedido.
                                </p>
                            </div>

                        </div>
                    </section>


                    {{-- SECCIÓN 3: CONTROL Y GARANTÍA --}}
                    <section class="pt-8 border-t border-line">

                        <div class="flex items-center gap-3 mb-6">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-brand-blue/10 text-brand-blue text-xs font-bold">
                                03
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-ink">
                                    Control y garantía
                                </h4>
                                <p class="text-xs text-muted">
                                    Parámetros iniciales para la gestión del producto.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Stock mínimo --}}
                            <div>
                                <label for="stock_minimo" class="block text-sm font-medium text-muted mb-2">
                                    Stock mínimo <span class="text-red-500">*</span>
                                </label>

                                <div class="relative">
                                    <input type="number" name="stock_minimo" id="stock_minimo"
                                        value="{{ old('stock_minimo', 0) }}" min="0" required
                                        class="form-input pr-20">

                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-muted pointer-events-none">
                                        Unidades
                                    </span>
                                </div>

                                @error('stock_minimo')
                                <span class="form-error">{{ $message }}</span>
                                @enderror

                                <p class="text-xs text-muted mt-2">
                                    Cantidad mínima de referencia para el control de existencias.
                                </p>
                            </div>


                            {{-- Garantía --}}
                            <div>
                                <label for="garantia_meses" class="block text-sm font-medium text-muted mb-2">
                                    Garantía <span class="text-red-500">*</span>
                                </label>

                                <div class="relative">
                                    <input type="number" name="garantia_meses" id="garantia_meses"
                                        value="{{ old('garantia_meses', 0) }}" min="0" required
                                        class="form-input pr-20">

                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-muted pointer-events-none">
                                        Meses
                                    </span>
                                </div>

                                @error('garantia_meses')
                                <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>
                    </section>


                    {{-- SECCIÓN 4: IMÁGENES --}}
                    <section class="pt-8 border-t border-line">

                        <div class="flex items-center gap-3 mb-6">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-brand-blue/10 text-brand-blue text-xs font-bold">
                                04
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-ink">
                                    Imágenes del producto
                                </h4>
                                <p class="text-xs text-muted">
                                    La primera imagen será la principal; podrá cambiarla luego al editar.
                                </p>
                            </div>
                        </div>

                        @include('catalogo::productos.partials.galeria-subida', ['etiqueta' => 'Agregar imágenes', 'esNuevoProducto' => 'true'])

                    </section>


                    {{-- SECCIÓN 5: VARIANTES --}}
                    @include('catalogo::productos.partials.variantes', ['numero' => '05'])


                    {{-- SECCIÓN 6: DOCUMENTACIÓN --}}
                    <section class="pt-8 border-t border-line">

                        <div class="flex items-center gap-3 mb-6">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-brand-blue/10 text-brand-blue text-xs font-bold">
                                06
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-ink">
                                    Documentación del producto
                                </h4>
                                <p class="text-xs text-muted">
                                    Suba manuales, fichas técnicas o documentos en formato TXT o Word.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Documentos --}}
                            <div class="md:col-span-2">
                                <label for="documentos" class="block text-sm font-medium text-muted mb-2">
                                    Documentos adjuntos <span class="text-muted">(opcional)</span>
                                </label>

                                <input type="file" name="documentos[]" id="documentos" multiple
                                    accept=".txt,.doc,.docx"
                                    class="block w-full text-sm text-muted
                                        file:mr-4 file:py-2 file:rounded-full file:border-0
                                        file:text-sm file:font-semibold file:bg-brand-blue file:text-white
                                        hover:file:bg-brand-blue-dark cursor-pointer">

                                <p class="text-xs text-muted mt-2">
                                    Formatos permitidos: .txt, .doc, .docx. Máximo 5 archivos, 5 MB cada uno.
                                </p>

                                @error('documentos.*')
                                <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>
                    </section>


                    {{-- SECCIÓN 7: REPUESTOS (solo maquinaria) --}}
                    <section class="pt-8 border-t border-line repuestos-section"
                        id="repuestos-section"
                        data-tipo-producto="{{ old('tipo_producto') }}">
                        {{-- Se muestra solo si el tipo es MAQUINARIA --}}

                        <div class="flex items-center gap-3 mb-6">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-brand-blue/10 text-brand-blue text-xs font-bold">
                                07
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-ink">
                                    Repuestos asociados
                                </h4>
                                <p class="text-xs text-muted">
                                    Seleccione los repuestos compatibles con esta maquinaria.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Selector de repuestos --}}
                            <div class="md:col-span-2">
                                <label for="repuestos" class="block text-sm font-medium text-muted mb-2">
                                    Repuestos compatibles <span class="text-muted">(opcional)</span>
                                </label>

                                <select name="repuestos[]" id="repuestos" multiple
                                    class="form-select h-auto min-h-[200px]">
                                    <option value="">Seleccione repuestos...</option>

                                    @php
                                        $repuestosDisponibles = Modules\Catalogo\Models\Producto::where('tipo_producto', 'REPUESTO')->where('estado', 'ACTIVO')->orderBy('nombre')->get();
                                    @endphp

                                    @foreach($repuestosDisponibles as $repuesto)
                                    <option value="{{ $repuesto->id_producto }}">
                                        {{ $repuesto->codigo }} - {{ $repuesto->nombre }} {{ $repuesto->modelo ? '('.$repuesto->modelo.')' : '' }}
                                    </option>
                                    @endforeach
                                </select>

                                <p class="text-xs text-muted mt-2">
                                    Solo aplica a productos de tipo <strong>MAQUINARIA</strong>.
                                    Los repuestos disponibles son aquellos marcados como tipo REPUESTO.
                                </p>

                                @error('repuestos.*')
                                <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>
                    </section>

                    {{-- SECCIÓN 8: DESCRIPCIÓN --}}
                    <section class="pt-8 border-t border-line">

                        <div class="flex items-center gap-3 mb-6">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-brand-blue/10 text-brand-blue text-xs font-bold">
                                08
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-ink">
                                    Descripción
                                </h4>
                                <p class="text-xs text-muted">
                                    Información adicional sobre el producto.
                                </p>
                            </div>
                        </div>

                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-muted mb-2">
                                Descripción del producto
                            </label>

                            <textarea name="descripcion" id="descripcion" rows="4"
                                placeholder="Escriba una descripción del producto, sus características o información relevante..."
                                class="form-input resize-y min-h-28">{{ old('descripcion') }}</textarea>
                        </div>

                    </section>


                    {{-- SECCIÓN 9: VISIBILIDAD --}}
                    <section class="pt-8 border-t border-line">

                        <div class="flex items-center gap-3 mb-6">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-brand-blue/10 text-brand-blue text-xs font-bold">
                                09
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-ink">
                                    Visibilidad del producto
                                </h4>
                                <p class="text-xs text-muted">
                                    Configure la publicación en el catálogo público.
                                </p>
                            </div>
                        </div>

                        <label for="es_publico"
                            class="flex items-start gap-4 p-4 rounded-xl border border-line bg-surface-2/60 cursor-pointer hover:border-brand-blue/50 transition duration-200">

                            <input type="checkbox" name="es_publico" id="es_publico" value="1"
                                {{ old('es_publico', true) ? 'checked' : '' }}
                                class="mt-1 w-4 h-4 rounded border border-line bg-surface-2 text-brand-blue focus:ring-brand-blue/50 focus:ring-offset-surface">

                            <div>
                                <span class="block text-sm font-semibold text-ink">
                                    Mostrar en el catálogo público
                                </span>

                                <span class="block text-xs text-muted mt-1 leading-relaxed">
                                    Al activar esta opción, el producto podrá mostrarse
                                    en el catálogo web público.
                                </span>
                            </div>

                        </label>

                    </section>

                </div>


                {{-- ACCIONES --}}
                <div class="px-6 sm:px-8 py-5 border-t border-line bg-surface-2/60">

                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-4">

                        <p class="text-xs text-muted">
                            Verifique la información antes de guardar.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-3">

                            <a href="{{ route('catalogo.productos.index') }}"
                                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-line bg-surface text-ink hover:bg-surface-2 hover:text-brand-green transition duration-200 text-sm font-semibold">
                                Cancelar
                            </a>

                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-brand-green text-white font-bold text-sm shadow-sm hover:bg-brand-green-dark transition duration-200">

                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>

                                Guardar producto
                            </button>

                        </div>

                    </div>

                </div>

            </form>

        </div>

        <p class="text-center text-xs text-muted mt-6">
            Ewiflor Importaciones · Administración de catálogo
        </p>

    </div>

</div>


{{-- ESTILOS LOCALES DEL FORMULARIO --}}
<style>
    .form-input,
    .form-select {
        display: block;
        width: 100%;
        min-height: 44px;
        padding: 10px 13px;
        border: 1px solid var(--border);
        border-radius: 10px;
        outline: none;
        background-color: var(--surface-2);
        color: var(--text);
        font-size: .875rem;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .form-input::placeholder {
        color: var(--muted);
    }

    .form-input:focus,
    .form-select:focus {
        border-color: var(--color-brand-green);
        box-shadow: 0 0 0 3px rgba(42, 152, 50, .12);
    }

    .form-select {
        cursor: pointer;
    }

    .form-select option {
        background: var(--surface);
        color: var(--text);
    }

    .form-error {
        display: block;
        margin-top: 7px;
        color: var(--color-red-500, #ef4444);
        font-size: .75rem;
    }

    input[type="file"]::file-selector-button {
        cursor: pointer;
    }

    /* Ocultar sección de repuestos si no es maquinaria */
    .repuestos-section {
        display: none;
    }

    .repuestos-section[data-tipo-producto="MAQUINARIA"] {
        display: block;
    }

    .repuestos-section.mostrar {
        display: block;
    }
</style>

<script>
    // Mostrar/ocultar sección de repuestos según tipo de producto
    const tipoSelect = document.getElementById('tipo_producto');
    const repuestosSection = document.getElementById('repuestos-section');

    function toggleRepuestos() {
        if (tipoSelect.value === 'MAQUINARIA') {
            repuestosSection.classList.add('mostrar');
        } else {
            repuestosSection.classList.remove('mostrar');
        }
    }

    tipoSelect.addEventListener('change', toggleRepuestos);
    // Ejecutar al cargar por si el valor viene por defecto
    toggleRepuestos();
</script>

@endsection
