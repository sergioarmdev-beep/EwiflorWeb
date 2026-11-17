@extends('layouts.app')

@section('title', $combo->exists ? 'Editar Combo' : 'Nuevo Combo')

@php
    $esEdicion = $combo->exists;
    $componentesIniciales = old('componentes', $componentes->map(fn ($c) => [
        'id_componente' => $c->id_componente,
        'cantidad' => $c->cantidad,
    ])->values());
    $campo = 'w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-xl text-slate-100 text-xs focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all';
    $etiqueta = 'block text-[11px] font-semibold uppercase tracking-wider text-slate-300 mb-1.5';
    $error = 'mt-1 block text-[11px] text-red-400';
@endphp

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 text-slate-100 space-y-6" style="--combo-acento: #38bdf8; --combo-suave: rgba(56, 189, 248, 0.12);">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-white">{{ $esEdicion ? 'Editar Combo' : 'Nuevo Combo' }}</h2>
            <p class="text-xs text-slate-300 mt-0.5">
                El combo se vende como producto normal; los componentes son informativos
            </p>
        </div>

        <a href="{{ route('promociones.combos.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 text-slate-300 hover:text-white text-xs font-semibold rounded-xl border border-slate-700 transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Volver</span>
        </a>
    </div>

    @if ($errors->any())
    <div class="bg-red-950/60 border border-red-500/40 rounded-2xl px-5 py-4 text-xs text-red-200">
        <p class="font-bold mb-1">Revisa los siguientes errores:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" enctype="multipart/form-data"
        action="{{ $esEdicion ? route('promociones.combos.update', $combo->id_producto) : route('promociones.combos.store') }}"
        x-data="comboForm({ componentes: @js($componentesIniciales), disponibles: @js($componentesDisponibles) })"
        class="space-y-6">

        @csrf
        @if($esEdicion)
        @method('PUT')
        @endif

        <!-- Datos del producto -->
        <div class="bg-slate-900 border border-sky-500/40 rounded-2xl p-5 shadow-xl space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-sky-300">Datos del combo</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="codigo" class="{{ $etiqueta }}">Código <span class="text-sky-400">*</span></label>
                    <input type="text" id="codigo" name="codigo" value="{{ old('codigo', $combo->codigo) }}" maxlength="30" required
                        placeholder="Ej.: COMBO-001" class="{{ $campo }}">
                    <p class="mt-1 text-[10px] text-slate-500">El SKU del combo se genera a partir de este código.</p>
                    @error('codigo') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="nombre" class="{{ $etiqueta }}">Nombre <span class="text-sky-400">*</span></label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $combo->nombre) }}" maxlength="150" required
                        placeholder="Ej.: Kit panadero inicial" class="{{ $campo }}">
                    @error('nombre') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="descripcion" class="{{ $etiqueta }}">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="2" maxlength="1000"
                    placeholder="Opcional" class="{{ $campo }}">{{ old('descripcion', $combo->descripcion) }}</textarea>
                @error('descripcion') <span class="{{ $error }}">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label for="categoria" class="{{ $etiqueta }}">Categoría <span class="text-sky-400">*</span></label>
                    <select id="categoria" name="categoria" required class="{{ $campo }}">
                        @foreach($categorias as $cat)
                        <option value="{{ $cat }}" {{ (string) old('categoria', $combo->categoria) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                    @error('categoria') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="id_marca" class="{{ $etiqueta }}">Marca <span class="text-sky-400">*</span></label>
                    <select id="id_marca" name="id_marca" required class="{{ $campo }}">
                        <option value="">—</option>
                        @foreach($marcas as $marca)
                        <option value="{{ $marca->id_marca }}" {{ (string) old('id_marca', $combo->id_marca) === (string) $marca->id_marca ? 'selected' : '' }}>{{ $marca->nombre }}</option>
                        @endforeach
                    </select>
                    @error('id_marca') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="modelo" class="{{ $etiqueta }}">Modelo</label>
                    <input type="text" id="modelo" name="modelo" value="{{ old('modelo', $combo->modelo) }}" maxlength="60" class="{{ $campo }}">
                    @error('modelo') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="precio_usd" class="{{ $etiqueta }}">Precio promocional (USD) <span class="text-sky-400">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400">$</span>
                        <input type="number" id="precio_usd" name="precio_usd" value="{{ old('precio_usd', $precioUsd ?? '') }}" min="0.01" step="0.01" required
                            class="{{ $campo }} pl-8">
                    </div>
                    <p class="mt-1 text-[10px] text-slate-500">Es el precio de venta del combo completo.</p>
                    @error('precio_usd') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="modalidad" class="{{ $etiqueta }}">Modalidad <span class="text-sky-400">*</span></label>
                    <select id="modalidad" name="modalidad" required class="{{ $campo }}">
                        <option value="INVENTARIABLE" {{ old('modalidad', $combo->modalidad) === 'INVENTARIABLE' ? 'selected' : '' }}>Inventariable</option>
                        <option value="BAJO_PEDIDO" {{ old('modalidad', $combo->modalidad) === 'BAJO_PEDIDO' ? 'selected' : '' }}>Bajo pedido</option>
                    </select>
                    @error('modalidad') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="stock_minimo" class="{{ $etiqueta }}">Stock mínimo</label>
                    <input type="number" id="stock_minimo" name="stock_minimo" value="{{ old('stock_minimo', $combo->stock_minimo ?? 0) }}" min="0" required class="{{ $campo }}">
                    @error('stock_minimo') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="garantia_meses" class="{{ $etiqueta }}">Garantía (meses)</label>
                    <input type="number" id="garantia_meses" name="garantia_meses" value="{{ old('garantia_meses', $combo->garantia_meses ?? 0) }}" min="0" required class="{{ $campo }}">
                    @error('garantia_meses') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
            </div>

            <label class="inline-flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                <input type="checkbox" name="es_publico" value="1" {{ old('es_publico', $combo->es_publico ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-sky-500 focus:ring-sky-500 focus:ring-offset-slate-900">
                Visible en el catálogo público
            </label>
        </div>

        <!-- Componentes -->
        <div class="bg-slate-900 border border-sky-500/40 rounded-2xl p-5 shadow-xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-sky-300">Productos incluidos</h3>
                <p class="text-[11px] text-slate-400">Mínimo dos productos; no se descuentan del inventario</p>
            </div>

            @error('componentes') <span class="{{ $error }}">{{ $message }}</span> @enderror

            <div class="space-y-2">
                <template x-for="(fila, i) in componentes" :key="i">
                    <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_auto] gap-3 items-center">
                        <select x-model="fila.id_componente" required
                            :name="'componentes[' + i + '][id_componente]'"
                            class="{{ $campo }}">
                            <option value="">— Selecciona producto —</option>
                            <template x-for="p in disponibles" :key="String(p.id_producto)">
                                <option :value="String(p.id_producto)" x-text="p.nombre + (p.modelo ? ' · ' + p.modelo : '')"></option>
                            </template>
                        </select>

                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-semibold uppercase text-slate-400">Cant.</span>
                            <input type="number" x-model.number="fila.cantidad" min="1" max="999" required
                                :name="'componentes[' + i + '][cantidad]'"
                                class="w-20 px-3 py-2 bg-slate-800 border border-slate-600 rounded-xl text-slate-100 text-xs text-center focus:outline-none focus:border-sky-400">
                        </div>

                        <button type="button" @click="quitar(i)" title="Quitar componente"
                            class="justify-self-end px-2.5 py-2 text-red-400 hover:text-red-300 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </template>

                <p x-show="componentes.length === 0" class="px-4 py-4 text-center text-[11px] text-slate-500 border border-dashed border-slate-700 rounded-xl">
                    Aún no agregaste productos al combo.
                </p>
            </div>

            <button type="button" @click="agregar()" :disabled="componentes.length >= 50"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-sky-500/50 bg-sky-950/40 text-sky-300 hover:bg-sky-950/70 text-xs font-semibold transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Agregar componente
            </button>
        </div>

        <!-- Galería -->
        <div class="bg-slate-900 border border-sky-500/40 rounded-2xl p-5 shadow-xl space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-sky-300">Galería</h3>

            @if($esEdicion)
            @include('catalogo::productos.partials.galeria-edicion', ['producto' => $combo])
            @endif

            <div>
                <label for="imagenes" class="{{ $etiqueta }}">Agregar imágenes</label>
                <input type="file" id="imagenes" name="imagenes[]" accept="image/jpeg,image/png,image/webp" multiple
                    class="w-full text-xs text-slate-400 file:mr-3 file:px-4 file:py-2 file:rounded-xl file:border-0 file:bg-sky-600 file:text-white file:text-xs file:font-semibold file:cursor-pointer hover:file:bg-sky-500">
                <p class="mt-1 text-[10px] text-slate-500">JPG, PNG o WEBP, hasta 2 MB por imagen (máx. 10 por vez).</p>
                @error('imagenes.*') <span class="{{ $error }}">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Acciones -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('promociones.combos.index') }}"
                class="px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-900 text-slate-300 hover:text-white text-xs font-semibold transition-colors cursor-pointer">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow-md transition-all active:scale-[0.99] cursor-pointer border border-sky-400">
                {{ $esEdicion ? 'Guardar cambios' : 'Crear combo' }}
            </button>
        </div>
    </form>
</div>
@endsection
