@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="sipala-pagination">
        <ul class="pagination-links" style="width: 100%; justify-content: space-between;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" style="gap: 6px;"><i class="fas fa-chevron-left" style="font-size: 11px;"></i> Sebelumnya</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" style="gap: 6px;">
                        <i class="fas fa-chevron-left" style="font-size: 11px;"></i> Sebelumnya
                    </a>
                </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" style="gap: 6px;">
                        Berikutnya <i class="fas fa-chevron-right" style="font-size: 11px;"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" style="gap: 6px;">Berikutnya <i class="fas fa-chevron-right" style="font-size: 11px;"></i></span>
                </li>
            @endif
        </ul>
    </nav>
@endif

