@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-whatsapp text-success"></i> WhatsApp Integration</h2>
            <p class="text-muted mb-0">Manage WhatsApp Cloud API integration and templates</p>
        </div>
        <div>
            @if($whatsappChannel && $whatsappChannel->isConfigured())
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
                    
                    @if($whatsappChannel && $whatsappChannel->isConfigured())
                        <div class="mb-3">
                            <label class="form-label text-muted">Provider</label>
                            <p class="mb-0">
                                @if($whatsappChannel->provider === 'ultramsg')
                                    <span class="badge bg-success">UltraMsg</span>
                                @else
                                    <span class="badge bg-primary">WhatsApp Cloud API</span>
                                @endif
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">
                                @if($whatsappChannel->provider === 'ultramsg')
                                    Instance ID
                                @else
                                    Phone Number ID
                                @endif
                            </label>
                            <p class="mb-0">
                                @if($whatsappChannel->provider === 'ultramsg')
                                    {{ $whatsappChannel->getCredential('instance_id') }}
                                @else
                                    {{ $whatsappChannel->getCredential('phone_number_id') }}
                                @endif
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Status</label>
                            <p class="mb-0">
                                @if($whatsappChannel->active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('whatsapp.configure') }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Edit Configuration
                            </a>
                            <button type="button" class="btn btn-outline-primary" onclick="testConnection()">
                                <i class="bi bi-activity"></i> Test Connection
                            </button>
                        </div>
                    @else
                        <p class="text-muted">WhatsApp is not configured yet. Set up UltraMsg to start sending WhatsApp messages in 5 minutes!</p>
                        <a href="{{ route('whatsapp.configure') }}" class="btn btn-success">
                            <i class="bi bi-rocket-takeoff"></i> Quick Setup (UltraMsg)
                        </a>
                    @endif
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
                        <button type="button" class="btn btn-outline-primary text-start" data-bs-toggle="modal" data-bs-target="#sendMessageModal" {{ $whatsappChannel && $whatsappChannel->isConfigured() ? '' : 'disabled' }}>
                            <i class="bi bi-send"></i> Send Test Message
                        </button>
                        <button type="button" class="btn btn-outline-primary text-start" data-bs-toggle="modal" data-bs-target="#interactiveMessageModal" {{ $whatsappChannel && $whatsappChannel->isConfigured() ? '' : 'disabled' }}>
                            <i class="bi bi-menu-button"></i> Send Interactive Message
                        </button>
                        <button type="button" class="btn btn-outline-primary text-start" onclick="syncTemplates()" {{ $whatsappChannel && $whatsappChannel->isConfigured() && $whatsappChannel->provider !== 'ultramsg' ? '' : 'disabled' }}>
                            <i class="bi bi-arrow-repeat"></i> Sync Templates from WhatsApp
                            @if($whatsappChannel && $whatsappChannel->provider === 'ultramsg')
                                <small class="d-block text-muted">(Cloud API only)</small>
                            @endif
                        </button>
                        <a href="{{ route('inbox.index') }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-inbox"></i> View WhatsApp Inbox
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates Section -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-file-text"></i> WhatsApp Templates</h5>
        </div>
        <div class="card-body">
            @if($templates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Language</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                                <tr>
                                    <td><strong>{{ $template->name }}</strong></td>
                                    <td>{{ strtoupper($template->language ?? 'en') }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $template->category ?? 'MARKETING' }}</span>
                                    </td>
                                    <td>
                                        @if($template->isWhatsAppApproved())
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-warning">{{ ucfirst($template->status ?? 'draft') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $template->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="useTemplate({{ $template->id }}, '{{ $template->name }}')" {{ $template->isWhatsAppApproved() ? '' : 'disabled' }}>
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
                    <p class="text-muted mt-3">
                        @if($whatsappChannel && $whatsappChannel->provider === 'ultramsg')
                            Templates are managed through UltraMsg dashboard. Template syncing is only available for WhatsApp Cloud API.
                        @else
                            No templates found. Sync templates from WhatsApp Business Manager.
                        @endif
                    </p>
                    @if($whatsappChannel && $whatsappChannel->provider !== 'ultramsg')
                        <button type="button" class="btn btn-primary" onclick="syncTemplates()" {{ $whatsappChannel && $whatsappChannel->isConfigured() ? '' : 'disabled' }}>
                            <i class="bi bi-arrow-repeat"></i> Sync Templates
                        </button>
                    @endif
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
                <h5 class="modal-title"><i class="bi bi-send"></i> Send WhatsApp Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="sendMessageForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Recipient Phone Number</label>
                        <select class="form-select select2-dropdown" id="recipientPhone" name="recipient" required>
                            <option value="">-- Select a contact or enter manually --</option>
                            <optgroup label="Your Contacts ({{ $contacts->count() }})">
                                @foreach($contacts as $contact)
                                    <option value="{{ $contact->contact }}">
                                        {{ $contact->name }} - {{ $contact->contact }} 
                                        @if($contact->department)
                                            ({{ $contact->department }})
                                        @endif
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
                        <input type="text" class="form-control" id="manualPhoneInput" placeholder="+254712345678">
                        <small class="text-muted">Include country code (e.g., +254 for Kenya)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="message" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Interactive Message Modal -->
