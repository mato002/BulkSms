@extends('layouts.app')

@section('content')
@include('components.modern-page-styles')

<div class="modern-page-container">
    <!-- Page Header -->
    <div class="modern-page-header">
        <div class="page-header-content">
            <div class="page-title-wrapper">
                <div class="page-icon-wrapper">
                    <i class="bi bi-megaphone"></i>
                </div>
                <div>
                    <h1 class="page-main-title">Campaigns</h1>
                    <p class="page-subtitle">Manage your messaging campaigns</p>
                </div>
            </div>
            <a href="{{ route('campaigns.create') }}" class="btn-primary-modern">
                <i class="bi bi-plus-lg"></i>
                <span>Create Campaign</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="stats-grid mb-4">
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-primary-gradient">
                <i class="bi bi-collection"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Total Campaigns</div>
                <div class="stat-value-modern">{{ number_format($campaigns->total()) }}</div>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-warning-gradient">
                <i class="bi bi-file-earmark"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Draft</div>
                <div class="stat-value-modern">{{ $campaigns->where('status', 'draft')->count() }}</div>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-info-gradient">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Sending</div>
                <div class="stat-value-modern">{{ $campaigns->where('status', 'sending')->count() }}</div>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-success-gradient">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Sent</div>
                <div class="stat-value-modern">{{ $campaigns->where('status', 'sent')->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="modern-card">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-funnel me-2"></i>Filter & Search
            </h3>
        </div>
        <div class="modern-card-body">
            <form method="GET" class="modern-filter-form-campaigns">
                <div class="filter-group">
                    <input type="text" name="search" class="modern-input" value="{{ request('search') }}" placeholder="Search by name or sender...">
                </div>
                <div class="filter-group">
                    <select name="channel" class="modern-select">
                        <option value="">All Channels</option>
                        <option value="sms" {{ request('channel')==='sms' ? 'selected' : '' }}>📱 SMS</option>
                        <option value="whatsapp" {{ request('channel')==='whatsapp' ? 'selected' : '' }}>💬 WhatsApp</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="status" class="modern-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status')==='draft' ? 'selected' : '' }}>📝 Draft</option>
                        <option value="sending" {{ request('status')==='sending' ? 'selected' : '' }}>⏳ Sending</option>
                        <option value="sent" {{ request('status')==='sent' ? 'selected' : '' }}>✅ Sent</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-primary-modern">
                        <i class="bi bi-search"></i>
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('campaigns.index') }}" class="btn-secondary-modern">
                        <i class="bi bi-x-circle"></i>
                        <span>Clear</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Campaigns Table -->
    <div class="modern-card mt-4">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-list-ul me-2"></i>All Campaigns
            </h3>
        </div>
        <div class="modern-card-body p-0">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Campaign Name</th>
                            <th>Channel</th>
                            <th>Sender ID</th>
                            <th>Recipients</th>
                            <th>Status</th>
                            <th>Sent/Failed</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaigns as $campaign)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $campaign->name }}</div>
                                <small class="text-muted">ID: #{{ $campaign->id }}</small>
                            </td>
                            <td>
                                <span class="badge-modern badge-{{ $campaign->channel ?? 'sms' }}">
                                    @if(($campaign->channel ?? 'sms') === 'whatsapp')
                                        <i class="bi bi-whatsapp"></i> WhatsApp
                                    @else
                                        <i class="bi bi-phone"></i> SMS
                                    @endif
                                </span>
                            </td>
                            <td>
                                <code class="text-muted">{{ $campaign->sender_id ?? '-' }}</code>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ number_format($campaign->total_recipients) }}</span>
                            </td>
                            <td>
                                @if($campaign->status === 'sent')
                                    <span class="badge-modern badge-success">
                                        <i class="bi bi-check-circle"></i> Sent
                                    </span>
                                @elseif($campaign->status === 'draft')
                                    <span class="badge-modern badge-warning">
                                        <i class="bi bi-file-earmark"></i> Draft
                                    </span>
                                @else
                                    <span class="badge-modern badge-info">
                                        <i class="bi bi-clock"></i> {{ ucfirst($campaign->status) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-success fw-semibold">{{ $campaign->sent_count ?? 0 }}</span>
                                @if(($campaign->failed_count ?? 0) > 0)
                                    <span class="text-danger">/ {{ $campaign->failed_count }}</span>
                                @endif
                            </td>
                            <td class="text-muted">
                                <div>{{ $campaign->created_at->format('M d, Y') }}</div>
                                <small>{{ $campaign->created_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-end">
                                <div class="action-buttons">
                                    <a href="{{ route('campaigns.show', $campaign->id) }}" class="btn-action btn-action-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('campaigns.edit', $campaign->id) }}" class="btn-action btn-action-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($campaign->status === 'draft')
                                        <form action="{{ route('campaigns.send', $campaign->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Send this campaign now?')">
                                            @csrf
                                            <button type="submit" class="btn-action btn-action-success" title="Send">
                                                <i class="bi bi-send"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-megaphone"></i>
                                    <h4>No Campaigns Found</h4>
                                    <p>Get started by creating your first campaign</p>
                                    <a href="{{ route('campaigns.create') }}" class="btn-primary-small mt-3">
                                        <i class="bi bi-plus-circle"></i>
                                        Create Campaign
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($campaigns->hasPages())
        <div class="modern-card-footer">
            {{ $campaigns->links('vendor.pagination.simple') }}
        </div>
        @endif
    </div>
</div>

<style>
.modern-filter-form-campaigns {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-action-info:hover {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

@media (max-width: 992px) {
    .modern-filter-form-campaigns {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        width: 100%;
    }
    
    .filter-actions .btn-primary-modern,
    .filter-actions .btn-secondary-modern {
        flex: 1;
    }
}
</style>
@endsection
