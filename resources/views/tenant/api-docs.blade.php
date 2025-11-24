@extends('layouts.app')

@section('title', 'API Documentation')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">API Documentation</h1>
                    <p class="text-muted mb-0">Integrate with our SMS platform using our REST API</p>
                </div>
                <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- API Credentials -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-key"></i> Your API Credentials
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-id-card"></i> Client ID
                            </label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control font-monospace" 
                                       value="{{ $client->id }}" 
                                       readonly 
                                       id="clientIdInputApi">
                                <button class="btn btn-outline-secondary" 
                                        onclick="copyToClipboard('{{ $client->id }}', 'clientIdInputApi')"
                                        title="Copy Client ID">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                Used in API endpoint URLs
                            </small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">API Key</label>
                            <div class="input-group mb-3">
                                @if(!$client->status)
                                    <input type="text" class="form-control font-monospace" 
                                           value="{{ substr($client->api_key, 0, 10) }}************************" 
                                           readonly
                                           id="apiKeyInputApi">
                                    <button class="btn btn-outline-secondary" disabled title="Activate account to reveal">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                @else
                                    <input type="text" class="form-control font-monospace" 
                                           value="{{ $client->api_key }}" 
                                           readonly
                                           id="apiKeyInputApi">
                                    <button class="btn btn-outline-secondary" 
                                            onclick="copyToClipboard('{{ $client->api_key }}', 'apiKeyInputApi')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Sender ID</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control font-monospace" 
                                       value="{{ $client->sender_id }}" 
                                       readonly
                                       id="senderIdInputApi">
                                <button class="btn btn-outline-secondary" 
                                        onclick="copyToClipboard('{{ $client->sender_id }}', 'senderIdInputApi')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Keep your API key secure!</strong> Never share it publicly or commit it to version control.
                        @if(!$client->status)
                            <div class="mt-2">
                                <span class="badge bg-warning text-dark"><i class="fas fa-lock"></i> Inactive Account</span>
                                <a href="{{ route('tenant.payment') }}" class="btn btn-sm btn-primary ms-2">Activate Now</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Environment Configuration -->
            <div class="card shadow mb-4 border-info">
                <div class="card-header py-3 bg-info text-white">
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
                                      id="envConfigTextareaApi" 
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
                                    onclick="copyEnvConfigApi()"
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
                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-code"></i>
                        <strong>Usage Example:</strong> Access these variables in your code using <code>env('BULKSMS_API_KEY')</code> or <code>getenv('BULKSMS_API_KEY')</code>
                    </div>
                </div>
            </div>

            <!-- Base URL -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-globe"></i> Base URL
                    </h6>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace" 
                               value="{{ url('/api') }}" 
                               readonly
                               id="baseUrlInputApi">
                        <button class="btn btn-outline-secondary" 
                                onclick="copyToClipboard('{{ url('/api') }}', 'baseUrlInputApi')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Send SMS -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-paper-plane"></i> Send SMS Message
                    </h6>
                </div>
                <div class="card-body">
                    <h6>Endpoint</h6>
                    <code class="d-block mb-3">POST {{ url('/api') }}/{{ $client->id }}/messages/send</code>
                    
                    <h6>Headers</h6>
                    <pre class="bg-light p-3 rounded mb-3"><code>Content-Type: application/json
