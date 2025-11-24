@extends('layouts.app')

@section('title', 'Profile Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Profile Settings</h1>
                    <p class="text-muted mb-0">Manage your account information and preferences</p>
                </div>
                <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Profile Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle"></i> Please correct the following errors:</h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('tenant.profile.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-user"></i> Full Name *
                                    </label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="{{ old('name', $user->name) }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope"></i> Email Address *
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="{{ old('email', $user->email) }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone"></i> Phone Number
                                    </label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="{{ old('phone', $user->phone) }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="company_name" class="form-label">
                                        <i class="fas fa-building"></i> Company Name *
                                    </label>
                                    <input type="text" class="form-control" id="company_name" name="company_name" 
                                           value="{{ old('company_name', $client->company_name) }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="sender_id" class="form-label">
                                        <i class="fas fa-id-card"></i> Sender ID *
                                    </label>
                                    <input type="text" class="form-control" id="sender_id" name="sender_id" 
                                           value="{{ old('sender_id', $client->sender_id) }}" required
                                           maxlength="11" pattern="[A-Z0-9]+" title="Only uppercase letters and numbers allowed">
                                    <small class="form-text text-muted">
                                        This will appear as the sender name in SMS messages (max 11 characters)
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Account Status</label>
                                    <div class="form-control-plaintext">
                                        <span class="badge bg-{{ $client->status ? 'success' : 'warning' }}">
                                            <i class="fas fa-{{ $client->status ? 'check-circle' : 'clock' }}"></i>
                                            {{ $client->status ? 'Active' : 'Pending Approval' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                            <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Account Details -->
        <div class="col-lg-4">
            <!-- Account Overview -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Account Overview</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 text-primary">{{ $client->tier }}</div>
                                <small class="text-muted">Account Tier</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 text-success">KES {{ number_format($client->balance, 2) }}</div>
                                <small class="text-muted">Current Balance</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Member Since:</span>
                        <span class="font-weight-bold">{{ $client->created_at->format('M d, Y') }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Last Login:</span>
                        <span class="font-weight-bold">{{ $user->updated_at->diffForHumans() }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">API Key:</span>
                        <span class="font-weight-bold">{{ substr($client->api_key, 0, 10) }}...</span>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Security</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('profile.password') }}" class="btn btn-outline-warning">
                            <i class="fas fa-key"></i> Change Password
                        </a>
                        <button class="btn btn-outline-danger" onclick="regenerateApiKey()">
                            <i class="fas fa-sync"></i> Regenerate API Key
                        </button>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Regenerating your API key will invalidate the current key and require updating all integrations.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('tenant.billing') }}" class="btn btn-outline-primary">
                            <i class="fas fa-wallet"></i> Manage Billing
                        </a>
                        <a href="{{ route('tenant.api-docs') }}" class="btn btn-outline-primary">
                            <i class="fas fa-code"></i> API Documentation
                        </a>
                        <a href="{{ route('tenant.notifications') }}" class="btn btn-outline-primary">
                            <i class="fas fa-bell"></i> Notifications
                        </a>
                        <a href="mailto:mathiasodhis@gmail.com" class="btn btn-outline-secondary">
                            <i class="fas fa-envelope"></i> Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function regenerateApiKey() {
    if (confirm('Are you sure you want to regenerate your API key? This will invalidate the current key and require updating all integrations.')) {
        // You can implement this via AJAX or redirect to a specific route
        fetch('{{ route("settings.regenerate-api-key") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('API key regenerated successfully! Your new API key is: ' + data.api_key);
                location.reload();
            } else {
                alert('Failed to regenerate API key. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}

// Phone number formatting
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.startsWith('254')) {
        value = '+' + value;
    } else if (value.startsWith('0')) {
        value = '+254' + value.substring(1);
    } else if (value && !value.startsWith('+')) {
        value = '+254' + value;
    }
    e.target.value = value;
});

// Sender ID formatting
document.getElementById('sender_id').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
});
</script>
@endsection


