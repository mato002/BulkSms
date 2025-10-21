@if ($paginator->hasPages())
    <div class="simple-pagination">
        <div class="pagination-info">
            <span class="pagination-text">
                Showing <strong>{{ $paginator->firstItem() }}</strong> to <strong>{{ $paginator->lastItem() }}</strong> of <strong>{{ $paginator->total() }}</strong> results
            </span>
        </div>
        <div class="pagination-buttons">
            @if ($paginator->onFirstPage())
                <button class="pagination-btn pagination-btn-disabled" disabled>
                    <i class="bi bi-chevron-left"></i>
                    <span>Previous</span>
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn pagination-btn-enabled">
                    <i class="bi bi-chevron-left"></i>
                    <span>Previous</span>
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn pagination-btn-enabled">
                    <span>Next</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
            @else
                <button class="pagination-btn pagination-btn-disabled" disabled>
                    <span>Next</span>
                    <i class="bi bi-chevron-right"></i>
                </button>
            @endif
        </div>
    </div>

    <style>
    .simple-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .pagination-info {
        flex: 1;
    }

    .pagination-text {
        color: #64748b;
        font-size: 0.875rem;
    }

    .pagination-text strong {
        color: #1e293b;
        font-weight: 600;
    }

    .pagination-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .pagination-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        cursor: pointer;
    }

    .pagination-btn i {
        font-size: 0.875rem;
    }

    .pagination-btn-enabled {
        background: white;
        color: #475569;
        border-color: #e2e8f0;
    }

    .pagination-btn-enabled:hover {
        background: #f8fafc;
        border-color: #667eea;
        color: #667eea;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
    }

    .pagination-btn-disabled {
        background: #f8fafc;
        color: #cbd5e1;
        border-color: #e2e8f0;
        cursor: not-allowed;
    }

    /* Responsive */
    @media (max-width: 640px) {
        .simple-pagination {
            flex-direction: column;
            align-items: stretch;
        }

        .pagination-info {
            text-align: center;
        }

        .pagination-buttons {
            width: 100%;
        }

        .pagination-btn {
            flex: 1;
            justify-content: center;
        }
    }
    </style>
@endif