X-API-Key: {{ $client->api_key }}</code></pre>
                    
                    <h6>Request Body</h6>
                    <pre class="bg-light p-3 rounded mb-3"><code>{
  "client_id": {{ $client->id }},
  "channel": "sms",
  "recipient": "254728883160",
  "body": "Hello from {{ $client->company_name }}!",
  "sender": "{{ $client->sender_id }}"
}</code></pre>
                    <div class="alert alert-info small mb-3">
                        <strong>Required Fields:</strong> client_id, channel, recipient, body<br>
                        <strong>Optional Fields:</strong> sender, subject, template_id, metadata
                    </div>
                    
                    <h6>Response</h6>
                    <pre class="bg-light p-3 rounded mb-3"><code>{
  "status": "success",
  "message": "Message sent successfully",
  "data": {
    "id": 123,
    "status": "sent",
    "provider_message_id": "msg_123456789"
  }
}</code></pre>
                    
                    <h6>cURL Example</h6>
                    <pre class="bg-light p-3 rounded"><code>curl -X POST {{ url('/api') }}/{{ $client->id }}/messages/send \
  -H "Content-Type: application/json" \
  -H "X-API-Key: {{ $client->api_key }}" \
  -d '{
    "client_id": {{ $client->id }},
    "channel": "sms",
    "recipient": "254728883160",
    "body": "Hello from {{ $client->company_name }}!",
    "sender": "{{ $client->sender_id }}"
  }'</code></pre>
                </div>
            </div>

            <!-- Send Bulk SMS -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users"></i> Send Bulk SMS
                    </h6>
                </div>
                <div class="card-body">
                    <h6>Endpoint</h6>
                    <code class="d-block mb-3">POST {{ url('/api') }}/{{ $client->id }}/messages/bulk</code>
                    
                    <h6>Request Body</h6>
                    <pre class="bg-light p-3 rounded mb-3"><code>{
  "recipients": [
    "254728883160",
    "254712345678",
    "254798765432"
  ],
  "message": "Bulk message from {{ $client->company_name }}!",
  "sender": "{{ $client->sender_id }}"
}</code></pre>
                    
                    <h6>Response</h6>
                    <pre class="bg-light p-3 rounded mb-3"><code>{
  "success": true,
  "campaign_id": "camp_123456789",
  "total_recipients": 3,
  "total_cost": 7.50,
  "status": "queued"
}</code></pre>
                </div>
            </div>

            <!-- Check Balance -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-wallet"></i> Check Balance
                    </h6>
                </div>
                <div class="card-body">
                    <h6>Endpoint</h6>
                    <code class="d-block mb-3">GET {{ url('/api') }}/{{ $client->id }}/balance</code>
                    
                    <h6>Headers</h6>
                    <pre class="bg-light p-3 rounded mb-3"><code>X-API-Key: {{ $client->api_key }}</code></pre>
                    
                    <h6>Response</h6>
                    <pre class="bg-light p-3 rounded mb-3"><code>{
  "success": true,
  "balance": {{ $client->balance }},
  "currency": "KES",
  "last_updated": "2024-01-15T10:30:00Z"
}</code></pre>
                </div>
            </div>

            <!-- Error Codes -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-exclamation-circle"></i> Error Codes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Message</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>400</code></td>
                                    <td>Bad Request</td>
                                    <td>Invalid request parameters</td>
                                </tr>
                                <tr>
                                    <td><code>401</code></td>
                                    <td>Unauthorized</td>
                                    <td>Invalid or missing API key</td>
                                </tr>
                                <tr>
                                    <td><code>402</code></td>
                                    <td>Payment Required</td>
                                    <td>Insufficient balance</td>
                                </tr>
                                <tr>
                                    <td><code>403</code></td>
                                    <td>Forbidden</td>
                                    <td>Access denied</td>
                                </tr>
                                <tr>
                                    <td><code>429</code></td>
                                    <td>Too Many Requests</td>
                                    <td>Rate limit exceeded</td>
                                </tr>
                                <tr>
                                    <td><code>500</code></td>
                                    <td>Internal Server Error</td>
                                    <td>Server error</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Account Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 text-primary">KES {{ number_format($client->balance, 2) }}</div>
                            <small class="text-muted">Balance</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-success">{{ $client->tier }}</div>
                            <small class="text-muted">Tier</small>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Sender ID:</span>
                        <span class="font-weight-bold">{{ $client->sender_id }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status:</span>
                        <span class="badge bg-{{ $client->status ? 'success' : 'warning' }}">
                            {{ $client->status ? 'Active' : 'Pending' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Rate Limits -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tachometer-alt"></i> Rate Limits
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Requests per minute:</span>
                            <strong>60</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Messages per minute:</span>
                            <strong>30</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Daily message limit:</span>
                            <strong>10,000</strong>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Rate limits may vary based on your account tier.
                    </small>
                </div>
            </div>

            <!-- SDKs & Libraries -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-code"></i> SDKs & Libraries
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="fab fa-php"></i> PHP SDK
                        </a>
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="fab fa-python"></i> Python SDK
                        </a>
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="fab fa-node-js"></i> Node.js SDK
                        </a>
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="fab fa-java"></i> Java SDK
                        </a>
                    </div>
                </div>
            </div>

            <!-- Support -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-question-circle"></i> Need Help?
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Having trouble with the API? Our support team is here to help.
                    </p>
                    <div class="d-grid gap-2">
                        <a href="mailto:mathiasodhis@gmail.com" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope"></i> Email Support
                        </a>
                        <a href="{{ route('tenant.notifications') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-bell"></i> Check Notifications
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
    if (inputId) {
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

// Copy entire .env configuration (API Docs page)
function copyEnvConfigApi() {
    const textarea = document.getElementById('envConfigTextareaApi');
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
            showEnvCopySuccessApi();
            // Deselect text
            window.getSelection().removeAllRanges();
        }).catch(function(err) {
            console.error('Clipboard API failed:', err);
            fallbackCopyEnvApi(text);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyEnvApi(text);
    }
}

// Fallback copy method for .env configuration (API Docs)
function fallbackCopyEnvApi(text) {
    const textarea = document.getElementById('envConfigTextareaApi');
    if (!textarea) {
        showCopyError();
        return;
    }
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showEnvCopySuccessApi();
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

// Show success notification for .env copy (API Docs)
function showEnvCopySuccessApi() {
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
    const textarea = document.getElementById('envConfigTextareaApi');
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

// Test API connection
function testApiConnection() {
    const apiKey = '{{ $client->api_key }}';
    const baseUrl = '{{ url('/api') }}';
    const clientId = '{{ $client->id }}';
    
    fetch(`${baseUrl}/${clientId}/balance`, {
        method: 'GET',
        headers: {
            'X-API-Key': apiKey,
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('API connection successful! Balance: KES ' + data.balance);
        } else {
            alert('API connection failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('API connection failed. Please check your credentials.');
    });
}
</script>
@endsection
