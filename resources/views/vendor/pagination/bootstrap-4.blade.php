@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Pagination">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="page-item disabled">
                <span class="page-link"><i class="fa-solid fa-chevron-left" style="font-size:10px"></i></span>
            </span>
        @else
            <a class="page-item" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">
                <span class="page-link"><i class="fa-solid fa-chevron-left" style="font-size:10px"></i></span>
            </a>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="page-item disabled"><span class="page-link">{{ $element }}</span></span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></span>
                    @else
                        <a class="page-item" href="{{ $url }}"><span class="page-link">{{ $page }}</span></a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a class="page-item" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">
                <span class="page-link"><i class="fa-solid fa-chevron-right" style="font-size:10px"></i></span>
            </a>
        @else
            <span class="page-item disabled">
                <span class="page-link"><i class="fa-solid fa-chevron-right" style="font-size:10px"></i></span>
            </span>
        @endif
    </nav>
@endif
