@extends('layouts.app')

@section('content')
@include('components.modern-page-styles')

<div class="modern-page-container">
    <!-- Page Header -->
    <div class="modern-page-header">
        <div class="page-header-content">
            <div class="page-title-wrapper">
                <div class="page-icon-wrapper">
                    <i class="bi bi-eye"></i>
                </div>
                <div>
                    <h1 class="page-main-title">{{ $campaign->name }}</h1>
                    <p class="page-subtitle">Campaign details and analytics</p>
                </div>
            </div>
            <div class="header-buttons">
                @if($campaign->status === 'draft')
                    <form action="{{ route('campaigns.send', $campaign->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn-primary-modern" onclick="return confirm('Send this campaign now?')">
                            <i class="bi bi-send"></i>
                            <span>Send Campaign</span>
                        </button>
                    </form>
                @endif
                <a href="{{ route('campaigns.edit', $campaign->id) }}" class="btn-secondary-modern">
                    <i class="bi bi-pencil"></i>
                    <span>Edit</span>
                </a>
                <a href="{{ route('campaigns.index') }}" class="btn-secondary-modern">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back</span>
                </a>
            </div>
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
            <div class="stat-icon-modern bg-info-gradient">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Total Recipients</div>
                <div class="stat-value-modern">{{ number_format($campaign->total_recipients) }}</div>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-primary-gradient">
                <i class="bi bi-send"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Sent</div>
                <div class="stat-value-modern">{{ number_format($campaign->sent_count ?? 0) }}</div>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-success-gradient">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Delivered</div>
                <div class="stat-value-modern">{{ number_format($campaign->delivered_count ?? 0) }}</div>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-danger-gradient">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Failed</div>
                <div class="stat-value-modern">{{ number_format($campaign->failed_count ?? 0) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Campaign Details -->
        <div class="col-lg-8">
            <div class="modern-card mb-4">
                <div class="modern-card-header">
                    <h3 class="modern-card-title">
                        <i class="bi bi-info-circle me-2"></i>Campaign Information
                    </h3>
                </div>
                <div class="modern-card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Channel</label>
                                <div class="info-value">
                                    <span class="badge-modern badge-{{ $campaign->channel ?? 'sms' }}">
                                        @if(($campaign->channel ?? 'sms') === 'whatsapp')
                                            <i class="bi bi-whatsapp"></i> WhatsApp
                                        @else
                                            <i class="bi bi-phone"></i> SMS
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Sender ID</label>
                                <div class="info-value">
                                    <code>{{ $campaign->sender_id ?? 'N/A' }}</code>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Status</label>
                                <div class="info-value">
                                    @if($campaign->status === 'sent')
                                        <span class="badge-modern badge-success"><i class="bi bi-check-circle"></i> Sent</span>
                                    @elseif($campaign->status === 'draft')
                                        <span class="badge-modern badge-warning"><i class="bi bi-file-earmark"></i> Draft</span>
                                    @else
                                        <span class="badge-modern badge-info"><i class="bi bi-clock"></i> {{ ucfirst($campaign->status) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Created</label>
                                <div class="info-value">
                                    {{ $campaign->created_at->format('M d, Y h:i A') }}
                                    <small class="text-muted d-block">{{ $campaign->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Content -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h3 class="modern-card-title">
                        <i class="bi bi-chat-text me-2"></i>Message Content
                    </h3>
                </div>
                <div class="modern-card-body">
                    <div class="message-preview-box">
                        {{ $campaign->message }}
                    </div>
                    <div class="mt-3 text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        <span class="text-muted">{{ strlen($campaign->message) }} characters</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Recipients Summary -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h3 class="modern-card-title">
                        <i class="bi bi-people me-2"></i>Recipients
                    </h3>
                </div>
                <div class="modern-card-body">
                    <div class="recipient-summary">
                        <div class="summary-item">
                            <span class="summary-label">Total</span>
                            <span class="summary-value">{{ number_format($campaign->total_recipients) }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Sent</span>
                            <span class="summary-value text-primary">{{ number_format($campaign->sent_count ?? 0) }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Delivered</span>
                            <span class="summary-value text-success">{{ number_format($campaign->delivered_count ?? 0) }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Failed</span>
                            <span class="summary-value text-danger">{{ number_format($campaign->failed_count ?? 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-item {
    margin-bottom: 1rem;
}

.info-label {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.375rem;
    font-weight: 600;
}

.info-value {
    font-size: 0.9375rem;
    color: #1e293b;
    font-weight: 500;
}

.message-preview-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.25rem;
    font-size: 0.9375rem;
    line-height: 1.6;
    color: #1e293b;
    white-space: pre-wrap;
}

.recipient-summary {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 8px;
}

.summary-label {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 500;
}

.summary-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1e293b;
}
</style>
@endsection
