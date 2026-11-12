@if ($paginator->hasPages())
<nav role="navigation" aria-label="Paginación" class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-4 border-t border-slate-800 bg-slate-950/40">

    <p class="text-xs text-slate-400">
        Mostrando
        <span class="font-semibold text-slate-200">{{ $paginator->firstItem() }}</span>
        a
        <span class="font-semibold text-slate-200">{{ $paginator->lastItem() }}</span>
        de
        <span class="font-semibold text-slate-200">{{ $paginator->total() }}</span>
        productos
    </p>

    <div class="flex items-center gap-1.5 flex-wrap justify-center">

        @if ($paginator->onFirstPage())
        <span class="px-3 py-1.5 rounded-lg border border-slate-800 text-slate-600 text-xs cursor-not-allowed">‹ Anterior</span>
        @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
            class="px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-900 text-slate-300 hover:text-white hover:border-sky-500/50 text-xs font-medium transition">‹ Anterior</a>
        @endif

        @foreach ($elements as $element)
        @if (is_string($element))
        <span class="px-2 text-slate-600 text-xs">{{ $element }}</span>
        @endif

        @if (is_array($element))
        @foreach ($element as $page => $url)
        @if ($page == $paginator->currentPage())
        <span aria-current="page" class="px-3 py-1.5 rounded-lg border border-blue-400 bg-blue-600 text-white text-xs font-semibold">{{ $page }}</span>
        @else
        <a href="{{ $url }}" aria-label="Ir a la página {{ $page }}"
            class="px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-900 text-slate-300 hover:text-white hover:border-sky-500/50 text-xs font-medium transition">{{ $page }}</a>
        @endif
        @endforeach
        @endif
        @endforeach

        @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next"
            class="px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-900 text-slate-300 hover:text-white hover:border-sky-500/50 text-xs font-medium transition">Siguiente ›</a>
        @else
        <span class="px-3 py-1.5 rounded-lg border border-slate-800 text-slate-600 text-xs cursor-not-allowed">Siguiente ›</span>
        @endif

    </div>
</nav>
@endif
