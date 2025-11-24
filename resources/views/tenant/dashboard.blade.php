@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Dashboard Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Welcome back, {{ $user->name }}!</h1>
                    <p class="text-muted mb-0">{{ $client->company_name }} Dashboard</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('tenant.profile') }}" class="btn btn-outline-primary">
                        <i class="fas fa-user-cog"></i> Profile
                    </a>
                    <a href="{{ route('tenant.billing') }}" class="btn btn-primary">
                        <i class="fas fa-wallet"></i> Top Up
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Balance Warning -->
    @if($lowBalanceWarning['is_low'])
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Low Balance Alert:</strong> {{ $lowBalanceWarning['message'] }}
                <a href="{{ route('tenant.billing') }}" class="alert-link">Top up now</a>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xxl-3 col-lg-3 col-md-6">
            <div class="card stat-card stat-card-primary h-100">
                <div class="card-body">
                    <div>
                        <div class="stat-label">Total Contacts</div>
                        <div class="stat-value">{{ number_format($stats['total_contacts']) }}</div>
                    </div>
                    <span class="stat-icon stat-icon-primary">
                        <i class="fas fa-users"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-lg-3 col-md-6">
            <div class="card stat-card stat-card-success h-100">
                <div class="card-body">
                    <div>
                        <div class="stat-label">Messages Sent</div>
                        <div class="stat-value">{{ number_format($stats['total_messages_sent']) }}</div>
                    </div>
                    <span class="stat-icon stat-icon-success">
                        <i class="fas fa-paper-plane"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-lg-3 col-md-6">
            <div class="card stat-card stat-card-info h-100">
                <div class="card-body">
                    <div>
                        <div class="stat-label">Success Rate</div>
                        <div class="stat-value">{{ $stats['success_rate'] }}%</div>
                        <div class="stat-subtext">Delivery performance</div>
                    </div>
                    <span class="stat-icon stat-icon-info">
                        <i class="fas fa-chart-line"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-lg-3 col-md-6">
            <div class="card stat-card stat-card-warning h-100">
                <div class="card-body">
                    <div>
                        <div class="stat-label">Current Balance</div>
                        <div class="stat-value">KES {{ number_format($stats['balance'], 2) }}</div>
                        <div class="stat-subtext">{{ number_format($stats['balance_in_units']) }} units</div>
                    </div>
                    <span class="stat-icon stat-icon-warning">
                        <i class="fas fa-wallet"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- Recent Activity -->
        <div class="col-xl-8 col-lg-7">
            <div class="card section-card h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                    <a href="{{ route('messages.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($recentActivity->count() > 0)
                        <div class="timeline">
                            @foreach($recentActivity as $activity)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-{{ $activity['color'] }}"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">{{ $activity['title'] }}</h6>
                                    <p class="timeline-text">{{ $activity['description'] }}</p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> {{ $activity['time']->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No recent activity to show.</p>
                            <a href="{{ route('campaigns.create') }}" class="btn btn-primary">Create Your First Campaign</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions & Scheduled Campaigns -->
        <div class="col-xl-4 col-lg-5">
            <div class="d-flex flex-column gap-4">
            <!-- Quick Actions -->
            <div class="card section-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('campaigns.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Campaign
                        </a>
                        <a href="{{ route('contacts.create') }}" class="btn btn-outline-primary">
                            <i class="fas fa-user-plus"></i> Add Contact
                        </a>
                        <a href="{{ route('contacts.import') }}" class="btn btn-outline-primary">
                            <i class="fas fa-file-import"></i> Import Contacts
                        </a>
                        <a href="{{ route('tenant.api-docs') }}" class="btn btn-outline-primary">
                            <i class="fas fa-code"></i> API Documentation
                        </a>
                    </div>
                </div>
            </div>

            <!-- Scheduled Campaigns -->
            <div class="card section-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Scheduled Campaigns</h6>
                </div>
                <div class="card-body">
                    @if($scheduledCampaigns->count() > 0)
                        @foreach($scheduledCampaigns as $campaign)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">{{ $campaign->name }}</h6>
                                <small class="text-muted">
                                    {{ $campaign->scheduled_at->format('M d, Y H:i') }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                        <a href="{{ route('campaigns.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300 mb-2"></i>
                            <p class="text-muted mb-0">No scheduled campaigns</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Environment Configuration -->
            <div class="card section-card section-card-info">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-file-code"></i> Environment Configuration (.env)
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <i class="fas fa-info-circle"></i> Copy these settings to your application's <code>.env</code> file for easy API integration.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-2">Configuration Block</label>
                        <div class="position-relative">
                            <textarea class="form-control font-monospace small" 
                                      id="envConfigTextarea" 
                                      rows="8" 
                                      readonly 
                                      style="font-size: 0.85rem; line-height: 1.6; background-color: #f8f9fa;"># BulkSMS API Configuration
# Generated for: {{ $client->company_name }}
# Client ID: {{ $client->id }}

BULKSMS_API_URL={{ url('/api') }}
BULKSMS_CLIENT_ID={{ $client->id }}
BULKSMS_API_KEY={{ $client->api_key }}
BULKSMS_SENDER_ID={{ $client->sender_id }}

# API Endpoints
BULKSMS_SEND_URL={{ url('/api') }}/{{ $client->id }}/messages/send
BULKSMS_BALANCE_URL={{ url('/api') }}/{{ $client->id }}/client/balance
BULKSMS_HISTORY_URL={{ url('/api') }}/{{ $client->id }}/messages/history</textarea>
                            <button class="btn btn-primary btn-sm position-absolute" 
                                    style="top: 10px; right: 10px; z-index: 10;"
                                    onclick="copyEnvConfig()"
                                    title="Copy entire .env configuration">
                                <i class="fas fa-copy"></i> Copy All
                            </button>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Paste this into your <code>.env</code> file and use these variables in your application code.
                            </small>
                        </div>
                    </div>
                    <div class="alert alert-warning small mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Security:</strong> Keep your API key secure. Never commit it to version control or share it publicly.
                    </div>
                </div>
            </div>

            <!-- Account Status -->
            <div class="card section-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Account Status</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 mb-0 text-{{ $client->status ? 'success' : 'warning' }}">
                                    <i class="fas fa-{{ $client->status ? 'check-circle' : 'clock' }}"></i>
                                </div>
                                <small class="text-muted">Status</small>
                                <div class="small">{{ $client->status ? 'Active' : 'Pending' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 mb-0 text-info">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <small class="text-muted">Tier</small>
                                <div class="small">{{ ucfirst($client->tier) }}</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">
                            <i class="fas fa-id-card"></i> Client ID
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control font-monospace small" 
                                   value="{{ $client->id }}" 
                                   readonly 
                                   id="clientIdInput">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    onclick="copyToClipboard('{{ $client->id }}', 'clientIdInput')"
                                    title="Copy Client ID">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Used in API endpoints: <code>/api/{{ $client->id }}/...</code>
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Sender ID</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control font-monospace small" 
                                   value="{{ $client->sender_id }}" 
                                   readonly 
                                   id="senderIdInput">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    onclick="copyToClipboard('{{ $client->sender_id }}', 'senderIdInput')"
                                    title="Copy Sender ID">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">API Key</label>
                        <div class="input-group input-group-sm">
                            @if(!$client->status)
                                <input type="text" class="form-control font-monospace small" 
                                       value="{{ substr($client->api_key, 0, 10) }}************************" 
                                       readonly 
                                       id="apiKeyInput">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="copyToClipboard('{{ $client->api_key }}', 'apiKeyInput')"
                                        title="Copy API Key (Full key will be copied)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            @else
                                <input type="text" class="form-control font-monospace small" 
                                       value="{{ $client->api_key }}" 
                                       readonly 
                                       id="apiKeyInput">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="copyToClipboard('{{ $client->api_key }}', 'apiKeyInput')"
                                        title="Copy API Key">
                                    <i class="fas fa-copy"></i>
                                </button>
                            @endif
                        </div>
                        <small class="text-muted">
                            @if(!$client->status)
                                <span class="text-warning">
                                    <i class="fas fa-lock"></i> Account pending activation. Full key will be copied.
                                </span><br>
                            @endif
                            <a href="{{ route('tenant.api-docs') }}" class="text-decoration-none">
                                <i class="fas fa-info-circle"></i> View API Documentation
                            </a>
                        </small>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    <!-- Monthly Statistics -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card section-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">This Month's Activity</h6>
                </div>
                <div class="card-body">
                    <div class="row row-cols-2 row-cols-md-4 g-4 text-center monthly-stats-grid">
                        <div class="col">
                            <div class="monthly-stat">
                                <div class="monthly-stat-value text-primary">{{ number_format($stats['messages_this_month']) }}</div>
                                <div class="monthly-stat-label">Messages Sent</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="monthly-stat">
                                <div class="monthly-stat-value text-success">{{ number_format($stats['total_campaigns']) }}</div>
                                <div class="monthly-stat-label">Total Campaigns</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="monthly-stat">
                                <div class="monthly-stat-value text-info">{{ number_format($stats['total_tags']) }}</div>
                                <div class="monthly-stat-label">Contact Tags</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="monthly-stat">
                                <div class="monthly-stat-value text-warning">{{ number_format($stats['onfon_balance'], 2) }}</div>
                                <div class="monthly-stat-label">Onfon Balance</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    position: relative;
    border: none;
    border-radius: 1rem;
    box-shadow: 0 12px 32px rgba(15, 30, 67, 0.08);
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.stat-card .card-body {
    padding: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
}
.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 38px rgba(15, 30, 67, 0.12);
}
.stat-label {
    font-size: 0.75rem;
    letter-spacing: 0.08rem;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 0.35rem;
}
.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.3rem;
}
.stat-subtext {
    font-size: 0.82rem;
    font-weight: 500;
    color: #6b7280;
}
.stat-icon {
    width: 3.25rem;
    height: 3.25rem;
    border-radius: 0.85rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
}
.stat-card::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: inherit;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.stat-card:hover::after {
    opacity: 1;
}
.stat-card-primary::after {
    background: linear-gradient(135deg, rgba(78, 115, 223, 0.18), rgba(78, 115, 223, 0));
}
.stat-card-success::after {
    background: linear-gradient(135deg, rgba(28, 200, 138, 0.18), rgba(28, 200, 138, 0));
}
.stat-card-info::after {
    background: linear-gradient(135deg, rgba(54, 185, 204, 0.18), rgba(54, 185, 204, 0));
}
.stat-card-warning::after {
    background: linear-gradient(135deg, rgba(246, 194, 62, 0.2), rgba(246, 194, 62, 0));
}
.stat-card-primary .stat-label,
.stat-card-primary .stat-icon {
    color: #4e73df;
}
.stat-card-primary .stat-icon {
    background: rgba(78, 115, 223, 0.12);
}
.stat-card-success .stat-label,
.stat-card-success .stat-icon {
    color: #1cc88a;
}
.stat-card-success .stat-icon {
    background: rgba(28, 200, 138, 0.12);
}
.stat-card-info .stat-label,
.stat-card-info .stat-icon {
    color: #36b9cc;
}
.stat-card-info .stat-icon {
    background: rgba(54, 185, 204, 0.12);
}
.stat-card-warning .stat-label,
.stat-card-warning .stat-icon {
    color: #f6c23e;
}
.stat-card-warning .stat-icon {
    background: rgba(246, 194, 62, 0.14);
}
@media (max-width: 575.98px) {
    .stat-card .card-body {
        flex-direction: column;
        align-items: flex-start;
    }
    .stat-card .stat-icon {
        align-self: flex-start;
    }
}

.section-card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 12px 32px rgba(15, 30, 67, 0.08);
    overflow: hidden;
}
.section-card .card-header {
    background: transparent;
    border-bottom: none;
    padding: 1.25rem 1.5rem 0.75rem;
}
.section-card .card-body {
    padding: 1.5rem;
}
.section-card .card-header h6 {
    color: #0f172a;
    font-weight: 700;
}
.section-card-info .card-header {
    background: linear-gradient(135deg, rgba(54, 185, 204, 0.12), rgba(54, 185, 204, 0.02));
}
.section-card-info .card-header h6 {
    color: #0f3d56;
}
.section-card-info .card-body {
    background: #f8fbff;
}

.monthly-stats-grid {
    margin-top: 0.5rem;
}
.monthly-stat {
    border-radius: 0.85rem;
    background: #f5f7fb;
    padding: 1.25rem 1rem;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4);
}
.monthly-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}
.monthly-stat-label {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.06rem;
    color: #6b7280;
    font-weight: 600;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #e9ecef;
}

.timeline-title {
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.timeline-text {
    margin-bottom: 5px;
    color: #666;
    font-size: 0.9rem;
}

/* Toast notification styles */
.toast-notification {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>

<script>
// Copy to clipboard function
function copyToClipboard(text, inputId) {
    // Try using the Clipboard API first (modern browsers)
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            showCopySuccess(inputId);
        }).catch(function(err) {
            console.error('Clipboard API failed:', err);
            fallbackCopyToClipboard(text, inputId);
        });
    } else {
        // Fallback for older browsers or non-secure contexts
        fallbackCopyToClipboard(text, inputId);
    }
}

// Fallback copy method for older browsers
function fallbackCopyToClipboard(text, inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.select();
        input.setSelectionRange(0, 99999); // For mobile devices
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopySuccess(inputId);
            } else {
                showCopyError();
            }
        } catch (err) {
            console.error('Fallback copy failed:', err);
            showCopyError();
        }
        
        // Deselect the text
        window.getSelection().removeAllRanges();
    } else {
        showCopyError();
    }
}

