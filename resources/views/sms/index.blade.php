@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-phone text-primary"></i> SMS Integration</h2>
            <p class="text-muted mb-0">Manage SMS messaging via Onfon Media</p>
        </div>
        <div>
            @if($smsChannel && $smsChannel->isConfigured())
                <span class="badge bg-success">
                    <i class="bi bi-check-circle"></i> Connected
                </span>
            @else
                <span class="badge bg-warning">
                    <i class="bi bi-exclamation-triangle"></i> Not Configured
                </span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Configuration Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-gear"></i> Configuration
                    </h5>
                    
                    @if($smsChannel && $smsChannel->isConfigured())
                        <div class="mb-3">
                            <label class="form-label text-muted">Provider</label>
                            <p class="mb-0">
                                <span class="badge bg-primary">Onfon Media</span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Status</label>
                            <p class="mb-0">
                                @if($smsChannel->active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('settings.index', ['channel' => 'sms']) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Edit Configuration
                            </a>
                            <button type="button" class="btn btn-outline-primary" onclick="testConnection()">
                                <i class="bi bi-activity"></i> Test Connection
                            </button>
                        </div>
                    @else
                        <p class="text-muted">SMS is not configured yet. Configure your Onfon Media credentials to start sending SMS messages.</p>
                        <a href="{{ route('settings.index', ['channel' => 'sms']) }}" class="btn btn-success">
                            <i class="bi bi-gear"></i> Configure SMS
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-bar-chart"></i> Statistics
                    </h5>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="mb-2">
                                <h3 class="mb-0 text-primary">{{ number_format($stats['total']) }}</h3>
                                <small class="text-muted">Total SMS</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="mb-2">
                                <h3 class="mb-0 text-success">{{ number_format($stats['sent']) }}</h3>
                                <small class="text-muted">Sent</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="mb-2">
                                <h3 class="mb-0 text-danger">{{ number_format($stats['failed']) }}</h3>
                                <small class="text-muted">Failed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-lightning"></i> Quick Actions
                    </h5>
                    
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary text-start" data-bs-toggle="modal" data-bs-target="#sendMessageModal" {{ $smsChannel && $smsChannel->isConfigured() ? '' : 'disabled' }}>
                            <i class="bi bi-send"></i> Send Test SMS
                        </button>
                        <a href="{{ route('messages.index', ['channel' => 'sms']) }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-envelope"></i> View SMS Messages
                        </a>
                        <a href="{{ route('templates.index', ['channel' => 'sms']) }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-file-text"></i> Manage Templates
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates Section -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-file-text"></i> SMS Templates</h5>
        </div>
        <div class="card-body">
            @if($templates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Content</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                                <tr>
                                    <td><strong>{{ $template->name }}</strong></td>
                                    <td>{{ Str::limit($template->content, 50) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $template->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($template->status ?? 'draft') }}</span>
                                    </td>
                                    <td>{{ $template->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="useTemplate({{ $template->id }}, '{{ $template->name }}')">
                                            <i class="bi bi-send"></i> Use
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-file-text" style="font-size: 3rem; color: #ddd;"></i>
                    <p class="text-muted mt-3">No SMS templates found. Create templates to streamline your messaging.</p>
                    <a href="{{ route('templates.create', ['channel' => 'sms']) }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Template
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Send Message Modal -->
<div class="modal fade" id="sendMessageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-send"></i> Send SMS Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="sendMessageForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Recipient Phone Number</label>
                        <select class="form-select" id="recipientPhone" name="recipient" required>
                            <option value="">-- Select a contact or enter manually --</option>
                            <optgroup label="Your Contacts ({{ $contacts->count() }})">
                                @foreach($contacts as $contact)
                                    <option value="{{ $contact->contact }}">
                                        {{ $contact->name }} - {{ $contact->contact }}
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Enter Manually">
                                <option value="manual">Type phone number manually...</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="manualPhoneDiv">
                        <label class="form-label">Enter Phone Number</label>
                        <input type="text" class="form-control" id="manualPhoneInput" placeholder="254712345678">
                        <small class="text-muted">Include country code (e.g., 254 for Kenya)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="message" rows="4" maxlength="1600" required></textarea>
                        <small class="text-muted">Maximum 1600 characters</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Send SMS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Test connection
function testConnection() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Testing...';
    btn.disabled = true;

    fetch('{{ route("sms.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const balance = data.data?.balance || 'N/A';
            alert('✅ Connection successful!\n\nProvider: ' + (data.data?.provider || 'Onfon Media') + '\nBalance: ' + balance);
        } else {
            alert('❌ Connection failed: ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Connection failed: ' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Handle manual phone input
document.getElementById('recipientPhone')?.addEventListener('change', function() {
    if (this.value === 'manual') {
        document.getElementById('manualPhoneDiv').classList.remove('d-none');
    } else {
        document.getElementById('manualPhoneDiv').classList.add('d-none');
    }
});

// Send message
document.getElementById('sendMessageForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('[type="submit"]');
    const originalText = btn.innerHTML;
    
    let recipient = document.getElementById('recipientPhone').value;
    if (recipient === 'manual') {
        recipient = document.getElementById('manualPhoneInput').value;
        if (!recipient) {
            alert('Please enter a phone number');
            return;
        }
    } else if (!recipient) {
        alert('Please select a contact or enter a phone number');
        return;
    }
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';
    btn.disabled = true;

    const payload = {
        recipient: recipient,
        message: formData.get('message')
    };

    fetch('{{ route("sms.send") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('✅ SMS sent successfully!');
            this.reset();
            bootstrap.Modal.getInstance(document.getElementById('sendMessageModal')).hide();
        } else {
            alert('❌ Failed to send: ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});

// Use template
function useTemplate(templateId, templateName) {
    alert('Template: ' + templateName + '\n\nThis will open the send message form with the template pre-filled.');
    // You can implement template loading logic here
}

// Reset form when modal is closed
$('#sendMessageModal').on('hidden.bs.modal', function() {
    document.getElementById('sendMessageForm').reset();
    document.getElementById('recipientPhone').value = '';
    document.getElementById('manualPhoneDiv').classList.add('d-none');
});
</script>
@endpush
@endsection

