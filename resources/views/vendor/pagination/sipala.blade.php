@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="sipala-pagination">
        <div class="pagination-info">
            Menampilkan
            <span class="pagination-bold">{{ $paginator->firstItem() }}</span>
            sampai
            <span class="pagination-bold">{{ $paginator->lastItem() }}</span>
            dari
            <span class="pagination-bold">{{ $paginator->total() }}</span>
            data
        </div>

        <ul class="pagination-links">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="Sebelumnya">
                    <span class="page-link"><i class="fas fa-chevron-left" style="font-size: 11px;"></i></span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">
                        <i class="fas fa-chevron-left" style="font-size: 11px;"></i>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link dots">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">
                        <i class="fas fa-chevron-right" style="font-size: 11px;"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="Berikutnya">
                    <span class="page-link"><i class="fas fa-chevron-right" style="font-size: 11px;"></i></span>
                </li>
            @endif
        </ul>
    </nav>
@endif

