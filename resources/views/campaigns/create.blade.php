@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-megaphone me-2"></i>Create Campaign</h1>
        <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('campaigns.store') }}" method="POST">
                @csrf
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Campaign Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="channel" class="form-label">Channel *</label>
                        <select class="form-select @error('channel') is-invalid @enderror" id="channel" name="channel" required>
                            <option value="sms" {{ old('channel', 'sms') == 'sms' ? 'selected' : '' }}>
                                <i class="bi bi-chat-dots"></i> SMS
                            </option>
                            <option value="whatsapp" {{ old('channel') == 'whatsapp' ? 'selected' : '' }}>
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </option>
                        </select>
                        @error('channel')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="sender_id" class="form-label">Sender ID <span id="sender-label">(SMS Only)</span></label>
                        <input type="text" class="form-control @error('sender_id') is-invalid @enderror" id="sender_id" name="sender_id" value="{{ old('sender_id', $client->sender_id ?? '') }}" placeholder="{{ $client->sender_id ?? 'Enter sender ID' }}">
                        <div class="form-text">
                            @if($client->sender_id ?? false)
                                Your default sender ID: <strong>{{ $client->sender_id }}</strong>
                            @else
                                For SMS: Your sender name. For WhatsApp: Leave blank
                            @endif
                        </div>
                        @error('sender_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="template_id" class="form-label">Template (Optional)</label>
                        <select class="form-select @error('template_id') is-invalid @enderror" id="template_id" name="template_id">
                            <option value="">-- None (Custom Message) --</option>
                            @foreach(\App\Models\Template::where('client_id', auth()->user()->client_id)->get() as $template)
                                <option value="{{ $template->id }}" 
                                        data-channel="{{ $template->channel }}"
                                        data-content="{{ $template->content }}"
                                        {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                    {{ $template->name }} ({{ ucfirst($template->channel) }})
                                </option>
                            @endforeach
                        </select>
                        @error('template_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="message" class="form-label">Message *</label>
                        <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="8" required>{{ old('message') }}</textarea>
                        <div class="form-text">Character count: <span id="charCount">0</span></div>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Recipients *</label>
                        
                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs mb-3" id="recipientTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts-panel" type="button" role="tab">
                                    <i class="bi bi-people me-1"></i> Select from Contacts ({{ count($contacts) }})
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual-panel" type="button" role="tab">
                                    <i class="bi bi-pencil me-1"></i> Manual Entry
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="recipientTabsContent">
                            <!-- Select from Contacts Tab -->
                            <div class="tab-pane fade show active" id="contacts-panel" role="tabpanel">
                                @if(count($contacts) > 0)
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <input type="text" id="contactSearch" class="form-control" placeholder="Search by name or phone...">
                                                </div>
                                                <div class="col-md-4">
                                                    <select id="departmentFilter" class="form-select">
                                                        <option value="">All Departments</option>
                                                        @foreach($departments as $dept)
                                                            <option value="{{ $dept }}">{{ $dept }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" id="selectAll" class="btn btn-outline-primary w-100">
                                                        <i class="bi bi-check-all"></i> Select All
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="alert alert-info py-2">
                                                <i class="bi bi-info-circle me-1"></i> 
                                                <span id="selectedCount">0</span> contact(s) selected
                                            </div>

                                            <div id="contactsList" style="max-height: 400px; overflow-y: auto;">
                                                @foreach($contacts as $contact)
                                                    <div class="form-check contact-item" data-name="{{ strtolower($contact->name) }}" data-contact="{{ strtolower($contact->contact) }}" data-department="{{ strtolower($contact->department ?? '') }}">
                                                        <input class="form-check-input contact-checkbox" type="checkbox" value="{{ $contact->contact }}" id="contact-{{ $contact->id }}">
                                                        <label class="form-check-label w-100" for="contact-{{ $contact->id }}">
                                                            <div class="d-flex justify-content-between">
                                                                <span>
                                                                    <strong>{{ $contact->name }}</strong>
                                                                    <small class="text-muted ms-2">{{ $contact->contact }}</small>
                                                                </span>
                                                                @if($contact->department)
                                                                    <span class="badge bg-secondary">{{ $contact->department }}</span>
                                                                @endif
                                                            </div>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        No contacts available. <a href="{{ route('contacts.create') }}" class="alert-link">Add contacts first</a> or use manual entry.
                                    </div>
                                @endif
                            </div>

                            <!-- Manual Entry Tab -->
                            <div class="tab-pane fade" id="manual-panel" role="tabpanel">
                                <textarea class="form-control" id="manualRecipients" rows="8" placeholder="+254712345678, +254723456789, ...">{{ old('recipients') }}</textarea>
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i> Enter phone numbers with country code (e.g., <strong>+254712345678</strong>), separated by commas
                                </div>
                            </div>
                        </div>

                        <!-- Hidden input for form submission -->
                        <input type="hidden" id="recipients" name="recipients" required>
                        @error('recipients')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Create Campaign</button>
                        <a href="{{ route('campaigns.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Character counter
document.getElementById('message').addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length;
});

// Channel selection handler
document.getElementById('channel').addEventListener('change', function() {
    const senderInput = document.getElementById('sender_id');
    const senderLabel = document.getElementById('sender-label');
    const templateSelect = document.getElementById('template_id');
    const defaultSender = senderInput.placeholder || '{{ $client->sender_id ?? '' }}';
    
    if (this.value === 'whatsapp') {
        senderInput.value = '';
        senderInput.removeAttribute('required');
        senderLabel.textContent = '(WhatsApp - Not Required)';
    } else {
        senderInput.value = defaultSender;
        senderInput.setAttribute('required', 'required');
        senderLabel.textContent = '(SMS Only) *';
    }
    
    // Filter templates by channel
    filterTemplatesByChannel(this.value);
});

// Template selection handler
document.getElementById('template_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value && selectedOption.dataset.content) {
        document.getElementById('message').value = selectedOption.dataset.content;
        document.getElementById('charCount').textContent = selectedOption.dataset.content.length;
    }
});

