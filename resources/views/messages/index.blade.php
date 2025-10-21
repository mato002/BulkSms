@extends('layouts.app')

@section('content')
<div class="modern-page-container">
    <!-- Page Header -->
    <div class="modern-page-header">
        <div class="page-header-content">
            <div class="page-title-wrapper">
                <div class="page-icon-wrapper">
                    <i class="bi bi-chat-dots-fill"></i>
                </div>
                <div>
                    <h1 class="page-main-title">Conversations</h1>
                    <p class="page-subtitle">View and manage all your message conversations</p>
                </div>
            </div>
            <a href="{{ route('messages.all') }}" class="btn-primary-modern">
                <i class="bi bi-envelope-paper"></i>
                <span>All Messages & Earnings</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid mb-4">
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-primary-gradient">
                <i class="bi bi-chat-square-text"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Total Conversations</div>
                <div class="stat-value-modern">{{ number_format($conversations->total()) }}</div>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-warning-gradient">
                <i class="bi bi-clock"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Open</div>
                <div class="stat-value-modern">{{ $conversations->where('status', 'open')->count() }}</div>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-danger-gradient">
                <i class="bi bi-envelope-exclamation"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Unread</div>
                <div class="stat-value-modern">{{ $conversations->where('unread_count', '>', 0)->count() }}</div>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-success-gradient">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Resolved</div>
                <div class="stat-value-modern">{{ $conversations->where('status', 'resolved')->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="modern-card">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-funnel me-2"></i>Filter Conversations
            </h3>
        </div>
        <div class="modern-card-body">
            <form method="GET" action="{{ route('messages.index') }}" class="modern-filter-form-messages">
                <div class="filter-group">
                    <input type="text" class="modern-input" name="search" placeholder="Search name, phone, or message..." value="{{ request('search') }}">
                </div>
                <div class="filter-group">
                    <select class="modern-select" name="channel">
                        <option value="">All Channels</option>
                        <option value="sms" {{ request('channel') === 'sms' ? 'selected' : '' }}>📱 SMS</option>
                        <option value="whatsapp" {{ request('channel') === 'whatsapp' ? 'selected' : '' }}>💬 WhatsApp</option>
                        <option value="email" {{ request('channel') === 'email' ? 'selected' : '' }}>✉️ Email</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select class="modern-select" name="status">
                        <option value="">All Status</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>⏳ Open</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>✅ Resolved</option>
                        <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>📦 Archived</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-primary-modern">
                        <i class="bi bi-search"></i>
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('messages.index') }}" class="btn-secondary-modern">
                        <i class="bi bi-x-circle"></i>
                        <span>Clear</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Conversations Table -->
    <div class="modern-card mt-4">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-list-ul me-2"></i>All Conversations
            </h3>
        </div>
        <div class="modern-card-body p-0">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Contact</th>
                            <th>Phone/Identifier</th>
                            <th>Channel</th>
                            <th>Last Message</th>
                            <th>Direction</th>
                            <th>Status</th>
                            <th>Last Activity</th>
                            <th>Unread</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($conversations as $c)
                        <tr class="{{ ($c->unread_count ?? 0) > 0 ? 'unread-row' : '' }}">
                            <td>
                                <div class="contact-info-cell">
                                    <div class="contact-avatar-small">
                                        {{ strtoupper(substr($c->contact_name ?? 'U', 0, 1)) }}
                                    </div>
                                    <span class="fw-semibold">{{ $c->contact_name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="text-muted">{{ $c->contact_phone }}</td>
                            <td>
                                <span class="badge-modern badge-{{ $c->channel }}">
                                    @if($c->channel === 'sms')
                                        <i class="bi bi-phone"></i>
                                    @elseif($c->channel === 'whatsapp')
                                        <i class="bi bi-whatsapp"></i>
                                    @else
                                        <i class="bi bi-envelope"></i>
                                    @endif
                                    {{ strtoupper($c->channel) }}
                                </span>
                            </td>
                            <td>
                                <div class="message-preview">{{ $c->last_message_preview }}</div>
                            </td>
                            <td>
                                @if($c->last_message_direction === 'inbound')
                                    <span class="badge-direction badge-inbound">
                                        <i class="bi bi-arrow-down"></i> Inbound
                                    </span>
                                @else
                                    <span class="badge-direction badge-outbound">
                                        <i class="bi bi-arrow-up"></i> Outbound
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($c->status === 'resolved')
                                    <span class="badge-status badge-resolved">
                                        <i class="bi bi-check-circle"></i> Resolved
                                    </span>
                                @elseif($c->status === 'archived')
                                    <span class="badge-status badge-archived">
                                        <i class="bi bi-archive"></i> Archived
                                    </span>
                                @else
                                    <span class="badge-status badge-open">
                                        <i class="bi bi-clock"></i> Open
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $c->last_message_at ? \Carbon\Carbon::parse($c->last_message_at)->diffForHumans() : '-' }}</td>
                            <td>
                                @if(($c->unread_count ?? 0) > 0)
                                    <span class="unread-badge">{{ $c->unread_count }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('inbox.show', $c->id) }}" class="btn-action-chat">
                                    <i class="bi bi-chat-dots"></i>
                                    <span>Open Chat</span>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-chat-square-text"></i>
                                    <h4>No Conversations Found</h4>
                                    <p>Start by sending a message to your contacts</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($conversations->hasPages())
        <div class="modern-card-footer">
            {{ $conversations->links('vendor.pagination.simple') }}
        </div>
        @endif
    </div>
