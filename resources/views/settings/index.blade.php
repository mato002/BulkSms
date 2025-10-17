@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1><i class="bi bi-gear-fill me-2"></i>Settings</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Client Information -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-building me-2"></i>Client Information</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('settings.client.update') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $client->name ?? '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact</label>
                            <input type="text" class="form-control" id="contact" name="contact" value="{{ $client->contact ?? '' }}" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sender_id" class="form-label">Default Sender ID</label>
                            <input type="text" class="form-control" id="sender_id" name="sender_id" value="{{ $client->sender_id ?? '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Balance</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control" value="{{ number_format($client->balance ?? 0, 2) }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update Client Info</button>
            </form>
        </div>
    </div>

    <!-- API Keys -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-key me-2"></i>API Keys & Authentication</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Important:</strong> Use this API key in the <code>X-API-KEY</code> header for all API requests.
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Your API Key</label>
                <div class="input-group">
                    <input type="text" class="form-control font-monospace" id="apiKeyInput" value="{{ $client->api_key ?? '' }}" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyApiKey()">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
            </div>

            <form action="{{ route('settings.regenerate-api-key') }}" method="POST" onsubmit="return confirm('Regenerate API key? This will invalidate the current key and break existing integrations!')">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-arrow-clockwise me-1"></i> Regenerate API Key
                </button>
            </form>

            <hr class="my-4">

            <h6 class="mb-3">API Endpoints</h6>
            <div class="table-responsive">
                <table class="table table-sm">
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
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-broadcast-pin me-2"></i>Channel Providers</h5>
        </div>
        <div class="card-body">
            @forelse($channelsWithCreds as $channel)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">
                            <span class="badge bg-secondary me-2">{{ strtoupper($channel->name) }}</span>
                            {{ ucfirst($channel->provider) }} Provider
                        </h6>
                        <div>
                            @if($channel->active)
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Inactive</span>
                            @endif
                        </div>
                    </div>

                    <button class="btn btn-sm btn-outline-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#channel-{{ $channel->id }}">
                        <i class="bi bi-pencil me-1"></i> Edit Configuration
                    </button>

                    <div class="collapse" id="channel-{{ $channel->id }}">
                        <form action="{{ route('settings.channel.update', $channel->id) }}" method="POST" class="border-top pt-3">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="active" required>
                                            <option value="1" {{ $channel->active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$channel->active ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            @if($channel->provider === 'onfon')
                                <h6 class="mb-3 text-primary"><i class="bi bi-gear-fill me-2"></i>Onfon Credentials</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">API Key</label>
                                    <input type="text" class="form-control font-monospace" name="api_key" 
                                           value="{{ $channel->credentials_array['api_key'] ?? '' }}" 
                                           placeholder="VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak=">
                                    <small class="text-muted">Your Onfon API key (ApiKey parameter)</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Client ID (UUID)</label>
                                    <input type="text" class="form-control font-monospace" name="client_id_value" 
                                           value="{{ $channel->credentials_array['client_id'] ?? '' }}" 
                                           placeholder="e27847c1-a9fe-4eef-b60d-ddb291b175ab">
                                    <small class="text-muted">Your Onfon tenant/client ID (ClientId parameter)</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Access Key Header</label>
                                    <input type="text" class="form-control font-monospace" name="access_key_header" 
                                           value="{{ $channel->credentials_array['access_key_header'] ?? '' }}" 
                                           placeholder="8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB">
                                    <small class="text-muted">AccessKey header value for authentication</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Default Sender ID</label>
                                    <input type="text" class="form-control" name="default_sender" 
                                           value="{{ $channel->credentials_array['default_sender'] ?? '' }}" 
                                           placeholder="PRADY_TECH">
                                    <small class="text-muted">Approved sender ID from Onfon portal</small>
                                </div>

                                <div class="alert alert-warning">
                                    <strong><i class="bi bi-exclamation-triangle me-2"></i>Important:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Get these credentials from your Onfon account manager</li>
                                        <li>Sender ID must be approved in Onfon portal</li>
                                        <li>Ensure your IP is whitelisted (if required)</li>
                                        <li>API endpoint: <code>https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS</code></li>
                                    </ul>
                                </div>
                            @endif

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Channel Configuration
                            </button>
                        </form>
                    </div>

                    <!-- Current Configuration Display -->
                    <div class="mt-3">
                        <small class="text-muted">
                            <strong>Current Config:</strong><br>
                            API Key: {{ isset($channel->credentials_array['api_key']) ? (Str::limit($channel->credentials_array['api_key'], 20) . '...') : 'Not set' }}<br>
                            Client ID: {{ isset($channel->credentials_array['client_id']) ? Str::limit($channel->credentials_array['client_id'], 30) : 'Not set' }}<br>
                            Sender: {{ $channel->credentials_array['default_sender'] ?? 'Not set' }}
                        </small>
                    </div>
                </div>
            @empty
                <p class="text-muted">No channels configured.</p>
            @endforelse
        </div>
    </div>

    <!-- Webhook Configuration -->
    <div class="card mb-4">
        <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="bi bi-webhook me-2"></i>Webhook URLs</h5>
        </div>
        <div class="card-body">
            <p class="mb-3">Configure these webhook URLs in your provider portals to receive delivery reports and inbound messages.</p>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Onfon Delivery Reports</label>
                <div class="input-group">
                    <input type="text" class="form-control font-monospace" value="{{ url('/api/webhooks/onfon/dlr') }}" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ url('/api/webhooks/onfon/dlr') }}')">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
                <small class="text-muted">Configure this in Onfon portal → Settings → Webhooks</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">WhatsApp Webhook</label>
                <div class="input-group">
                    <input type="text" class="form-control font-monospace" value="{{ url('/api/webhooks/whatsapp') }}" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ url('/api/webhooks/whatsapp') }}')">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
                <small class="text-muted">Configure in WhatsApp Business API settings</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Email Webhook</label>
                <div class="input-group">
                    <input type="text" class="form-control font-monospace" value="{{ url('/api/webhooks/email') }}" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ url('/api/webhooks/email') }}')">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
                <small class="text-muted">Configure in your email provider settings</small>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>System Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Laravel Version:</strong> {{ app()->version() }}</p>
                    <p><strong>PHP Version:</strong> {{ PHP_VERSION }}</p>
                    <p><strong>Environment:</strong> <span class="badge {{ app()->environment('production') ? 'bg-danger' : 'bg-warning' }}">{{ strtoupper(app()->environment()) }}</span></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Database:</strong> {{ config('database.default') }}</p>
                    <p><strong>Queue Connection:</strong> {{ config('queue.default') }}</p>
                    <p><strong>Cache Driver:</strong> {{ config('cache.default') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyApiKey() {
    const input = document.getElementById('apiKeyInput');
    input.select();
    document.execCommand('copy');
    
    // Show feedback
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
    btn.classList.add('btn-success');
    btn.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-secondary');
    }, 2000);
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Show feedback
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-secondary');
        
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    });
}
</script>
@endsection

