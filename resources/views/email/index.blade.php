@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-envelope text-info"></i> Email Integration</h2>
            <p class="text-muted mb-0">Manage email messaging via SMTP</p>
        </div>
        <div>
            @if($emailChannel && $emailChannel->isConfigured())
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
                    
                    @if($emailChannel && $emailChannel->isConfigured())
                        @php
                            $creds = is_string($emailChannel->credentials) ? json_decode($emailChannel->credentials, true) : $emailChannel->credentials;
                        @endphp
                        <div class="mb-3">
                            <label class="form-label text-muted">Provider</label>
                            <p class="mb-0">
                                <span class="badge bg-info">SMTP</span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">SMTP Host</label>
                            <p class="mb-0">{{ $creds['host'] ?? 'Not set' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Status</label>
                            <p class="mb-0">
                                @if($emailChannel->active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('settings.index', ['channel' => 'email']) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Edit Configuration
                            </a>
                            <button type="button" class="btn btn-outline-primary" onclick="testConnection()">
                                <i class="bi bi-activity"></i> Test Connection
                            </button>
                        </div>
                    @else
                        <p class="text-muted">Email is not configured yet. Configure your SMTP credentials to start sending emails.</p>
                        <a href="{{ route('settings.index', ['channel' => 'email']) }}" class="btn btn-success">
                            <i class="bi bi-gear"></i> Configure Email
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
                                <h3 class="mb-0 text-info">{{ number_format($stats['total']) }}</h3>
                                <small class="text-muted">Total Emails</small>
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
                        <button type="button" class="btn btn-outline-primary text-start" data-bs-toggle="modal" data-bs-target="#sendMessageModal" {{ $emailChannel && $emailChannel->isConfigured() ? '' : 'disabled' }}>
                            <i class="bi bi-send"></i> Send Test Email
                        </button>
                        <a href="{{ route('messages.index', ['channel' => 'email']) }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-envelope"></i> View Email Messages
                        </a>
                        <a href="{{ route('templates.index', ['channel' => 'email']) }}" class="btn btn-outline-primary text-start">
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
            <h5 class="mb-0"><i class="bi bi-file-text"></i> Email Templates</h5>
        </div>
        <div class="card-body">
            @if($templates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                                <tr>
                                    <td><strong>{{ $template->name }}</strong></td>
                                    <td>{{ $template->subject ?? 'N/A' }}</td>
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
                    <p class="text-muted mt-3">No email templates found. Create templates to streamline your messaging.</p>
                    <a href="{{ route('templates.create', ['channel' => 'email']) }}" class="btn btn-primary">
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
                <h5 class="modal-title"><i class="bi bi-send"></i> Send Email Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="sendMessageForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Recipient Email</label>
                        <select class="form-select" id="recipientEmail" name="recipient" required>
                            <option value="">-- Select a contact or enter manually --</option>
                            <optgroup label="Your Contacts ({{ $contacts->count() }})">
                                @foreach($contacts as $contact)
                                    @if($contact->email)
                                        <option value="{{ $contact->email }}">
                                            {{ $contact->name }} - {{ $contact->email }}
                                        </option>
                                    @endif
                                @endforeach
                            </optgroup>
                            <optgroup label="Enter Manually">
                                <option value="manual">Type email address manually...</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="manualEmailDiv">
                        <label class="form-label">Enter Email Address</label>
                        <input type="email" class="form-control" id="manualEmailInput" placeholder="recipient@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" class="form-control" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="message" rows="6" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Send Email
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

    fetch('{{ route("email.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('✅ Connection successful!\n\nProvider: ' + (data.data?.provider || 'SMTP') + '\nTest email sent.');
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

// Handle manual email input
document.getElementById('recipientEmail')?.addEventListener('change', function() {
    if (this.value === 'manual') {
        document.getElementById('manualEmailDiv').classList.remove('d-none');
    } else {
        document.getElementById('manualEmailDiv').classList.add('d-none');
    }
});

// Send message
document.getElementById('sendMessageForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('[type="submit"]');
    const originalText = btn.innerHTML;
    
    let recipient = document.getElementById('recipientEmail').value;
    if (recipient === 'manual') {
        recipient = document.getElementById('manualEmailInput').value;
        if (!recipient) {
            alert('Please enter an email address');
            return;
        }
    } else if (!recipient) {
        alert('Please select a contact or enter an email address');
        return;
    }
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';
    btn.disabled = true;

    const payload = {
        recipient: recipient,
        subject: formData.get('subject'),
        message: formData.get('message')
    };

    fetch('{{ route("email.send") }}', {
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
            alert('✅ Email sent successfully!');
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
    document.getElementById('recipientEmail').value = '';
    document.getElementById('manualEmailDiv').classList.add('d-none');
});
</script>
@endpush
@endsection

