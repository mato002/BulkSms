@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">{{ $client->name }}</h1>
            <p class="text-muted mb-0">Sender ID: <span class="fw-semibold">{{ $client->sender_id }}</span></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.senders.edit', $client->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-2"></i>Edit
            </a>
            <a href="{{ route('admin.senders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Overview Cards -->
    <div class="row g-3 mb-4">
        <!-- Basic Info Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" width="100">Name:</td>
                            <td class="fw-semibold">{{ $client->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Sender ID:</td>
                            <td class="fw-semibold">{{ $client->sender_id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Contact:</td>
                            <td>{{ $client->contact }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                @if($client->status)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle me-1"></i>Inactive
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ $client->created_at->format('M d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Local Balance Card -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100 bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-database me-2"></i>
                        <h6 class="opacity-75 mb-0">Local Balance</h6>
                    </div>
                    <h2 class="display-5 fw-bold mb-2">KES {{ number_format($client->balance, 2) }}</h2>
                    <small class="opacity-75">{{ number_format($client->getBalanceInUnits(), 2) }} units</small>
                    <form action="{{ route('admin.senders.update-balance', $client->id) }}" method="POST" class="mt-3">
                        @csrf
                        @method('POST')
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" 
                                       name="amount" 
                                       step="0.01" 
                                       min="0" 
                                       placeholder="Amount" 
                                       required
                                       class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <select name="action" class="form-select form-select-sm">
                                    <option value="add">Add</option>
                                    <option value="deduct">Deduct</option>
                                    <option value="set">Set</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <select name="type" class="form-select form-select-sm">
                                    <option value="ksh">KSH</option>
                                    <option value="units">Units</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <button type="submit" class="btn btn-light btn-sm w-100">
                                    <i class="bi bi-cash-stack me-1"></i>Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Onfon Balance Card -->
        <div class="col-lg-3">
            @php
                $hasOnfonCreds = !empty($client->settings['onfon_credentials'] ?? []);
            @endphp
            <div class="card shadow-sm h-100 {{ $hasOnfonCreds ? 'bg-gradient' : 'bg-light' }}" 
                 style="{{ $hasOnfonCreds ? 'background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);' : '' }}">
                <div class="card-body {{ $hasOnfonCreds ? 'text-white' : '' }}">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-cloud me-2"></i>
                        <h6 class="{{ $hasOnfonCreds ? 'opacity-75' : 'text-muted' }} mb-0">Onfon Balance</h6>
                    </div>
                    @if($hasOnfonCreds)
                        <h2 class="display-5 fw-bold mb-2" id="onfon-balance-value">
                            @if($client->onfon_balance !== null)
                                KES {{ number_format($client->onfon_balance, 2) }}
                            @else
                                <span class="opacity-50">Not synced</span>
                            @endif
                        </h2>
                        @if($client->onfon_last_sync)
                            <small class="opacity-75">{{ $client->onfon_last_sync->diffForHumans() }}</small>
                        @else
                            <small class="opacity-75">Never synced</small>
                        @endif
                        <form action="{{ route('admin.senders.sync-onfon-balance', $client->id) }}" 
                              method="POST" 
                              class="mt-3" 
                              id="sync-balance-form">
                            @csrf
                            <button type="submit" class="btn btn-light btn-sm w-100">
                                <i class="bi bi-arrow-repeat me-1"></i>Sync from Onfon
                            </button>
                        </form>
                    @else
                        <p class="text-muted mb-3">
                            <i class="bi bi-info-circle me-1"></i>No Onfon credentials configured
                        </p>
                        <a href="{{ route('admin.senders.edit', $client->id) }}" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-gear me-1"></i>Configure Onfon
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- API Key Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-key me-2"></i>API Credentials</h5>
                </div>
                <div class="card-body">
                    <label class="form-label small text-muted mb-2">API Key</label>
                    <div class="input-group input-group-sm mb-3">
                        <input type="text" 
                               class="form-control font-monospace small" 
                               value="{{ $client->api_key }}" 
                               readonly>
                        <button onclick="copyToClipboard('{{ $client->api_key }}')" 
                                class="btn btn-outline-secondary">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <div class="d-grid gap-2">
                        <button onclick="copyToClipboard('{{ $client->api_key }}')" 
                                class="btn btn-sm btn-primary">
                            <i class="bi bi-clipboard me-1"></i>Copy API Key
                        </button>
                        <form action="{{ route('admin.senders.regenerate-api-key', $client->id) }}" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure? This will invalidate the current API key.');">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-sm btn-warning w-100">
                                <i class="bi bi-arrow-clockwise me-1"></i>Regenerate Key
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-info bg-opacity-10 rounded p-3">
                            <i class="bi bi-chat-dots-fill text-info fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted mb-0 small">Messages</p>
                            <h4 class="mb-0">{{ number_format($stats['total_messages']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-success bg-opacity-10 rounded p-3">
                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted mb-0 small">Delivered</p>
                            <h4 class="mb-0">{{ number_format($stats['delivered_messages']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-warning bg-opacity-10 rounded p-3">
                            <i class="bi bi-megaphone-fill text-warning fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted mb-0 small">Campaigns</p>
                            <h4 class="mb-0">{{ number_format($stats['total_campaigns']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded p-3">
                            <i class="bi bi-people-fill text-primary fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted mb-0 small">Contacts</p>
                            <h4 class="mb-0">{{ number_format($stats['total_contacts']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Messages -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Messages</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">Recipient</th>
                            <th class="px-4 py-3">Message</th>
                            <th class="px-4 py-3">Channel</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentMessages as $message)
                            <tr>
                                <td class="px-4 py-3">{{ $message->recipient }}</td>
                                <td class="px-4 py-3">{{ Str::limit($message->body, 50) }}</td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-secondary">{{ ucfirst($message->channel) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($message->status === 'delivered')
                                        <span class="badge bg-success">Delivered</span>
                                    @elseif($message->status === 'failed')
                                        <span class="badge bg-danger">Failed</span>
                                    @elseif($message->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($message->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-muted small">{{ $message->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                    <p class="text-muted mt-2">No messages yet</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Associated Users -->
    @if($client->users->count() > 0)
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>Associated Users</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($client->users as $user)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                                <span class="text-primary fw-bold small">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                            </div>
                                            <span class="fw-semibold">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-muted">{{ $user->email }}</td>
                                    <td class="px-4 py-3">
                                        <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-primary' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-muted small">{{ $user->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
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
                <div class="toast-body">API Key copied to clipboard!</div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    });
}
</script>
@endpush
@endsection
