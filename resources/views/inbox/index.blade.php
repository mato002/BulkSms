@extends('layouts.app')

@section('content')
@include('components.modern-page-styles')

<div class="modern-page-container">
    <!-- Page Header -->
    <div class="modern-page-header">
        <div class="page-header-content">
            <div class="page-title-wrapper">
                <div class="page-icon-wrapper">
                    <i class="bi bi-inbox-fill"></i>
                </div>
                <div>
                    <h1 class="page-main-title">Inbox</h1>
                    <p class="page-subtitle">Manage your conversations and messages</p>
                </div>
            </div>
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
            <form method="GET" action="{{ route('inbox.index') }}" class="modern-filter-form-inbox">
                <div class="filter-group">
                    <input type="text" class="modern-input" name="search" placeholder="Search conversations..." value="{{ request('search') }}">
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
                    <a href="{{ route('inbox.index') }}" class="btn-secondary-modern">
                        <i class="bi bi-x-circle"></i>
                        <span>Clear</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Conversations List -->
    <div class="modern-card mt-4">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-list-ul me-2"></i>All Conversations
            </h3>
        </div>
        <div class="modern-card-body p-0">
            <div class="conversation-list">
                @forelse($conversations as $conv)
                <a href="{{ route('inbox.show', $conv->id) }}" class="conversation-item {{ $conv->unread_count > 0 ? 'unread' : '' }}">
                    <div class="conversation-avatar">
                        {{ strtoupper(substr($conv->contact_name, 0, 1)) }}
                    </div>
                    <div class="conversation-content">
                        <div class="conversation-header">
                            <h6 class="conversation-name">
                                {{ $conv->contact_name }}
                                @if($conv->unread_count > 0)
                                    <span class="unread-badge-small">{{ $conv->unread_count }}</span>
                                @endif
                            </h6>
                            <small class="conversation-time">
                                {{ $conv->last_message_at ? \Carbon\Carbon::parse($conv->last_message_at)->diffForHumans() : 'Never' }}
                            </small>
                        </div>
                        <div class="conversation-meta">
                            <small class="conversation-phone">{{ $conv->contact_phone }}</small>
                            <div class="conversation-badges">
                                <span class="badge-modern badge-{{ $conv->channel }}">
                                    @if($conv->channel === 'sms')
                                        <i class="bi bi-phone"></i>
                                    @elseif($conv->channel === 'whatsapp')
                                        <i class="bi bi-whatsapp"></i>
                                    @else
                                        <i class="bi bi-envelope"></i>
                                    @endif
                                </span>
                                @if($conv->status === 'resolved')
                                    <span class="badge-modern badge-success"><i class="bi bi-check-circle"></i></span>
                                @elseif($conv->status === 'archived')
                                    <span class="badge-modern badge-info"><i class="bi bi-archive"></i></span>
                                @endif
                            </div>
                        </div>
                        <p class="conversation-preview">
                            @if($conv->last_message_direction === 'inbound')
                                <i class="bi bi-arrow-down-circle text-success me-1"></i>
                            @else
                                <i class="bi bi-arrow-up-circle text-primary me-1"></i>
                            @endif
                            {{ $conv->last_message_preview }}
                        </p>
                    </div>
                </a>
                @empty
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h4>No Conversations Yet</h4>
                    <p>Conversations will appear here when customers reply to your messages</p>
                </div>
                @endforelse
            </div>
        </div>
        @if($conversations->hasPages())
        <div class="modern-card-footer">
            {{ $conversations->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<style>
/* Filter Form Grid */
.modern-filter-form-inbox {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

/* Conversation List */
.conversation-list {
    display: flex;
    flex-direction: column;
}

.conversation-item {
    display: flex;
    gap: 1rem;
    padding: 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s;
}

.conversation-item:hover {
    background: #f8fafc;
}

.conversation-item:last-child {
    border-bottom: none;
}

.conversation-item.unread {
    background: rgba(102, 126, 234, 0.05);
}

.conversation-item.unread .conversation-name {
    font-weight: 700;
}

.conversation-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
    flex-shrink: 0;
}

.conversation-content {
    flex: 1;
    min-width: 0;
}

.conversation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.25rem;
}

.conversation-name {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.conversation-time {
    color: #94a3b8;
    font-size: 0.75rem;
}

.conversation-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.conversation-phone {
    color: #64748b;
    font-size: 0.8125rem;
}

.conversation-badges {
    display: flex;
    gap: 0.375rem;
}

.conversation-preview {
    color: #64748b;
    font-size: 0.875rem;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.unread-badge-small {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    padding: 0 0.375rem;
    background: #ef4444;
    color: white;
    border-radius: 10px;
    font-size: 0.6875rem;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 992px) {
    .modern-filter-form-inbox {
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
    .conversation-item {
        padding: 1rem;
    }
    
    .conversation-avatar {
        width: 40px;
        height: 40px;
        font-size: 0.875rem;
    }
    
    .conversation-preview {
        font-size: 0.8125rem;
    }
}
</style>
@endsection
