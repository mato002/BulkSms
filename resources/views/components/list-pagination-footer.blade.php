@props(['paginator', 'exportRoute' => null, 'exportLabel' => 'Export CSV', 'turboFrame' => 'main-content'])

@if($paginator->total() > 0 || $exportRoute)
<div class="list-pagination-footer d-flex flex-wrap justify-content-between align-items-center gap-3 p-3 border-top bg-white">
    <div class="flex-grow-1">
        @if($paginator->total() > 0)
            {{ $paginator->links('vendor.pagination.simple', ['turboFrame' => $turboFrame]) }}
        @endif
    </div>
    @if($exportRoute)
        <a href="{{ $exportRoute }}" class="btn btn-sm btn-outline-success ms-auto" @if($turboFrame) data-turbo-frame="{{ $turboFrame }}" @endif>
            <i class="bi bi-download me-1"></i>{{ $exportLabel }}
        </a>
    @endif
</div>
@endif
