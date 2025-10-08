@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-building me-2"></i>Senders Management</h1>
            <p class="text-muted mb-0">Manage all sender companies, pricing, and balances</p>
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.senders.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add New Sender
        </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    @php
        $clientId = session('client_id', 1);
        $client = \App\Models\Client::find($clientId);
    @endphp

    @if(!auth()->user()->isAdmin())
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Balance (Units)</p>
                            <h3 class="mb-0">{{ number_format($client->getBalanceInUnits(), 2) }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-wallet2 text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Balance (KSH)</p>
                            <h3 class="mb-0">{{ number_format($client->balance, 2) }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-cash-coin text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Price Per Unit</p>
                            <h3 class="mb-0">KSH {{ number_format($client->price_per_unit ?? 1, 2) }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-tag text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Company Name</p>
                            <h3 class="mb-0 fs-5">{{ $client->company_name ?? $client->sender_id }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-building text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sender Details Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="bi bi-building me-2"></i>Your Company Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Company Name:</th>
                            <td><strong>{{ $client->company_name ?? $client->sender_id }}</strong></td>
                        </tr>
                        <tr>
                            <th>Contact Person:</th>
                            <td>{{ $client->name }}</td>
                        </tr>
                        <tr>
                            <th>Contact Info:</th>
                            <td>{{ $client->contact }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
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
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Balance (Units):</th>
                            <td><strong class="text-primary">{{ number_format($client->getBalanceInUnits(), 2) }} Units</strong></td>
                        </tr>
                        <tr>
                            <th>Balance (KSH):</th>
                            <td><strong class="text-success">KSH {{ number_format($client->balance, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Price Per Unit:</th>
                            <td>KSH {{ number_format($client->price_per_unit ?? 1, 2) }}</td>
                        </tr>
                        <tr>
                            <th>API Key:</th>
                            <td>
                                <code class="text-muted small">{{ substr($client->api_key, 0, 20) }}...</code>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Unit Converter Calculator -->
            <div class="mt-4 p-3 bg-light rounded">
                <h6 class="mb-3"><i class="bi bi-calculator me-2"></i>Unit Converter</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small">Units to KSH</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="unitsInput" placeholder="Enter units" step="0.01">
                            <span class="input-group-text">=</span>
                            <input type="text" class="form-control" id="kshOutput" readonly placeholder="KSH">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">KSH to Units</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="kshInput" placeholder="Enter KSH" step="0.01">
                            <span class="input-group-text">=</span>
                            <input type="text" class="form-control" id="unitsOutput" readonly placeholder="Units">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Admin View - All Senders -->
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search company name, contact...">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status')==='1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status')==='0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel me-1"></i> Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('senders.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Company Name</th>
                            <th>Contact</th>
                            <th>Balance (Units)</th>
                            <th>Balance (KSH)</th>
                            <th>Price/Unit</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $clients = \App\Models\Client::when(request('search'), function($q) {
                                $search = request('search');
                                $q->where(function($query) use ($search) {
                                    $query->where('name', 'like', "%{$search}%")
                                          ->orWhere('contact', 'like', "%{$search}%")
                                          ->orWhere('sender_id', 'like', "%{$search}%")
                                          ->orWhere('company_name', 'like', "%{$search}%");
                                });
                            })
                            ->when(request('status') !== null && request('status') !== '', function($q) {
                                $q->where('status', request('status'));
                            })
                            ->orderBy('created_at', 'desc')
                            ->paginate(15);
                        @endphp

                        @forelse($clients as $sender)
                        <tr>
                            <td>
                                <strong>{{ $sender->company_name ?? $sender->sender_id }}</strong>
                                <br><small class="text-muted">{{ $sender->name }}</small>
                            </td>
                            <td>{{ $sender->contact }}</td>
                            <td><span class="badge bg-primary">{{ number_format($sender->getBalanceInUnits(), 2) }}</span></td>
                            <td><span class="badge bg-success">KSH {{ number_format($sender->balance, 2) }}</span></td>
                            <td>KSH {{ number_format($sender->price_per_unit ?? 1, 2) }}</td>
                            <td>
                                @if($sender->status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.senders.show', $sender->id) }}" class="btn btn-sm btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.senders.edit', $sender->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-building" style="font-size: 2rem;"></i>
                                <div class="mt-2">No senders found.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $clients->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<script>
// Unit converter calculator
const pricePerUnit = {{ $client->price_per_unit ?? 1 }};

document.getElementById('unitsInput')?.addEventListener('input', function() {
    const units = parseFloat(this.value) || 0;
    const ksh = (units * pricePerUnit).toFixed(2);
    document.getElementById('kshOutput').value = ksh;
});

document.getElementById('kshInput')?.addEventListener('input', function() {
    const ksh = parseFloat(this.value) || 0;
    const units = (ksh / pricePerUnit).toFixed(2);
    document.getElementById('unitsOutput').value = units;
});
</script>
@endsection

