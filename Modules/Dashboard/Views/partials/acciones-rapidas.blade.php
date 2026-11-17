@php
    $muted = 'text-slate-500 dark:text-slate-400';
    $label = 'text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400';
    $panel = 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm';
    $primaryBg = 'bg-[#0B6E45] hover:bg-[#095638] dark:bg-emerald-600 dark:hover:bg-emerald-700';
    $primaryText = 'text-white';
    $outline = 'border border-slate-200 dark:border-slate-700 hover:border-[#0B6E45]/50 dark:hover:border-[#0B6E45]/60';
    $disabled = 'opacity-50 cursor-not-allowed';
@endphp

<section class="grid grid-cols-1 lg:grid-cols-2 gap-5" aria-label="Acciones rápidas">

    {{-- Tarjeta de acciones --}}
    <article class="{{ $panel }} p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="{{ $label }}">Acciones rápidas</h2>
            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/20 px-2 py-1 rounded">
                Acceso directo
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

            {{-- Registrar venta (gerencia y vendedor) --}}
            @if ($esGerencia || $esVendedor)
            <a href="{{ route('ventas.create', [], false) }}"
                class="flex items-center gap-3 px-4 py-3.5 {{ $primaryBg }} {{ $primaryText }} rounded-xl font-medium transition-colors">
                <span class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-emerald-200">
                    💰
                </span>
                <div>
                    <p class="text-sm font-semibold leading-tight">Registrar venta</p>
                    <p class="text-[10px] text-emerald-100">Nueva venta directa</p>
                </div>
            </a>
            @endif

            {{-- Crear cotización (gerencia y vendedor) --}}
            @if ($esGerencia || $esVendedor)
            <a href="{{ route('cotizaciones.create', [], false) }}"
                class="flex items-center gap-3 px-4 py-3.5 {{ $outline }} text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl font-medium transition-colors">
                <span class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">
                    📋
                </span>
                <div>
                    <p class="text-sm font-semibold leading-tight">Crear cotización</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Nueva cotización para cliente</p>
                </div>
            </a>
            @endif

            {{-- Crear producto (solo gerencia) --}}
            @if ($esGerencia)
            <a href="{{ route('catalogo.productos.create', [], false) }}"
                class="flex items-center gap-3 px-4 py-3.5 {{ $outline }} text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl font-medium transition-colors">
                <span class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">
                    🏷️
                </span>
                <div>
                    <p class="text-sm font-semibold leading-tight">Crear producto</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Alta de producto en catálogo</p>
                </div>
            </a>
            @endif

            {{-- Registrar movimiento (solo gerencia) --}}
            @if ($esGerencia)
            <a href="{{ route('inventario.movimientos.create', [], false) }}"
                class="flex items-center gap-3 px-4 py-3.5 {{ $outline }} text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl font-medium transition-colors">
                <span class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">
                    🔄
                </span>
                <div>
                    <p class="text-sm font-semibold leading-tight">Registrar movimiento</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Entrada/salida de inventario</p>
                </div>
            </a>
            @endif

            {{-- Registrar compra (no disponible, solo gerencia ve el placeholder) --}}
            @if ($esGerencia)
            <div class="flex items-center gap-3 px-4 py-3.5 {{ $disabled }} text-slate-400 border border-dashed border-slate-300 dark:border-slate-700/50 bg-slate-50 dark:bg-slate-900 rounded-xl font-medium">
                <span class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-800/50 flex items-center justify-center text-slate-400">
                    🛒
                </span>
                <div>
                    <p class="text-sm font-semibold leading-tight">Registrar compra</p>
                    <p class="text-[10px] text-slate-400">Módulo de compras pendiente</p>
                </div>
            </div>
            @endif

        </div>

        <p class="mt-4 text-xs {{ $muted }} text-center">
            Algunas acciones requieren permisos de Gerente o Administrador.
        </p>
    </article>

</section>
