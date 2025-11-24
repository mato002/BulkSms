@extends('layouts.app')

@section('content')
<div class="modern-page-container">
    <!-- Page Header -->
    <div class="modern-page-header">
        <div class="page-header-content">
            <div class="page-title-wrapper">
                <div class="page-icon-wrapper">
                    <i class="bi bi-gear-fill"></i>
                </div>
                <div>
                    <h1 class="page-main-title">Settings</h1>
                    <p class="page-subtitle">Manage your account settings and preferences</p>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn-secondary-modern" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Refresh</span>
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Client Information -->
    <div class="modern-card mb-4">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-building me-2"></i>Client Information
            </h3>
        </div>
        <div class="modern-card-body">
            <form action="{{ route('settings.client.update') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-semibold">Company Name</label>
                        <input type="text" class="modern-input" id="name" name="name" value="{{ $client->name ?? '' }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="contact" class="form-label fw-semibold">Contact</label>
                        <input type="text" class="modern-input" id="contact" name="contact" value="{{ $client->contact ?? '' }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="sender_id" class="form-label fw-semibold">Default Sender ID</label>
                        <input type="text" class="modern-input" id="sender_id" name="sender_id" value="{{ $client->sender_id ?? '' }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Balance</label>
                        <input type="text" class="modern-input" value="$ {{ number_format($client->balance ?? 0, 2) }}" readonly>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn-primary-modern">
                        <i class="bi bi-save"></i>
                        <span>Update Client Info</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- API Keys -->
    <div class="modern-card mb-4">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-key me-2"></i>API Keys & Authentication
            </h3>
        </div>
        <div class="modern-card-body">
            <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Important:</strong> Use this API key in the <code>X-API-KEY</code> header for all API requests.
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Your API Key</label>
                <div class="input-group">
                    <input type="text" class="modern-input font-monospace" id="apiKeyInput" value="{{ $client->api_key ?? '' }}" readonly>
                    <button class="btn-secondary-modern" type="button" onclick="copyApiKey()">
                        <i class="bi bi-clipboard"></i>
                        <span>Copy</span>
                    </button>
                </div>
            </div>

            <form action="{{ route('settings.regenerate-api-key') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning" onclick="confirmAction(event, 'Regenerate API Key?', 'This will invalidate the current key and break existing integrations!', 'Yes, regenerate it!')">
                    <i class="bi bi-arrow-clockwise me-2"></i>Regenerate API Key
                </button>
            </form>

            <hr class="my-4">

            <h6 class="mb-3"><i class="bi bi-list-ul me-2"></i>API Endpoints</h6>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>Endpoint</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge bg-success">POST</span></td>
                            <td><code>/api/{company_id}/messages/send</code></td>
                            <td>Send SMS/WhatsApp/Email</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-primary">GET</span></td>
                            <td><code>/api/{company_id}/sms/history</code></td>
                            <td>Get message history</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-primary">GET</span></td>
                            <td><code>/api/{company_id}/contacts</code></td>
                            <td>List contacts</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-success">POST</span></td>
                            <td><code>/api/{company_id}/contacts/bulk-import</code></td>
                            <td>Import contacts</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Channel Providers -->
    <div class="modern-card mb-4">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-broadcast-pin me-2"></i>Channel Providers
            </h3>
        </div>
        <div class="modern-card-body">
            @foreach($channelsWithCreds as $channel)
                @php
                    $collapseId = 'channel-' . ($channel->id ?? $channel->name);
                    $isSelectedChannel = isset($selectedChannel) && $selectedChannel === $channel->name;
                @endphp
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0">
                                <i class="bi bi-{{ $channel->channel_info['icon'] ?? 'broadcast' }} me-2"></i>
                                <span class="badge bg-secondary me-2">{{ strtoupper($channel->name) }}</span>
                                {{ ucfirst($channel->provider) }} Provider
                            </h6>
                            @if(isset($channel->channel_info['description']))
                                <small class="text-muted">{{ $channel->channel_info['description'] }}</small>
                            @endif
                        </div>
                        <div>
                            @if(isset($channel->exists) && !$channel->exists)
                                <span class="badge bg-warning"><i class="bi bi-exclamation-triangle me-1"></i>Not Configured</span>
                            @elseif($channel->active ?? false)
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Inactive</span>
                            @endif
                        </div>
                    </div>

                    @if(isset($channel->exists) && !$channel->exists)
                        <!-- Channel doesn't exist - show create button -->
                        <form action="{{ route('settings.channel.create') }}" method="POST">
                            @csrf
                            <input type="hidden" name="name" value="{{ $channel->name }}">
                            <input type="hidden" name="provider" value="{{ $channel->provider }}">
                            <button type="submit" class="btn-primary-modern">
                                <i class="bi bi-plus-circle"></i>
                                <span>Create {{ ucfirst($channel->name) }} Channel</span>
                            </button>
                            <p class="text-muted mt-2 mb-0">
                                <small>This will create a new {{ ucfirst($channel->name) }} channel that you can then configure.</small>
                            </p>
                        </form>
                    @else
                        <!-- Channel exists - show edit form -->
                        <button class="btn-secondary-modern mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="{{ $isSelectedChannel ? 'true' : 'false' }}">
                            <i class="bi bi-pencil"></i>
                            <span>Edit Configuration</span>
                        </button>

                        <div class="collapse {{ $isSelectedChannel ? 'show' : '' }}" id="{{ $collapseId }}">
                            <form action="{{ route('settings.channel.update', $channel->id) }}" method="POST" class="border-top pt-3">
                                @csrf

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Status</label>
                                        <select class="modern-select" name="active" required>
                                            <option value="1" {{ ($channel->active ?? false) ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !($channel->active ?? false) ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                @if($channel->provider === 'onfon')
                                <h6 class="mt-4 mb-3 text-primary"><i class="bi bi-gear-fill me-2"></i>Onfon Credentials</h6>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">API Key</label>
                                        <input type="text" class="modern-input font-monospace" name="api_key" 
                                               value="{{ $channel->credentials_array['api_key'] ?? '' }}" 
                                               placeholder="VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak=">
                                        <small class="text-muted">Your Onfon API key (ApiKey parameter)</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Client ID (UUID)</label>
                                        <input type="text" class="modern-input font-monospace" name="client_id_value" 
                                               value="{{ $channel->credentials_array['client_id'] ?? '' }}" 
                                               placeholder="e27847c1-a9fe-4eef-b60d-ddb291b175ab">
                                        <small class="text-muted">Your Onfon tenant/client ID (ClientId parameter)</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Access Key Header</label>
                                        <input type="text" class="modern-input font-monospace" name="access_key_header" 
                                               value="{{ $channel->credentials_array['access_key_header'] ?? '' }}" 
                                               placeholder="8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB">
                                        <small class="text-muted">AccessKey header value for authentication</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Default Sender ID</label>
                                        <input type="text" class="modern-input" name="default_sender" 
                                               value="{{ $channel->credentials_array['default_sender'] ?? '' }}" 
                                               placeholder="PRADY_TECH">
                                        <small class="text-muted">Approved sender ID from Onfon portal</small>
                                    </div>
                                </div>

                                <div class="alert alert-warning mt-3">
                                    <strong><i class="bi bi-exclamation-triangle me-2"></i>Important:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Get these credentials from your Onfon account manager</li>
                                        <li>Sender ID must be approved in Onfon portal</li>
                                        <li>Ensure your IP is whitelisted (if required)</li>
                                        <li>API endpoint: <code>https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS</code></li>
                                    </ul>
                                </div>
                                @elseif($channel->provider === 'smtp')
                                    <h6 class="mt-4 mb-3 text-primary"><i class="bi bi-gear-fill me-2"></i>SMTP Email Credentials</h6>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">SMTP Host</label>
                                            <input type="text" class="modern-input" name="smtp_host" 
                                                   value="{{ $channel->credentials_array['host'] ?? 'smtp.gmail.com' }}" 
                                                   placeholder="smtp.gmail.com">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">SMTP Port</label>
                                            <input type="number" class="modern-input" name="smtp_port" 
                                                   value="{{ $channel->credentials_array['port'] ?? 587 }}" 
                                                   placeholder="587">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Username/Email</label>
                                            <input type="text" class="modern-input" name="smtp_username" 
                                                   value="{{ $channel->credentials_array['username'] ?? '' }}" 
                                                   placeholder="your-email@gmail.com">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Password</label>
                                            <input type="password" class="modern-input" name="smtp_password" 
                                                   value="{{ $channel->credentials_array['password'] ?? '' }}" 
                                                   placeholder="Your email password or app password">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Encryption</label>
                                            <select class="modern-select" name="smtp_encryption">
                                                <option value="tls" {{ ($channel->credentials_array['encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                                <option value="ssl" {{ ($channel->credentials_array['encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">From Email</label>
                                            <input type="email" class="modern-input" name="from_email" 
                                                   value="{{ $channel->credentials_array['from_email'] ?? '' }}" 
                                                   placeholder="noreply@example.com">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">From Name</label>
                                            <input type="text" class="modern-input" name="from_name" 
                                                   value="{{ $channel->credentials_array['from_name'] ?? '' }}" 
                                                   placeholder="Your Company Name">
                                        </div>
                                    </div>
                                @elseif($channel->provider === 'ultramsg' || $channel->provider === 'whatsapp_cloud')
                                    <h6 class="mt-4 mb-3 text-primary"><i class="bi bi-gear-fill me-2"></i>WhatsApp Credentials</h6>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>Note:</strong> WhatsApp channels are configured via the <a href="{{ route('whatsapp.configure') }}">WhatsApp Configuration</a> page.
                                    </div>
                                @endif

                                <button type="submit" class="btn-primary-modern mt-3">
                                    <i class="bi bi-save"></i>
                                    <span>Save Channel Configuration</span>
                                </button>
                            </form>
                        </div>

                        <!-- Current Configuration Display -->
                        <div class="mt-3">
                            <small class="text-muted">
                                <strong>Current Settings:</strong> 
                                @if($channel->provider === 'onfon')
                                    API Key: <code class="text-muted">{{ isset($channel->credentials_array['api_key']) && !empty($channel->credentials_array['api_key']) ? substr($channel->credentials_array['api_key'], 0, 20) . '...' : 'Not set' }}</code>
                                @elseif($channel->provider === 'smtp')
                                    Host: <code class="text-muted">{{ $channel->credentials_array['host'] ?? 'Not set' }}</code>
                                @else
                                    Provider: {{ ucfirst($channel->provider) }}
                                @endif
                            </small>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Webhook URLs -->
    <div class="modern-card mb-4">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-link-45deg me-2"></i>Webhook URLs
            </h3>
        </div>
        <div class="modern-card-body">
            <div class="mb-3">
                <label class="form-label fw-semibold">SMS Webhook</label>
                <div class="input-group">
                    <input type="text" class="modern-input font-monospace" value="{{ url('/api/webhooks/sms') }}" readonly>
                    <button class="btn-secondary-modern" type="button" onclick="copyToClipboard('{{ url('/api/webhooks/sms') }}')">
                        <i class="bi bi-clipboard"></i>
                        <span>Copy</span>
                    </button>
                </div>
                <small class="text-muted">Configure this in Onfon portal → Settings → Webhooks</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">WhatsApp Webhook</label>
                <div class="input-group">
                    <input type="text" class="modern-input font-monospace" value="{{ url('/api/webhooks/whatsapp') }}" readonly>
                    <button class="btn-secondary-modern" type="button" onclick="copyToClipboard('{{ url('/api/webhooks/whatsapp') }}')">
                        <i class="bi bi-clipboard"></i>
                        <span>Copy</span>
                    </button>
                </div>
                <small class="text-muted">Configure in WhatsApp Business API settings</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email Webhook</label>
                <div class="input-group">
                    <input type="text" class="modern-input font-monospace" value="{{ url('/api/webhooks/email') }}" readonly>
                    <button class="btn-secondary-modern" type="button" onclick="copyToClipboard('{{ url('/api/webhooks/email') }}')">
                        <i class="bi bi-clipboard"></i>
                        <span>Copy</span>
                    </button>
                </div>
                <small class="text-muted">Configure in your email provider settings</small>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="modern-card">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-info-circle me-2"></i>System Information
            </h3>
        </div>
        <div class="modern-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Company ID</label>
                    <input type="text" class="modern-input font-monospace" value="{{ $client->id ?? 'N/A' }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Account Status</label>
                    <input type="text" class="modern-input" value="{{ isset($client->active) && $client->active ? 'Active' : 'Inactive' }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Created</label>
                    <input type="text" class="modern-input" value="{{ $client->created_at ? (is_string($client->created_at) ? $client->created_at : $client->created_at->format('M d, Y')) : 'N/A' }}" readonly>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyApiKey() {
    const apiKeyInput = document.getElementById('apiKeyInput');
    apiKeyInput.select();
    apiKeyInput.setSelectionRange(0, 99999); // For mobile devices
    
    navigator.clipboard.writeText(apiKeyInput.value).then(function() {
        alert('API Key copied to clipboard!');
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Webhook URL copied to clipboard!');
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>

@if(Auth::user()->isAdmin())
<!-- Admin Settings Section -->
<div class="modern-page-container mt-4">
    <div class="modern-card">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-shield-lock me-2"></i>Admin Settings
            </h3>
        </div>
        <div class="modern-card-body">
        @if($adminSettings && $adminSettings->count() > 0)
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <!-- Balance & Notification Settings -->
            <form method="POST" action="{{ route('settings.admin.update') }}" id="adminSettingsForm">
                @csrf
                <!-- Hidden admin_phone field for legacy compatibility -->
                <input type="hidden" name="admin_phone" value="{{ $adminSettings->where('key', 'admin_phone')->first()->value ?? '254722295194' }}">
                <div class="row g-3 mb-4">
                    <!-- Low Balance Threshold -->
                    <div class="col-md-4">
                        <label for="low_balance_threshold" class="form-label">
                            <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                            Low Balance Threshold
                        </label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control" 
                                   id="low_balance_threshold" 
                                   name="low_balance_threshold" 
                                   value="{{ $adminSettings->where('key', 'low_balance_threshold')->first()->value ?? 1000 }}"
                                   min="1" 
                                   step="1">
                            <span class="input-group-text">units</span>
                        </div>
                        <div class="form-text">Send SMS alert when Onfon balance drops below this number</div>
                    </div>

                    <!-- Refresh Interval -->
                    <div class="col-md-4">
                        <label for="balance_refresh_interval" class="form-label">
                            <i class="bi bi-clock text-info me-2"></i>
                            Balance Refresh Interval
                        </label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control" 
                                   id="balance_refresh_interval" 
                                   name="balance_refresh_interval" 
                                   value="{{ $adminSettings->where('key', 'balance_refresh_interval')->first()->value ?? 60 }}"
                                   min="1" 
                                   max="1440" 
                                   step="1">
                            <span class="input-group-text">minutes</span>
                        </div>
                        <div class="form-text">How often to automatically refresh Onfon balance</div>
                    </div>

                    <!-- Current Balance Display -->
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="bi bi-wallet text-success me-2"></i>
                            Current Onfon Balance
                        </label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ number_format($onfonBalance, 2) }}" 
                                   readonly>
                            <span class="input-group-text">units</span>
                        </div>
                        <div class="form-text">
                            @if($onfonCacheAvailable)
                                Last refreshed: {{ $onfonBalanceLastRefreshed }}
                            @else
                                Cache unavailable – showing fallback value.
                            @endif
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary-modern" id="updateAdminSettingsBtn">
                    <i class="bi bi-save"></i>
                    <span>Update Admin Settings</span>
                </button>
            </form>
        @endif

        <!-- Phone Numbers Management -->
        <div class="mt-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="bi bi-phone text-primary me-2"></i>
                    Alert Phone Numbers
                </h5>
                <button type="button" class="btn-primary-modern" data-bs-toggle="modal" data-bs-target="#addPhoneModal">
                    <i class="bi bi-plus-lg"></i>
                    <span>Add Phone Number</span>
                </button>
            </div>

            @if($phoneNumbers && $phoneNumbers->count() > 0)
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone Number</th>
                                <th>Notes</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($phoneNumbers as $phone)
                                <tr>
                                    <td><strong>{{ $phone->name ?: 'No name' }}</strong></td>
                                    <td><code>{{ $phone->phone_number }}</code></td>
                                    <td><small class="text-muted">{{ $phone->notes ?: 'No notes' }}</small></td>
                                    <td>
                                        @if($phone->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Disabled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('settings.phone.toggle', $phone->id) }}" 
                                               class="btn btn-outline-{{ $phone->is_active ? 'warning' : 'success' }}"
                                               title="{{ $phone->is_active ? 'Disable' : 'Enable' }}">
                                                <i class="bi bi-{{ $phone->is_active ? 'pause' : 'play' }}"></i>
                                            </a>
                                            <a href="{{ route('settings.phone.delete', $phone->id) }}" 
                                               class="btn btn-outline-danger"
                                               onclick="event.preventDefault(); Swal.fire({title: 'Are you sure?', text: 'Are you sure you want to delete this phone number?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, delete it!', cancelButtonText: 'Cancel', reverseButtons: true}).then((result) => { if (result.isConfirmed) { window.location.href = '{{ route('settings.phone.delete', $phone->id) }}'; } });"
                                               title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-phone text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No phone numbers added yet</p>
                    <button type="button" class="btn-primary-modern" data-bs-toggle="modal" data-bs-target="#addPhoneModal">
                        <i class="bi bi-plus-lg"></i>
                        <span>Add First Phone Number</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Phone Number Modal -->
<div class="modal fade" id="addPhoneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('settings.phone.add') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Alert Phone Number</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="phone_number" class="form-label fw-semibold">Phone Number *</label>
                        <input type="text" 
                               class="modern-input" 
                               id="phone_number" 
                               name="phone_number" 
                               placeholder="254722295194"
                               required>
                        <small class="text-muted">Include country code (e.g., 254722295194)</small>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Contact Name</label>
                        <input type="text" 
                               class="modern-input" 
                               id="name" 
                               name="name" 
                               placeholder="John Doe">
                        <small class="text-muted">Optional name for this contact</small>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold">Notes</label>
                        <textarea class="modern-input" 
                                  id="notes" 
                                  name="notes" 
                                  rows="2"
                                  placeholder="Optional notes about this contact"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-modern">
                        <i class="bi bi-plus-lg"></i>
                        <span>Add Phone Number</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
// Debug admin settings form
document.addEventListener('DOMContentLoaded', function() {
    const adminForm = document.getElementById('adminSettingsForm');
    const submitBtn = document.getElementById('updateAdminSettingsBtn');
    
    if (adminForm && submitBtn) {
        console.log('Admin settings form found');
        
        adminForm.addEventListener('submit', function(e) {
            console.log('Admin settings form submitted');
            console.log('Form data:', new FormData(adminForm));
            
            // Show loading state
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Updating...';
            submitBtn.disabled = true;
        });
        
        submitBtn.addEventListener('click', function(e) {
            console.log('Update admin settings button clicked');
        });
    } else {
        console.log('Admin settings form not found');
    }
});
</script>
@endsection

