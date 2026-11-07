@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="{{ __('app.page') }}">
        <span>{{ __('app.page') }} {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
        <div class="page-links">
            @if ($paginator->onFirstPage())
                <span class="page-link disabled" aria-disabled="true">&lsaquo;</span>
            @else
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">&lsaquo;</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="page-link disabled">{{ $element }}</span>
                @elseif (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page-link active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">&rsaquo;</a>
            @else
                <span class="page-link disabled" aria-disabled="true">&rsaquo;</span>
            @endif
        </div>
    </nav>
@endif
