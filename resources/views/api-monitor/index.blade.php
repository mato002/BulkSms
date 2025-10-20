@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-chart-line"></i> API Request Monitor</h2>
            <p class="text-muted">Real-time monitoring of API requests and performance</p>
        </div>
        <div>
            <button class="btn btn-primary" onclick="refreshData()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <a href="{{ route('api-monitor.statistics') }}" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Statistics
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Today's Requests</h6>
                            <h2 class="mb-0">{{ number_format($stats['total_today']) }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-paper-plane fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Successful</h6>
                            <h2 class="mb-0">{{ number_format($stats['successful_today']) }}</h2>
                            <small class="text-white-50">
                                {{ $stats['total_today'] > 0 ? round(($stats['successful_today'] / $stats['total_today']) * 100, 1) : 0 }}% success rate
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Failed</h6>
                            <h2 class="mb-0">{{ number_format($stats['failed_today']) }}</h2>
                            <small class="text-white-50">
                                {{ $stats['total_today'] > 0 ? round(($stats['failed_today'] / $stats['total_today']) * 100, 1) : 0 }}% failure rate
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Avg Response Time</h6>
                            <h2 class="mb-0">{{ number_format($stats['avg_response_time'], 0) }}ms</h2>
                            <small class="text-white-50">Today's average</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tachometer-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i> Filters
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('api-monitor.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Client</label>
                    <select name="client_id" class="form-select">
                        <option value="">All Clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Method</label>
                    <select name="method" class="form-select">
                        <option value="">All</option>
                        <option value="GET" {{ request('method') == 'GET' ? 'selected' : '' }}>GET</option>
                        <option value="POST" {{ request('method') == 'POST' ? 'selected' : '' }}>POST</option>
                        <option value="PUT" {{ request('method') == 'PUT' ? 'selected' : '' }}>PUT</option>
                        <option value="DELETE" {{ request('method') == 'DELETE' ? 'selected' : '' }}>DELETE</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- API Logs Table -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> API Request Logs
            <span class="badge bg-secondary float-end">{{ $logs->total() }} total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>Client</th>
                            <th>Method</th>
                            <th>Endpoint</th>
                            <th>IP Address</th>
                            <th>Status</th>
                            <th>Response Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr class="{{ !$log->success ? 'table-danger' : '' }}">
                                <td>
                                    <small>{{ $log->created_at->format('Y-m-d H:i:s') }}</small><br>
                                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <strong>{{ $log->client?->name ?? 'Unknown' }}</strong><br>
                                    <small class="text-muted">ID: {{ $log->client_id ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->method == 'GET' ? 'success' : ($log->method == 'POST' ? 'primary' : 'warning') }}">
                                        {{ $log->method }}
                                    </span>
                                </td>
                                <td>
                                    <code>{{ $log->endpoint }}</code>
                                </td>
                                <td>
                                    <small>{{ $log->ip_address }}</small>
                                </td>
                                <td>
                                    @if($log->success)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> {{ $log->response_status }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times"></i> {{ $log->response_status }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->response_time_ms < 1000 ? 'success' : ($log->response_time_ms < 3000 ? 'warning' : 'danger') }}">
                                        {{ number_format($log->response_time_ms, 0) }}ms
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('api-monitor.show', $log->id) }}" class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No API requests found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<script>
function refreshData() {
    location.reload();
}

// Auto-refresh every 30 seconds
setInterval(refreshData, 30000);
</script>

<style>
.opacity-50 {
    opacity: 0.5;
}

code {
    background: #f4f4f4;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.9em;
}
</style>
@endsection

