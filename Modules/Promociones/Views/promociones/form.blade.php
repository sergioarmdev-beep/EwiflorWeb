@extends('layouts.app')

@section('title', $promocion->exists ? 'Editar Promoción' : 'Nueva Promoción')

@php
    $esEdicion = $promocion->exists;
    // old('variantes') llega como array del formulario; normalize a array de ints.
    $variantesSeleccionadas = array_map(
        'intval',
        (array) old('variantes', $variantesPromocion->pluck('id_variante')->all())
    );
    $campo = 'w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-xl text-slate-100 text-xs focus:outline-none focus:border-fuchsia-400 focus:ring-2 focus:ring-fuchsia-500/30 transition-all';
    $etiqueta = 'block text-[11px] font-semibold uppercase tracking-wider text-slate-300 mb-1.5';
    $error = 'mt-1 block text-[11px] text-red-400';
@endphp

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 text-slate-100 space-y-6" style="--promo-acento: #e879f9; --promo-suave: rgba(232, 121, 249, 0.12);">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-white">{{ $esEdicion ? 'Editar Promoción' : 'Nueva Promoción' }}</h2>
            <p class="text-xs text-slate-300 mt-0.5">
                {{ $esEdicion ? '«'.$promocion->nombre.'»' : 'El descuento se precargará en ventas y cotizaciones mientras la promoción esté vigente' }}
            </p>
        </div>

        <a href="{{ route('promociones.index') }}"
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

    <form method="POST" action="{{ $esEdicion ? route('promociones.update', $promocion->id_promocion) : route('promociones.store') }}"
        x-data="promocionForm({
            items: @js($variantesBusqueda),
            seleccionadas: @js($variantesSeleccionadas),
            tipo: @js(old('tipo_descuento', $promocion->tipo_descuento ?? 'PCT')),
            valor: @js(old('valor_descuento', $promocion->valor_descuento ?? '')),
        })"
        class="space-y-6">

        @csrf
        @if($esEdicion)
        @method('PUT')
        @endif

        <!-- Datos generales -->
        <div class="bg-slate-900 border border-fuchsia-500/40 rounded-2xl p-5 shadow-xl space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-fuchsia-300">Datos de la promoción</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nombre" class="{{ $etiqueta }}">Nombre <span class="text-fuchsia-400">*</span></label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $promocion->nombre) }}" maxlength="120" required
                        placeholder="Ej.:liquidación de accesorios…" class="{{ $campo }}">
                    @error('nombre') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="descripcion" class="{{ $etiqueta }}">Descripción</label>
                    <input type="text" id="descripcion" name="descripcion" value="{{ old('descripcion', $promocion->descripcion) }}" maxlength="250"
                        placeholder="Opcional" class="{{ $campo }}">
                    @error('descripcion') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <span class="{{ $etiqueta }}">Tipo de descuento <span class="text-fuchsia-400">*</span></span>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl border cursor-pointer text-xs font-semibold transition
                            border-slate-600 bg-slate-800 text-slate-300"
                            :class="tipo === 'PCT' ? '!border-fuchsia-400 !bg-fuchsia-950/60 !text-fuchsia-200' : 'hover:border-slate-500'">
                            <input type="radio" name="tipo_descuento" value="PCT" x-model="tipo" class="sr-only">
                            Porcentaje %
                        </label>
                        <label class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl border cursor-pointer text-xs font-semibold transition
                            border-slate-600 bg-slate-800 text-slate-300"
                            :class="tipo === 'USD' ? '!border-fuchsia-400 !bg-fuchsia-950/60 !text-fuchsia-200' : 'hover:border-slate-500'">
                            <input type="radio" name="tipo_descuento" value="USD" x-model="tipo" class="sr-only">
                            USD fijos
                        </label>
                    </div>
                    @error('tipo_descuento') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="valor_descuento" class="{{ $etiqueta }}">
                        <span x-show="tipo === 'PCT'">Descuento (%) <span class="text-fuchsia-400">*</span></span>
                        <span x-show="tipo === 'USD'" x-cloak>Descuento (USD por unidad) <span class="text-fuchsia-400">*</span></span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400" x-text="tipo === 'PCT' ? '%' : '$'"></span>
                        <input type="number" id="valor_descuento" name="valor_descuento" x-model="valor" min="0.01" max="999999.99" step="0.01" required
                            class="{{ $campo }} pl-8" :max="tipo === 'PCT' ? '100' : '999999.99'">
                    </div>
                    <p class="mt-1 text-[10px] text-slate-500" x-show="tipo === 'USD'" x-cloak>
                        Se convierte a % sobre el precio de cada variante al vender.
                    </p>
                    @error('valor_descuento') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="estado" class="{{ $etiqueta }}">Estado</label>
                    <select id="estado" name="estado" class="{{ $campo }}">
                        <option value="ACTIVA" {{ old('estado', $promocion->estado ?? 'ACTIVA') === 'ACTIVA' ? 'selected' : '' }}>Activa</option>
                        <option value="INACTIVA" {{ old('estado', $promocion->estado ?? 'ACTIVA') === 'INACTIVA' ? 'selected' : '' }}>Inactiva</option>
                    </select>
                    @error('estado') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="fecha_inicio" class="{{ $etiqueta }}">Fecha de inicio <span class="text-fuchsia-400">*</span></label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', $promocion->fecha_inicio?->format('Y-m-d')) }}" required
                        class="{{ $campo }}">
                    @error('fecha_inicio') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="fecha_fin" class="{{ $etiqueta }}">Fecha de fin</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="{{ old('fecha_fin', $promocion->fecha_fin?->format('Y-m-d')) }}"
                        min="{{ old('fecha_inicio', $promocion->fecha_inicio?->format('Y-m-d')) }}"
                        class="{{ $campo }}">
                    <p class="mt-1 text-[10px] text-slate-500">Si la dejas vacía, la promoción queda abierta hasta desactivarla.</p>
                    @error('fecha_fin') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Selección de variantes -->
        <div class="bg-slate-900 border border-fuchsia-500/40 rounded-2xl p-5 shadow-xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-fuchsia-300">Variantes promocionadas</h3>
                <p class="text-[11px] text-slate-400">
                    <span class="font-mono font-bold text-fuchsia-300" x-text="seleccionadas.length"></span>
                    variante(s) seleccionada(s)
                </p>
            </div>

            @error('variantes') <span class="{{ $error }}">{{ $message }}</span> @enderror

            <!-- Picker de producto -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="picker_producto" class="{{ $etiqueta }}">Producto</label>
                    <select id="picker_producto" x-model="productoSel" class="{{ $campo }}">
                        <option value="">— Todos los productos —</option>
                        <template x-for="p in productos()" :key="p.id">
                            <option :value="String(p.id)" x-text="p.nombre"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label for="buscar_variante" class="{{ $etiqueta }}">Filtrar por SKU</label>
                    <input type="search" id="buscar_variante" x-model.trim="busqueda" maxlength="60"
                        placeholder="SKU o color…" class="{{ $campo }}">
                </div>
            </div>

            <!-- Listado de variantes del producto elegido -->
            <div class="max-h-72 overflow-y-auto rounded-xl border border-slate-700 bg-slate-950/60 divide-y divide-slate-800/70">
                <template x-for="v in variantesVisibles()" :key="v.id_variante">
                    <label class="flex items-center justify-between gap-3 px-4 py-2.5 cursor-pointer hover:bg-slate-800/40 transition-colors"
                        :class="seleccionadas.includes(v.id_variante) ? 'bg-fuchsia-950/20' : ''">
                        <span class="flex items-center gap-3 min-w-0">
                            <input type="checkbox"
                                name="variantes[]"
                                :value="String(v.id_variante)"
                                :checked="seleccionadas.includes(v.id_variante)"
                                @change="alternar(v.id_variante)"
                                class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-fuchsia-500 focus:ring-fuchsia-500">
                            <span class="min-w-0">
                                <span class="block font-mono text-xs font-semibold text-slate-100" x-text="v.sku"></span>
                                <span class="block text-[11px] text-slate-400 truncate" x-text="v.producto + (v.color ? ' · ' + v.color : '')"></span>
                            </span>
                        </span>

                        <span class="shrink-0 text-right">
                            <span class="block text-xs font-mono text-emerald-400" x-text="v.precio ? 'USD ' + Number(v.precio).toFixed(2) : 'sin precio'"></span>
                            <span class="block text-[10px] font-mono text-fuchsia-300"
                                x-show="precioFinal(v) !== null"
                                x-text="'con promo: USD ' + Number(precioFinal(v)).toFixed(2)"></span>
                        </span>
                    </label>
                </template>

                <p x-show="variantesVisibles().length === 0" class="px-4 py-6 text-center text-[11px] text-slate-500">
                    Elige un producto o busca por SKU para ver sus variantes.
                </p>
            </div>

            <p class="text-[10px] text-slate-500">
                Regla: una variante no puede tener dos promociones <span class="font-semibold text-slate-300">ACTIVAS</span> con fechas solapadas
                (la base de datos lo impide).
            </p>
        </div>

        <!-- Acciones -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('promociones.index') }}"
                class="px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-900 text-slate-300 hover:text-white text-xs font-semibold transition-colors cursor-pointer">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2.5 bg-fuchsia-600 hover:bg-fuchsia-500 text-white text-xs font-bold rounded-xl shadow-md transition-all active:scale-[0.99] cursor-pointer border border-fuchsia-400">
                {{ $esEdicion ? 'Guardar cambios' : 'Crear promoción' }}
            </button>
        </div>
    </form>
</div>
@endsection
