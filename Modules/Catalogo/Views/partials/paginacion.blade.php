@if ($paginator->hasPages())
<nav role="navigation" aria-label="Paginación" class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-4 border-t border-line bg-surface-2/60">

    <p class="text-xs text-muted">
        Mostrando
        <span class="font-semibold text-ink">{{ $paginator->firstItem() }}</span>
        a
        <span class="font-semibold text-ink">{{ $paginator->lastItem() }}</span>
        de
        <span class="font-semibold text-ink">{{ $paginator->total() }}</span>
        productos
    </p>

    <div class="flex items-center gap-1.5 flex-wrap justify-center">

        @if ($paginator->onFirstPage())
        <span class="px-3 py-1.5 rounded-lg border border-line text-muted text-xs cursor-not-allowed">‹ Anterior</span>
        @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
            class="px-3 py-1.5 rounded-lg border border-line bg-surface text-ink hover:text-brand-green hover:border-brand-green/50 text-xs font-medium transition">‹ Anterior</a>
        @endif

        @foreach ($elements as $element)
        @if (is_string($element))
        <span class="px-2 text-muted text-xs">{{ $element }}</span>
        @endif

        @if (is_array($element))
        @foreach ($element as $page => $url)
        @if ($page == $paginator->currentPage())
        <span aria-current="page" class="px-3 py-1.5 rounded-lg border border-brand-green bg-brand-green text-white text-xs font-semibold">{{ $page }}</span>
        @else
        <a href="{{ $url }}" aria-label="Ir a la página {{ $page }}"
            class="px-3 py-1.5 rounded-lg border border-line bg-surface text-ink hover:text-brand-green hover:border-brand-green/50 text-xs font-medium transition">{{ $page }}</a>
        @endif
        @endforeach
        @endif
        @endforeach

        @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next"
            class="px-3 py-1.5 rounded-lg border border-line bg-surface text-ink hover:text-brand-green hover:border-brand-green/50 text-xs font-medium transition">Siguiente ›</a>
        @else
        <span class="px-3 py-1.5 rounded-lg border border-line text-muted text-xs cursor-not-allowed">Siguiente ›</span>
        @endif

    </div>
</nav>
@endif