</div>

<style>
/* Modern Page Container */
.modern-page-container {
    padding: 1.5rem;
    max-width: 100%;
}

/* Page Header */
.modern-page-header {
    margin-bottom: 1.5rem;
}

.page-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.page-title-wrapper {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.page-icon-wrapper {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.page-main-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.page-subtitle {
    color: #64748b;
    margin: 0.25rem 0 0;
    font-size: 0.875rem;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-card-modern {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s;
}

.stat-card-modern:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.stat-icon-modern {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: white;
    flex-shrink: 0;
}

.bg-primary-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-success-gradient {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.bg-warning-gradient {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.bg-danger-gradient {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.stat-content-modern {
    flex: 1;
}

.stat-label-modern {
    font-size: 0.75rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.stat-value-modern {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
}

/* Modern Card */
.modern-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.modern-card-header {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.modern-card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
}

.modern-card-body {
    padding: 1.25rem;
}

.modern-card-footer {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

/* Filter Form */
.modern-filter-form-messages {
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

.modern-input,
.modern-select {
    padding: 0.625rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: all 0.2s;
    width: 100%;
}

.modern-input:focus,
.modern-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Buttons */
.btn-primary-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    text-decoration: none;
}

.btn-primary-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    color: white;
}

.btn-secondary-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.5rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #64748b;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary-modern:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #475569;
}

/* Modern Table */
.modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.modern-table thead th {
    background: #f8fafc;
    color: #64748b;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
}

.modern-table tbody tr {
    transition: background 0.2s;
}

.modern-table tbody tr:hover {
    background: #f8fafc;
}

.modern-table tbody tr.unread-row {
    background: rgba(102, 126, 234, 0.05);
}

.modern-table tbody td {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.875rem;
}

.contact-info-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.contact-avatar-small {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.75rem;
    flex-shrink: 0;
}

.message-preview {
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #64748b;
}

/* Badges */
.badge-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-sms {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.badge-whatsapp {
    background: rgba(37, 211, 102, 0.1);
    color: #25D366;
}

.badge-email {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.badge-direction {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-inbound {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.badge-outbound {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-open {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.badge-resolved {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.badge-archived {
    background: rgba(100, 116, 139, 0.1);
    color: #64748b;
}

.unread-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    height: 24px;
    padding: 0 0.5rem;
    background: #ef4444;
    color: white;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.btn-action-chat {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    color: #667eea;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-action-chat:hover {
    background: #667eea;
    border-color: #667eea;
    color: white;
}

/* Empty State */
.empty-state {
    padding: 2rem;
}

.empty-state i {
    font-size: 3rem;
    color: #cbd5e1;
    margin-bottom: 1rem;
}

.empty-state h4 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #64748b;
    margin-bottom: 0;
}

/* Responsive */
@media (max-width: 992px) {
    .modern-filter-form-messages {
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

@media (max-width: 768px) {
    .modern-page-container {
        padding: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .message-preview {
        max-width: 150px;
    }
}
</style>
@endsection
