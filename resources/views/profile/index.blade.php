@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;
@endphp
<div class="container-fluid px-4">
    <!-- Header with Profile Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="position-relative" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 200px;">
                    <div class="position-absolute bottom-0 start-0 p-4 w-100" style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                        <div class="d-flex align-items-end">
                            <div class="position-relative">
                                @if($user->avatar)
                                    <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" class="rounded-circle border border-4 border-white" style="width: 120px; height: 120px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle border border-4 border-white bg-white d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                                        <i class="bi bi-person-circle text-secondary" style="font-size: 80px;"></i>
                                    </div>
                                @endif
                                <button type="button" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0" data-bs-toggle="modal" data-bs-target="#avatarModal" style="width: 35px; height: 35px;">
                                    <i class="bi bi-camera-fill"></i>
                                </button>
                            </div>
                            <div class="ms-4 text-white mb-2">
                                <h2 class="mb-1 fw-bold">{{ $user->name }}</h2>
                                <p class="mb-0 opacity-75"><i class="bi bi-envelope me-2"></i>{{ $user->email }}</p>
                                @if($user->phone)
                                    <p class="mb-0 opacity-75"><i class="bi bi-telephone me-2"></i>{{ $user->phone }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="bi bi-chat-dots-fill text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Total Messages</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_messages']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="bi bi-megaphone-fill text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Campaigns</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_campaigns']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="bi bi-people-fill text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Contacts</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_contacts']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Sent</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['messages_sent']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                                <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Failed</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['messages_failed']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                <i class="bi bi-graph-up-arrow text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Success Rate</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_messages'] > 0 ? number_format(($stats['messages_sent'] / $stats['total_messages']) * 100, 1) : 0 }}%</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Profile Content -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                                <i class="bi bi-person me-1"></i> Profile Information
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                <i class="bi bi-shield-lock me-1"></i> Security
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab">
                                <i class="bi bi-sliders me-1"></i> Preferences
                            </button>
                        </li>
                    </ul>
                </div>
        <div class="card-body">
                    <div class="tab-content">
                        <!-- Profile Information Tab -->
                        <div class="tab-pane fade show active" id="profile" role="tabpanel">
                            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                                <div class="row g-3">
                <div class="col-md-6">
                                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                </div>

                <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Phone Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="+1 234 567 8900">
                                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                </div>

                <div class="col-md-6">
                                        <label class="form-label fw-semibold">Timezone</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-globe"></i></span>
                                            <select name="timezone" class="form-select @error('timezone') is-invalid @enderror">
                                                <option value="">Select Timezone</option>
                                                <option value="UTC" {{ old('timezone', $user->timezone) == 'UTC' ? 'selected' : '' }}>UTC (Coordinated Universal Time)</option>
                                                <option value="America/New_York" {{ old('timezone', $user->timezone) == 'America/New_York' ? 'selected' : '' }}>Eastern Time (ET)</option>
                                                <option value="America/Chicago" {{ old('timezone', $user->timezone) == 'America/Chicago' ? 'selected' : '' }}>Central Time (CT)</option>
                                                <option value="America/Denver" {{ old('timezone', $user->timezone) == 'America/Denver' ? 'selected' : '' }}>Mountain Time (MT)</option>
                                                <option value="America/Los_Angeles" {{ old('timezone', $user->timezone) == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time (PT)</option>
                                                <option value="Europe/London" {{ old('timezone', $user->timezone) == 'Europe/London' ? 'selected' : '' }}>London (GMT)</option>
                                                <option value="Europe/Paris" {{ old('timezone', $user->timezone) == 'Europe/Paris' ? 'selected' : '' }}>Paris (CET)</option>
                                                <option value="Asia/Dubai" {{ old('timezone', $user->timezone) == 'Asia/Dubai' ? 'selected' : '' }}>Dubai (GST)</option>
                                                <option value="Asia/Tokyo" {{ old('timezone', $user->timezone) == 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo (JST)</option>
                                                <option value="Australia/Sydney" {{ old('timezone', $user->timezone) == 'Australia/Sydney' ? 'selected' : '' }}>Sydney (AEST)</option>
                                            </select>
                                            @error('timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Bio</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-text-paragraph"></i></span>
                                            <textarea name="bio" class="form-control @error('bio') is-invalid @enderror" rows="4" placeholder="Tell us about yourself...">{{ old('bio', $user->bio) }}</textarea>
                                            @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <small class="text-muted">Maximum 500 characters</small>
                                    </div>

                                    <div class="col-12">
                                        <hr class="my-3">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="bi bi-check2-circle me-1"></i> Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Security Tab -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <form action="{{ route('profile.password') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="alert alert-info border-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Password Requirements:</strong> Minimum 8 characters with a mix of letters, numbers, and symbols.
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Current Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                </div>

                <div class="col-md-6">
                                        <label class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                            <input type="password" name="password_confirmation" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <hr class="my-3">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="bi bi-shield-check me-1"></i> Update Password
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <hr class="my-4">

                            <div class="row">
                                <div class="col-12">
                                    <h6 class="fw-semibold mb-3">Two-Factor Authentication</h6>
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 fw-semibold">Enable Two-Factor Authentication</p>
                                                    <small class="text-muted">Add an extra layer of security to your account</small>
                                                </div>
                                                <button class="btn btn-outline-primary" disabled>
                                                    <i class="bi bi-shield-plus me-1"></i> Enable 2FA
                                                </button>
                                            </div>
                                            <small class="text-muted d-block mt-2"><i class="bi bi-info-circle me-1"></i>Coming soon</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preferences Tab -->
                        <div class="tab-pane fade" id="preferences" role="tabpanel">
                            <form action="{{ route('profile.preferences') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <h6 class="fw-semibold mb-3">Notification Settings</h6>
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="card bg-light border-0">
                                            <div class="card-body">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" name="email_notifications" id="emailNotif" value="1" {{ ($user->preferences['email_notifications'] ?? true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="emailNotif">
                                                        <strong>Email Notifications</strong>
                                                        <small class="d-block text-muted">Receive notifications via email</small>
                                                    </label>
                                                </div>

                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" name="message_alerts" id="msgAlerts" value="1" {{ ($user->preferences['message_alerts'] ?? true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="msgAlerts">
                                                        <strong>Message Alerts</strong>
                                                        <small class="d-block text-muted">Get notified when you receive new messages</small>
                                                    </label>
                                                </div>

                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" name="campaign_updates" id="campUpdates" value="1" {{ ($user->preferences['campaign_updates'] ?? true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="campUpdates">
                                                        <strong>Campaign Updates</strong>
                                                        <small class="d-block text-muted">Receive updates about your campaigns</small>
                                                    </label>
                                                </div>

                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" name="sms_notifications" id="smsNotif" value="1" {{ ($user->preferences['sms_notifications'] ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="smsNotif">
                                                        <strong>SMS Notifications</strong>
                                                        <small class="d-block text-muted">Receive important alerts via SMS</small>
                                                    </label>
                                                </div>

                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="marketing_emails" id="marketing" value="1" {{ ($user->preferences['marketing_emails'] ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="marketing">
                                                        <strong>Marketing Emails</strong>
                                                        <small class="d-block text-muted">Receive promotional emails and updates</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                </div>

                <div class="col-12">
                                        <hr class="my-3">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="bi bi-check2-circle me-1"></i> Save Preferences
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Account Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2"></i>Account Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Role</small>
                        <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Client</small>
                        <strong>{{ $user->client->name ?? 'N/A' }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Member Since</small>
                        <strong>{{ $user->created_at->format('M d, Y') }}</strong>
                    </div>
                    <div>
                        <small class="text-muted d-block mb-1">Last Updated</small>
                        <strong>{{ $user->updated_at->diffForHumans() }}</strong>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-clock-history me-2"></i>Recent Activity</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentActivity as $activity)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <p class="mb-1 small">
                                            <i class="bi bi-{{ $activity->channel == 'sms' ? 'phone' : ($activity->channel == 'whatsapp' ? 'whatsapp' : 'envelope') }} me-1"></i>
                                            Sent to <strong>{{ $activity->recipient }}</strong>
                                            <span class="text-muted">via {{ ucfirst($activity->channel) }}</span>
                                        </p>
                                        <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                    </div>
                                    <span class="badge bg-{{ $activity->status == 'sent' || $activity->status == 'delivered' ? 'success' : ($activity->status == 'failed' ? 'danger' : 'warning') }} ms-2">
                                        {{ ucfirst($activity->status) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                No recent activity
                            </div>
                        @endforelse
                    </div>
                </div>
                @if($recentActivity->count() > 0)
                    <div class="card-footer bg-white border-top text-center">
                        <a href="{{ route('messages.index') }}" class="text-decoration-none small">
                            View All Messages <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Avatar Upload Modal -->
<div class="modal fade" id="avatarModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-image me-2"></i>Update Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="text-center mb-3">
                        @if($user->avatar)
                            <img src="{{ Storage::url($user->avatar) }}" alt="Current Avatar" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px;">
                                <i class="bi bi-person-circle text-secondary" style="font-size: 100px;"></i>
                            </div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Choose New Picture</label>
                        <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/*" required>
                        @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">JPG, PNG or GIF. Max 2MB.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    @if($user->avatar)
                        <a href="{{ route('profile.avatar.delete') }}" class="btn btn-outline-danger me-auto" onclick="event.preventDefault(); document.getElementById('delete-avatar-form').submit();">
                            <i class="bi bi-trash me-1"></i> Remove
                        </a>
                    @endif
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($user->avatar)
<form id="delete-avatar-form" action="{{ route('profile.avatar.delete') }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endif

<style>
    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 2px solid transparent;
    }
    .nav-tabs .nav-link:hover {
        border-color: transparent;
        color: #495057;
    }
    .nav-tabs .nav-link.active {
        color: #667eea;
        border-bottom: 2px solid #667eea;
        background-color: transparent;
    }
    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
    }
    .input-group .form-control,
    .input-group .form-select {
        border-left: none;
    }
    .input-group .form-control:focus,
    .input-group .form-select:focus {
        border-left: none;
    }
    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }
</style>
@endsection


