@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Analytics & Reports</h1>
        <form method="GET" action="{{ route('analytics.index') }}" class="d-flex gap-2">
            <input type="date" class="form-control" name="start_date" value="{{ $startDate }}" style="width: auto;">
            <input type="date" class="form-control" name="end_date" value="{{ $endDate }}" style="width: auto;">
            <button type="submit" class="btn btn-primary">Apply</button>
        </form>
    </div>

    <!-- Stats Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Messages</h6>
                    <h2>{{ number_format($stats['total']) }}</h2>
                    <small>{{ $startDate }} to {{ $endDate }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Sent</h6>
                    <h2>{{ number_format($stats['sent']) }}</h2>
                    <small>{{ $stats['total'] > 0 ? round(($stats['sent']/$stats['total'])*100, 1) : 0 }}% of total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Delivered</h6>
                    <h2>{{ number_format($stats['delivered']) }}</h2>
                    <small>{{ $stats['total'] > 0 ? round(($stats['delivered']/$stats['total'])*100, 1) : 0 }}% of total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-title">Failed</h6>
                    <h2>{{ number_format($stats['failed']) }}</h2>
                    <small>{{ $stats['total'] > 0 ? round(($stats['failed']/$stats['total'])*100, 1) : 0 }}% of total</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Messages by Channel -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Messages by Channel</h5>
                </div>
                <div class="card-body">
                    <canvas id="channelChart" style="max-height: 300px;"></canvas>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Channel</th>
                                    <th class="text-end">Count</th>
                                    <th class="text-end">Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byChannel as $item)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ strtoupper($item->channel) }}</span></td>
                                    <td class="text-end">{{ number_format($item->count) }}</td>
                                    <td class="text-end">{{ $stats['total'] > 0 ? round(($item->count/$stats['total'])*100, 1) : 0 }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Rate by Channel -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Success Rate by Channel</h5>
                </div>
                <div class="card-body">
                    <canvas id="successRateChart" style="max-height: 300px;"></canvas>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Channel</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Successful</th>
                                    <th class="text-end">Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($successRateByChannel as $item)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ strtoupper($item->channel) }}</span></td>
                                    <td class="text-end">{{ number_format($item->total) }}</td>
                                    <td class="text-end">{{ number_format($item->successful) }}</td>
                                    <td class="text-end">
                                        <span class="badge {{ $item->success_rate >= 90 ? 'bg-success' : ($item->success_rate >= 70 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $item->success_rate }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Trend -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daily Message Trend (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyTrendChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Recipients -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top 10 Recipients</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Recipient</th>
                                    <th class="text-end">Messages Sent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topRecipients as $recipient)
                                <tr>
                                    <td>{{ $recipient->recipient }}</td>
                                    <td class="text-end">{{ number_format($recipient->count) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cost Summary -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Cost Summary</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <h1 class="display-4 text-primary">${{ number_format($totalCost, 2) }}</h1>
                        <p class="text-muted mb-0">Total cost for selected period</p>
                        <small class="text-muted">{{ $startDate }} to {{ $endDate }}</small>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <h5>{{ number_format($stats['total']) }}</h5>
                            <small class="text-muted">Messages</small>
                        </div>
                        <div class="col-6">
                            <h5>${{ $stats['total'] > 0 ? number_format($totalCost / $stats['total'], 4) : '0.0000' }}</h5>
                            <small class="text-muted">Avg. Cost/Message</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Channel Distribution Chart
const channelCtx = document.getElementById('channelChart').getContext('2d');
new Chart(channelCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($byChannel->pluck('channel')->map(fn($c) => strtoupper($c))) !!},
        datasets: [{
            data: {!! json_encode($byChannel->pluck('count')) !!},
            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Success Rate Chart
const successCtx = document.getElementById('successRateChart').getContext('2d');
new Chart(successCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($successRateByChannel->pluck('channel')->map(fn($c) => strtoupper($c))) !!},
        datasets: [{
            label: 'Success Rate (%)',
            data: {!! json_encode($successRateByChannel->pluck('success_rate')) !!},
            backgroundColor: '#10b981'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});

// Daily Trend Chart
const dailyCtx = document.getElementById('dailyTrendChart').getContext('2d');
new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($dailyMessages->pluck('date')) !!},
        datasets: [{
            label: 'Messages',
            data: {!! json_encode($dailyMessages->pluck('count')) !!},
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endsection

