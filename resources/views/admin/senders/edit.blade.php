@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-pencil-square me-2"></i>Edit Sender</h1>
            <p class="text-muted mb-0">Update sender information and settings</p>
        </div>
        <a href="{{ route('admin.senders.show', $client->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Edit Form -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.senders.update', $client->id) }}">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3">Sender Information</h5>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Sender Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name', $client->name) }}"
                                   required
                                   class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sender_id" class="form-label">Sender ID <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="sender_id" 
                                       id="sender_id" 
                                       value="{{ old('sender_id', $client->sender_id) }}"
                                       required
                                       maxlength="11"
                                       class="form-control @error('sender_id') is-invalid @enderror">
                                <div class="form-text">Max 11 characters</div>
                                @error('sender_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="contact" class="form-label">Contact <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="contact" 
                                       id="contact" 
                                       value="{{ old('contact', $client->contact) }}"
                                       required
                                       class="form-control @error('contact') is-invalid @enderror">
                                @error('contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="balance" class="form-label">Balance</label>
                                <input type="number" 
                                       name="balance" 
                                       id="balance" 
                                       value="{{ old('balance', $client->balance) }}"
                                       step="0.01"
                                       min="0"
                                       class="form-control @error('balance') is-invalid @enderror">
                                @error('balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="1" {{ old('status', $client->status) == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status', $client->status) == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('admin.senders.show', $client->id) }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Update Sender
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- API Key Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-key me-2"></i>API Credentials</h5>
                </div>
                <div class="card-body">
                    <label class="form-label fw-semibold">Current API Key</label>
                    <div class="input-group mb-3">
                        <input type="text" 
                               class="form-control font-monospace" 
                               value="{{ $client->api_key }}" 
                               readonly>
                        <button type="button" 
                                onclick="copyToClipboard('{{ $client->api_key }}')"
                                class="btn btn-outline-secondary">
                            <i class="bi bi-clipboard me-1"></i>Copy
                        </button>
                        <form action="{{ route('admin.senders.regenerate-api-key', $client->id) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Are you sure? The old API key will stop working immediately.');">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-arrow-clockwise me-1"></i>Regenerate
                            </button>
                        </form>
                    </div>
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Regenerating will invalidate the current API key
                    </div>
                </div>
            </div>

            <!-- Onfon Wallet Management -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Onfon Media Wallet</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.senders.onfon-credentials', $client->id) }}">
                        @csrf
                        
                        @php
                            $onfonCreds = $client->settings['onfon_credentials'] ?? [];
                        @endphp

                        <div class="mb-3">
                            <label for="onfon_api_key" class="form-label">Onfon API Key</label>
                            <input type="text" 
                                   name="onfon_api_key" 
                                   id="onfon_api_key" 
                                   value="{{ old('onfon_api_key', $onfonCreds['api_key'] ?? '') }}"
                                   placeholder="VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak="
                                   class="form-control @error('onfon_api_key') is-invalid @enderror">
                            @error('onfon_api_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="onfon_client_id" class="form-label">Onfon Client ID</label>
                            <input type="text" 
                                   name="onfon_client_id" 
                                   id="onfon_client_id" 
                                   value="{{ old('onfon_client_id', $onfonCreds['client_id'] ?? '') }}"
                                   placeholder="e27847c1-a9fe-4eef-b60d-ddb291b175ab"
                                   class="form-control @error('onfon_client_id') is-invalid @enderror">
                            @error('onfon_client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="onfon_access_key" class="form-label">Access Key Header (Optional)</label>
                            <input type="text" 
                                   name="onfon_access_key" 
                                   id="onfon_access_key" 
                                   value="{{ old('onfon_access_key', $onfonCreds['access_key_header'] ?? '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB') }}"
                                   class="form-control">
                            <div class="form-text">Leave blank to use default</div>
                        </div>

                        <div class="mb-3">
                            <label for="default_sender" class="form-label">Default Sender ID</label>
                            <input type="text" 
                                   name="default_sender" 
                                   id="default_sender" 
                                   value="{{ old('default_sender', $onfonCreds['default_sender'] ?? $client->sender_id) }}"
                                   maxlength="11"
                                   class="form-control">
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="auto_sync_balance" 
                                   id="auto_sync_balance"
                                   {{ old('auto_sync_balance', $client->auto_sync_balance) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_sync_balance">
                                Auto-sync balance before sending messages
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Save Onfon Credentials
                            </button>
                            <button type="button" 
                                    onclick="testOnfonConnection()" 
                                    class="btn btn-outline-primary">
                                <i class="bi bi-wifi me-2"></i>Test Connection
                            </button>
                        </div>
                    </form>

                    @if(!empty($onfonCreds))
                        <hr class="my-4">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small">Local Balance</span>
                                        <i class="bi bi-database text-primary"></i>
                                    </div>
                                    <h4 class="mb-0">KES {{ number_format($client->balance, 2) }}</h4>
                                    <small class="text-muted">{{ number_format($client->getBalanceInUnits(), 2) }} units</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small">Onfon Balance</span>
                                        <i class="bi bi-cloud text-success"></i>
                                    </div>
                                    <h4 class="mb-0" id="onfon-balance-display">
                                        @if($client->onfon_balance)
                                            KES {{ number_format($client->onfon_balance, 2) }}
                                        @else
                                            <span class="text-muted">Not synced</span>
                                        @endif
                                    </h4>
                                    @if($client->onfon_last_sync)
                                        <small class="text-muted">{{ $client->onfon_last_sync->diffForHumans() }}</small>
                                    @else
                                        <small class="text-muted">Never synced</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('admin.senders.sync-onfon-balance', $client->id) }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-arrow-repeat me-2"></i>Sync Balance from Onfon Now
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Associated Users -->
            @if($client->users->count() > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Associated Users</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @foreach($client->users as $user)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $user->name }}</h6>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                    <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-primary' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Danger Zone -->
            @if($client->id != 1)
                <div class="card shadow-sm border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Danger Zone</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Delete this sender</h6>
                                <p class="text-muted small mb-0">This action cannot be undone. All associated data will be permanently deleted.</p>
                            </div>
                            <form action="{{ route('admin.senders.destroy', $client->id) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you absolutely sure? This will permanently delete the sender and all associated data.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash me-1"></i>Delete Sender
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle me-2"></i>Sender Details</h5>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">ID:</td>
                            <td class="fw-semibold">#{{ $client->id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ $client->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Last Updated:</td>
                            <td>{{ $client->updated_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                @if($client->status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showToast('success', 'API Key copied to clipboard!');
    });
}

function testOnfonConnection() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing...';

    fetch('{{ route('admin.senders.test-onfon', $client->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', `Connection successful! Balance: KES ${data.balance || 0}`);
            if (data.balance) {
                document.getElementById('onfon-balance-display').innerHTML = 
                    `KES ${parseFloat(data.balance).toFixed(2)}`;
            }
        } else {
            showToast('error', data.message || 'Connection failed');
        }
    })
    .catch(error => {
        showToast('error', 'Error: ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function showToast(type, message) {
    const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
    const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
    
    const toast = document.createElement('div');
    toast.className = 'position-fixed top-0 end-0 p-3';
    toast.style.zIndex = '11';
    toast.innerHTML = `
        <div class="toast show" role="alert">
            <div class="toast-header ${bgClass} text-white">
                <i class="${icon} me-2"></i>
                <strong class="me-auto">${type === 'success' ? 'Success' : 'Error'}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}
</script>
@endpush
@endsection
