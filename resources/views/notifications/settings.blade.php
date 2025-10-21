@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Sidebar -->
            <div class="list-group">
                <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action">
                    <i class="fas fa-cog"></i> General Settings
                </a>
                <a href="{{ route('notifications.settings') }}" class="list-group-item list-group-item-action active">
                    <i class="fas fa-bell"></i> Notifications
                </a>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-bell"></i> Notification Settings</h4>
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
                            <h5 class="border-bottom pb-2"><i class="fas fa-money-bill-wave text-warning"></i> Low Balance Alerts</h5>
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
                            <h5 class="border-bottom pb-2"><i class="fas fa-exclamation-triangle text-danger"></i> Failed Delivery Alerts</h5>
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
                            <h5 class="border-bottom pb-2"><i class="fas fa-paper-plane text-primary"></i> Campaign Notifications</h5>
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
                            <h5 class="border-bottom pb-2"><i class="fas fa-chart-line text-success"></i> Summary Reports</h5>
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
                            <h5 class="border-bottom pb-2"><i class="fas fa-paper-plane text-info"></i> Notification Channels</h5>
                            <p class="text-muted small">Choose how you want to receive notifications</p>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="notify_via_email" id="notify_via_email" value="1" {{ $settings->notify_via_email ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_via_email">
                                    <i class="fas fa-envelope"></i> Email Notifications
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="notify_via_sms" id="notify_via_sms" value="1" {{ $settings->notify_via_sms ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_via_sms">
                                    <i class="fas fa-sms"></i> SMS Notifications
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="notify_via_browser" id="notify_via_browser" value="1" {{ $settings->notify_via_browser ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_via_browser">
                                    <i class="fas fa-bell"></i> In-App Notifications
                                </label>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </div>
                    </form>
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
</script>
@endsection


