@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Welcome back! Here's what's happening with your messaging platform.</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-outline-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-2"></i>Refresh
            </button>
            <a href="{{ route('campaigns.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>New Campaign
            </a>
        </div>
    </div>

    <!-- Real-Time Clock & Countdown -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-4">
            <div class="time-widget">
                <div class="time-widget-icon">
                    <i class="bi bi-clock-fill"></i>
                </div>
                <div class="time-widget-content">
                    <div class="time-widget-label">Current Time</div>
                    <div class="time-widget-value" id="currentTime">--:--:--</div>
                    <div class="time-widget-date" id="currentDate">Loading...</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="time-widget countdown-widget">
                <div class="time-widget-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="time-widget-content">
                    <div class="time-widget-label">Time Until End of Day</div>
                    <div class="time-widget-value countdown-value" id="endOfDayCountdown">--:--:--</div>
                    <div class="time-widget-date">
                        <span id="endOfDayProgress" class="progress-text">--</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="time-widget scheduled-widget">
                <div class="time-widget-icon">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="time-widget-content">
                    <div class="time-widget-label">Next Scheduled Campaign</div>
                    <div class="time-widget-value" id="nextCampaignCountdown" style="font-size: 1.5rem;">
                        @if($nextScheduledCampaign)
                            <span class="countdown-value">Loading...</span>
                        @else
                            <span class="text-muted" style="font-size: 1rem;">No upcoming</span>
                        @endif
                    </div>
                    <div class="time-widget-date" id="nextCampaignName">
                        @if($nextScheduledCampaign)
                            {{ $nextScheduledCampaign->name }}
                        @else
                            Schedule a campaign to see countdown
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="bi bi-envelope-fill"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Messages</div>
                    <div class="stat-value">{{ number_format($stats['total_messages']) }}</div>
                    <div class="stat-footer">
                        <span class="text-success"><i class="bi bi-arrow-up"></i> {{ $stats['today_messages'] }}</span>
                        <span class="text-muted ms-2">today</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Success Rate</div>
                    <div class="stat-value">{{ $stats['success_rate'] }}%</div>
                    <div class="stat-footer">
                        <span>{{ number_format($stats['sent']) }} sent successfully</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="bi bi-clock-fill"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Pending</div>
                    <div class="stat-value">{{ number_format($stats['pending']) }}</div>
                    <div class="stat-footer">
                        <span>In queue</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-danger">
                <div class="stat-icon">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Failed</div>
                    <div class="stat-value">{{ number_format($stats['failed']) }}</div>
                    <div class="stat-footer">
                        <span>{{ $stats['total_messages'] > 0 ? round(($stats['failed'] / $stats['total_messages']) * 100, 1) : 0 }}% failure rate</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Monitoring -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom mb-0">
                        <i class="bi bi-shield-check me-2"></i>Security Monitoring
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <p class="text-muted mb-3">Monitor password reset activities and security events</p>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.security-logs') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-shield-exclamation me-2"></i>View Security Logs
                                </a>
                                <button class="btn btn-outline-secondary" onclick="location.reload()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="security-status">
                                <i class="bi bi-shield-fill-check text-success" style="font-size: 2rem;"></i>
                                <div class="mt-2">
                                    <small class="text-muted">System Status</small>
                                    <div class="fw-bold text-success">Secure</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <!-- Activity Chart -->
        <div class="col-12 col-lg-8">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom mb-0">
                        <i class="bi bi-graph-up me-2"></i>Messages Activity (Last 7 Days)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Channel Distribution -->
        <div class="col-12 col-lg-4">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom mb-0">
                        <i class="bi bi-pie-chart me-2"></i>By Channel
                    </h5>
                </div>
                <div class="card-body">
                    @if($messagesByChannel->count() > 0)
                        <canvas id="channelChart" height="200"></canvas>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox display-4"></i>
                            <p class="mt-2">No messages yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats and Activity -->
    <div class="row g-3 mb-4">
        <!-- Quick Stats -->
        <div class="col-12 col-lg-4">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom mb-0">
                        <i class="bi bi-speedometer2 me-2"></i>Quick Stats
                    </h5>
                </div>
                <div class="card-body">
                    <div class="quick-stat-item">
                        <div class="d-flex align-items-center">
                            <div class="quick-stat-icon bg-primary-subtle">
                                <i class="bi bi-people text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="quick-stat-label">Total Contacts</div>
                                <div class="quick-stat-value">{{ number_format($stats['total_contacts']) }}</div>
                            </div>
                            <a href="{{ route('contacts.index') }}" class="btn btn-sm btn-link">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="quick-stat-item">
                        <div class="d-flex align-items-center">
                            <div class="quick-stat-icon bg-success-subtle">
                                <i class="bi bi-file-text text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="quick-stat-label">Templates</div>
                                <div class="quick-stat-value">{{ number_format($stats['total_templates']) }}</div>
                            </div>
                            <a href="{{ route('templates.index') }}" class="btn btn-sm btn-link">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="quick-stat-item">
                        <div class="d-flex align-items-center">
                            <div class="quick-stat-icon bg-warning-subtle">
                                <i class="bi bi-megaphone text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="quick-stat-label">Campaigns</div>
                                <div class="quick-stat-value">{{ number_format($stats['total_campaigns']) }}</div>
                            </div>
                            <a href="{{ route('campaigns.index') }}" class="btn btn-sm btn-link">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="quick-stat-item mb-0">
                        <div class="d-flex align-items-center">
                            <div class="quick-stat-icon bg-info-subtle">
                                <i class="bi bi-currency-dollar text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="quick-stat-label">Total Cost</div>
                                <div class="quick-stat-value">${{ number_format($stats['total_cost'], 2) }}</div>
                            </div>
                            <a href="{{ route('analytics.index') }}" class="btn btn-sm btn-link">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Channel Performance -->
        <div class="col-12 col-lg-4">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom mb-0">
                        <i class="bi bi-bar-chart me-2"></i>Channel Performance
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($channelPerformance as $channel)
                        <div class="channel-performance-item {{ !$loop->last ? 'mb-3' : '' }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="channel-name">
                                    <i class="bi bi-{{ $channel->channel === 'sms' ? 'phone' : ($channel->channel === 'whatsapp' ? 'whatsapp' : 'envelope') }} me-2"></i>
                                    {{ strtoupper($channel->channel) }}
                                </span>
                                <span class="channel-rate">{{ $channel->success_rate }}%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar {{ $channel->success_rate >= 80 ? 'bg-success' : ($channel->success_rate >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                     style="width: {{ $channel->success_rate }}%"></div>
                            </div>
                            <div class="channel-stats mt-1">
                                <small class="text-muted">{{ number_format($channel->successful) }} / {{ number_format($channel->total) }} successful</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox display-6"></i>
                            <p class="mt-2 mb-0">No channel data yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-12 col-lg-4">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom mb-0">
                        <i class="bi bi-clock-history me-2"></i>Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="activity-timeline">
                        @forelse($recentActivity as $activity)
                            <div class="activity-item">
                                <div class="activity-icon activity-icon-{{ $activity['color'] }}">
                                    <i class="bi {{ $activity['icon'] }}"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">{{ $activity['title'] }}</div>
                                    <div class="activity-description">{{ $activity['description'] }}</div>
                                    <div class="activity-time">{{ \Carbon\Carbon::parse($activity['time'])->diffForHumans() }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-inbox display-6"></i>
                                <p class="mt-2 mb-0">No recent activity</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Campaigns -->
    @if($recentCampaigns->count() > 0)
    <div class="row g-3">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="card-title-custom mb-0">
                        <i class="bi bi-megaphone me-2"></i>Recent Campaigns
                    </h5>
                    <a href="{{ route('campaigns.index') }}" class="btn btn-sm btn-outline-primary">
                        View All <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Campaign Name</th>
                                    <th>Channel</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentCampaigns as $campaign)
                                <tr>
                                    <td>
                                        <strong>{{ $campaign->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ strtoupper($campaign->channel) }}</span>
                                    </td>
                                    <td>
                                        @if($campaign->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($campaign->status === 'running')
                                            <span class="badge bg-primary">Running</span>
                                        @elseif($campaign->status === 'draft')
                                            <span class="badge bg-secondary">Draft</span>
                                        @else
                                            <span class="badge bg-warning">{{ ucfirst($campaign->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($campaign->created_at)->diffForHumans() }}</td>
                                    <td>
                                        <a href="{{ route('campaigns.show', $campaign->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
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
    @endif
</div>

<style>
    .dashboard-container {
        max-width: 100%;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
    }

    /* Stat Cards */
    .stat-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
        display: flex;
        gap: 1rem;
        transition: all 0.2s;
        height: 100%;
    }

    .stat-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .stat-card-primary .stat-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .stat-card-success .stat-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .stat-card-warning .stat-icon {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .stat-card-danger .stat-icon {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .stat-content {
        flex: 1;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 0.25rem;
    }

    .stat-value {
        font-size: 1.875rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.2;
    }

    .stat-footer {
        margin-top: 0.5rem;
        font-size: 0.875rem;
    }

    /* Dashboard Cards */
    .dashboard-card {
        background: white;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        height: 100%;
    }

    .card-header-custom {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .card-title-custom {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
    }

    /* Quick Stats */
    .quick-stat-item {
        padding: 1rem 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .quick-stat-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .quick-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .quick-stat-label {
        font-size: 0.875rem;
        color: #64748b;
    }

    .quick-stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
    }

    /* Channel Performance */
    .channel-performance-item {
        padding-bottom: 0.75rem;
    }

    .channel-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.875rem;
    }

    .channel-rate {
        font-weight: 700;
        color: #1e293b;
    }

    .channel-stats {
        font-size: 0.75rem;
    }

    /* Activity Timeline */
    .activity-timeline {
        position: relative;
    }

    .activity-item {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .activity-item:last-child {
        margin-bottom: 0;
    }

    .activity-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.875rem;
    }

    .activity-icon-primary {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .activity-icon-success {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .activity-icon-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .activity-icon-warning {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .activity-content {
        flex: 1;
    }

    .activity-title {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.875rem;
    }

    .activity-description {
        color: #64748b;
        font-size: 0.875rem;
        margin-top: 0.125rem;
    }

    .activity-time {
        color: #94a3b8;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    /* Time Widget Styles */
    .time-widget {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 0.75rem;
        padding: 1.5rem;
        display: flex;
        gap: 1rem;
        align-items: center;
        height: 100%;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .time-widget::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .countdown-widget {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .scheduled-widget {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .time-widget-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: white;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }

    .time-widget-content {
        flex: 1;
        color: white;
        position: relative;
        z-index: 1;
    }

    .time-widget-label {
        font-size: 0.875rem;
        opacity: 0.9;
        margin-bottom: 0.25rem;
    }

    .time-widget-value {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1.2;
        font-family: 'Courier New', monospace;
    }

    .countdown-value {
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.8;
        }
    }

    .time-widget-date {
        font-size: 0.875rem;
        opacity: 0.9;
        margin-top: 0.25rem;
    }

    .progress-text {
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .stat-value {
            font-size: 1.5rem;
        }

        .time-widget-value {
            font-size: 1.5rem;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Activity Chart
    const activityCtx = document.getElementById('activityChart');
    if (activityCtx) {
        new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($chartData, 'date')) !!},
                datasets: [{
                    label: 'Messages',
                    data: {!! json_encode(array_column($chartData, 'count')) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
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
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // Channel Chart
    const channelCtx = document.getElementById('channelChart');
    if (channelCtx) {
        const channelData = {!! $messagesByChannel->toJson() !!};
        new Chart(channelCtx, {
            type: 'doughnut',
            data: {
                labels: channelData.map(c => c.channel.toUpperCase()),
                datasets: [{
                    data: channelData.map(c => c.count),
                    backgroundColor: [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6'
                    ]
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
    }

    // Real-Time Clock and Countdown
    function updateTime() {
        const now = new Date();
        
        // Current Time
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('currentTime').textContent = `${hours}:${minutes}:${seconds}`;
        
        // Current Date
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', options);
        
        // End of Day Countdown
        const endOfDay = new Date();
        endOfDay.setHours(23, 59, 59, 999);
        const timeUntilEnd = endOfDay - now;
        
        const hoursLeft = Math.floor(timeUntilEnd / (1000 * 60 * 60));
        const minutesLeft = Math.floor((timeUntilEnd % (1000 * 60 * 60)) / (1000 * 60));
        const secondsLeft = Math.floor((timeUntilEnd % (1000 * 60)) / 1000);
        
        document.getElementById('endOfDayCountdown').textContent = 
            `${String(hoursLeft).padStart(2, '0')}:${String(minutesLeft).padStart(2, '0')}:${String(secondsLeft).padStart(2, '0')}`;
        
        // Calculate percentage of day passed
        const startOfDay = new Date();
        startOfDay.setHours(0, 0, 0, 0);
        const dayLength = endOfDay - startOfDay;
        const dayPassed = now - startOfDay;
        const percentPassed = Math.round((dayPassed / dayLength) * 100);
        
        document.getElementById('endOfDayProgress').textContent = `${percentPassed}% of day complete`;
        
        // Next Campaign Countdown (if exists)
        @if($nextScheduledCampaign && $nextScheduledCampaign->scheduled_at)
        const campaignTime = new Date('{{ $nextScheduledCampaign->scheduled_at }}');
        const timeUntilCampaign = campaignTime - now;
        
        if (timeUntilCampaign > 0) {
            const days = Math.floor(timeUntilCampaign / (1000 * 60 * 60 * 24));
            const hours = Math.floor((timeUntilCampaign % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((timeUntilCampaign % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeUntilCampaign % (1000 * 60)) / 1000);
            
            let countdownText = '';
            if (days > 0) {
                countdownText = `${days}d ${String(hours).padStart(2, '0')}h ${String(minutes).padStart(2, '0')}m`;
            } else if (hours > 0) {
                countdownText = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            } else {
                countdownText = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }
            
            const countdownElement = document.querySelector('#nextCampaignCountdown .countdown-value');
            if (countdownElement) {
                countdownElement.textContent = countdownText;
            }
        } else {
            const countdownElement = document.querySelector('#nextCampaignCountdown .countdown-value');
            if (countdownElement) {
                countdownElement.textContent = 'Campaign time!';
                countdownElement.style.color = '#fbbf24';
            }
        }
        @endif
    }
    
    // Update immediately and then every second
    updateTime();
    setInterval(updateTime, 1000);
</script>
@endsection