<div class="modal fade" id="interactiveMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-menu-button"></i> Send Interactive Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="interactiveMessageForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Recipient Phone Number</label>
                        <select class="form-select select2-dropdown" id="interactiveRecipientPhone" name="recipient" required>
                            <option value="">-- Select a contact or enter manually --</option>
                            <optgroup label="Your Contacts ({{ $contacts->count() }})">
                                @foreach($contacts as $contact)
                                    <option value="{{ $contact->contact }}">
                                        {{ $contact->name }} - {{ $contact->contact }} 
                                        @if($contact->department)
                                            ({{ $contact->department }})
                                        @endif
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Enter Manually">
                                <option value="manual">Type phone number manually...</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="interactiveManualPhoneDiv">
                        <label class="form-label">Enter Phone Number</label>
                        <input type="text" class="form-control" id="interactiveManualPhoneInput" placeholder="+254712345678">
                        <small class="text-muted">Include country code (e.g., +254 for Kenya)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message Type</label>
                        <select class="form-select" name="type" id="interactiveType" required>
                            <option value="button">Button Message</option>
                            <option value="list">List Message</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Header (Optional)</label>
                        <input type="text" class="form-control" name="header">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message Body</label>
                        <textarea class="form-control" name="body" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Footer (Optional)</label>
                        <input type="text" class="form-control" name="footer">
                    </div>
                    <div id="buttonsSection">
                        <label class="form-label">Buttons (Max 3)</label>
                        <div id="buttonsContainer">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" placeholder="Button 1 Text" data-button-index="0">
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addButton()">
                            <i class="bi bi-plus"></i> Add Button
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Send Interactive Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Initialize Select2 on dropdowns
$(document).ready(function() {
    // Initialize Select2 for Send Message
    $('#recipientPhone').select2({
        theme: 'bootstrap-5',
        placeholder: '🔍 Search or select a contact...',
        allowClear: true,
        dropdownParent: $('#sendMessageModal'),
        width: '100%'
    });
    
    // Initialize Select2 for Interactive Message
    $('#interactiveRecipientPhone').select2({
        theme: 'bootstrap-5',
        placeholder: '🔍 Search or select a contact...',
        allowClear: true,
        dropdownParent: $('#interactiveMessageModal'),
        width: '100%'
    });
});

// Handle manual phone input toggle for Send Message
$('#recipientPhone').on('change', function() {
    const manualDiv = document.getElementById('manualPhoneDiv');
    if (this.value === 'manual') {
        manualDiv.classList.remove('d-none');
        document.getElementById('manualPhoneInput').focus();
    } else {
        manualDiv.classList.add('d-none');
    }
});

// Handle manual phone input toggle for Interactive Message
$('#interactiveRecipientPhone').on('change', function() {
    const manualDiv = document.getElementById('interactiveManualPhoneDiv');
    if (this.value === 'manual') {
        manualDiv.classList.remove('d-none');
        document.getElementById('interactiveManualPhoneInput').focus();
    } else {
        manualDiv.classList.add('d-none');
    }
});

