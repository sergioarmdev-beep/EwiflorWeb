@extends('layouts.app')

@section('title', 'Stock de ' . $producto->nombre)

@section('content')
@php
    $esAdmin = in_array(Auth::user()->nombre_rol, ['GERENTE', 'ADMINISTRADOR']);
    $inventariable = $producto->modalidad === 'INVENTARIABLE';
    $stockBajo = $inventariable && $totales['disponible'] <= (int) $producto->stock_minimo;
@endphp

<div class="min-h-screen bg-slate-950 text-slate-100 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto space-y-8">

        {{-- ENCABEZADO --}}
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-3">
                <a href="{{ route('catalogo.productos.index') }}" class="hover:text-sky-400 transition">Catálogo</a>
                <span>/</span>
                <span class="text-sky-400">Stock</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-11 h-11 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Stock del producto</h2>
                    </div>
                    <p class="text-sm text-slate-400">
                        <span class="font-mono text-slate-300">{{ $producto->codigo }}</span>
                        · {{ $producto->nombre }}
                        @if($producto->marca) · {{ $producto->marca->nombre }} @endif
                    </p>
                </div>

                <a href="{{ route('catalogo.productos.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-900 text-slate-300 hover:text-white hover:border-sky-500/50 transition duration-200 text-sm font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7M3 12h18" />
                    </svg>
                    Volver al catálogo
                </a>
            </div>
        </div>

        @unless($inventariable)
        <div class="rounded-xl border border-dashed border-slate-700 bg-slate-950/50 px-6 py-8 text-center text-xs text-slate-400">
            Este producto es <span class="font-semibold text-slate-200">bajo pedido</span> y no maneja stock.
            Para controlar existencias cambie su modalidad a «Inventariable» desde la edición del producto.
        </div>
        @else

        {{-- RESUMEN --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-slate-900 border border-sky-500/40 rounded-2xl px-5 py-4 shadow-xl">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Stock total</p>
                <p class="mt-1 text-2xl font-bold text-white font-mono">{{ $totales['total'] }}</p>
            </div>

            <div class="bg-slate-900 border border-sky-500/40 rounded-2xl px-5 py-4 shadow-xl">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Reservado</p>
                <p class="mt-1 text-2xl font-bold text-sky-300 font-mono">{{ $totales['reservado'] }}</p>
            </div>

            <div class="bg-slate-900 border {{ $stockBajo ? 'border-amber-500/50' : 'border-sky-500/40' }} rounded-2xl px-5 py-4 shadow-xl">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Disponible</p>
                <p class="mt-1 text-2xl font-bold font-mono {{ $stockBajo ? 'text-amber-300' : 'text-emerald-300' }}">{{ $totales['disponible'] }}</p>
            </div>

            <div class="bg-slate-900 border border-sky-500/40 rounded-2xl px-5 py-4 shadow-xl">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Stock mínimo</p>
                <p class="mt-1 text-2xl font-bold text-slate-200 font-mono">{{ $producto->stock_minimo }}</p>
            </div>
        </div>

        @if($stockBajo)
        <div class="flex items-center gap-2 rounded-xl border border-amber-500/30 bg-amber-950/40 px-4 py-3 text-xs text-amber-300">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
            El stock disponible ({{ $totales['disponible'] }}) está en o por debajo del mínimo ({{ $producto->stock_minimo }}). Conviene reponer.
        </div>
        @endif

        {{-- 01 · EXISTENCIAS ACTUALES --}}
        <section class="bg-slate-900 border border-sky-500/40 rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-800">
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-sky-500/10 text-sky-400 text-xs font-bold">01</span>
                <div>
                    <h4 class="text-sm font-bold text-white">Existencias actuales</h4>
                    <p class="text-xs text-slate-500">Cantidad por variante y almacén.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800 text-sm text-left">
                    <thead class="bg-slate-950/50 text-slate-400 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-5 font-semibold">SKU</th>
                            <th class="py-3.5 px-5 font-semibold">Color</th>
                            <th class="py-3.5 px-5 font-semibold">Almacén</th>
                            <th class="py-3.5 px-5 font-semibold">Sucursal</th>
                            <th class="py-3.5 px-5 text-center font-semibold">Total</th>
                            <th class="py-3.5 px-5 text-center font-semibold">Reservado</th>
                            <th class="py-3.5 px-5 text-center font-semibold">Disponible</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300 text-xs">
                        @forelse($existencias as $fila)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-5 font-mono text-slate-400 font-medium">{{ $fila->sku }}</td>
                            <td class="py-3.5 px-5">{{ $fila->color ?: '—' }}</td>
                            <td class="py-3.5 px-5 text-slate-100 font-semibold">
                                {{ $fila->almacen }}
                                @if($fila->ubicacion_interna)
                                <span class="block text-[10px] font-normal text-slate-500">{{ $fila->ubicacion_interna }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5">{{ $fila->sucursal }}</td>
                            <td class="py-3.5 px-5 text-center font-mono text-slate-200 font-medium">{{ $fila->cantidad_total }}</td>
                            <td class="py-3.5 px-5 text-center font-mono text-sky-300">{{ $fila->cantidad_reservada }}</td>
                            <td class="py-3.5 px-5 text-center font-mono text-emerald-300 font-semibold">{{ $fila->cantidad_total - $fila->cantidad_reservada }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-10 px-6 text-center text-slate-500">
                                <span class="text-xs font-medium text-slate-400">Todavía no hay existencias registradas para este producto.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- 02 · REGISTRAR ENTRADA --}}
        @if($esAdmin)
        <section class="bg-slate-900 border border-sky-500/40 rounded-2xl shadow-xl p-5 sm:p-6">
            <div class="flex items-center gap-3 mb-6">
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-sky-500/10 text-sky-400 text-xs font-bold">02</span>
                <div>
                    <h4 class="text-sm font-bold text-white">Registrar entrada de stock</h4>
                    <p class="text-xs text-slate-500">Suma unidades a una variante en el almacén elegido y deja el movimiento en el historial de inventario.</p>
                </div>
            </div>

            @if($variantesActivas->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-700 bg-slate-950/50 px-6 py-8 text-center text-xs text-slate-400">
                El stock se registra por variante. Agregue al menos una variante activa (con su SKU) desde
                <a href="{{ route('catalogo.productos.edit', $producto->id_producto) }}" class="text-sky-400 hover:text-sky-300 font-semibold">Editar producto</a>.
            </div>
            @elseif($almacenes->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-700 bg-slate-950/50 px-6 py-8 text-center text-xs text-slate-400">
                No hay almacenes activos registrados. Cree un almacén antes de registrar stock.
            </div>
            @else
            <form method="POST" action="{{ route('catalogo.stock.entrada', $producto->id_producto) }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                    <div class="md:col-span-5">
                        <label for="id_variante" class="block text-xs font-medium text-slate-300 mb-1.5">Variante *</label>
                        <select name="id_variante" id="id_variante" required class="form-select">
                            <option value="">Seleccione…</option>
                            @foreach($variantesActivas as $v)
                            <option value="{{ $v->id_variante }}" {{ (string) old('id_variante') === (string) $v->id_variante ? 'selected' : '' }}>
                                {{ $v->sku }}{{ $v->color ? ' — ' . $v->color : '' }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_variante')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="md:col-span-5">
                        <label for="id_almacen" class="block text-xs font-medium text-slate-300 mb-1.5">Almacén de destino *</label>
                        <select name="id_almacen" id="id_almacen" required class="form-select">
                            <option value="">Seleccione…</option>
                            @foreach($almacenes->groupBy('sucursal') as $sucursal => $lista)
                            <optgroup label="{{ $sucursal }}">
                                @foreach($lista as $a)
                                <option value="{{ $a->id_almacen }}" {{ (string) old('id_almacen') === (string) $a->id_almacen ? 'selected' : '' }}>{{ $a->almacen }}</option>
                                @endforeach
                            </optgroup>
                            @endforeach
                        </select>
                        @error('id_almacen')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="cantidad" class="block text-xs font-medium text-slate-300 mb-1.5">Cantidad *</label>
                        <input type="number" name="cantidad" id="cantidad" min="1" max="1000000" step="1" required
                            value="{{ old('cantidad') }}" placeholder="0" class="form-input font-mono">
                        @error('cantidad')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="md:col-span-12">
                        <label for="motivo" class="block text-xs font-medium text-slate-300 mb-1.5">Motivo</label>
                        <input type="text" name="motivo" id="motivo" maxlength="200"
                            value="{{ old('motivo') }}" placeholder="Ej. Compra a proveedor, importación, inventario inicial"
                            class="form-input">
                        <p class="mt-1 text-[11px] text-slate-500">Si lo deja vacío se guardará como «Ingreso de mercadería».</p>
                        @error('motivo')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-150 active:scale-[0.99] cursor-pointer border border-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Registrar entrada
                    </button>
                </div>
            </form>
            @endif
        </section>
        @endif

        @endunless
    </div>
</div>
@endsection
