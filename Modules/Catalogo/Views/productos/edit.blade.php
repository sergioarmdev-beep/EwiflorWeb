@extends('layouts.app')

@section('title', 'Editar Producto')

@section('content')

<div class="min-h-screen py-10 px-4 sm:px-6 lg:px-8">

    <div class="max-w-5xl mx-auto">

        {{-- ENCABEZADO --}}
        <div class="mb-8">

            <div class="flex items-center gap-2 text-sm text-muted mb-3">
                <a href="{{ route('catalogo.productos.index') }}" class="hover:text-brand-blue transition">
                    Catálogo
                </a>
                <span>/</span>
                <span class="text-brand-blue">Editar producto</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                <div>
                    <div class="flex items-center gap-3 mb-2">

                        <div class="w-11 h-11 rounded-xl border border-brand-blue/20 bg-brand-blue/10 flex items-center justify-center text-brand-blue">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M16.862 4.487l1.687-1.687a2.1 2.1 0 013.01 2.93l-1.687 1.687m-3.01-2.93L7.5 13.85l-.75 3.4 3.4-.75 9.712-9.083m-3.01-2.93l3.01 2.93M19 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6" />
                            </svg>
                        </div>

                        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-ink">
                            Editar producto
                        </h2>

                    </div>

                    <p class="text-sm text-muted max-w-xl">
                        Actualice la información, clasificación y características
                        del producto seleccionado.
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


        {{-- TARJETA PRINCIPAL --}}
        <div class="bg-surface border border-line rounded-2xl shadow-soft overflow-hidden">

            {{-- CABECERA --}}
            <div class="px-6 sm:px-8 py-5 border-b border-line bg-surface-2/60">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                    <div class="flex items-center gap-3">

                        <div class="w-9 h-9 rounded-lg bg-brand-blue/10 flex items-center justify-center text-brand-blue">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 20h9M16.5 3.5a2.12 2.12 0 013 3L8 18l-4 1 1-4 11.5-11.5z" />
                            </svg>
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-ink">
                                Información del producto
                            </h3>
                            <p class="text-xs text-muted mt-0.5">
                                Modificación de datos existentes.
                            </p>
                        </div>

                    </div>

                    <span class="inline-flex items-center gap-2 w-fit px-3 py-1.5 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-500 text-xs font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Modo edición
                    </span>

                </div>
            </div>


            <form action="{{ route('catalogo.productos.update', $producto->id_producto) }}" method="POST" enctype="multipart/form-data">

                @csrf
                @method('PUT')

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
                                    value="{{ old('codigo', $producto->codigo) }}" required
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
                                    value="{{ old('nombre', $producto->nombre) }}" required
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
                                        {{ old('id_marca', $producto->id_marca) == $marca->id_marca ? 'selected' : '' }}>
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
                                    value="{{ old('modelo', $producto->modelo) }}"
                                    class="form-input">

                                @error('modelo')
                                <span class="form-error">{{ $message }}</span>
                                @enderror
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
                                    Organización y modalidad comercial.
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
                                        {{ old('categoria', $producto->categoria) == $cat ? 'selected' : '' }}>
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
                                        {{ old('tipo_producto', $producto->tipo_producto) == $tipo ? 'selected' : '' }}>
                                        {{ $tipo }}
                                    </option>
                                    @endforeach

                                </select>

                                @if($producto->tipo_producto === 'COMBO')
                                <p class="mt-2 text-xs text-brand-blue flex items-center gap-1.5">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    Este producto es un combo: se edita desde
                                    <a href="{{ route('promociones.combos.edit', $producto->id_producto) }}" class="font-semibold underline hover:text-brand-blue-dark">Promociones → Combos</a>.
                                </p>
                                @endif

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
                                        {{ old('modalidad', $producto->modalidad) == 'INVENTARIABLE' ? 'selected' : '' }}>
                                        INVENTARIABLE
                                    </option>

                                    <option value="BAJO_PEDIDO"
                                        {{ old('modalidad', $producto->modalidad) == 'BAJO_PEDIDO' ? 'selected' : '' }}>
                                        BAJO PEDIDO
                                    </option>

                                </select>

                                @error('modalidad')
                                <span class="form-error">{{ $message }}</span>
                                @enderror

                                <p class="text-xs text-muted mt-2">
                                    Define si el producto se gestiona mediante inventario
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
                                    Parámetros de gestión del producto.
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
                                        value="{{ old('stock_minimo', $producto->stock_minimo) }}"
                                        min="0" required
                                        class="form-input pr-20">

                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-muted pointer-events-none">
                                        Unidades
                                    </span>

                                </div>

                                @error('stock_minimo')
                                <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>


                            {{-- Garantía --}}
                            <div>
                                <label for="garantia_meses" class="block text-sm font-medium text-muted mb-2">
                                    Garantía <span class="text-red-500">*</span>
                                </label>

                                <div class="relative">

                                    <input type="number" name="garantia_meses" id="garantia_meses"
                                        value="{{ old('garantia_meses', $producto->garantia_meses) }}"
                                        min="0" required
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


                    {{-- SECCIÓN 4: VISIBILIDAD --}}
                    <section class="pt-8 border-t border-line">

                        <div class="flex items-center gap-3 mb-6">

                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-brand-blue/10 text-brand-blue text-xs font-bold">
                                04
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-ink">
                                    Visibilidad del producto
                                </h4>
                                <p class="text-xs text-muted">
                                    Configuración de publicación en el catálogo.
                                </p>
                            </div>

                        </div>

                        <label for="es_publico"
                            class="flex items-start gap-4 p-4 rounded-xl border border-line bg-surface-2/60 cursor-pointer hover:border-brand-blue/50 transition duration-200">

                            <input type="checkbox" name="es_publico" id="es_publico" value="1"
                                {{ old('es_publico', $producto->es_publico) ? 'checked' : '' }}
                                class="mt-1 w-4 h-4 rounded border border-line bg-surface-2 text-brand-blue focus:ring-brand-blue/50 focus:ring-offset-surface">

                            <div>
                                <span class="block text-sm font-semibold text-ink">
                                    Hacer producto visible al público
                                </span>

                                <span class="block text-xs text-muted mt-1 leading-relaxed">
                                    Al activar esta opción, el producto podrá mostrarse
                                    en el catálogo web público.
                                </span>
                            </div>

                        </label>

                    </section>


                    {{-- SECCIÓN 5: GALERÍA DE IMÁGENES --}}
                    <section class="pt-8 border-t border-line">

                        <div class="flex items-center gap-3 mb-6">

                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-brand-blue/10 text-brand-blue text-xs font-bold">
                                05
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-ink">
                                    Galería de imágenes
                                </h4>
                                <p class="text-xs text-muted">
                                    Administre las fotos del producto y elija cuál es la principal.
                                </p>
                            </div>

                        </div>

                        @include('catalogo::productos.partials.galeria-edicion', ['producto' => $producto])

                        @include('catalogo::productos.partials.galeria-subida', ['etiqueta' => 'Agregar más imágenes', 'esNuevoProducto' => 'false'])

                    </section>


                    {{-- SECCIÓN 6: VARIANTES --}}
                    @include('catalogo::productos.partials.variantes', ['numero' => '06', 'variantes' => $producto->variantes])


                    {{-- SECCIÓN 7: DOCUMENTACIÓN --}}
                    <section class="pt-8 border-t border-line">

                        <div class="flex items-center gap-3 mb-6">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-brand-blue/10 text-brand-blue text-xs font-bold">
                                07
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-ink">
                                    Documentación del producto
                                </h4>
                                <p class="text-xs text-muted">
                                    Adjunte manuales, fichas técnicas o documentos en formato TXT o Word.
                                </p>
                            </div>
                        </div>

                        {{-- Documentos existentes --}}
                        @if($producto->documentos->count() > 0)
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-muted">Documentos actuales</span>
                                <span class="text-xs text-muted">{{ $producto->documentos->count() }} archivo(s)</span>
                            </div>
                            <div class="space-y-2">
                                @foreach($producto->documentos as $documento)
                                <div class="flex items-center gap-3 p-3 rounded-lg border border-line bg-surface-2/60">
                                    <input type="checkbox" name="eliminar_documentos[]" value="{{ $documento->id_documento }}"
                                        class="mt-1 w-4 h-4 rounded border border-line bg-surface-2 text-brand-blue focus:ring-brand-blue/50 focus:ring-offset-surface">
                                    <div class="flex items-center gap-2 flex-1">
                                        <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-sm text-ink">{{ $documento->nombre_original }}</span>
                                        <span class="text-xs text-muted ml-2">({{ strtoupper($documento->extension) }})</span>
                                    </div>
                                    <a href="{{ Storage::url($documento->ruta_archivo) }}" target="_blank"
                                        class="text-xs text-brand-blue hover:text-brand-blue-dark underline">Descargar</a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Nuevos documentos --}}
                            <div class="md:col-span-2">
                                <label for="documentos" class="block text-sm font-medium text-muted mb-2">
                                    Agregar nuevos documentos <span class="text-muted">(opcional)</span>
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


                    {{-- SECCIÓN 8: REPUESTOS (solo maquinaria) --}}
                    <section class="pt-8 border-t border-line repuestos-section"
                        id="repuestos-section"
                        data-tipo-producto="{{ $producto->tipo_producto }}">

                        <div class="flex items-center gap-3 mb-6">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-brand-blue/10 text-brand-blue text-xs font-bold">
                                08
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-ink">
                                    Repuestos asociados
                                </h4>
                                <p class="text-xs text-muted">
                                    Gestionar los repuestos compatibles con esta maquinaria.
                                </p>
                            </div>
                        </div>

                        {{-- Repuestos ya asociados --}}
                        @if($producto->repuestos->count() > 0)
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-muted">Repuestos vinculados</span>
                            </div>
                            <div class="space-y-2">
                                @foreach($producto->repuestos as $repuesto)
                                <div class="flex items-center gap-3 p-3 rounded-lg border border-line bg-surface-2/60">
                                    <input type="hidden" name="repuestos[]" value="{{ $repuesto->id_producto }}">
                                    <span class="w-8 h-8 rounded-lg bg-surface-2 flex items-center justify-center text-muted text-sm font-mono">
                                        {{ strtoupper(substr($repuesto->codigo, 0, 2)) }}
                                    </span>
                                    <div class="flex-1">
                                        <span class="text-sm text-ink block">{{ $repuesto->nombre }}</span>
                                        <span class="text-xs text-muted">{{ $repuesto->codigo }} {{ $repuesto->modelo ? '- '.$repuesto->modelo : '' }}</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Agregar más repuestos --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label for="repuestos-nuevos" class="block text-sm font-medium text-muted mb-2">
                                    Agregar repuestos <span class="text-muted">(opcional)</span>
                                </label>

                                <select name="repuestos[]" id="repuestos-nuevos" multiple
                                    class="form-select h-auto min-h-[200px]">
                                    <option value="">Seleccione repuestos...</option>

                                    @php
                                        $idsExistentes = $producto->repuestos->pluck('id_producto')->toArray();
                                        $repuestosDisponibles = Modules\Catalogo\Models\Producto::where('tipo_producto', 'REPUESTO')->where('estado', 'ACTIVO')
                                            ->whereNotIn('id_producto', $idsExistentes)
                                            ->orderBy('nombre')->get();
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
                            </div>
                        </div>
                    </section>


                    {{-- SECCIÓN 9: DESCRIPCIÓN --}}
                    <section class="pt-8 border-t border-line">

                        <div class="flex items-center gap-3 mb-6">

                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-brand-blue/10 text-brand-blue text-xs font-bold">
                                07
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-ink">
                                    Descripción
                                </h4>
                                <p class="text-xs text-muted">
                                    Información complementaria del producto.
                                </p>
                            </div>

                        </div>

                        <div>

                            <label for="descripcion" class="block text-sm font-medium text-muted mb-2">
                                Descripción del producto
                            </label>

                            <textarea name="descripcion" id="descripcion" rows="4"
                                class="form-input resize-y min-h-28">{{ old('descripcion', $producto->descripcion) }}</textarea>

                            @error('descripcion')
                            <span class="form-error">{{ $message }}</span>
                            @enderror

                        </div>

                    </section>

                </div>


                {{-- BOTONES --}}
                <div class="px-6 sm:px-8 py-5 border-t border-line bg-surface-2/60">

                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-4">

                        <p class="text-xs text-muted">
                            Revise los cambios antes de guardarlos.
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

                                Guardar cambios

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


{{-- ESTILOS DEL FORMULARIO --}}
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
    const tipoSelect = document.getElementById('tipo_producto');
    const repuestosSection = document.getElementById('repuestos-section');

    function toggleRepuestos() {
        if (tipoSelect && tipoSelect.value === 'MAQUINARIA') {
            repuestosSection.classList.add('mostrar');
        } else if (repuestosSection) {
            repuestosSection.classList.remove('mostrar');
        }
    }

    if (tipoSelect) {
        tipoSelect.addEventListener('change', toggleRepuestos);
    }
    toggleRepuestos();
</script>

@endsection
