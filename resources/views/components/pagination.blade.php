@props(['paginator'])

@if($paginator->hasPages())
    <nav class="flex flex-col gap-3 border-t border-gray-100 px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between"
        aria-label="Pagination">
        <p class="text-xs text-gray-500">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        </p>

        <div class="flex items-center gap-1">
            @if($paginator->onFirstPage())
                <span class="inline-flex h-9 items-center rounded-lg border border-gray-200 px-3 text-gray-400"
                    aria-disabled="true">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="inline-flex h-9 items-center rounded-lg border border-gray-200 bg-white px-3 font-medium text-hut-dark transition hover:border-hut-green hover:text-hut-green">Previous</a>
            @endif

            <div class="hidden items-center gap-1 sm:flex">
                @foreach($paginator->getUrlRange(max(1, $paginator->currentPage() - 1), min($paginator->lastPage(), $paginator->currentPage() + 1)) as $page => $url)
                    @if($page == $paginator->currentPage())
                        <span
                            class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-hut-green px-3 font-semibold text-white"
                            aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                            class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 font-medium text-hut-dark transition hover:border-hut-green hover:text-hut-green">{{ $page }}</a>
                    @endif
                @endforeach
            </div>

            <span class="px-2 text-xs text-gray-500 sm:hidden">Page {{ $paginator->currentPage() }} of
                {{ $paginator->lastPage() }}</span>

            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                    class="inline-flex h-9 items-center rounded-lg border border-gray-200 bg-white px-3 font-medium text-hut-dark transition hover:border-hut-green hover:text-hut-green">Next</a>
            @else
                <span class="inline-flex h-9 items-center rounded-lg border border-gray-200 px-3 text-gray-400"
                    aria-disabled="true">Next</span>
            @endif
        </div>
    </nav>
@endif