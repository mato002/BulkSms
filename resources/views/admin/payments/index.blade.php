@extends('layouts.app')

@section('page_title', 'All Payments')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="mb-1"><i class="bi bi-credit-card me-2"></i>All Payments</h1>
            <p class="text-muted mb-0">Incoming and outgoing wallet activity across every sender</p>
        </div>
        <a href="{{ route('admin.payments.export', request()->query()) }}" class="btn btn-outline-primary">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-0 small">Credits Today</p>
                    <h3 class="mb-0 text-success">KES {{ number_format($stats['credits_today'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-0 small">Credits This Month</p>
                    <h3 class="mb-0 text-success">KES {{ number_format($stats['credits_month'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-0 small">Pending</p>
                    <h3 class="mb-0 text-warning">{{ $stats['pending_count'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-0 small">All-Time Credits</p>
                    <h3 class="mb-0">KES {{ number_format($stats['total_credits'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <x-list-results-frame>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-3" data-auto-filter data-turbo-frame="main-content">
                <div class="col-md-3">
                    <input type="text" name="search" data-auto-filter-search value="{{ request('search') }}"
                           placeholder="Search ref, receipt, sender..."
                           class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="client_id" class="form-select">
                        <option value="">All Senders</option>
                        @foreach($senders as $sender)
                            <option value="{{ $sender->id }}" {{ (string) request('client_id') === (string) $sender->id ? 'selected' : '' }}>
                                {{ $sender->sender_id }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit (incoming)</option>
                        <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit (usage)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="payment_method" class="form-select">
                        <option value="">All Sources</option>
                        <option value="mpesa" {{ request('payment_method') === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                        <option value="partner" {{ request('payment_method') === 'partner' ? 'selected' : '' }}>Partner API</option>
                        <option value="manual" {{ request('payment_method') === 'manual' ? 'selected' : '' }}>Manual</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control" placeholder="From">
                </div>
                <div class="col-md-3">
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control" placeholder="To">
                </div>
                <div class="col-md-2">
                    <x-per-page-select :current="request('per_page', 25)" class="form-select" />
                </div>
                @if(request()->hasAny(['search', 'client_id', 'type', 'payment_method', 'status', 'from_date', 'to_date']))
                    <div class="col-md-2">
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary w-100" data-turbo-frame="main-content">Clear</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Sender</th>
                            <th>Type</th>
                            <th>Source</th>
                            <th>Reference</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th>Receipt / Phone</th>
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
                                @if($transaction->client)
                                    <a href="{{ route('admin.senders.show', $transaction->client_id) }}" class="text-decoration-none">
                                        <span class="badge bg-primary-subtle text-primary font-monospace">{{ $transaction->client->sender_id }}</span>
                                    </a>
                                    <div class="small text-muted">{{ $transaction->client->name }}</div>
                                @else
                                    <span class="text-muted">Unknown</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->type === 'credit')
                                    <span class="badge bg-success-subtle text-success">Credit</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Debit</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->payment_method === 'mpesa')
                                    <span class="badge bg-success-subtle text-success"><i class="bi bi-phone"></i> M-Pesa</span>
                                @elseif($transaction->payment_method === 'partner')
                                    <span class="badge bg-primary-subtle text-primary"><i class="bi bi-plug"></i> Partner API</span>
                                @elseif($transaction->payment_method === 'manual')
                                    <span class="badge bg-info-subtle text-info"><i class="bi bi-person"></i> Manual</span>
                                @elseif($transaction->type === 'debit')
                                    <span class="badge bg-secondary-subtle text-secondary"><i class="bi bi-chat-dots"></i> SMS Usage</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $transaction->payment_method_label }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="font-monospace small d-block">{{ $transaction->transaction_ref }}</span>
                                @if($transaction->external_reference)
                                    <span class="small text-muted">Ext: {{ $transaction->external_reference }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">{{ $transaction->description ?? '—' }}</div>
                            </td>
                            <td class="text-end">
                                <strong class="{{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type === 'credit' ? '+' : '-' }} KES {{ number_format($transaction->amount, 2) }}
                                </strong>
                            </td>
                            <td>
                                @if($transaction->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($transaction->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($transaction->status === 'processing')
                                    <span class="badge bg-info">Processing</span>
                                @elseif($transaction->status === 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($transaction->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->mpesa_receipt)
                                    <span class="badge bg-light text-dark font-monospace">{{ $transaction->mpesa_receipt }}</span>
                                @elseif($transaction->payment_phone)
                                    <span class="small text-muted">{{ $transaction->payment_phone }}</span>
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
                <i class="bi bi-inbox display-4 text-muted"></i>
                <p class="text-muted mt-3 mb-0">No payments match your filters.</p>
            </div>
            @endif
        </div>

        <x-list-pagination-footer
            :paginator="$transactions"
            :export-route="route('admin.payments.export', request()->query())"
        />
    </div>
    </x-list-results-frame>
</div>
@endsection
