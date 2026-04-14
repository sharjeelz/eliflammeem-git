@if ($paginator->hasPages())
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mt-4">

        {{-- Result count --}}
        <div class="text-muted fs-7">
            Showing <strong>{{ $paginator->firstItem() }}</strong>
            to <strong>{{ $paginator->lastItem() }}</strong>
            of <strong>{{ $paginator->total() }}</strong> results
        </div>

        {{-- Page buttons --}}
        <div class="d-flex align-items-center gap-1">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <button class="btn btn-sm btn-light" disabled>&#8249; Prev</button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-sm btn-light">&#8249; Prev</a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <button class="btn btn-sm btn-light disabled">…</button>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button class="btn btn-sm btn-primary">{{ $page }}</button>
                        @else
                            <a href="{{ $url }}" class="btn btn-sm btn-light">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-sm btn-light">Next &#8250;</a>
            @else
                <button class="btn btn-sm btn-light" disabled>Next &#8250;</button>
            @endif

        </div>
    </div>
@endif
