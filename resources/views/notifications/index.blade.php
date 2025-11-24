@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0"><i class="bi bi-bell"></i> Notifications</h1>
                    <p class="text-muted mb-0">Stay updated with your account activity</p>
                </div>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4" id="notificationTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                <i class="bi bi-bell"></i> Notifications
                @if($notifications->where('is_read', false)->count() > 0)
                    <span class="badge bg-danger ms-2">{{ $notifications->where('is_read', false)->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab">
                <i class="bi bi-gear"></i> Settings
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="notificationTabsContent">
        <!-- Notifications Tab -->
        <div class="tab-pane fade show active" id="notifications" role="tabpanel">
            <div class="row">
                <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-bell"></i> Your Notifications
                    </h6>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="markAllAsRead()">
                            <i class="bi bi-check-all"></i> Mark All Read
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($notifications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                                <div class="list-group-item list-group-item-action {{ $notification->is_read ? '' : 'bg-light' }}" 
                                     data-notification-id="{{ $notification->id }}">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="me-3">
                                                    @php
                                                        $iconClass = match($notification->type ?? 'info') {
                                                            'success' => 'bi-check-circle text-success',
                                                            'warning' => 'bi-exclamation-triangle text-warning',
                                                            'error', 'danger' => 'bi-x-circle text-danger',
                                                            'info' => 'bi-info-circle text-info',
                                                            default => 'bi-bell text-primary'
                                                        };
                                                    @endphp
                                                    <i class="bi {{ $iconClass }}" style="font-size: 1.5rem;"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 {{ $notification->is_read ? 'text-muted' : 'fw-bold' }}">
                                                        {{ $notification->title }}
                                                    </h6>
                                                    <p class="mb-1 {{ $notification->is_read ? 'text-muted' : '' }}">
                                                        {{ $notification->message }}
                                                    </p>
                                                    @if($notification->link && $notification->link !== '#')
                                                        <a href="{{ $notification->link }}" class="btn btn-sm btn-link p-0">
                                                            View details <i class="bi bi-arrow-right"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column align-items-end">
                                            <small class="text-muted mb-1">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </small>
                                            <div class="btn-group btn-group-sm" role="group">
                                                @if(!$notification->is_read)
                                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                                            onclick="markAsRead({{ $notification->id }})">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                                        onclick="deleteNotification({{ $notification->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($notification->metadata)
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                @if(isset($notification->metadata['campaign_name']))
                                                    Campaign: <strong>{{ $notification->metadata['campaign_name'] }}</strong>
                                                @endif
                                                @if(isset($notification->metadata['message_count']))
                                                    | Messages: <strong>{{ $notification->metadata['message_count'] }}</strong>
                                                @endif
                                                @if(isset($notification->metadata['balance']))
                                                    | Balance: <strong>KES {{ number_format($notification->metadata['balance'], 2) }}</strong>
                                                @endif
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="card-footer bg-white">
                            {{ $notifications->links('vendor.pagination.simple') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-muted">No notifications yet</h5>
                            <p class="text-muted">You'll receive notifications about your account activity, campaigns, and important updates here.</p>
                            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                <i class="bi bi-house"></i> Go to Dashboard
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Quick Stats -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-bar-chart"></i> Notification Stats
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="h4 text-primary">{{ $notifications->where('is_read', false)->count() }}</div>
                                    <small class="text-muted">Unread</small>
                                </div>
                                <div class="col-6">
                                    <div class="h4 text-success">{{ $notifications->where('is_read', true)->count() }}</div>
                                    <small class="text-muted">Read</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Tab -->
        <div class="tab-pane fade" id="settings" role="tabpanel">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="bi bi-gear"></i> Notification Settings</h5>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form action="{{ route('notifications.update-settings') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <!-- Low Balance Alerts -->
                                <div class="mb-4">
                                    <h5 class="border-bottom pb-2"><i class="bi bi-cash-coin text-warning"></i> Low Balance Alerts</h5>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="low_balance_enabled" id="low_balance_enabled" value="1" {{ $settings->low_balance_enabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="low_balance_enabled">
                                            <strong>Enable Low Balance Alerts</strong>
                                            <p class="text-muted small mb-0">Get notified when your balance falls below a threshold</p>
                                        </label>
                                    </div>
                                    <div class="mb-3" id="low_balance_settings">
                                        <label class="form-label">Alert Threshold (KES)</label>
                                        <input type="number" name="low_balance_threshold" class="form-control" value="{{ $settings->low_balance_threshold }}" step="0.01" min="0">
                                        <small class="text-muted">You'll be notified when balance drops below this amount</small>
                                    </div>
                                </div>

                                <!-- Failed Delivery Alerts -->
                                <div class="mb-4">
                                    <h5 class="border-bottom pb-2"><i class="bi bi-exclamation-triangle text-danger"></i> Failed Delivery Alerts</h5>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="failed_delivery_enabled" id="failed_delivery_enabled" value="1" {{ $settings->failed_delivery_enabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="failed_delivery_enabled">
                                            <strong>Enable Failed Delivery Alerts</strong>
                                            <p class="text-muted small mb-0">Get notified about high failure rates</p>
                                        </label>
                                    </div>
                                    <div class="mb-3" id="failed_delivery_settings">
                                        <label class="form-label">Alert After (number of failures)</label>
                                        <input type="number" name="failed_delivery_threshold" class="form-control" value="{{ $settings->failed_delivery_threshold }}" min="1">
                                        <small class="text-muted">Alert when this many messages fail in one hour</small>
                                    </div>
                                </div>

                                <!-- Campaign Notifications -->
                                <div class="mb-4">
                                    <h5 class="border-bottom pb-2"><i class="bi bi-megaphone text-primary"></i> Campaign Notifications</h5>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="campaign_complete_enabled" id="campaign_complete_enabled" value="1" {{ $settings->campaign_complete_enabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="campaign_complete_enabled">
                                            <strong>Campaign Completion Alerts</strong>
                                            <p class="text-muted small mb-0">Notify when campaigns finish sending</p>
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="large_campaign_warning_enabled" id="large_campaign_warning_enabled" value="1" {{ $settings->large_campaign_warning_enabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="large_campaign_warning_enabled">
                                            <strong>Large Campaign Warnings</strong>
                                            <p class="text-muted small mb-0">Warn before sending large campaigns</p>
                                        </label>
                                    </div>
                                    <div class="mb-3" id="large_campaign_settings">
                                        <label class="form-label">Large Campaign Threshold (recipients)</label>
                                        <input type="number" name="large_campaign_threshold" class="form-control" value="{{ $settings->large_campaign_threshold }}" min="1">
                                        <small class="text-muted">Campaigns larger than this will trigger a warning</small>
                                    </div>
                                </div>

                                <!-- Summary Reports -->
                                <div class="mb-4">
                                    <h5 class="border-bottom pb-2"><i class="bi bi-graph-up text-success"></i> Summary Reports</h5>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="daily_summary_enabled" id="daily_summary_enabled" value="1" {{ $settings->daily_summary_enabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="daily_summary_enabled">
                                            <strong>Daily Summary</strong>
                                            <p class="text-muted small mb-0">Receive daily activity summaries</p>
                                        </label>
                                    </div>
                                    <div class="mb-3" id="daily_summary_settings">
                                        <label class="form-label">Send At</label>
                                        <input type="time" name="daily_summary_time" class="form-control" value="{{ $settings->daily_summary_time ? substr($settings->daily_summary_time, 0, 5) : '09:00' }}">
                                    </div>

                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="weekly_summary_enabled" id="weekly_summary_enabled" value="1" {{ $settings->weekly_summary_enabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="weekly_summary_enabled">
                                            <strong>Weekly Summary</strong>
                                            <p class="text-muted small mb-0">Receive weekly activity summaries</p>
                                        </label>
                                    </div>
                                    <div class="mb-3" id="weekly_summary_settings">
                                        <label class="form-label">Send On</label>
                                        <select name="weekly_summary_day" class="form-select">
                                            <option value="monday" {{ $settings->weekly_summary_day == 'monday' ? 'selected' : '' }}>Monday</option>
                                            <option value="tuesday" {{ $settings->weekly_summary_day == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                                            <option value="wednesday" {{ $settings->weekly_summary_day == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                                            <option value="thursday" {{ $settings->weekly_summary_day == 'thursday' ? 'selected' : '' }}>Thursday</option>
                                            <option value="friday" {{ $settings->weekly_summary_day == 'friday' ? 'selected' : '' }}>Friday</option>
                                            <option value="saturday" {{ $settings->weekly_summary_day == 'saturday' ? 'selected' : '' }}>Saturday</option>
                                            <option value="sunday" {{ $settings->weekly_summary_day == 'sunday' ? 'selected' : '' }}>Sunday</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Notification Channels -->
                                <div class="mb-4">
                                    <h5 class="border-bottom pb-2"><i class="bi bi-broadcast text-info"></i> Notification Channels</h5>
                                    <p class="text-muted small">Choose how you want to receive notifications</p>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="notify_via_email" id="notify_via_email" value="1" {{ $settings->notify_via_email ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notify_via_email">
                                            <i class="bi bi-envelope"></i> Email Notifications
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="notify_via_sms" id="notify_via_sms" value="1" {{ $settings->notify_via_sms ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notify_via_sms">
                                            <i class="bi bi-chat-dots"></i> SMS Notifications
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="notify_via_browser" id="notify_via_browser" value="1" {{ $settings->notify_via_browser ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notify_via_browser">
                                            <i class="bi bi-bell"></i> In-App Notifications
                                        </label>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-save"></i> Save Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle visibility of sub-settings based on checkboxes
document.addEventListener('DOMContentLoaded', function() {
    const toggles = [
        { checkbox: 'low_balance_enabled', settings: 'low_balance_settings' },
        { checkbox: 'failed_delivery_enabled', settings: 'failed_delivery_settings' },
        { checkbox: 'large_campaign_warning_enabled', settings: 'large_campaign_settings' },
        { checkbox: 'daily_summary_enabled', settings: 'daily_summary_settings' },
        { checkbox: 'weekly_summary_enabled', settings: 'weekly_summary_settings' }
    ];

    toggles.forEach(function(toggle) {
        const checkbox = document.getElementById(toggle.checkbox);
        const settings = document.getElementById(toggle.settings);
        
        if (checkbox && settings) {
            function updateVisibility() {
                settings.style.display = checkbox.checked ? 'block' : 'none';
            }
            
            updateVisibility();
            checkbox.addEventListener('change', updateVisibility);
        }
    });
});

// Mark notification as read
function markAsRead(notificationId) {
    fetch(`{{ route('notifications.mark-read', '') }}/${notificationId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.classList.remove('bg-light');
                notificationElement.querySelector('h6').classList.add('text-muted');
                notificationElement.querySelector('h6').classList.remove('fw-bold');
                notificationElement.querySelector('p').classList.add('text-muted');
                
                // Remove the mark as read button
                const markAsReadBtn = notificationElement.querySelector('button[onclick*="markAsRead"]');
                if (markAsReadBtn) {
                    markAsReadBtn.remove();
                }
                
                // Reload to update stats
                setTimeout(() => location.reload(), 500);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to mark notification as read. Please try again.');
    });
}

// Mark all notifications as read
function markAllAsRead() {
    Swal.fire({
        title: 'Mark all as read?',
        text: 'This will mark all notifications as read.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, mark all as read!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ route("notifications.mark-all-read") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to mark all notifications as read. Please try again.');
        });
    }
}

// Delete notification
function deleteNotification(notificationId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Delete this notification?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ route('notifications.delete', '') }}/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove from UI
                const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.remove();
                    // Reload to update stats
                    setTimeout(() => location.reload(), 500);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete notification. Please try again.');
        });
    }
}
</script>
@endsection

