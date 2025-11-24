@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-chart-bar"></i> API Statistics</h2>
            <p class="text-muted">Detailed analytics and insights for API requests</p>
        </div>
        <div>
            <a href="{{ route('api-monitor.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Monitor
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('api-monitor.statistics') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="period" class="form-label">Time Period</label>
                    <select name="period" id="period" class="form-select" onchange="this.form.submit()">
                        <option value="day" {{ $period === 'day' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="year" {{ $period === 'year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="client_id" class="form-label">Client (Optional)</label>
                    <select name="client_id" id="client_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ $clientId == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Total Requests</h6>
                            <h2 class="mb-0">{{ number_format($stats['total_requests']) }}</h2>
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
                            <h2 class="mb-0">{{ number_format($stats['successful_requests']) }}</h2>
                            <small class="text-white-50">
                                @if($stats['total_requests'] > 0)
                                    {{ number_format(($stats['successful_requests'] / $stats['total_requests']) * 100, 1) }}% success rate
                                @else
                                    0% success rate
                                @endif
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
                            <h2 class="mb-0">{{ number_format($stats['failed_requests']) }}</h2>
                            <small class="text-white-50">
                                @if($stats['total_requests'] > 0)
                                    {{ number_format(($stats['failed_requests'] / $stats['total_requests']) * 100, 1) }}% failure rate
                                @else
                                    0% failure rate
                                @endif
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
                            <small class="text-white-50">
                                Min: {{ number_format($stats['min_response_time'], 0) }}ms | 
                                Max: {{ number_format($stats['max_response_time'], 0) }}ms
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Requests by Hour Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Requests by Hour</h5>
                </div>
                <div class="card-body">
                    <canvas id="hourlyChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Requests by Endpoint Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-route"></i> Top Endpoints</h5>
                </div>
                <div class="card-body">
                    <canvas id="endpointChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Endpoints Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list"></i> Endpoint Breakdown</h5>
        </div>
        <div class="card-body">
            @if($endpointStats->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Endpoint</th>
                                <th class="text-end">Total Requests</th>
                                <th class="text-end">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($endpointStats as $endpoint)
                                <tr>
                                    <td>
                                        <code>{{ $endpoint->endpoint }}</code>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($endpoint->total) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        @if($stats['total_requests'] > 0)
                                            <span class="badge bg-primary">
                                                {{ number_format(($endpoint->total / $stats['total_requests']) * 100, 1) }}%
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">0%</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No endpoint data available for the selected period</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Prepare data for charts
const hourlyData = @json($hourlyStats);
const endpointData = @json($endpointStats);

// Create hourly labels (0-23)
const hourlyLabels = Array.from({length: 24}, (_, i) => {
    const hour = i.toString().padStart(2, '0');
    return hour + ':00';
});

// Create hourly values array
const hourlyValues = Array(24).fill(0);
hourlyData.forEach(item => {
    hourlyValues[item.hour] = item.total;
});

// Hourly Chart
const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
new Chart(hourlyCtx, {
    type: 'line',
    data: {
        labels: hourlyLabels,
        datasets: [{
            label: 'Requests',
            data: hourlyValues,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 3,
            pointHoverRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            },
            x: {
                ticks: {
                    maxRotation: 45,
                    minRotation: 45
                }
            }
        }
    }
});

// Endpoint Chart
const endpointCtx = document.getElementById('endpointChart').getContext('2d');
new Chart(endpointCtx, {
    type: 'bar',
    data: {
        labels: endpointData.map(e => {
            // Truncate long endpoint names
            const parts = e.endpoint.split('/');
            return parts.length > 2 ? '...' + parts.slice(-2).join('/') : e.endpoint;
        }),
        datasets: [{
            label: 'Requests',
            data: endpointData.map(e => e.total),
            backgroundColor: [
                '#3b82f6',
                '#10b981',
                '#f59e0b',
                '#ef4444',
                '#8b5cf6',
                '#ec4899',
                '#06b6d4',
                '#84cc16',
                '#f97316',
                '#6366f1'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const index = context.dataIndex;
                        return endpointData[index].endpoint + ': ' + context.parsed.y + ' requests';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            },
            x: {
                ticks: {
                    maxRotation: 45,
                    minRotation: 45
                }
            }
        }
    }
});
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
    color: #e83e8c;
}

.card {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: none;
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
}

.table-responsive {
    max-height: 400px;
    overflow-y: auto;
}
</style>
@endsection

