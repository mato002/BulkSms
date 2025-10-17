@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="mb-1"><i class="bi bi-building me-2"></i>Sender Management</h1>
            <p class="text-muted mb-0">Manage all your senders/tenants and their API keys</p>
        </div>
        <a href="{{ route('admin.senders.create') }}" class="btn btn-primary w-100 w-md-auto">
            <i class="bi bi-plus-lg me-2"></i>Add New Sender
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded p-3">
                            <i class="bi bi-people-fill text-primary fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted mb-0 small">Total Senders</p>
                            <h3 class="mb-0">{{ $stats['total_clients'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-success bg-opacity-10 rounded p-3">
                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted mb-0 small">Active Senders</p>
                            <h3 class="mb-0">{{ $stats['active_clients'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-warning bg-opacity-10 rounded p-3">
                            <i class="bi bi-cash-stack text-warning fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted mb-0 small">Total Balance</p>
                            <h3 class="mb-0">{{ number_format($stats['total_balance'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-info bg-opacity-10 rounded p-3">
                            <i class="bi bi-chat-dots-fill text-info fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted mb-0 small">Total Messages</p>
                            <h3 class="mb-0">{{ number_format($stats['total_messages']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.senders.index') }}" class="row g-3">
                <div class="col-md-5">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search by name, contact, sender ID, or API key..."
                           class="form-control">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                </div>
                @if(request('search') || request('status') !== null)
                    <div class="col-md-2">
                        <a href="{{ route('admin.senders.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-lg me-1"></i>Clear
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Senders Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">Sender Details</th>
                            <th class="px-4 py-3">API Key</th>
                            <th class="px-4 py-3">Balance</th>
                            <th class="px-4 py-3">Usage</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                <span class="text-primary fw-bold">{{ strtoupper(substr($client->name, 0, 2)) }}</span>
                                            </div>
                                        </div>
                                        <div class="ms-3">
                                            <div class="fw-semibold">{{ $client->name }}</div>
                                            <div class="text-muted small">{{ $client->sender_id }}</div>
                                            <div class="text-muted" style="font-size: 0.8rem;">{{ $client->contact }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <code class="small bg-light px-2 py-1 rounded">{{ substr($client->api_key, 0, 20) }}...</code>
                                        <button onclick="copyToClipboard('{{ $client->api_key }}')" 
                                                class="btn btn-sm btn-link text-muted p-1 ms-1" 
                                                title="Copy API Key">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="fw-semibold">{{ number_format($client->balance, 2) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div>{{ $client->campaigns_count }} campaigns</div>
                                    <div class="text-muted small">{{ $client->sms_count }} messages</div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($client->status)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('admin.senders.show', $client->id) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.senders.edit', $client->id) }}" 
                                           class="btn btn-sm btn-outline-warning" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.senders.toggle-status', $client->id) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="btn btn-sm {{ $client->status ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                                                    title="{{ $client->status ? 'Deactivate' : 'Activate' }}">
                                                <i class="bi {{ $client->status ? 'bi-x-circle' : 'bi-check-circle' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                    <p class="text-muted mt-2">No senders found</p>
                                    <a href="{{ route('admin.senders.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i>Add Your First Sender
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($clients->hasPages())
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-center">
                        {{ $clients->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Bootstrap toast notification
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '11';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header bg-success text-white">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong class="me-auto">Success</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    API Key copied to clipboard!
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>
@endpush
@endsection
