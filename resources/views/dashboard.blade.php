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

    <!-- Wallet & Balance Cards (if client has balance tracking) -->
    @if($currentClient && $currentClient->balance !== null)
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-success" style="cursor: pointer;" onclick="window.location='{{ route('wallet.index') }}'">
                <div class="stat-icon">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">
                        Wallet Balance
                        @if($currentClient->balance < 100)
                            <span class="badge bg-warning" title="Low balance">
                                <i class="bi bi-exclamation-triangle"></i>
                            </span>
                        @endif
                    </div>
                    <div class="stat-value">KSh {{ number_format($stats['local_balance'], 2) }}</div>
                    <div class="stat-footer d-flex justify-content-between align-items-center">
                        <span class="text-muted">{{ number_format($stats['balance_units'], 2) }} units</span>
                        <a href="{{ route('wallet.topup') }}" class="btn btn-sm btn-success" onclick="event.stopPropagation()">
                            <i class="bi bi-plus-circle"></i> Top Up
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <i class="bi bi-credit-card"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label d-flex justify-content-between align-items-center">
                        <span>Onfon Balance</span>
                        <button class="btn btn-sm btn-info" id="syncOnfonBtn" onclick="syncOnfonBalance()" title="Sync from Onfon">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                    <div class="stat-value" id="onfonBalanceValue">
                        @if($stats['system_onfon_balance'] > 0)
                            {{ number_format($stats['system_onfon_balance'], 2) }} units
                        @else
                            <span class="text-muted" style="font-size: 1rem;">Loading...</span>
                        @endif
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted" id="onfonLastSync">Onfon Credits Available</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="bi bi-tag"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Price Per Unit</div>
                    <div class="stat-value">KSh {{ number_format($stats['price_per_unit'], 2) }}</div>
                    <div class="stat-footer">
                        <span class="text-muted">Per SMS unit</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Spent</div>
                    <div class="stat-value">KSh {{ number_format($stats['total_cost'], 2) }}</div>
                    <div class="stat-footer">
                        <span class="text-muted">All time</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Admin Stats (Only for Admins) -->
    @if($isAdmin)
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="card-title-custom mb-0">
                        <i class="bi bi-shield-check me-2"></i>Admin Overview
                    </h5>
                    <a href="{{ route('admin.senders.index') }}" class="btn btn-sm btn-outline-primary">
                        Manage Senders <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 col-md-3 mb-3 mb-md-0">
                            <div class="admin-stat">
                                <div class="admin-stat-icon bg-primary-subtle">
                                    <i class="bi bi-building text-primary"></i>
                                </div>
                                <div class="admin-stat-value">{{ number_format($stats['total_clients']) }}</div>
                                <div class="admin-stat-label">Total Clients</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3 mb-md-0">
                            <div class="admin-stat">
                                <div class="admin-stat-icon bg-success-subtle">
                                    <i class="bi bi-check-circle text-success"></i>
                                </div>
                                <div class="admin-stat-value">{{ number_format($stats['active_clients']) }}</div>
                                <div class="admin-stat-label">Active Clients</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="admin-stat">
                                <div class="admin-stat-icon bg-info-subtle">
                                    <i class="bi bi-people text-info"></i>
                                </div>
                                <div class="admin-stat-value">{{ number_format($stats['total_users']) }}</div>
                                <div class="admin-stat-label">Total Users</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="admin-stat">
                                <div class="admin-stat-icon bg-warning-subtle">
                                    <i class="bi bi-broadcast text-warning"></i>
                                </div>
                                <div class="admin-stat-value">{{ number_format($stats['total_channels']) }}</div>
                                <div class="admin-stat-label">Total Channels</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

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

    <!-- Channel Breakdown -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom mb-0">
                        <i class="bi bi-broadcast me-2"></i>Messages by Channel
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <div class="channel-card channel-sms">
                                <div class="channel-card-icon">
                                    <i class="bi bi-phone-fill"></i>
                                </div>
                                <div class="channel-card-content">
                                    <div class="channel-card-label">SMS Messages</div>
                                    <div class="channel-card-value">{{ number_format($stats['sms_count']) }}</div>
                                    <div class="channel-card-footer">
                                        {{ $stats['total_messages'] > 0 ? round(($stats['sms_count'] / $stats['total_messages']) * 100, 1) : 0 }}% of total
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="channel-card channel-whatsapp">
                                <div class="channel-card-icon">
                                    <i class="bi bi-whatsapp"></i>
                                </div>
                                <div class="channel-card-content">
                                    <div class="channel-card-label">WhatsApp Messages</div>
                                    <div class="channel-card-value">{{ number_format($stats['whatsapp_count']) }}</div>
                                    <div class="channel-card-footer">
                                        {{ $stats['total_messages'] > 0 ? round(($stats['whatsapp_count'] / $stats['total_messages']) * 100, 1) : 0 }}% of total
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="channel-card channel-email">
                                <div class="channel-card-icon">
                                    <i class="bi bi-envelope-fill"></i>
                                </div>
                                <div class="channel-card-content">
                                    <div class="channel-card-label">Email Messages</div>
                                    <div class="channel-card-value">{{ number_format($stats['email_count']) }}</div>
                                    <div class="channel-card-footer">
                                        {{ $stats['total_messages'] > 0 ? round(($stats['email_count'] / $stats['total_messages']) * 100, 1) : 0 }}% of total
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Monitoring -->
    @if($isAdmin)
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
    @endif

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
                    <div class="quick-stat-item">
                        <div class="d-flex align-items-center">
                            <div class="quick-stat-icon bg-info-subtle">
                                <i class="bi bi-phone text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="quick-stat-label">SMS Sent</div>
                                <div class="quick-stat-value">{{ number_format($stats['sms_count']) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="quick-stat-item mb-0">
                        <div class="d-flex align-items-center">
                            <div class="quick-stat-icon bg-success-subtle" style="background: rgba(37, 211, 102, 0.1) !important;">
                                <i class="bi bi-whatsapp" style="color: #25D366;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="quick-stat-label">WhatsApp Sent</div>
                                <div class="quick-stat-value">{{ number_format($stats['whatsapp_count']) }}</div>
                            </div>
                            <a href="{{ route('whatsapp.index') }}" class="btn btn-sm btn-link">
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
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="card-title-custom mb-0">
                        <i class="bi bi-clock-history me-2"></i>Recent Activity
                    </h5>
                    @if(count($recentActivity) > 5)
                    <button class="btn btn-sm btn-link text-decoration-none" id="toggleActivityBtn" onclick="toggleRecentActivity()">
                        <span id="toggleActivityText">Show All</span>
                        <i class="bi bi-chevron-down" id="toggleActivityIcon"></i>
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="activity-timeline" id="activityTimeline">
                        @forelse($recentActivity as $index => $activity)
                            <div class="activity-item" data-activity-index="{{ $index }}" style="{{ $index >= 5 ? 'display: none;' : '' }}">
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
                    <div class="d-flex gap-2">
                        @if($recentCampaigns->count() > 5)
                        <button class="btn btn-sm btn-link text-decoration-none" id="toggleCampaignsBtn" onclick="toggleRecentCampaigns()">
                            <span id="toggleCampaignsText">Show All</span>
                            <i class="bi bi-chevron-down" id="toggleCampaignsIcon"></i>
                        </button>
                        @endif
                        <a href="{{ route('campaigns.index') }}" class="btn btn-sm btn-outline-primary">
                            View All <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
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
                            <tbody id="campaignsTableBody">
                                @foreach($recentCampaigns as $index => $campaign)
                                <tr class="campaign-row" data-campaign-index="{{ $index }}" style="{{ $index >= 5 ? 'display: none;' : '' }}">
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

    .stat-card-info .stat-icon {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
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

    .stat-label .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1;
    }

    #syncOnfonBtn:hover {
        transform: scale(1.1);
        transition: transform 0.2s ease;
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

    /* Admin Stats */
    .admin-stat {
        text-align: center;
    }

    .admin-stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 1rem;
    }

    .admin-stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }

    .admin-stat-label {
        font-size: 0.875rem;
        color: #64748b;
    }

    /* Channel Cards */
    .channel-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        border: 2px solid transparent;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.2s;
        height: 100%;
    }

    .channel-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .channel-sms {
        border-color: #3b82f6;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(59, 130, 246, 0.02) 100%);
    }

    .channel-whatsapp {
        border-color: #25D366;
        background: linear-gradient(135deg, rgba(37, 211, 102, 0.05) 0%, rgba(37, 211, 102, 0.02) 100%);
    }

    .channel-email {
        border-color: #f59e0b;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(245, 158, 11, 0.02) 100%);
    }

    .channel-card-icon {
        width: 60px;
        height: 60px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        flex-shrink: 0;
    }

    .channel-sms .channel-card-icon {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .channel-whatsapp .channel-card-icon {
        background: rgba(37, 211, 102, 0.1);
        color: #25D366;
    }

    .channel-email .channel-card-icon {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .channel-card-content {
        flex: 1;
    }

    .channel-card-label {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 0.25rem;
    }

    .channel-card-value {
        font-size: 1.875rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }

    .channel-card-footer {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    @media (max-width: 992px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .header-actions {
            width: 100%;
            margin-top: 0.5rem;
        }

        .header-actions .btn {
            flex: 1;
        }

        .channel-card {
            margin-bottom: 0.75rem;
        }
    }

    @media (max-width: 768px) {
        .page-title {
            font-size: 1.5rem;
        }

        .stat-value {
            font-size: 1.5rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            font-size: 1.25rem;
        }

        .time-widget {
            padding: 1rem;
        }

        .time-widget-value {
            font-size: 1.5rem;
        }

        .time-widget-icon {
            width: 48px;
            height: 48px;
            font-size: 1.5rem;
        }

        .admin-stat-icon {
            width: 48px;
            height: 48px;
            font-size: 1.25rem;
        }

        .admin-stat-value {
            font-size: 1.5rem;
        }

        .channel-card-icon {
            width: 48px;
            height: 48px;
            font-size: 1.5rem;
        }

        .channel-card-value {
            font-size: 1.5rem;
        }

        /* Stack table on mobile */
        .table-responsive table {
            font-size: 0.875rem;
        }

        .table-responsive th,
        .table-responsive td {
            padding: 0.5rem;
        }
    }

    @media (max-width: 576px) {
        .page-title {
            font-size: 1.25rem;
        }

        .stat-card {
            padding: 1rem;
        }

        .stat-value {
            font-size: 1.25rem;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .time-widget {
            padding: 0.75rem;
        }

        .time-widget-value {
            font-size: 1.25rem;
        }

        .time-widget-icon {
            width: 40px;
            height: 40px;
            font-size: 1.25rem;
        }

        .channel-card {
            padding: 1rem;
        }

        .channel-card-icon {
            width: 40px;
            height: 40px;
            font-size: 1.25rem;
        }

        .channel-card-value {
            font-size: 1.25rem;
        }

        .quick-stat-icon {
            width: 32px;
            height: 32px;
            font-size: 1rem;
        }

        .quick-stat-value {
            font-size: 1rem;
        }

        .admin-stat-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .admin-stat-value {
            font-size: 1.25rem;
        }

        .card-header-custom {
            padding: 1rem;
        }

        .card-title-custom {
            font-size: 0.9rem;
        }

        /* Make tables mobile-friendly with horizontal scroll */
        .table-responsive {
            margin: 0 -1rem;
            padding: 0 1rem;
        }

        .table {
            font-size: 0.8rem;
        }

        .table th,
        .table td {
            padding: 0.5rem 0.25rem;
            white-space: nowrap;
        }

        /* Stack action buttons in tables */
        .table .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.4rem;
        }
    }

    @media (max-width: 400px) {
        .page-title {
            font-size: 1.1rem;
        }

        .stat-card {
            flex-direction: column;
            text-align: center;
            padding: 0.75rem;
        }

        .stat-icon {
            margin: 0 auto 0.5rem;
        }

        .time-widget {
            flex-direction: column;
            text-align: center;
        }

        .time-widget-icon {
            margin: 0 auto 0.5rem;
        }

        .time-widget-value {
            font-size: 1.1rem;
        }

        .header-actions {
            flex-direction: column;
        }

        .header-actions .btn {
            width: 100%;
        }

        .channel-card {
            flex-direction: column;
            text-align: center;
        }

        .channel-card-icon {
            margin: 0 auto 0.5rem;
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

    // Onfon Balance Sync Function
    function syncOnfonBalance() {
        const btn = document.getElementById('syncOnfonBtn');
        const balanceValue = document.getElementById('onfonBalanceValue');
        const lastSync = document.getElementById('onfonLastSync');
        
        // Disable button and show loading
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-clockwise spinner-border spinner-border-sm"></i>';
        
        // Fetch fresh balance from API
        fetch('/api/onfon/balance/refresh', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update balance display
                balanceValue.innerHTML = data.balance.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' units';
                
                // Update last sync time
                lastSync.textContent = 'Just now';
                
                // Show success notification
                showNotification('success', `Balance updated: ${data.balance.toFixed(2)} units`);
            } else {
                showNotification('error', data.message || 'Failed to sync balance');
            }
        })
        .catch(error => {
            console.error('Balance sync error:', error);
            showNotification('error', 'Network error. Please try again.');
        })
        .finally(() => {
            // Re-enable button
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-clockwise"></i>';
        });
    }

    // Auto-refresh Onfon balance every 30 seconds
    let balanceRefreshInterval = null;
    
    function startBalanceAutoRefresh() {
        // Clear any existing interval
        if (balanceRefreshInterval) {
            clearInterval(balanceRefreshInterval);
        }
        
        // Auto-refresh every 30 seconds
        balanceRefreshInterval = setInterval(() => {
            fetchOnfonBalanceQuietly();
        }, 30000); // 30 seconds
    }

    // Fetch balance without showing loading or notifications
    function fetchOnfonBalanceQuietly() {
        fetch('/api/onfon/balance/refresh', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const balanceValue = document.getElementById('onfonBalanceValue');
                const currentBalance = parseFloat(balanceValue.textContent.replace(/[^0-9.]/g, ''));
                const newBalance = parseFloat(data.balance);
                
                // Only update if balance changed
                if (currentBalance !== newBalance) {
                    balanceValue.innerHTML = newBalance.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + ' units';
                    
                    // Add pulse animation to show update
                    balanceValue.style.animation = 'pulse 0.5s ease-in-out';
                    setTimeout(() => {
                        balanceValue.style.animation = '';
                    }, 500);
                }
            }
        })
        .catch(error => {
            console.error('Auto-refresh error:', error);
        });
    }

    // Start auto-refresh when page loads
    document.addEventListener('DOMContentLoaded', function() {
        startBalanceAutoRefresh();
        
        // Also fetch immediately on page load
        setTimeout(() => {
            fetchOnfonBalanceQuietly();
        }, 2000);
    });

    // Notification helper function
    function showNotification(type, message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 150);
        }, 5000);
    }

    // Toggle Recent Activity
    let activityExpanded = false;
    function toggleRecentActivity() {
        const items = document.querySelectorAll('.activity-item');
        const btn = document.getElementById('toggleActivityBtn');
        const text = document.getElementById('toggleActivityText');
        const icon = document.getElementById('toggleActivityIcon');
        
        activityExpanded = !activityExpanded;
        
        items.forEach((item, index) => {
            if (index >= 5) {
                item.style.display = activityExpanded ? 'flex' : 'none';
            }
        });
        
        if (activityExpanded) {
            text.textContent = 'Show Less';
            icon.className = 'bi bi-chevron-up';
        } else {
            text.textContent = 'Show All';
            icon.className = 'bi bi-chevron-down';
        }
    }

    // Toggle Recent Campaigns
    let campaignsExpanded = false;
    function toggleRecentCampaigns() {
        const rows = document.querySelectorAll('.campaign-row');
        const btn = document.getElementById('toggleCampaignsBtn');
        const text = document.getElementById('toggleCampaignsText');
        const icon = document.getElementById('toggleCampaignsIcon');
        
        campaignsExpanded = !campaignsExpanded;
        
        rows.forEach((row, index) => {
            if (index >= 5) {
                row.style.display = campaignsExpanded ? 'table-row' : 'none';
            }
        });
        
        if (campaignsExpanded) {
            text.textContent = 'Show Less';
            icon.className = 'bi bi-chevron-up';
        } else {
            text.textContent = 'Show All';
            icon.className = 'bi bi-chevron-down';
        }
    }
</script>
@endsection
