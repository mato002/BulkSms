@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">📚 API Documentation</h1>
            <p class="text-muted mb-0">Integrate our messaging API into your applications</p>
        </div>
        <div>
            <a href="{{ route('settings.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-key"></i> View API Keys
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-3">Quick Start</h6>
                    <ul class="list-unstyled mb-4">
                        <li><a href="#authentication" class="text-decoration-none d-block py-1">Authentication</a></li>
                        <li><a href="#base-url" class="text-decoration-none d-block py-1">Base URL</a></li>
                        <li><a href="#quick-example" class="text-decoration-none d-block py-1">Quick Example</a></li>
                    </ul>

                    <h6 class="text-uppercase text-muted small mb-3">Endpoints</h6>
                    <ul class="list-unstyled">
                        @foreach($endpoints as $category)
                        <li class="mb-2">
                            <a href="#{{ Str::slug($category['category']) }}" class="text-decoration-none fw-semibold">
                                {{ $category['category'] }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Authentication Section -->
            <div id="authentication" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h3 class="mb-3">🔐 Authentication</h3>
                    <p>All API requests require authentication using your API key. Include it in the request header:</p>
                    
                    <div class="bg-dark text-light p-3 rounded mb-3">
                        <code class="text-light">X-API-Key: {{ $client->api_key }}</code>
                        <button class="btn btn-sm btn-outline-light float-end" onclick="copyToClipboard('{{ $client->api_key }}')">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Keep your API key secret!</strong> Never share it publicly or commit it to version control.
                    </div>
                </div>
            </div>

            <!-- Base URL Section -->
            <div id="base-url" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h3 class="mb-3">🌐 Base URL</h3>
                    <p>All API endpoints are relative to:</p>
                    <div class="bg-dark text-light p-3 rounded mb-3">
                        <code class="text-light">{{ $baseUrl }}</code>
                        <button class="btn btn-sm btn-outline-light float-end" onclick="copyToClipboard('{{ $baseUrl }}')">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                    <p class="mb-0 text-muted">Your Client ID: <strong>{{ $client->id }}</strong></p>
                </div>
            </div>

            <!-- Quick Example -->
            <div id="quick-example" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h3 class="mb-3">⚡ Quick Example</h3>
                    <p>Send your first SMS in seconds:</p>
                    
                    <!-- Language Tabs -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#curl-quick">cURL</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#php-quick">PHP</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#python-quick">Python</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#js-quick">JavaScript</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- cURL -->
                        <div class="tab-pane fade show active" id="curl-quick">
                            <pre class="bg-dark text-light p-3 rounded"><code>curl -X POST {{ $baseUrl }}/{{ $client->id }}/messages/send \
  -H "X-API-Key: {{ $client->api_key }}" \
  -H "Content-Type: application/json" \
  -d '{
    "channel": "sms",
    "recipient": "254712345678",
    "body": "Hello from our API!",
    "sender": "{{ $client->sender_id }}"
  }'</code></pre>
                        </div>

                        <!-- PHP -->
                        <div class="tab-pane fade" id="php-quick">
                            <pre class="bg-dark text-light p-3 rounded"><code>&lt;?php
$ch = curl_init('{{ $baseUrl }}/{{ $client->id }}/messages/send');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: {{ $client->api_key }}',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'channel' => 'sms',
    'recipient' => '254712345678',
    'body' => 'Hello from our API!',
    'sender' => '{{ $client->sender_id }}'
]));

$response = curl_exec($ch);
$result = json_decode($response, true);
print_r($result);
?&gt;</code></pre>
                        </div>

                        <!-- Python -->
                        <div class="tab-pane fade" id="python-quick">
                            <pre class="bg-dark text-light p-3 rounded"><code>import requests
import json

url = '{{ $baseUrl }}/{{ $client->id }}/messages/send'
headers = {
    'X-API-Key': '{{ $client->api_key }}',
    'Content-Type': 'application/json'
}
data = {
    'channel': 'sms',
    'recipient': '254712345678',
    'body': 'Hello from our API!',
    'sender': '{{ $client->sender_id }}'
}

