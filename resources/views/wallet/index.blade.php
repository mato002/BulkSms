@extends('layouts.app')

@section('page_title', 'My Wallet')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-1">💰 My Wallet</h1>
            <p class="text-muted mb-0">Manage your balance and view transaction history</p>
        </div>
        <div class="d-flex gap-2 w-100 w-md-auto flex-column flex-sm-row">
            @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-credit-card"></i> All Senders' Payments
            </a>
            @endif
            <a href="{{ route('wallet.export') }}" class="btn btn-outline-primary">
                <i class="bi bi-download"></i> Export CSV
            </a>
            <a href="{{ route('wallet.topup') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Top Up Balance
            </a>
        </div>
    </div>

    <!-- Balance Cards -->
    <div class="row g-4 mb-4">
        <!-- Current Balance -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="text-white-50 small mb-1">Current Balance</div>
                            <h2 class="mb-0">KES {{ number_format($client->balance, 2) }}</h2>
                        </div>
                        <div class="bg-white bg-opacity-25 p-2 rounded">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                    </div>
                    <div class="small text-white-50">
                        <i class="bi bi-lightning-charge-fill"></i> 
                        {{ number_format($client->getBalanceInUnits()) }} SMS Units
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Top-ups -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="text-muted small mb-1">Total Top-ups</div>
                            <h3 class="mb-0 text-success">KES {{ number_format($stats['total_topups'], 2) }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-2 rounded">
                            <i class="bi bi-arrow-down-circle-fill fs-4 text-success"></i>
                        </div>
                    </div>
                    <div class="small text-muted">All time deposits</div>
                </div>
            </div>
        </div>

        <!-- Total Spent -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="text-muted small mb-1">Total Spent</div>
                            <h3 class="mb-0 text-danger">KES {{ number_format($stats['total_spent'], 2) }}</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-2 rounded">
                            <i class="bi bi-arrow-up-circle-fill fs-4 text-danger"></i>
                        </div>
                    </div>
                    <div class="small text-muted">All time usage</div>
                </div>
            </div>
        </div>

        <!-- Pending Top-ups -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="text-muted small mb-1">Pending</div>
                            <h3 class="mb-0 text-warning">{{ $stats['pending_topups'] }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-2 rounded">
                            <i class="bi bi-clock-fill fs-4 text-warning"></i>
                        </div>
                    </div>
                    <div class="small text-muted">Awaiting confirmation</div>
                </div>
            </div>
        </div>
    </div>

    <x-list-results-frame>
    <!-- Transaction History -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('wallet.index') }}" class="row g-2" data-auto-filter data-turbo-frame="main-content">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" data-auto-filter-search
                           placeholder="Search ref, receipt..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit</option>
                        <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-1">
                    <x-per-page-select :current="request('per_page', 25)" class="form-select form-select-sm" />
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Transaction History</h5>
        </div>
        <div class="card-body p-0">
            @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">Date</th>
                            <th class="border-0">Transaction ID</th>
                            <th class="border-0">Type</th>
                            <th class="border-0">Description</th>
                            <th class="border-0">Payment Method</th>
                            <th class="border-0 text-end">Amount</th>
                            <th class="border-0">Status</th>
                            <th class="border-0">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                        <tr>
                            <td>
                                <div class="small">{{ $transaction->created_at->format('M d, Y') }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $transaction->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                <span class="font-monospace small">{{ $transaction->transaction_ref }}</span>
                            </td>
                            <td>
                                @if($transaction->type === 'credit')
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="bi bi-arrow-down-circle"></i> Credit
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">
                                        <i class="bi bi-arrow-up-circle"></i> Debit
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="small">{{ $transaction->description ?? 'N/A' }}</div>
                                @if($transaction->external_reference)
                                    <div class="text-muted" style="font-size: 0.75rem;">Ref: {{ $transaction->external_reference }}</div>
                                @endif
                            </td>
                            <td>
                                @if($transaction->payment_method)
                                    @if($transaction->payment_method === 'mpesa')
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="bi bi-phone"></i> M-Pesa
                                        </span>
                                    @elseif($transaction->payment_method === 'manual')
                                        <span class="badge bg-info-subtle text-info">
                                            <i class="bi bi-person"></i> Manual
                                        </span>
                                    @elseif($transaction->payment_method === 'partner')
                                        <span class="badge bg-primary-subtle text-primary">
                                            <i class="bi bi-plug"></i> Partner API
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            {{ ucfirst($transaction->payment_method) }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong class="{{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type === 'credit' ? '+' : '-' }} 
                                    KES {{ number_format($transaction->amount, 2) }}
                                </strong>
                            </td>
                            <td>
                                @if($transaction->status === 'completed')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Completed
                                    </span>
                                @elseif($transaction->status === 'processing')
                                    <span class="badge bg-info">
                                        <i class="bi bi-hourglass-split"></i> Processing
                                    </span>
                                @elseif($transaction->status === 'pending')
                                    <span class="badge bg-warning">
                                        <i class="bi bi-clock"></i> Pending
                                    </span>
                                @elseif($transaction->status === 'failed')
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle"></i> Failed
                                    </span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($transaction->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->mpesa_receipt)
                                    <span class="badge bg-light text-dark font-monospace">{{ $transaction->mpesa_receipt }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h5 class="mt-3 text-muted">No transactions yet</h5>
                <p class="text-muted mb-4">Your transaction history will appear here</p>
                <a href="{{ route('wallet.topup') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Make Your First Top-up
                </a>
            </div>
            @endif
        </div>
        
        @if($transactions->hasPages() || $transactions->total() > 0)
        <x-list-pagination-footer
            :paginator="$transactions"
            :export-route="route('wallet.export', request()->query())"
        />
        @endif
    </div>
    </x-list-results-frame>
</div>

@push('styles')
<style>
    .table tbody tr {
        transition: all 0.2s;
    }
    .table tbody tr:hover {
        background-color: rgba(0,0,0,0.02);
    }
</style>
@endpush
@endsection