// Show success notification
function showCopySuccess(inputId) {
    // Remove any existing toast
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create success toast
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = '<i class="fas fa-check"></i> Copied to clipboard!';
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        z-index: 9999;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 8px;
    `;
    document.body.appendChild(toast);
    
    // Remove toast after 2 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 2000);
    
    // Visual feedback on the input field
    const input = document.getElementById(inputId);
    if (input) {
        const originalBg = input.style.backgroundColor;
        input.style.backgroundColor = '#d4edda';
        input.style.transition = 'background-color 0.3s';
        setTimeout(() => {
            input.style.backgroundColor = originalBg;
        }, 300);
    }
}

// Show error notification
function showCopyError() {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Failed to copy. Please select and copy manually.';
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #dc3545;
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        z-index: 9999;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 8px;
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Copy entire .env configuration
function copyEnvConfig() {
    const textarea = document.getElementById('envConfigTextarea');
    if (!textarea) {
        showCopyError();
        return;
    }
    
    // Select all text in textarea
    textarea.select();
    textarea.setSelectionRange(0, 99999); // For mobile devices
    
    const text = textarea.value;
    
    // Try using the Clipboard API first (modern browsers)
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            showEnvCopySuccess();
            // Deselect text
            window.getSelection().removeAllRanges();
        }).catch(function(err) {
            console.error('Clipboard API failed:', err);
            fallbackCopyEnv(text);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyEnv(text);
    }
}

// Fallback copy method for .env configuration
function fallbackCopyEnv(text) {
    const textarea = document.getElementById('envConfigTextarea');
    if (!textarea) {
        showCopyError();
        return;
    }
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showEnvCopySuccess();
        } else {
            showCopyError();
        }
    } catch (err) {
        console.error('Fallback copy failed:', err);
        showCopyError();
    }
    
    // Deselect the text
    window.getSelection().removeAllRanges();
    textarea.blur();
}

// Show success notification for .env copy
function showEnvCopySuccess() {
    // Remove any existing toast
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create success toast
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = '<i class="fas fa-check"></i> .env configuration copied to clipboard!';
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #17a2b8;
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        z-index: 9999;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 8px;
    `;
    document.body.appendChild(toast);
    
    // Visual feedback on textarea
    const textarea = document.getElementById('envConfigTextarea');
    if (textarea) {
        const originalBg = textarea.style.backgroundColor;
        const originalBorder = textarea.style.borderColor;
        textarea.style.backgroundColor = '#d1ecf1';
        textarea.style.borderColor = '#17a2b8';
        textarea.style.transition = 'all 0.3s';
        setTimeout(() => {
            textarea.style.backgroundColor = originalBg;
            textarea.style.borderColor = originalBorder;
        }, 500);
    }
    
    // Remove toast after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}
</script>
@endsection


