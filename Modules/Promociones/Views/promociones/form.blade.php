@extends('layouts.app')

@section('title', $promocion->exists ? 'Editar Promoción' : 'Nueva Promoción')

@php
    $esEdicion = $promocion->exists;
    // old('variantes') llega como array del formulario; normalize a array de ints.
    $variantesSeleccionadas = array_map(
        'intval',
        (array) old('variantes', $variantesPromocion->pluck('id_variante')->all())
    );
    $campo = 'w-full px-3 py-2 rounded-xl bg-surface-2 border border-line text-ink text-xs focus:border-brand-green/50 focus:ring-brand-green/20';
    $etiqueta = 'block text-[11px] font-semibold uppercase tracking-wider text-muted mb-1.5';
    $error = 'mt-1 block text-[11px] text-red-500';
@endphp

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 text-ink space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-ink">{{ $esEdicion ? 'Editar Promoción' : 'Nueva Promoción' }}</h2>
            <p class="text-xs text-muted mt-0.5">
                {{ $esEdicion ? '«'.$promocion->nombre.'»' : 'El descuento se precargará en ventas y cotizaciones mientras la promoción esté vigente' }}
            </p>
        </div>

        <a href="{{ route('promociones.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-surface text-ink hover:text-brand-green text-xs font-semibold rounded-xl border border-line transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Volver</span>
        </a>
    </div>

    @if ($errors->any())
    <div class="rounded-xl border border-red-500/40 bg-red-500/20 px-5 py-4 text-xs text-red-500">
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
        <div class="bg-surface border border-line rounded-2xl p-5 shadow-soft space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-blue">Datos de la promoción</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nombre" class="{{ $etiqueta }}">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $promocion->nombre) }}" maxlength="120" required
                        placeholder="Ej.: liquidación de accesorios…" class="{{ $campo }}">
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
                    <span class="{{ $etiqueta }}">Tipo de descuento <span class="text-red-500">*</span></span>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl border cursor-pointer text-xs font-semibold transition
                            border border-line bg-surface-2 text-muted"
                            :class="tipo === 'PCT' ? '!border-brand-blue !bg-brand-blue/20 !text-brand-blue' : 'hover:border-brand-blue/40'">
                            <input type="radio" name="tipo_descuento" value="PCT" x-model="tipo" class="sr-only">
                            Porcentaje %
                        </label>
                        <label class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl border cursor-pointer text-xs font-semibold transition
                            border border-line bg-surface-2 text-muted"
                            :class="tipo === 'USD' ? '!border-brand-blue !bg-brand-blue/20 !text-brand-blue' : 'hover:border-brand-blue/40'">
                            <input type="radio" name="tipo_descuento" value="USD" x-model="tipo" class="sr-only">
                            USD fijos
                        </label>
                    </div>
                    @error('tipo_descuento') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="valor_descuento" class="{{ $etiqueta }}">
                        <span x-show="tipo === 'PCT'">Descuento (%) <span class="text-red-500">*</span></span>
                        <span x-show="tipo === 'USD'" x-cloak>Descuento (USD por unidad) <span class="text-red-500">*</span></span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-muted" x-text="tipo === 'PCT' ? '%' : '$'"></span>
                        <input type="number" id="valor_descuento" name="valor_descuento" x-model="valor" min="0.01" max="999999.99" step="0.01" required
                            class="{{ $campo }} pl-8" :max="tipo === 'PCT' ? '100' : '999999.99'">
                    </div>
                    <p class="mt-1 text-[10px] text-muted" x-show="tipo === 'USD'" x-cloak>
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
                    <label for="fecha_inicio" class="{{ $etiqueta }}">Fecha de inicio <span class="text-red-500">*</span></label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', $promocion->fecha_inicio?->format('Y-m-d')) }}" required
                        class="{{ $campo }}">
                    @error('fecha_inicio') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="fecha_fin" class="{{ $etiqueta }}">Fecha de fin</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="{{ old('fecha_fin', $promocion->fecha_fin?->format('Y-m-d')) }}"
                        min="{{ old('fecha_inicio', $promocion->fecha_inicio?->format('Y-m-d')) }}"
                        class="{{ $campo }}">
                    <p class="mt-1 text-[10px] text-muted">Si la dejas vacía, la promoción queda abierta hasta desactivarla.</p>
                    @error('fecha_fin') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Selección de variantes -->
        <div class="bg-surface border border-line rounded-2xl p-5 shadow-soft space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-blue">Variantes promocionadas</h3>
                <p class="text-[11px] text-muted">
                    <span class="font-mono font-bold text-brand-blue" x-text="seleccionadas.length"></span>
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
            <div class="max-h-72 overflow-y-auto rounded-xl border border-line bg-surface-2/60 divide-y divide-line">
                <template x-for="v in variantesVisibles()" :key="v.id_variante">
                    <label class="flex items-center justify-between gap-3 px-4 py-2.5 cursor-pointer hover:bg-surface-2 transition-colors"
                        :class="seleccionadas.includes(v.id_variante) ? 'bg-brand-blue/20' : ''">
                        <span class="flex items-center gap-3 min-w-0">
                            <input type="checkbox"
                                name="variantes[]"
                                :value="String(v.id_variante)"
                                :checked="seleccionadas.includes(v.id_variante)"
                                @change="alternar(v.id_variante)"
                                class="w-4 h-4 rounded border border-line bg-surface-2 text-brand-blue focus:border-brand-green/50 focus:ring-brand-green/20">
                            <span class="min-w-0">
                                <span class="block font-mono text-xs font-semibold text-ink" x-text="v.sku"></span>
                                <span class="block text-[11px] text-muted truncate" x-text="v.producto + (v.color ? ' · ' + v.color : '')"></span>
                            </span>
                        </span>

                        <span class="shrink-0 text-right">
                            <span class="block text-xs font-mono text-brand-green" x-text="v.precio ? 'USD ' + Number(v.precio).toFixed(2) : 'sin precio'"></span>
                            <span class="block text-[10px] font-mono text-brand-blue"
                                x-show="precioFinal(v) !== null"
                                x-text="'con promo: USD ' + Number(precioFinal(v)).toFixed(2)"></span>
                        </span>
                    </label>
                </template>

                <p x-show="variantesVisibles().length === 0" class="px-4 py-6 text-center text-[11px] text-muted">
                    Elige un producto o busca por SKU para ver sus variantes.
                </p>
            </div>

            <p class="text-[10px] text-muted">
                Regla: una variante no puede tener dos promociones <span class="font-semibold text-muted">ACTIVAS</span> con fechas solapadas
                (la base de datos lo impide).
            </p>
        </div>

        <!-- Acciones -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('promociones.index') }}"
                class="px-4 py-2.5 rounded-xl border border-line bg-surface text-ink hover:text-brand-green text-xs font-semibold transition-colors cursor-pointer">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-sm transition-all active:scale-[0.99] cursor-pointer border border-brand-green/50">
                {{ $esEdicion ? 'Guardar cambios' : 'Crear promoción' }}
            </button>
        </div>
    </form>
</div>
@endsection
