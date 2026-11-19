<section class="grid grid-cols-1 gap-5 lg:grid-cols-2" aria-label="Acciones rápidas">

    {{-- Tarjeta de acciones --}}
    <article class="rounded-2xl border border-line bg-surface/90 p-5 shadow-soft sm:p-6">

        {{-- Encabezado --}}
        <div class="mb-6 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold tracking-tight text-ink">
                    Acciones rápidas
                </h2>
                <p class="mt-1 text-xs text-muted">
                    Accesos directos a las operaciones frecuentes
                </p>
            </div>

            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-brand-green/20 bg-brand-green/10 px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-brand-green">
                <span class="h-1.5 w-1.5 rounded-full bg-brand-green"></span>
                Acceso directo
            </span>
        </div>

        {{-- Islas de acciones --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

            {{-- Registrar venta --}}
            @if ($esGerencia || $esVendedor)
            <a href="{{ route('ventas.create', [], false) }}"
                class="group flex min-h-[82px] items-center gap-3 rounded-xl border border-brand-green/25 bg-brand-green/10 p-4 transition-all duration-200 hover:-translate-y-0.5 hover:border-brand-green/50 hover:bg-brand-green/15 hover:shadow-md hover:shadow-brand-green/5">

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-green text-white shadow-sm transition-transform duration-200 group-hover:scale-105">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v20m5-16H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                    </svg>
                </span>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-ink transition-colors group-hover:text-brand-green">
                        Registrar venta
                    </p>
                    <p class="mt-1 text-[11px] text-muted">
                        Nueva venta directa
                    </p>
                </div>

                <svg class="h-4 w-4 shrink-0 text-muted transition-all duration-200 group-hover:translate-x-1 group-hover:text-brand-green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                </svg>
            </a>
            @endif

            {{-- Crear cotización --}}
            @if ($esGerencia || $esVendedor)
            <a href="{{ route('cotizaciones.create', [], false) }}"
                class="group flex min-h-[82px] items-center gap-3 rounded-xl border border-line bg-surface-2/50 p-4 transition-all duration-200 hover:-translate-y-0.5 hover:border-brand-green/40 hover:bg-surface-2 hover:shadow-md">

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-line bg-surface text-muted transition-all duration-200 group-hover:border-brand-green/30 group-hover:bg-brand-green/10 group-hover:text-brand-green">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 3h8l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 3v5h5M9 13h6m-6 4h6" />
                    </svg>
                </span>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-ink transition-colors group-hover:text-brand-green">
                        Crear cotización
                    </p>
                    <p class="mt-1 text-[11px] text-muted">
                        Nueva cotización para cliente
                    </p>
                </div>

                <svg class="h-4 w-4 shrink-0 text-muted transition-all duration-200 group-hover:translate-x-1 group-hover:text-brand-green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                </svg>
            </a>
            @endif

            {{-- Crear producto --}}
            @if ($esGerencia)
            <a href="{{ route('catalogo.productos.create', [], false) }}"
                class="group flex min-h-[82px] items-center gap-3 rounded-xl border border-line bg-surface-2/50 p-4 transition-all duration-200 hover:-translate-y-0.5 hover:border-brand-green/40 hover:bg-surface-2 hover:shadow-md">

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-line bg-surface text-muted transition-all duration-200 group-hover:border-brand-green/30 group-hover:bg-brand-green/10 group-hover:text-brand-green">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m12 3 9 4.5v9L12 21l-9-4.5v-9L12 3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3.5 7.7 8.5 4.5 8.5-4.5M12 12.2V21" />
                    </svg>
                </span>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-ink transition-colors group-hover:text-brand-green">
                        Crear producto
                    </p>
                    <p class="mt-1 text-[11px] text-muted">
                        Alta de producto en catálogo
                    </p>
                </div>

                <svg class="h-4 w-4 shrink-0 text-muted transition-all duration-200 group-hover:translate-x-1 group-hover:text-brand-green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                </svg>
            </a>
            @endif

            {{-- Registrar movimiento --}}
            @if ($esGerencia)
            <a href="{{ route('inventario.movimientos.create', [], false) }}"
                class="group flex min-h-[82px] items-center gap-3 rounded-xl border border-line bg-surface-2/50 p-4 transition-all duration-200 hover:-translate-y-0.5 hover:border-brand-green/40 hover:bg-surface-2 hover:shadow-md">

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-line bg-surface text-muted transition-all duration-200 group-hover:border-brand-green/30 group-hover:bg-brand-green/10 group-hover:text-brand-green">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7h-7m0 0 3-3m-3 3 3 3M4 17h7m0 0-3 3m3-3-3-3" />
                    </svg>
                </span>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-ink transition-colors group-hover:text-brand-green">
                        Registrar movimiento
                    </p>
                    <p class="mt-1 text-[11px] text-muted">
                        Entrada o salida de inventario
                    </p>
                </div>

                <svg class="h-4 w-4 shrink-0 text-muted transition-all duration-200 group-hover:translate-x-1 group-hover:text-brand-green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                </svg>
            </a>
            @endif

            {{-- Registrar compra: pendiente 
            @if ($esGerencia)
            <div aria-disabled="true"
                class="flex min-h-[82px] cursor-not-allowed items-center gap-3 rounded-xl border border-dashed border-line bg-surface-2/30 p-4 opacity-60">

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-dashed border-line bg-surface-2 text-muted">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l2.2 11.2a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 1.9-1.4L22 8H6" />
                        <circle cx="10" cy="20" r="1" />
                        <circle cx="18" cy="20" r="1" />
                    </svg>
                </span>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-ink">
                        Registrar compra
                    </p>
                    <p class="mt-1 text-[11px] text-muted">
                        Módulo de compras pendiente
                    </p>
                </div>

                <span class="rounded-md border border-line bg-surface-2 px-2 py-1 text-[9px] font-bold uppercase tracking-wider text-muted">
                    Próximamente
                </span>
            </div>
            @endif
        --}}
        </div>

        {{-- Nota inferior --}}
        <div class="mt-5 flex items-center gap-2 border-t border-line pt-4">
            <svg class="h-4 w-4 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="9" />
                <path stroke-linecap="round" d="M12 11v5m0-8h.01" />
            </svg>

            <p class="text-[11px] text-muted">
                Las acciones disponibles dependen de los permisos de tu cuenta.
            </p>
        </div>

    </article>

</section>