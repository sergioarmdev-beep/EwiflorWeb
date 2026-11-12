@extends('layouts.app')

@section('title', 'Registrar Nuevo Producto')

@section('content')

<div class="min-h-screen bg-slate-950 text-slate-100 py-10 px-4 sm:px-6 lg:px-8">

    <div class="max-w-5xl mx-auto">

        {{-- ENCABEZADO --}}
        <div class="mb-8">
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-3">
                <span>Catálogo</span>
                <span>/</span>
                <span class="text-sky-400">Nuevo producto</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-11 h-11 rounded-xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center text-sky-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                        </div>

                        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                            Registrar producto
                        </h2>
                    </div>

                    <p class="text-sm text-slate-400 max-w-xl">
                        Complete la información general, clasificación y características
                        del nuevo producto para incorporarlo al catálogo.
                    </p>
                </div>

                <a href="{{ route('catalogo.productos.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-900 text-slate-300 hover:text-white hover:border-sky-500/50 transition duration-200 text-sm font-semibold">

                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7 7-7M3 12h18" />
                    </svg>

                    Volver al catálogo
                </a>

            </div>
        </div>


        {{-- FORMULARIO --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl shadow-black/20 overflow-hidden">

            {{-- CABECERA DEL FORMULARIO --}}
            <div class="px-6 sm:px-8 py-5 border-b border-slate-800 bg-slate-900/80">
                <div class="flex items-center gap-3">

                    <div class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-base font-bold text-white">
                            Información del producto
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
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
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-sky-500/10 text-sky-400 text-xs font-bold">
                                01
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-white">
                                    Identificación
                                </h4>
                                <p class="text-xs text-slate-500">
                                    Datos principales para reconocer el producto.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Código --}}
                            <div>
                                <label for="codigo" class="block text-sm font-medium text-slate-300 mb-2">
                                    Código <span class="text-rose-400">*</span>
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
                                <label for="nombre" class="block text-sm font-medium text-slate-300 mb-2">
                                    Nombre del producto <span class="text-rose-400">*</span>
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
                                <label for="id_marca" class="block text-sm font-medium text-slate-300 mb-2">
                                    Marca <span class="text-rose-400">*</span>
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
                                <label for="modelo" class="block text-sm font-medium text-slate-300 mb-2">
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
                    <section class="pt-8 border-t border-slate-800">

                        <div class="flex items-center gap-3 mb-6">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-sky-500/10 text-sky-400 text-xs font-bold">
                                02
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-white">
                                    Clasificación del producto
                                </h4>
                                <p class="text-xs text-slate-500">
                                    Defina cómo se organiza y comercializa el producto.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Categoría --}}
                            <div>
                                <label for="categoria" class="block text-sm font-medium text-slate-300 mb-2">
                                    Categoría <span class="text-rose-400">*</span>
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
                                <label for="tipo_producto" class="block text-sm font-medium text-slate-300 mb-2">
                                    Tipo de producto <span class="text-rose-400">*</span>
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

                                <label for="modalidad" class="block text-sm font-medium text-slate-300 mb-2">
                                    Modalidad <span class="text-rose-400">*</span>
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

                                <p class="text-xs text-slate-500 mt-2">
                                    Seleccione si el producto se gestiona mediante inventario
                                    o se comercializa bajo pedido.
                                </p>
                            </div>

                        </div>
                    </section>


                    {{-- SECCIÓN 3: CONTROL Y GARANTÍA --}}
                    <section class="pt-8 border-t border-slate-800">

                        <div class="flex items-center gap-3 mb-6">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-sky-500/10 text-sky-400 text-xs font-bold">
                                03
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-white">
                                    Control y garantía
                                </h4>
                                <p class="text-xs text-slate-500">
                                    Parámetros iniciales para la gestión del producto.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Stock mínimo --}}
                            <div>
                                <label for="stock_minimo" class="block text-sm font-medium text-slate-300 mb-2">
                                    Stock mínimo <span class="text-rose-400">*</span>
                                </label>

                                <div class="relative">
                                    <input type="number" name="stock_minimo" id="stock_minimo"
                                        value="{{ old('stock_minimo', 0) }}" min="0" required
                                        class="form-input pr-20">

                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-500 pointer-events-none">
                                        Unidades
                                    </span>
                                </div>

                                @error('stock_minimo')
                                <span class="form-error">{{ $message }}</span>
                                @enderror

                                <p class="text-xs text-slate-500 mt-2">
                                    Cantidad mínima de referencia para el control de existencias.
                                </p>
                            </div>


                            {{-- Garantía --}}
                            <div>
                                <label for="garantia_meses" class="block text-sm font-medium text-slate-300 mb-2">
                                    Garantía <span class="text-rose-400">*</span>
                                </label>

                                <div class="relative">
                                    <input type="number" name="garantia_meses" id="garantia_meses"
                                        value="{{ old('garantia_meses', 0) }}" min="0" required
                                        class="form-input pr-20">

                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-500 pointer-events-none">
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
                    <section class="pt-8 border-t border-slate-800">

                        <div class="flex items-center gap-3 mb-6">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-sky-500/10 text-sky-400 text-xs font-bold">
                                04
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-white">
                                    Imágenes del producto
                                </h4>
                                <p class="text-xs text-slate-500">
                                    La primera imagen será la principal; podrá cambiarla luego al editar.
                                </p>
                            </div>
                        </div>

                        @include('catalogo::productos.partials.galeria-subida', ['etiqueta' => 'Agregar imágenes', 'esNuevoProducto' => 'true'])

                    </section>


                    {{-- SECCIÓN 5: VARIANTES --}}
                    @include('catalogo::productos.partials.variantes', ['numero' => '05'])


                    {{-- SECCIÓN 6: DESCRIPCIÓN --}}
                    <section class="pt-8 border-t border-slate-800">

                        <div class="flex items-center gap-3 mb-6">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-sky-500/10 text-sky-400 text-xs font-bold">
                                06
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-white">
                                    Descripción
                                </h4>
                                <p class="text-xs text-slate-500">
                                    Información adicional sobre el producto.
                                </p>
                            </div>
                        </div>

                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-slate-300 mb-2">
                                Descripción del producto
                            </label>

                            <textarea name="descripcion" id="descripcion" rows="4"
                                placeholder="Escriba una descripción del producto, sus características o información relevante..."
                                class="form-input resize-y min-h-28">{{ old('descripcion') }}</textarea>
                        </div>

                    </section>


                    {{-- SECCIÓN 7: VISIBILIDAD --}}
                    <section class="pt-8 border-t border-slate-800">

                        <div class="flex items-center gap-3 mb-6">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-sky-500/10 text-sky-400 text-xs font-bold">
                                07
                            </span>

                            <div>
                                <h4 class="text-sm font-bold text-white">
                                    Visibilidad del producto
                                </h4>
                                <p class="text-xs text-slate-500">
                                    Configure la publicación en el catálogo público.
                                </p>
                            </div>
                        </div>

                        <label for="es_publico"
                            class="flex items-start gap-4 p-4 rounded-xl border border-slate-700 bg-slate-950/50 cursor-pointer hover:border-sky-500/40 transition duration-200">

                            <input type="checkbox" name="es_publico" id="es_publico" value="1"
                                {{ old('es_publico', true) ? 'checked' : '' }}
                                class="mt-1 w-4 h-4 rounded border-slate-600 bg-slate-800 text-sky-500 focus:ring-sky-500 focus:ring-offset-slate-900">

                            <div>
                                <span class="block text-sm font-semibold text-slate-200">
                                    Mostrar en el catálogo público
                                </span>

                                <span class="block text-xs text-slate-500 mt-1 leading-relaxed">
                                    Al activar esta opción, el producto podrá mostrarse
                                    en el catálogo web público.
                                </span>
                            </div>

                        </label>

                    </section>

                </div>


                {{-- ACCIONES --}}
                <div class="px-6 sm:px-8 py-5 border-t border-slate-800 bg-slate-950/50">

                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-4">

                        <p class="text-xs text-slate-500">
                            Verifique la información antes de guardar.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-3">

                            <a href="{{ route('catalogo.productos.index') }}"
                                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-slate-700 bg-slate-900 text-slate-300 hover:bg-slate-800 hover:text-white transition duration-200 text-sm font-semibold">
                                Cancelar
                            </a>

                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-500 hover:to-blue-500 text-white font-bold text-sm shadow-lg shadow-blue-950/30 transition duration-200">

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

        <p class="text-center text-xs text-slate-600 mt-6">
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
        border: 1px solid #334155;
        border-radius: 10px;
        outline: none;
        background-color: #020617;
        color: #e2e8f0;
        font-size: .875rem;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .form-input::placeholder {
        color: #64748b;
    }

    .form-input:focus,
    .form-select:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, .12);
    }

    .form-select {
        cursor: pointer;
    }

    .form-select option {
        background: #0f172a;
        color: #e2e8f0;
    }

    .form-error {
        display: block;
        margin-top: 7px;
        color: #fb7185;
        font-size: .75rem;
    }

    input[type="file"]::file-selector-button {
        cursor: pointer;
    }
</style>

@endsection