response = requests.post(url, headers=headers, json=data)
print(response.json())</code></pre>
                        </div>

                        <!-- JavaScript -->
                        <div class="tab-pane fade" id="js-quick">
                            <pre class="bg-dark text-light p-3 rounded"><code>fetch('{{ $baseUrl }}/{{ $client->id }}/messages/send', {
  method: 'POST',
  headers: {
    'X-API-Key': '{{ $client->api_key }}',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    channel: 'sms',
    recipient: '254712345678',
    body: 'Hello from our API!',
    sender: '{{ $client->sender_id }}'
  })
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));</code></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- API Endpoints -->
            @foreach($endpoints as $category)
            <div id="{{ Str::slug($category['category']) }}" class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">{{ $category['category'] }}</h4>
                </div>
                <div class="card-body">
                    @foreach($category['endpoints'] as $endpoint)
                    <div class="border-bottom pb-4 mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-{{ $endpoint['method'] === 'GET' ? 'info' : 'success' }} me-2">
                                {{ $endpoint['method'] }}
                            </span>
                            <code class="fs-6">{{ $endpoint['path'] }}</code>
                        </div>
                        
                        <h5 class="mb-2">{{ $endpoint['name'] }}</h5>
                        <p class="text-muted">{{ $endpoint['description'] }}</p>

                        @if(count($endpoint['parameters']) > 0)
                        <h6 class="mb-2 mt-3">Parameters</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($endpoint['parameters'] as $param)
                                    <tr>
                                        <td><code>{{ $param['name'] }}</code></td>
                                        <td><span class="badge bg-light text-dark">{{ $param['type'] }}</span></td>
                                        <td>
                                            @if($param['required'])
                                                <span class="badge bg-danger">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                        <td>{{ $param['description'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif

                        @if($endpoint['example'])
                        <h6 class="mb-2 mt-3">Example Request Body</h6>
                        <pre class="bg-dark text-light p-3 rounded"><code>{{ json_encode($endpoint['example'], JSON_PRETTY_PRINT) }}</code></pre>
                        @endif

                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="tryEndpoint('{{ $endpoint['method'] }}', '{{ $baseUrl }}{{ $endpoint['path'] }}', {{ json_encode($endpoint['example'] ?? []) }})">
                            <i class="bi bi-play-circle"></i> Try it out
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            <!-- Response Format -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Response Format</h4>
                </div>
                <div class="card-body">
                    <h5>Success Response</h5>
                    <pre class="bg-dark text-light p-3 rounded"><code>{
  "status": "success",
  "message": "Operation completed successfully",
  "data": {
    // Response data here
  }
}</code></pre>

                    <h5 class="mt-4">Error Response</h5>
                    <pre class="bg-dark text-light p-3 rounded"><code>{
  "status": "error",
  "message": "Error message description",
  "errors": {
    // Validation errors (if any)
  }
}</code></pre>
                </div>
            </div>

            <!-- Error Codes -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning">
                    <h4 class="mb-0">HTTP Status Codes</h4>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Meaning</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><strong>200</strong></td><td>Success - Request completed successfully</td></tr>
                            <tr><td><strong>201</strong></td><td>Created - Resource created successfully</td></tr>
                            <tr><td><strong>400</strong></td><td>Bad Request - Invalid request parameters</td></tr>
                            <tr><td><strong>401</strong></td><td>Unauthorized - Invalid API key</td></tr>
                            <tr><td><strong>403</strong></td><td>Forbidden - Access denied</td></tr>
                            <tr><td><strong>404</strong></td><td>Not Found - Resource not found</td></tr>
                            <tr><td><strong>422</strong></td><td>Validation Error - Request validation failed</td></tr>
                            <tr><td><strong>429</strong></td><td>Too Many Requests - Rate limit exceeded</td></tr>
                            <tr><td><strong>500</strong></td><td>Server Error - Internal server error</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test API Modal -->
<div class="modal fade" id="testApiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Test API Endpoint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Request</label>
                    <div id="testRequest" class="bg-dark text-light p-3 rounded font-monospace small"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Response</label>
                    <div id="testResponse" class="bg-dark text-light p-3 rounded font-monospace small">
                        <em class="text-muted">Waiting for response...</em>
                    </div>
                </div>
                <div id="testError" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="executeTest">Execute Request</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    });
}

let currentTestRequest = null;

function tryEndpoint(method, url, exampleData) {
    const modal = new bootstrap.Modal(document.getElementById('testApiModal'));
    
    currentTestRequest = {
        method: method,
        url: url,
        data: exampleData,
        headers: {
            'X-API-Key': '{{ $client->api_key }}',
            'Content-Type': 'application/json'
        }
    };
    
    const requestDisplay = `${method} ${url}\nHeaders:\n  X-API-Key: {{ $client->api_key }}\n  Content-Type: application/json${exampleData ? '\n\nBody:\n' + JSON.stringify(exampleData, null, 2) : ''}`;
    
    document.getElementById('testRequest').textContent = requestDisplay;
    document.getElementById('testResponse').innerHTML = '<em class="text-muted">Click "Execute Request" to test</em>';
    document.getElementById('testError').classList.add('d-none');
    
    modal.show();
}

document.getElementById('executeTest')?.addEventListener('click', async function() {
    if (!currentTestRequest) return;
    
    const responseDiv = document.getElementById('testResponse');
    const errorDiv = document.getElementById('testError');
    
    responseDiv.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div> Executing request...';
    errorDiv.classList.add('d-none');
    
    try {
        const options = {
            method: currentTestRequest.method,
            headers: currentTestRequest.headers
        };
        
        if (currentTestRequest.method !== 'GET' && currentTestRequest.data) {
            options.body = JSON.stringify(currentTestRequest.data);
        }
        
        const response = await fetch(currentTestRequest.url, options);
        const data = await response.json();
        
        responseDiv.textContent = JSON.stringify(data, null, 2);
        
        if (!response.ok) {
            errorDiv.textContent = `HTTP ${response.status}: ${data.message || 'Request failed'}`;
            errorDiv.classList.remove('d-none');
        }
    } catch (error) {
        responseDiv.textContent = 'Request failed';
        errorDiv.textContent = error.message;
        errorDiv.classList.remove('d-none');
    }
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>
@endpush

@push('styles')
<style>
    pre code {
        display: block;
        overflow-x: auto;
        white-space: pre;
    }
    .sticky-top {
        z-index: 100;
    }
</style>
@endpush
@endsection

