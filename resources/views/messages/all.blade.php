@extends('layouts.app')

@section('content')
<div class="modern-page-container">
    <!-- Debug Info (Remove this section once you confirm everything works) -->
    @if(isset($debugInfo))
    <div class="alert alert-info mb-4" style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 8px; padding: 1rem;">
        <h5 style="margin: 0 0 0.5rem 0; color: #1976d2;">🔍 Debug Info - Senders Found</h5>
        <p style="margin: 0;"><strong>Total Senders:</strong> {{ $debugInfo['total_senders_found'] }}</p>
        <p style="margin: 0.5rem 0 0 0;"><strong>Senders List:</strong> 
            @if(count($debugInfo['senders_list']) > 0)
                <span style="color: #1976d2; font-weight: 600;">
                    {{ implode(', ', $debugInfo['senders_list']) }}
                </span>
            @else
                <span style="color: #d32f2f;">No senders found - This means no messages have sender field populated</span>
            @endif
        </p>
        <p style="margin: 0.5rem 0 0 0;"><strong>Analytics Cards:</strong> {{ $debugInfo['analytics_count'] }} sender(s) will be displayed below</p>
    </div>
    @endif

    <!-- Page Header -->
    <div class="modern-page-header">
        <div class="page-header-content">
            <div class="page-title-wrapper">
                <div class="page-icon-wrapper">
                    <i class="bi bi-envelope-paper-fill"></i>
                </div>
                <div>
                    <h1 class="page-main-title">All Messages</h1>
                    <p class="page-subtitle">View all messages with sender breakdown and earnings analytics</p>
                </div>
            </div>
            <a href="{{ route('messages.index') }}" class="btn-secondary-modern">
                <i class="bi bi-chat-dots"></i>
                <span>Conversations View</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid mb-4">
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-primary-gradient">
                <i class="bi bi-envelope-check"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Total Messages</div>
                <div class="stat-value-modern">{{ number_format($stats['total_messages']) }}</div>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-success-gradient">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Sent</div>
                <div class="stat-value-modern">{{ number_format($stats['sent']) }}</div>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-danger-gradient">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Failed</div>
                <div class="stat-value-modern">{{ number_format($stats['failed']) }}</div>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-warning-gradient">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Pending</div>
                <div class="stat-value-modern">{{ number_format($stats['pending']) }}</div>
            </div>
        </div>
        <div class="stat-card-modern stat-card-highlight">
            <div class="stat-icon-modern bg-earnings-gradient">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Total Earnings</div>
                <div class="stat-value-modern">KSH {{ number_format($stats['total_cost'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Sender Analytics Section -->
    @if($senderAnalytics->count() > 0)
    <div class="modern-card mb-4">
        <div class="modern-card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
            <h3 class="modern-card-title" style="color: white; font-size: 1.125rem;">
                <i class="bi bi-bar-chart-fill me-2"></i>
                Sender Performance & Earnings 
                <span class="badge" style="background: rgba(255,255,255,0.3); padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem; margin-left: 0.5rem;">
                    {{ $senderAnalytics->count() }} {{ $senderAnalytics->count() === 1 ? 'Sender' : 'Senders' }}
                </span>
            </h3>
            <p style="color: rgba(255,255,255,0.9); margin: 0.5rem 0 0 0; font-size: 0.875rem;">
                All your senders are listed below with their performance metrics
            </p>
        </div>
        <div class="modern-card-body">
            <div class="sender-analytics-grid">
                @foreach($senderAnalytics as $index => $analytics)
                <div class="sender-analytics-card" style="border: 2px solid {{ ['#667eea', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'][$index % 6] }};">
                    <div class="sender-analytics-header">
                        <div class="sender-name-badge" style="font-size: 1rem;">
                            <i class="bi bi-person-badge"></i> {{ $analytics->sender }}
                        </div>
                        <div class="sender-earnings" style="font-size: 1.5rem;">KSH {{ number_format($analytics->total_earnings, 2) }}</div>
                    </div>
                    <div class="sender-analytics-stats">
                        <div class="sender-stat">
                            <span class="sender-stat-label">Total</span>
                            <span class="sender-stat-value">{{ number_format($analytics->total_messages) }}</span>
                        </div>
                        <div class="sender-stat">
                            <span class="sender-stat-label">Success</span>
                            <span class="sender-stat-value text-success">{{ number_format($analytics->successful_messages) }}</span>
                        </div>
                        <div class="sender-stat">
                            <span class="sender-stat-label">Failed</span>
                            <span class="sender-stat-value text-danger">{{ number_format($analytics->failed_messages) }}</span>
                        </div>
                        <div class="sender-stat">
                            <span class="sender-stat-label">Success Rate</span>
                            <span class="sender-stat-value">
                                {{ $analytics->total_messages > 0 ? number_format(($analytics->successful_messages / $analytics->total_messages) * 100, 1) : 0 }}%
                            </span>
                        </div>
                    </div>
                    <div class="sender-analytics-footer">
                        <a href="{{ route('messages.all', ['sender' => $analytics->sender]) }}" class="btn-filter-sender">
                            <i class="bi bi-funnel"></i> View Messages
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @else
    <div class="modern-card mb-4">
        <div class="modern-card-header" style="background: #fff3cd; border-color: #ffc107;">
            <h3 class="modern-card-title" style="color: #856404;">
                <i class="bi bi-exclamation-triangle me-2"></i>No Senders Found
            </h3>
        </div>
        <div class="modern-card-body">
            <p style="margin: 0; color: #856404;">
                No sender analytics available. This could mean:
            </p>
            <ul style="margin: 0.5rem 0 0 1.5rem; color: #856404;">
                <li>No messages have been sent yet</li>
                <li>Messages don't have sender field populated</li>
                <li>All messages have NULL sender values</li>
            </ul>
            <p style="margin: 1rem 0 0 0; color: #856404;">
                <strong>Tip:</strong> When sending messages via API, make sure to include the "sender" field.
            </p>
        </div>
    </div>
    @endif

    <!-- Filter Card -->
    <div class="modern-card">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-funnel me-2"></i>Filter Messages
            </h3>
        </div>
        <div class="modern-card-body">
            <form method="GET" action="{{ route('messages.all') }}" class="modern-filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label">Search</label>
                        <input type="text" class="modern-input" name="search" placeholder="Recipient, body, or sender..." value="{{ request('search') }}">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Sender</label>
                        <select class="modern-select" name="sender">
                            <option value="">All Senders</option>
                            @foreach($senders as $sender)
                                <option value="{{ $sender }}" {{ request('sender') === $sender ? 'selected' : '' }}>{{ $sender }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Channel</label>
                        <select class="modern-select" name="channel">
                            <option value="">All Channels</option>
                            <option value="sms" {{ request('channel') === 'sms' ? 'selected' : '' }}>📱 SMS</option>
                            <option value="whatsapp" {{ request('channel') === 'whatsapp' ? 'selected' : '' }}>💬 WhatsApp</option>
                            <option value="email" {{ request('channel') === 'email' ? 'selected' : '' }}>✉️ Email</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select class="modern-select" name="status">
                            <option value="">All Status</option>
                            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>✅ Sent</option>
                            <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>📬 Delivered</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>❌ Failed</option>
                            <option value="queued" {{ request('status') === 'queued' ? 'selected' : '' }}>⏳ Queued</option>
                            <option value="sending" {{ request('status') === 'sending' ? 'selected' : '' }}>📤 Sending</option>
                        </select>
                    </div>
                </div>
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label">Date From</label>
                        <input type="date" class="modern-input" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Date To</label>
                        <input type="date" class="modern-input" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="filter-group"></div>
                    <div class="filter-group"></div>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-primary-modern">
                        <i class="bi bi-search"></i>
                        <span>Apply Filters</span>
                    </button>
                    <a href="{{ route('messages.all') }}" class="btn-secondary-modern">
                        <i class="bi bi-x-circle"></i>
                        <span>Clear All</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="modern-card mt-4">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-list-ul me-2"></i>Messages
                @if(request('sender'))
                    <span class="badge-modern badge-primary ms-2">Filtered by: {{ request('sender') }}</span>
                @endif
            </h3>
        </div>
        <div class="modern-card-body p-0">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Sender</th>
                            <th>Recipient</th>
                            <th>Channel</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Cost</th>
                            <th>Sent At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $message)
                        <tr>
                            <td class="text-muted">#{{ $message->id }}</td>
                            <td>
                                <span class="sender-badge">{{ $message->sender ?? 'N/A' }}</span>
                            </td>
                            <td class="fw-semibold">{{ $message->recipient }}</td>
                            <td>
                                <span class="badge-modern badge-{{ $message->channel }}">
                                    @if($message->channel === 'sms')
                                        <i class="bi bi-phone"></i>
                                    @elseif($message->channel === 'whatsapp')
                                        <i class="bi bi-whatsapp"></i>
                                    @else
                                        <i class="bi bi-envelope"></i>
                                    @endif
                                    {{ strtoupper($message->channel) }}
                                </span>
                            </td>
                            <td>
                                <div class="message-preview">{{ Str::limit($message->body, 50) }}</div>
                            </td>
                            <td>
                                @if(in_array($message->status, ['sent', 'delivered']))
                                    <span class="badge-status badge-success">
                                        <i class="bi bi-check-circle"></i> {{ ucfirst($message->status) }}
                                    </span>
                                @elseif($message->status === 'failed')
                                    <span class="badge-status badge-failed">
                                        <i class="bi bi-x-circle"></i> Failed
                                    </span>
                                @elseif($message->status === 'sending')
                                    <span class="badge-status badge-sending">
                                        <i class="bi bi-arrow-clockwise"></i> Sending
                                    </span>
                                @else
                                    <span class="badge-status badge-pending">
                                        <i class="bi bi-clock"></i> {{ ucfirst($message->status) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if(in_array($message->status, ['sent', 'delivered']))
                                    <span class="cost-badge">KSH {{ number_format($message->cost, 2) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-muted">
                                @if($message->sent_at)
                                    {{ \Carbon\Carbon::parse($message->sent_at)->format('M d, Y H:i') }}
                                @else
                                    {{ \Carbon\Carbon::parse($message->created_at)->format('M d, Y H:i') }}
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-envelope-x"></i>
                                    <h4>No Messages Found</h4>
                                    <p>Try adjusting your filters or send your first message</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($messages->hasPages())
        <div class="modern-card-footer">
            {{ $messages->links('vendor.pagination.simple') }}
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

.stat-card-highlight {
    border: 2px solid #f59e0b;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
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

.bg-earnings-gradient {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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

/* Sender Analytics Grid */
.sender-analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.sender-analytics-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
    background: white;
    transition: all 0.2s;
}

.sender-analytics-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.sender-analytics-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.sender-name-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
}

.sender-earnings {
    font-size: 1.25rem;
    font-weight: 700;
    color: #059669;
}

.sender-analytics-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.sender-stat {
    text-align: center;
    padding: 0.5rem;
    background: #f8fafc;
    border-radius: 6px;
}

.sender-stat-label {
    display: block;
    font-size: 0.625rem;
    color: #64748b;
    text-transform: uppercase;
    margin-bottom: 0.25rem;
}

.sender-stat-value {
    display: block;
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
}

.sender-analytics-footer {
    text-align: center;
}

.btn-filter-sender {
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

.btn-filter-sender:hover {
    background: #667eea;
    border-color: #667eea;
    color: white;
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
.modern-filter-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
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

.modern-table tbody td {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.875rem;
}

.sender-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    border: 1px solid rgba(102, 126, 234, 0.3);
    border-radius: 6px;
    color: #667eea;
    font-weight: 600;
    font-size: 0.875rem;
}

.message-preview {
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #64748b;
}

.cost-badge {
    display: inline-block;
    padding: 0.25rem 0.625rem;
    background: rgba(5, 150, 105, 0.1);
    border-radius: 6px;
    color: #059669;
    font-weight: 600;
    font-size: 0.875rem;
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

.badge-primary {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
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

.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-success {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.badge-failed {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.badge-sending {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.badge-pending {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
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
@media (max-width: 1200px) {
    .sender-analytics-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
}

@media (max-width: 992px) {
    .filter-row {
        grid-template-columns: 1fr 1fr;
    }
    
    .sender-analytics-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .modern-page-container {
        padding: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-row {
        grid-template-columns: 1fr;
    }
    
    .sender-analytics-grid {
        grid-template-columns: 1fr;
    }
    
    .message-preview {
        max-width: 150px;
    }
    
    .page-header-content {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
@endsection

