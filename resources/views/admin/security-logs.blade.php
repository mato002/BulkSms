@extends('layouts.app')

@push('styles')
<style>
    .log-entry {
        background: white;
        border-left: 4px solid #dc3545;
        margin-bottom: 10px;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .log-entry.success {
        border-left-color: #28a745;
    }
    .log-entry.warning {
        border-left-color: #ffc107;
    }
    .log-time {
        color: #6c757d;
        font-size: 0.9em;
    }
    .log-message {
        font-weight: 500;
        margin-bottom: 5px;
    }
    .log-details {
        font-family: monospace;
        font-size: 0.85em;
        color: #495057;
        background: #f8f9fa;
        padding: 8px;
        border-radius: 3px;
        margin-top: 5px;
    }
    .refresh-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="bi bi-shield-check"></i> Security Logs</h1>
                    <p class="text-muted">Password reset activities and security events</p>
                </div>
                <div>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                    <button onclick="location.reload()" class="btn btn-primary">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </div>

            @if(empty($logs))
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    No security events found. This could mean:
                    <ul class="mb-0 mt-2">
                        <li>No password reset attempts have been made</li>
                        <li>Log file doesn't exist or is empty</li>
                        <li>No recent security events</li>
                    </ul>
                </div>
            @else
                <div class="row">
                    <div class="col-12">
                        <h5>Recent Security Events ({{ count($logs) }} found)</h5>
                        <div class="mt-3">
                            @foreach($logs as $log)
                                @php
                                    $isSuccess = strpos($log, 'Password Reset Success') !== false;
                                    $isWarning = strpos($log, 'Failed Password Reset') !== false;
                                    $logClass = $isSuccess ? 'success' : ($isWarning ? 'warning' : '');
                                @endphp
                                <div class="log-entry {{ $logClass }}">
                                    <div class="log-message">
                                        @if($isSuccess)
                                            <i class="bi bi-check-circle text-success"></i>
                                            <strong>Password Reset Success</strong>
                                        @elseif($isWarning)
                                            <i class="bi bi-exclamation-triangle text-warning"></i>
                                            <strong>Failed Password Reset Attempt</strong>
                                        @else
                                            <i class="bi bi-info-circle text-info"></i>
                                            <strong>Security Event</strong>
                                        @endif
                                    </div>
                                    <div class="log-details">{{ $log }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-4">
                <div class="alert alert-warning">
                    <i class="bi bi-shield-exclamation"></i>
                    <strong>Security Monitoring:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Green entries:</strong> Successful password resets</li>
                        <li><strong>Yellow entries:</strong> Failed password reset attempts (potential security threats)</li>
                        <li><strong>Red entries:</strong> Other security events</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<button onclick="location.reload()" class="btn btn-primary refresh-btn" title="Refresh Logs">
    <i class="bi bi-arrow-clockwise"></i>
</button>
@endsection

@push('scripts')
<script>
    // Auto-refresh every 30 seconds
    setInterval(function() {
        location.reload();
    }, 30000);
</script>
@endpush

