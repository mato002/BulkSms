@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Notifications</h1>
                    <p class="text-muted mb-0">Stay updated with your account activity</p>
                </div>
                <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bell"></i> Your Notifications
                    </h6>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="markAllAsRead()">
                            <i class="fas fa-check-double"></i> Mark All Read
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearAllNotifications()">
                            <i class="fas fa-trash"></i> Clear All
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($notifications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                                <div class="list-group-item list-group-item-action {{ $notification->read_at ? '' : 'bg-light' }}" 
                                     data-notification-id="{{ $notification->id }}">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="me-3">
                                                    @if($notification->type === 'success')
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    @elseif($notification->type === 'warning')
                                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                                    @elseif($notification->type === 'error')
                                                        <i class="fas fa-times-circle text-danger"></i>
                                                    @elseif($notification->type === 'info')
                                                        <i class="fas fa-info-circle text-info"></i>
                                                    @else
                                                        <i class="fas fa-bell text-primary"></i>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 {{ $notification->read_at ? 'text-muted' : 'fw-bold' }}">
                                                        {{ $notification->title }}
                                                    </h6>
                                                    <p class="mb-1 {{ $notification->read_at ? 'text-muted' : '' }}">
                                                        {{ $notification->message }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column align-items-end">
                                            <small class="text-muted mb-1">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </small>
                                            <div class="btn-group btn-group-sm" role="group">
                                                @if(!$notification->read_at)
                                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                                            onclick="markAsRead({{ $notification->id }})">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                                        onclick="deleteNotification({{ $notification->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($notification->data)
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                @if(isset($notification->data['campaign_name']))
                                                    Campaign: <strong>{{ $notification->data['campaign_name'] }}</strong>
                                                @endif
                                                @if(isset($notification->data['message_count']))
                                                    | Messages: <strong>{{ $notification->data['message_count'] }}</strong>
                                                @endif
                                                @if(isset($notification->data['balance']))
                                                    | Balance: <strong>KES {{ number_format($notification->data['balance'], 2) }}</strong>
                                                @endif
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="card-footer">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-bell-slash text-muted" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-muted">No notifications yet</h5>
                            <p class="text-muted">You'll receive notifications about your account activity, campaigns, and important updates here.</p>
                            <a href="{{ route('tenant.dashboard') }}" class="btn btn-primary">
                                <i class="fas fa-home"></i> Go to Dashboard
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Notification Settings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cog"></i> Notification Settings
                    </h6>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                        <label class="form-check-label" for="emailNotifications">
                            Email notifications
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="campaignNotifications" checked>
                        <label class="form-check-label" for="campaignNotifications">
                            Campaign updates
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="balanceNotifications" checked>
                        <label class="form-check-label" for="balanceNotifications">
                            Balance alerts
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="systemNotifications" checked>
                        <label class="form-check-label" for="systemNotifications">
                            System updates
                        </label>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Notification Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 text-primary">{{ $notifications->whereNull('read_at')->count() }}</div>
                            <small class="text-muted">Unread</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-success">{{ $notifications->whereNotNull('read_at')->count() }}</div>
                            <small class="text-muted">Read</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-question-circle"></i> Need Help?
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Notifications help you stay informed about your account activity, campaign status, and important system updates.
                    </p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('tenant.api-docs') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-book"></i> API Documentation
                        </a>
                        <a href="mailto:mathiasodhis@gmail.com" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-envelope"></i> Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Mark notification as read
function markAsRead(notificationId) {
    fetch(`/tenant/notifications/${notificationId}/read`, {
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
            notificationElement.classList.remove('bg-light');
            notificationElement.querySelector('h6').classList.add('text-muted');
            notificationElement.querySelector('h6').classList.remove('fw-bold');
            notificationElement.querySelector('p').classList.add('text-muted');
            
            // Remove the mark as read button
            const markAsReadBtn = notificationElement.querySelector('button[onclick*="markAsRead"]');
            if (markAsReadBtn) {
                markAsReadBtn.remove();
            }
            
            // Update stats
            updateNotificationStats();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to mark notification as read. Please try again.');
    });
}

// Mark all notifications as read
function markAllAsRead() {
    if (confirm('Mark all notifications as read?')) {
        fetch('/tenant/notifications/mark-all-read', {
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
    if (confirm('Delete this notification?')) {
        fetch(`/tenant/notifications/${notificationId}`, {
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
                notificationElement.remove();
                
                // Update stats
                updateNotificationStats();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete notification. Please try again.');
        });
    }
}

// Clear all notifications
function clearAllNotifications() {
    if (confirm('Clear all notifications? This action cannot be undone.')) {
        fetch('/tenant/notifications/clear-all', {
            method: 'DELETE',
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
            alert('Failed to clear all notifications. Please try again.');
        });
    }
}

// Update notification stats
function updateNotificationStats() {
    // This would typically make an AJAX call to get updated stats
    // For now, we'll just reload the page
    setTimeout(() => {
        location.reload();
    }, 1000);
}
</script>
@endsection