// Filter templates based on selected channel
function filterTemplatesByChannel(channel) {
    const templateSelect = document.getElementById('template_id');
    const options = templateSelect.querySelectorAll('option');
    
    options.forEach(option => {
        if (option.value === '') {
            option.style.display = 'block'; // Always show "None" option
        } else {
            const templateChannel = option.dataset.channel;
            option.style.display = (templateChannel === channel) ? 'block' : 'none';
        }
    });
    
    // Reset selection if current template doesn't match channel
    const currentOption = templateSelect.options[templateSelect.selectedIndex];
    if (currentOption.value && currentOption.dataset.channel !== channel) {
        templateSelect.value = '';
    }
}

// ===== RECIPIENT SELECTION =====

// Update selected count and hidden input
function updateRecipients() {
    const activeTab = document.querySelector('#recipientTabs .nav-link.active').id;
    const hiddenInput = document.getElementById('recipients');
    
    if (activeTab === 'contacts-tab') {
        // Get selected contacts
        const selectedContacts = Array.from(document.querySelectorAll('.contact-checkbox:checked'))
            .map(cb => cb.value);
        hiddenInput.value = selectedContacts.join(', ');
        document.getElementById('selectedCount').textContent = selectedContacts.length;
    } else {
        // Get manual entry
        const manualValue = document.getElementById('manualRecipients').value.trim();
        hiddenInput.value = manualValue;
    }
}

// Contact search functionality
const contactSearch = document.getElementById('contactSearch');
if (contactSearch) {
    contactSearch.addEventListener('input', function() {
        filterContacts();
    });
}

// Department filter
const departmentFilter = document.getElementById('departmentFilter');
if (departmentFilter) {
    departmentFilter.addEventListener('change', function() {
        filterContacts();
    });
}

// Filter contacts based on search and department
function filterContacts() {
    const searchTerm = (contactSearch?.value || '').toLowerCase();
    const selectedDept = (departmentFilter?.value || '').toLowerCase();
    const contactItems = document.querySelectorAll('.contact-item');
    
    contactItems.forEach(item => {
        const name = item.dataset.name || '';
        const contact = item.dataset.contact || '';
        const dept = item.dataset.department || '';
        
        const matchesSearch = !searchTerm || name.includes(searchTerm) || contact.includes(searchTerm);
        const matchesDept = !selectedDept || dept === selectedDept;
        
        item.style.display = (matchesSearch && matchesDept) ? 'block' : 'none';
    });
}

// Select all visible contacts
const selectAllBtn = document.getElementById('selectAll');
if (selectAllBtn) {
    selectAllBtn.addEventListener('click', function() {
        const visibleCheckboxes = Array.from(document.querySelectorAll('.contact-item'))
            .filter(item => item.style.display !== 'none')
            .map(item => item.querySelector('.contact-checkbox'));
        
        const allChecked = visibleCheckboxes.every(cb => cb.checked);
        
        visibleCheckboxes.forEach(cb => {
            cb.checked = !allChecked;
        });
        
        this.innerHTML = allChecked 
            ? '<i class="bi bi-check-all"></i> Select All'
            : '<i class="bi bi-x-lg"></i> Deselect All';
        
        updateRecipients();
    });
}

// Listen to checkbox changes
document.querySelectorAll('.contact-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateRecipients);
});

// Listen to manual input changes
const manualRecipients = document.getElementById('manualRecipients');
if (manualRecipients) {
    manualRecipients.addEventListener('input', updateRecipients);
}

// Listen to tab changes
document.querySelectorAll('#recipientTabs button').forEach(tab => {
    tab.addEventListener('shown.bs.tab', updateRecipients);
});

// Form validation before submit
document.querySelector('form').addEventListener('submit', function(e) {
    const recipients = document.getElementById('recipients').value.trim();
    if (!recipients) {
        e.preventDefault();
        alert('Please select at least one recipient or enter phone numbers manually.');
        return false;
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const channel = document.getElementById('channel').value;
    filterTemplatesByChannel(channel);
    updateRecipients();
});
</script>
@endsection

