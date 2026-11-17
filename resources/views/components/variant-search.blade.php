@props([
    'name' => '',
    'items' => [],
    'value' => '',
    'placeholder' => 'Buscar por SKU, nombre o marca…',
])

{{--
    Buscador/autocompletado de variantes.

    - `name`  nombre del input oculto que se envía al formulario. En las filas
              dinámicas (`<template>`) se deja vacío y el JavaScript de la vista
              lo asigna tras clonar la fila (por eso el input lleva `data-name`).
    - `items` array JSON con las variantes indexables.
    - `value` id de variante preseleccionado (validación / edición).
--}}

<div class="relative min-w-[220px]"
    x-data="variantSearch(@js($items))"
    @click.outside="open = false"
    @keydown.escape.window="open = false">

    {{-- Input real: es el que viaja al formulario. --}}
    <input type="hidden"
        x-ref="hidden"
        name="{{ $name }}"
        data-name="id_variante"
        value="{{ $value }}">

    <div class="relative">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2">
            <svg class="h-4 w-4 text-slate-400 dark:text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" />
            </svg>
        </div>

        <input type="text"
            x-ref="input"
            x-model="query"
            @focus="open = true"
            @input="open = true; highlighted = 0"
            @keydown.arrow-down.prevent="highlighted = Math.min(highlighted + 1, resultados.length - 1)"
            @keydown.arrow-up.prevent="highlighted = Math.max(highlighted - 1, 0)"
            @keydown.enter.prevent="elegir(resultados[highlighted])"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            aria-label="Buscar variante"
            class="w-full rounded-md border border-slate-300 bg-white py-1 pl-7 pr-7 text-sm text-slate-900 placeholder-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:placeholder-slate-500">

        <button type="button"
            x-show="selectedId"
            x-cloak
            @click="limpiar()"
            title="Quitar variante"
            class="absolute inset-y-0 right-0 flex items-center px-2 text-slate-400 hover:text-rose-500 dark:text-slate-500">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Desplegable de resultados (`position: fixed` para no ser recortado por la tabla). --}}
    <div x-show="open" x-cloak>
        <div x-bind:style="estiloDesplegable()"
            class="fixed z-50 max-h-64 overflow-auto rounded-lg border border-slate-200 bg-white shadow-xl shadow-slate-900/10 dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/50">

            <template x-for="(item, i) in resultados" :key="item.id">
                <button type="button"
                    @click="elegir(item)"
                    @mouseenter="highlighted = i"
                    class="flex w-full items-center justify-between gap-3 px-3 py-2 text-left transition"
                    :class="highlighted === i ? 'bg-slate-100 dark:bg-slate-800' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'">

                    <span class="min-w-0">
                        <span class="block truncate text-sm font-semibold text-slate-900 dark:text-slate-100" x-text="item.sku"></span>
                        <span class="block truncate text-xs text-slate-500 dark:text-slate-400"
                            x-text="item.nombre + (item.color ? ' · ' + item.color : '') + (item.marca ? ' · ' + item.marca : '')"></span>
                    </span>

                    <span class="shrink-0 flex flex-col items-end gap-1">
                        <span class="text-xs font-semibold"
                            :class="item.precio ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'"
                            x-text="item.precio ? 'USD ' + Number(item.precio).toFixed(2) : 'sin precio'"></span>

                        <template x-if="promoEtiqueta(item)">
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-fuchsia-600/15 text-fuchsia-600 border border-fuchsia-500/30 dark:text-fuchsia-300"
                                x-text="promoEtiqueta(item)"></span>
                        </template>

                        <template x-if="item.es_combo">
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-600/15 text-sky-600 border border-sky-500/30 dark:text-sky-300">COMBO</span>
                        </template>
                    </span>

                </button>
            </template>

            <p x-show="resultados.length === 0"
                class="px-3 py-3 text-center text-xs text-slate-500">
                No se encontraron variantes.
            </p>

        </div>
    </div>
</div>
