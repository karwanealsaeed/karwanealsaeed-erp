@if ($paginator->hasPages())
    <nav
        class="cp-pagination"
        aria-label="{{ $label ?? 'Pagination' }}"
    >
        <p>
            Showing
            <strong>{{ $paginator->firstItem() ?? 0 }}</strong>
            to
            <strong>{{ $paginator->lastItem() ?? 0 }}</strong>
            of
            <strong>{{ $paginator->total() }}</strong>
        </p>

        <div class="cp-pagination-actions">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true">
                    Previous
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                >
                    Previous
                </a>
            @endif

            <span aria-current="page">
                Page {{ $paginator->currentPage() }}
                of {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                >
                    Next
                </a>
            @else
                <span aria-disabled="true">
                    Next
                </span>
            @endif
        </div>
    </nav>
@endif