// Test connection
function testConnection() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Testing...';
    btn.disabled = true;

    fetch('{{ route("whatsapp.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('✅ Connection successful!\n\nPhone Number: ' + (data.data.display_phone_number || 'N/A'));
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

// Send message
document.getElementById('sendMessageForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('[type="submit"]');
    const originalText = btn.innerHTML;
    
    // Get recipient - either from dropdown or manual input
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

    fetch('{{ route("whatsapp.send") }}', {
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
            alert('✅ Message sent successfully!');
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

// Interactive message
let buttonCount = 1;

function addButton() {
    if (buttonCount >= 3) {
        alert('Maximum 3 buttons allowed');
        return;
    }
    buttonCount++;
    const container = document.getElementById('buttonsContainer');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" class="form-control" placeholder="Button ${buttonCount} Text" data-button-index="${buttonCount - 1}">
        <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove(); buttonCount--;">
            <i class="bi bi-trash"></i>
        </button>
    `;
    container.appendChild(div);
}

document.getElementById('interactiveMessageForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    // Get recipient - either from dropdown or manual input
    let recipient = document.getElementById('interactiveRecipientPhone').value;
    if (recipient === 'manual') {
        recipient = document.getElementById('interactiveManualPhoneInput').value;
        if (!recipient) {
            alert('Please enter a phone number');
            return;
        }
    } else if (!recipient) {
        alert('Please select a contact or enter a phone number');
        return;
    }
    
    const buttons = [];
    
    document.querySelectorAll('#buttonsContainer input').forEach((input, index) => {
        if (input.value.trim()) {
            buttons.push({
                type: 'reply',
                reply: {
                    id: 'btn_' + index,
                    title: input.value.trim()
                }
            });
        }
    });

    const payload = {
        recipient: recipient,
        type: formData.get('type'),
        header: formData.get('header'),
        body: formData.get('body'),
        footer: formData.get('footer'),
        buttons: buttons
    };

    const btn = this.querySelector('[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';
    btn.disabled = true;

    fetch('{{ route("whatsapp.send.interactive") }}', {
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
            alert('✅ Interactive message sent successfully!');
            this.reset();
            buttonCount = 1;
            document.getElementById('buttonsContainer').innerHTML = `
                <div class="input-group mb-2">
                    <input type="text" class="form-control" placeholder="Button 1 Text" data-button-index="0">
                </div>
            `;
            bootstrap.Modal.getInstance(document.getElementById('interactiveMessageModal')).hide();
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

// Sync templates
function syncTemplates() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Syncing...';
    btn.disabled = true;

    fetch('{{ route("whatsapp.templates.fetch") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('✅ ' + data.message);
            window.location.reload();
        } else {
            alert('❌ Sync failed: ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Reset forms when modals are closed
$('#sendMessageModal').on('hidden.bs.modal', function() {
    document.getElementById('sendMessageForm').reset();
    $('#recipientPhone').val('').trigger('change');
    document.getElementById('manualPhoneDiv').classList.add('d-none');
});

$('#interactiveMessageModal').on('hidden.bs.modal', function() {
    document.getElementById('interactiveMessageForm').reset();
    $('#interactiveRecipientPhone').val('').trigger('change');
    document.getElementById('interactiveManualPhoneDiv').classList.add('d-none');
    // Reset buttons
    buttonCount = 1;
    document.getElementById('buttonsContainer').innerHTML = `
        <div class="input-group mb-2">
            <input type="text" class="form-control" placeholder="Button 1 Text" data-button-index="0">
        </div>
    `;
});

// Use template
function useTemplate(templateId, templateName) {
    // Build contact list for selection
    const contacts = @json($contacts);
    let contactOptions = 'Select a contact:\n\n';
    contacts.forEach((contact, index) => {
        contactOptions += `${index + 1}. ${contact.name} - ${contact.contact}\n`;
    });
    contactOptions += '\nEnter the number of the contact (or type "manual" to enter phone manually):';
    
    const selection = prompt(contactOptions);
    if (!selection) return;
    
    let recipient;
    if (selection.toLowerCase() === 'manual') {
        recipient = prompt('Enter recipient phone number (with country code):');
        if (!recipient) return;
    } else {
        const contactIndex = parseInt(selection) - 1;
        if (contactIndex >= 0 && contactIndex < contacts.length) {
            recipient = contacts[contactIndex].contact;
        } else {
            alert('Invalid selection');
            return;
        }
    }

    const variables = prompt('Enter template variables (comma-separated, if any):');
    const variablesArray = variables ? variables.split(',').map(v => v.trim()) : [];

    fetch('{{ route("whatsapp.send") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            recipient: recipient,
            message: 'Template message: ' + templateName,
            template_id: templateId,
            variables: variablesArray
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('✅ Template message sent successfully!');
        } else {
            alert('❌ Failed to send: ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
    });
}
</script>
@endpush

