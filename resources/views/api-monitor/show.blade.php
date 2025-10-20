@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-file-alt"></i> API Request Details</h2>
            <p class="text-muted">Complete information about this API request</p>
        </div>
        <a href="{{ route('api-monitor.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <!-- Request Overview -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header {{ $log->success ? 'bg-success text-white' : 'bg-danger text-white' }}">
                    <h5 class="mb-0">
                        <i class="fas fa-{{ $log->success ? 'check-circle' : 'times-circle' }}"></i>
                        {{ $log->success ? 'Successful Request' : 'Failed Request' }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Request ID:</dt>
                                <dd class="col-sm-8"><code>#{{ $log->id }}</code></dd>

                                <dt class="col-sm-4">Client:</dt>
                                <dd class="col-sm-8">
                                    <strong>{{ $log->client?->name ?? 'Unknown' }}</strong>
                                    @if($log->client)
                                        <br><small class="text-muted">ID: {{ $log->client->id }}</small>
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Endpoint:</dt>
                                <dd class="col-sm-8"><code>{{ $log->endpoint }}</code></dd>

                                <dt class="col-sm-4">Method:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-{{ $log->method == 'GET' ? 'success' : ($log->method == 'POST' ? 'primary' : 'warning') }}">
                                        {{ $log->method }}
                                    </span>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Timestamp:</dt>
                                <dd class="col-sm-8">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}<br>
                                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                </dd>

                                <dt class="col-sm-4">IP Address:</dt>
                                <dd class="col-sm-8">{{ $log->ip_address }}</dd>

                                <dt class="col-sm-4">Status Code:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-{{ $log->success ? 'success' : 'danger' }}">
                                        {{ $log->response_status }}
                                    </span>
                                </dd>

                                <dt class="col-sm-4">Response Time:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-{{ $log->response_time_ms < 1000 ? 'success' : ($log->response_time_ms < 3000 ? 'warning' : 'danger') }}">
                                        {{ number_format($log->response_time_ms, 2) }}ms
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    @if($log->error_message)
                        <div class="alert alert-danger mt-3">
                            <strong><i class="fas fa-exclamation-triangle"></i> Error:</strong> {{ $log->error_message }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Request Details -->
    <div class="row">
        <!-- Request Headers -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-arrows-alt-h"></i> Request Headers
                </div>
                <div class="card-body">
                    <pre class="mb-0"><code>{{ json_encode($log->request_headers, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
        </div>

        <!-- Request Body -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-file-code"></i> Request Body
                </div>
                <div class="card-body">
                    @if($log->request_body)
                        <pre class="mb-0"><code>{{ json_encode($log->request_body, JSON_PRETTY_PRINT) }}</code></pre>
                    @else
                        <p class="text-muted">No request body</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Response Body -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header {{ $log->success ? 'bg-success' : 'bg-danger' }} text-white">
                    <i class="fas fa-reply"></i> Response Body
                </div>
                <div class="card-body">
                    @if($log->response_body)
                        <pre class="mb-0"><code>{{ json_encode($log->response_body, JSON_PRETTY_PRINT) }}</code></pre>
                    @else
                        <p class="text-muted">No response body</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- User Agent -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-secret"></i> User Agent
                </div>
                <div class="card-body">
                    <code>{{ $log->user_agent ?? 'Not available' }}</code>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
pre {
    background: #f4f4f4;
    padding: 15px;
    border-radius: 5px;
    max-height: 400px;
    overflow-y: auto;
}

code {
    color: #e83e8c;
    font-size: 0.9em;
}

pre code {
    color: #333;
}

dl dd {
    margin-bottom: 0.5rem;
}
</style>
@endsection

