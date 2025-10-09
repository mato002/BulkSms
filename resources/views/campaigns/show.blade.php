@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Section -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('campaigns.index') }}">Campaigns</a></li>
                    <li class="breadcrumb-item active">{{ $campaign->name }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 d-flex align-items-center">
                <i class="bi bi-megaphone me-2 text-primary"></i>
                {{ $campaign->name }}
            </h1>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                @if($campaign->status === 'draft')
                    <form action="{{ route('campaigns.send', $campaign->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Send this campaign now?')">
                            <i class="bi bi-send me-1"></i> Send Campaign
                        </button>
                    </form>
                @endif
                <a href="{{ route('campaigns.edit', $campaign->id) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Status Overview Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 0.75rem; font-weight: 600;">Status</h6>
                            @if($campaign->status === 'sent')
                                <span class="badge bg-success fs-6 px-3 py-2">
                                    <i class="bi bi-check-circle me-1"></i> Sent
                                </span>
                            @elseif($campaign->status === 'draft')
                                <span class="badge bg-secondary fs-6 px-3 py-2">
                                    <i class="bi bi-file-earmark me-1"></i> Draft
                                </span>
                            @elseif($campaign->status === 'scheduled')
                                <span class="badge bg-info fs-6 px-3 py-2">
                                    <i class="bi bi-clock me-1"></i> Scheduled
                                </span>
                            @else
                                <span class="badge bg-warning fs-6 px-3 py-2">
                                    <i class="bi bi-hourglass-split me-1"></i> {{ ucfirst($campaign->status) }}
                                </span>
                            @endif
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-info-circle fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 0.75rem; font-weight: 600;">Total Recipients</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($campaign->total_recipients) }}</h3>
                            <small class="text-muted">contacts targeted</small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-people fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 0.75rem; font-weight: 600;">Delivered</h6>
                            <h3 class="mb-0 fw-bold text-success">{{ number_format($campaign->delivered_count ?? 0) }}</h3>
                            @php
                                $deliveryRate = $campaign->total_recipients > 0 
                                    ? round(($campaign->delivered_count ?? 0) / $campaign->total_recipients * 100, 1) 
                                    : 0;
                            @endphp
                            <small class="text-muted">{{ $deliveryRate }}% delivery rate</small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-check2-circle fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 0.75rem; font-weight: 600;">Total Cost</h6>
                            <h3 class="mb-0 fw-bold">${{ number_format($campaign->total_cost ?? 0, 2) }}</h3>
                            @if($campaign->total_recipients > 0)
                                <small class="text-muted">${{ number_format(($campaign->total_cost ?? 0) / $campaign->total_recipients, 4) }}/msg</small>
                            @endif
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-currency-dollar fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Statistics -->
    @if($campaign->status === 'sent' && $campaign->total_recipients > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-bar-chart me-2 text-primary"></i>
                        Delivery Analytics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            @php
                                $sentCount = $campaign->sent_count ?? 0;
                                $deliveredCount = $campaign->delivered_count ?? 0;
                                $failedCount = $campaign->failed_count ?? 0;
                                $pendingCount = $campaign->total_recipients - $sentCount;
                                
                                $sentPercent = round($sentCount / $campaign->total_recipients * 100, 1);
                                $deliveredPercent = round($deliveredCount / $campaign->total_recipients * 100, 1);
                                $failedPercent = round($failedCount / $campaign->total_recipients * 100, 1);
                                $pendingPercent = round($pendingCount / $campaign->total_recipients * 100, 1);
                            @endphp
                            
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold">Overall Progress</span>
                                    <span class="text-muted">{{ $sentCount + $deliveredCount + $failedCount }} / {{ $campaign->total_recipients }}</span>
                                </div>
                                <div class="progress" style="height: 30px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ $deliveredPercent }}%;" 
                                         title="Delivered: {{ $deliveredCount }}">
                                        @if($deliveredPercent > 10)
                                            {{ $deliveredPercent }}%
                                        @endif
                                    </div>
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: {{ $sentPercent - $deliveredPercent }}%;" 
                                         title="Sent: {{ $sentCount }}">
                                        @if(($sentPercent - $deliveredPercent) > 10)
                                            {{ round($sentPercent - $deliveredPercent, 1) }}%
                                        @endif
                                    </div>
                                    <div class="progress-bar bg-danger" role="progressbar" 
                                         style="width: {{ $failedPercent }}%;" 
                                         title="Failed: {{ $failedCount }}">
                                        @if($failedPercent > 10)
                                            {{ $failedPercent }}%
                                        @endif
                                    </div>
                                    <div class="progress-bar bg-secondary" role="progressbar" 
                                         style="width: {{ $pendingPercent }}%;" 
                                         title="Pending: {{ $pendingCount }}">
                                        @if($pendingPercent > 10)
                                            {{ $pendingPercent }}%
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-6 col-md-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary rounded p-2 me-2">
                                            <i class="bi bi-send text-white"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ number_format($sentCount) }}</div>
                                            <small class="text-muted">Sent</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success rounded p-2 me-2">
                                            <i class="bi bi-check-circle text-white"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ number_format($deliveredCount) }}</div>
                                            <small class="text-muted">Delivered</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-danger rounded p-2 me-2">
                                            <i class="bi bi-x-circle text-white"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ number_format($failedCount) }}</div>
                                            <small class="text-muted">Failed</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-secondary rounded p-2 me-2">
                                            <i class="bi bi-hourglass-split text-white"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ number_format($pendingCount) }}</div>
                                            <small class="text-muted">Pending</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="text-center p-4 bg-light rounded">
                                <div class="display-4 fw-bold text-success">{{ $deliveryRate }}%</div>
                                <div class="text-muted mt-2">Success Rate</div>
                                @if($deliveryRate >= 95)
                                    <div class="badge bg-success mt-2">Excellent</div>
                                @elseif($deliveryRate >= 85)
                                    <div class="badge bg-info mt-2">Good</div>
                                @elseif($deliveryRate >= 70)
                                    <div class="badge bg-warning mt-2">Fair</div>
                                @else
                                    <div class="badge bg-danger mt-2">Needs Attention</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Campaign Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        Campaign Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-semibold mb-1">Channel</label>
                                <div>
                                    @if(($campaign->channel ?? 'sms') === 'whatsapp')
                                        <span class="badge bg-success fs-6 px-3 py-2">
                                            <i class="bi bi-whatsapp me-1"></i> WhatsApp
                                        </span>
                                    @else
                                        <span class="badge bg-primary fs-6 px-3 py-2">
                                            <i class="bi bi-chat-dots me-1"></i> SMS
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-semibold mb-1">Sender ID</label>
                                <div class="fs-6 fw-medium">{{ $campaign->sender_id ?? 'N/A' }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-semibold mb-1">Template</label>
                                <div class="fs-6">
                                    @if($campaign->template_id && $campaign->template)
                                        <a href="#" class="text-decoration-none">
                                            {{ $campaign->template->name ?? 'N/A' }}
                                        </a>
                                    @else
                                        <span class="text-muted">No template used</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-semibold mb-1">Created At</label>
                                <div class="fs-6">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    {{ $campaign->created_at->format('M d, Y') }}
                                    <span class="text-muted">at {{ $campaign->created_at->format('h:i A') }}</span>
                                </div>
                                <small class="text-muted">{{ $campaign->created_at->diffForHumans() }}</small>
                            </div>

                            @if($campaign->scheduled_at)
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-semibold mb-1">Scheduled For</label>
                                <div class="fs-6">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ \Carbon\Carbon::parse($campaign->scheduled_at)->format('M d, Y h:i A') }}
                                </div>
                            </div>
                            @endif

                            @if($campaign->sent_at)
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-semibold mb-1">Sent At</label>
                                <div class="fs-6">
                                    <i class="bi bi-send-check me-1 text-success"></i>
                                    {{ $campaign->sent_at->format('M d, Y') }}
                                    <span class="text-muted">at {{ $campaign->sent_at->format('h:i A') }}</span>
                                </div>
                                <small class="text-muted">{{ $campaign->sent_at->diffForHumans() }}</small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Content -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-chat-left-text me-2 text-primary"></i>
                        Message Content
                    </h5>
                </div>
                <div class="card-body">
                    <div class="bg-light rounded p-4 position-relative" style="border-left: 4px solid #0d6efd;">
                        <div class="message-preview" style="white-space: pre-wrap; font-family: system-ui, -apple-system, sans-serif; line-height: 1.6;">{{ $campaign->message }}</div>
                        @php
                            $messageLength = strlen($campaign->message);
                            $smsCount = ceil($messageLength / 160);
                        @endphp
                        <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-secondary">{{ $messageLength }} characters</span>
                                <span class="badge bg-info ms-2">{{ $smsCount }} SMS {{ $smsCount > 1 ? 'parts' : 'part' }}</span>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" onclick="navigator.clipboard.writeText(`{{ addslashes($campaign->message) }}`)">
                                <i class="bi bi-clipboard me-1"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recipients List -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-people me-2 text-primary"></i>
                        Recipients
                        <span class="badge bg-primary ms-2">{{ number_format($campaign->total_recipients) }}</span>
                    </h5>
                    <div>
                        <input type="text" id="recipientSearch" class="form-control form-control-sm" placeholder="Search recipients..." style="min-width: 200px;">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 400px;">
                        @php
                            $recipients = is_array($campaign->recipients) ? $campaign->recipients : (json_decode($campaign->recipients, true) ?? []);
                        @endphp
                        @if(count($recipients) > 0)
                        <table class="table table-hover mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="border-0" style="width: 50px;">#</th>
                                    <th class="border-0">Phone Number</th>
                                    <th class="border-0 text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody id="recipientTableBody">
                                @foreach($recipients as $index => $recipient)
                                <tr class="recipient-row">
                                    <td class="text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <i class="bi bi-telephone me-2"></i>
                                        <span class="recipient-phone">{{ $recipient }}</span>
                                    </td>
                                    <td class="text-end">
                                        @if($campaign->status === 'sent')
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i> Sent
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-hourglass-split me-1"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            <p class="mb-0">No recipients found</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-graph-up me-2"></i>
                        Quick Stats
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <div class="text-muted small">Total Recipients</div>
                            <div class="h4 mb-0 fw-bold">{{ number_format($campaign->total_recipients) }}</div>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-people fs-4 text-info"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <div class="text-muted small">Messages Sent</div>
                            <div class="h4 mb-0 fw-bold">{{ number_format($campaign->sent_count ?? 0) }}</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-send fs-4 text-primary"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <div class="text-muted small">Delivered</div>
                            <div class="h4 mb-0 fw-bold text-success">{{ number_format($campaign->delivered_count ?? 0) }}</div>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-check2-circle fs-4 text-success"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Failed</div>
                            <div class="h4 mb-0 fw-bold text-danger">{{ number_format($campaign->failed_count ?? 0) }}</div>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="bi bi-x-circle fs-4 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Campaign Timeline -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-clock-history me-2 text-primary"></i>
                        Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary rounded-circle p-2" style="width: 35px; height: 35px;">
                                        <i class="bi bi-plus-circle text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-semibold">Created</div>
                                    <div class="text-muted small">{{ $campaign->created_at->format('M d, Y h:i A') }}</div>
                                    <div class="text-muted small">{{ $campaign->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        </div>

                        @if($campaign->scheduled_at)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-info rounded-circle p-2" style="width: 35px; height: 35px;">
                                        <i class="bi bi-clock text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-semibold">Scheduled</div>
                                    <div class="text-muted small">{{ \Carbon\Carbon::parse($campaign->scheduled_at)->format('M d, Y h:i A') }}</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($campaign->sent_at)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-success rounded-circle p-2" style="width: 35px; height: 35px;">
                                        <i class="bi bi-send-check text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-semibold">Sent</div>
                                    <div class="text-muted small">{{ $campaign->sent_at->format('M d, Y h:i A') }}</div>
                                    <div class="text-muted small">{{ $campaign->sent_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($campaign->status === 'draft')
                        <div class="timeline-item">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-secondary rounded-circle p-2" style="width: 35px; height: 35px;">
                                        <i class="bi bi-hourglass-split text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-semibold">Pending</div>
                                    <div class="text-muted small">Waiting to be sent</div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Cost Breakdown -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-cash-stack me-2 text-primary"></i>
                        Cost Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Cost</span>
                            <span class="fw-bold fs-5">${{ number_format($campaign->total_cost ?? 0, 2) }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%;"></div>
                        </div>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Cost per Message</span>
                            @if($campaign->total_recipients > 0)
                                <span class="fw-semibold">${{ number_format(($campaign->total_cost ?? 0) / $campaign->total_recipients, 4) }}</span>
                            @else
                                <span class="fw-semibold">$0.00</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">SMS Parts</span>
                            @php
                                $messageLength = strlen($campaign->message);
                                $smsCount = ceil($messageLength / 160);
                            @endphp
                            <span class="fw-semibold">{{ $smsCount }} {{ $smsCount > 1 ? 'parts' : 'part' }}</span>
                        </div>
                    </div>

                    <div class="bg-light rounded p-3 text-center">
                        <small class="text-muted d-block mb-1">Estimated Total</small>
                        <div class="h4 mb-0 fw-bold text-primary">${{ number_format($campaign->total_cost ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 17px;
    top: 35px;
    height: calc(100% - 10px);
    width: 2px;
    background: #e9ecef;
}

.timeline {
    position: relative;
}

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
}

.badge {
    font-weight: 500;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.progress {
    border-radius: 10px;
}

.progress-bar {
    transition: width 0.6s ease;
}
</style>

<script>
// Search functionality for recipients
document.getElementById('recipientSearch')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.recipient-row');
    
    rows.forEach(row => {
        const phone = row.querySelector('.recipient-phone').textContent.toLowerCase();
        if (phone.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
@endsection
