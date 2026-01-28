{{--
    Pagination Partial
    Works with Laravel's paginator
--}}

@php
    $paginator = $paginator ?? $products ?? null;
@endphp

@if ($paginator && $paginator->hasPages())
    <nav aria-label="Page navigation" class="pagination_nav">
        <ul class="pagination justify-content-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Page Numbers --}}
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $start = max(1, $currentPage - 2);
                $end = min($lastPage, $currentPage + 2);
            @endphp

            {{-- First page --}}
            @if($start > 1)
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                </li>
                @if($start > 2)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif
            @endif

            {{-- Page range --}}
            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $currentPage)
                    <li class="page-item active">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                    </li>
                @endif
            @endfor

            {{-- Last page --}}
            @if($end < $lastPage)
                @if($end < $lastPage - 1)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a>
                </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                </li>
            @endif
        </ul>
    </nav>
    
    {{-- Results Info --}}
    <div class="pagination_info text-center mt-3">
        <p class="text-muted mb-0">
            Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} 
            of {{ $paginator->total() }} results
        </p>
    </div>
@endif

@pushOnce('styles')
<style>
.pagination_nav {
    margin-top: 30px;
}

.pagination {
    gap: 5px;
}

.pagination .page-link {
    border-radius: 8px;
    border: 1px solid #ddd;
    color: #555;
    padding: 10px 16px;
    font-size: 14px;
    transition: all 0.2s;
}

.pagination .page-link:hover {
    background: var(--primary-color, #ff6b6b);
    border-color: var(--primary-color, #ff6b6b);
    color: #fff;
}

.pagination .page-item.active .page-link {
    background: var(--primary-color, #ff6b6b);
    border-color: var(--primary-color, #ff6b6b);
    color: #fff;
}

.pagination .page-item.disabled .page-link {
    background: #f8f9fa;
    color: #ccc;
}

.pagination_info {
    font-size: 13px;
}

@media (max-width: 575px) {
    .pagination .page-link {
        padding: 8px 12px;
        font-size: 13px;
    }
}
</style>
@endPushOnce