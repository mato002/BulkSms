@extends('layouts.app')

@section('content')
<div class="campaign-create-container">
    <!-- Page Header -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-wrapper">
                <div class="page-icon-wrapper">
                    <i class="bi bi-megaphone"></i>
                </div>
                <div>
                    <h1 class="page-main-title">Create New Campaign</h1>
                    <p class="page-subtitle">Design and send your message to multiple recipients</p>
                </div>
            </div>
            <a href="{{ route('campaigns.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
    </div>

    <form action="{{ route('campaigns.store') }}" method="POST">
        @csrf
        
        <div class="campaign-form-grid">
            <!-- Left Column - Main Details -->
            <div class="form-section">
                <!-- Campaign Details Card -->
                <div class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-icon">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <h3 class="form-card-title">Campaign Details</h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-group">
                            <label for="name" class="form-label-modern">Campaign Name <span class="required">*</span></label>
                            <input type="text" 
                                   class="form-control-modern @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="Enter campaign name..."
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="channel" class="form-label-modern">Channel <span class="required">*</span></label>
                                <div class="select-wrapper">
                                    <select class="form-control-modern @error('channel') is-invalid @enderror" id="channel" name="channel" required>
                                        <option value="sms" {{ old('channel', 'sms') == 'sms' ? 'selected' : '' }}>
                                            📱 SMS
                                        </option>
                                        <option value="whatsapp" {{ old('channel') == 'whatsapp' ? 'selected' : '' }}>
                                            💬 WhatsApp
                                        </option>
                                    </select>
                                    <i class="bi bi-chevron-down select-arrow"></i>
                                </div>
                                @error('channel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="sender_id" class="form-label-modern">Sender ID <span id="sender-label" class="text-muted">(SMS Only)</span></label>
                                <div class="select-wrapper">
                                    <select class="form-control-modern @error('sender_id') is-invalid @enderror" id="sender_id" name="sender_id">
                                        <option value="">-- Select Sender --</option>
                                        @foreach($senders as $sender)
                                            <option value="{{ $sender->sender_id }}" 
                                                    {{ old('sender_id', $client->sender_id ?? '') == $sender->sender_id ? 'selected' : '' }}>
                                                {{ $sender->name }} - {{ $sender->sender_id }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="bi bi-chevron-down select-arrow"></i>
                                </div>
                                <small class="form-hint">
                                    @if($client->sender_id ?? false)
                                        Default: <strong>{{ $client->sender_id }}</strong>
                                    @else
                                        For WhatsApp: Leave blank
                                    @endif
                                </small>
                                @error('sender_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="template_id" class="form-label-modern">Message Template <span class="optional-badge">Optional</span></label>
                            <div class="select-wrapper">
                                <select class="form-control-modern @error('template_id') is-invalid @enderror" id="template_id" name="template_id">
                                    <option value="">-- Create Custom Message --</option>
                                    @foreach(\App\Models\Template::where('client_id', auth()->user()->client_id)->get() as $template)
                                        <option value="{{ $template->id }}" 
                                                data-channel="{{ $template->channel }}"
                                                data-content="{{ $template->content }}"
                                                {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                            {{ $template->name }} ({{ ucfirst($template->channel) }})
                                        </option>
                                    @endforeach
                                </select>
                                <i class="bi bi-chevron-down select-arrow"></i>
                            </div>
                            @error('template_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Message Card -->
                <div class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-icon">
                            <i class="bi bi-chat-text"></i>
                        </div>
                        <h3 class="form-card-title">Message Content</h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-group">
                            <label for="message" class="form-label-modern">Your Message <span class="required">*</span></label>
                            <div class="textarea-wrapper">
                                <textarea class="form-control-modern textarea-modern @error('message') is-invalid @enderror" 
                                          id="message" 
                                          name="message" 
                                          rows="6" 
                                          placeholder="Type your message here..."
                                          required>{{ old('message') }}</textarea>
                                <div class="char-counter">
                                    <span id="charCount">0</span> characters
                                </div>
                            </div>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Recipients -->
            <div class="form-section">
                <div class="form-card recipients-card">
                    <div class="form-card-header">
                        <div class="form-card-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h3 class="form-card-title">Recipients <span class="required">*</span></h3>
                    </div>
                    <div class="form-card-body">
                        <!-- Tab Navigation -->
                        <div class="custom-tabs" id="recipientTabs" role="tablist">
                            <button class="custom-tab active" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts-panel" type="button" role="tab">
                                <i class="bi bi-people-fill"></i>
                                <span>Select Contacts</span>
                                <span class="tab-badge">{{ count($contacts) }}</span>
                            </button>
                            <button class="custom-tab" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual-panel" type="button" role="tab">
                                <i class="bi bi-pencil-square"></i>
                                <span>Manual Entry</span>
                            </button>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content" id="recipientTabsContent">
                            <!-- Select from Contacts Tab -->
                            <div class="tab-pane fade show active" id="contacts-panel" role="tabpanel">
                                @if(count($contacts) > 0)
                                    <!-- Filters -->
                                    <div class="filters-section">
                                        <div class="search-box">
                                            <i class="bi bi-search"></i>
                                            <input type="text" id="contactSearch" placeholder="Search contacts...">
                                        </div>
                                        <div class="filter-select">
                                            <select id="departmentFilter">
                                                <option value="">All Departments</option>
                                                @foreach($departments as $dept)
                                                    <option value="{{ $dept }}">{{ $dept }}</option>
                                                @endforeach
                                            </select>
                                            <i class="bi bi-chevron-down select-arrow"></i>
                                        </div>
                                    </div>

                                    <!-- Selection Info -->
                                    <div class="selection-info">
                                        <div class="selection-badge">
                                            <i class="bi bi-check-circle"></i>
                                            <span id="selectedCount">0</span> selected
                                        </div>
                                        <button type="button" id="selectAll" class="btn-select-all">
                                            <i class="bi bi-check-all"></i>
                                            Select All
                                        </button>
                                    </div>

                                    <!-- Contacts List -->
                                    <div id="contactsList" class="contacts-list">
                                        @foreach($contacts as $contact)
                                            <label class="contact-item" data-name="{{ strtolower($contact->name) }}" data-contact="{{ strtolower($contact->contact) }}" data-department="{{ strtolower($contact->department ?? '') }}">
                                                <input class="contact-checkbox" type="checkbox" value="{{ $contact->contact }}" id="contact-{{ $contact->id }}">
                                                <div class="contact-checkbox-custom">
                                                    <i class="bi bi-check"></i>
                                                </div>
                                                <div class="contact-info">
                                                    <div class="contact-name">{{ $contact->name }}</div>
                                                    <div class="contact-phone">{{ $contact->contact }}</div>
                                                </div>
                                                @if($contact->department)
                                                    <span class="contact-badge">{{ $contact->department }}</span>
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <h4>No Contacts Available</h4>
                                        <p>Add contacts first or use manual entry below</p>
                                        <a href="{{ route('contacts.create') }}" class="btn-primary-small">
                                            <i class="bi bi-plus-circle"></i>
                                            Add Contacts
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <!-- Manual Entry Tab -->
                            <div class="tab-pane fade" id="manual-panel" role="tabpanel">
                                <div class="manual-entry-section">
                                    <div class="textarea-wrapper">
                                        <textarea class="form-control-modern textarea-modern" 
                                                  id="manualRecipients" 
                                                  rows="12" 
                                                  placeholder="+254712345678, +254723456789, +254734567890...">{{ old('recipients') }}</textarea>
                                    </div>
                                    <div class="info-box">
                                        <i class="bi bi-lightbulb"></i>
                                        <div>
                                            <strong>Format:</strong> Enter phone numbers with country code (e.g., <code>+254712345678</code>), separated by commas
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden input for form submission -->
                        <input type="hidden" id="recipients" name="recipients" required>
                        @error('recipients')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="form-actions">
            <a href="{{ route('campaigns.index') }}" class="btn-secondary-modern">
                <i class="bi bi-x-lg"></i>
                Cancel
            </a>
            <button type="submit" class="btn-primary-modern">
                <i class="bi bi-rocket"></i>
                Create Campaign
            </button>
        </div>
    </form>
</div>

<style>
/* Campaign Create - Professional Styling */
.campaign-create-container {
    padding: 1.5rem;
    max-width: 1400px;
    margin: 0 auto;
}

/* Page Header */
.page-header-section {
    margin-bottom: 2rem;
}

.page-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.page-title-wrapper {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.page-icon-wrapper {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.page-main-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.page-subtitle {
    color: #64748b;
    margin: 0.25rem 0 0;
    font-size: 0.875rem;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    color: #64748b;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-back:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #475569;
    transform: translateX(-2px);
}

/* Form Grid */
.campaign-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

/* Form Cards */
.form-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    margin-bottom: 1.25rem;
    transition: all 0.2s;
}

.form-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.form-card-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.form-card-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.form-card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.form-card-body {
    padding: 1.25rem;
}

/* Form Elements */
.form-group {
    margin-bottom: 1.25rem;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-label-modern {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.5rem;
}

.required {
    color: #ef4444;
    font-weight: 700;
}

.optional-badge {
    font-size: 0.75rem;
    font-weight: 500;
    color: #64748b;
    background: #f1f5f9;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    margin-left: 0.5rem;
}

.form-control-modern {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: white;
    transition: all 0.2s;
    color: #1e293b;
}

.form-control-modern:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-control-modern::placeholder {
    color: #94a3b8;
}

.select-wrapper {
    position: relative;
}

.select-wrapper select {
    appearance: none;
    padding-right: 2.5rem;
}

.select-arrow {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
    font-size: 0.875rem;
}

.form-hint {
    display: block;
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.375rem;
}

.textarea-wrapper {
    position: relative;
}

.textarea-modern {
    resize: vertical;
    min-height: 120px;
    line-height: 1.6;
}

.char-counter {
    position: absolute;
    bottom: 0.75rem;
    right: 1rem;
    font-size: 0.75rem;
    color: #94a3b8;
    background: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

/* Custom Tabs */
.custom-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    background: #f1f5f9;
    padding: 0.375rem;
    border-radius: 10px;
}

.custom-tab {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: transparent;
    border: none;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
}

.custom-tab:hover {
    background: rgba(255, 255, 255, 0.5);
    color: #475569;
}

.custom-tab.active {
    background: white;
    color: #667eea;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.tab-badge {
    background: #e2e8f0;
    color: #64748b;
    padding: 0.125rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.custom-tab.active .tab-badge {
    background: #e0e7ff;
    color: #667eea;
}

/* Filters */
.filters-section {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.search-box {
    position: relative;
}

.search-box i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.9rem;
}

.search-box input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filter-select {
    position: relative;
    min-width: 200px;
}

.filter-select select {
    width: 100%;
    padding: 0.75rem 2.5rem 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.875rem;
    appearance: none;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-select select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Selection Info */
.selection-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding: 0.75rem 1rem;
    background: #f0f9ff;
    border-radius: 10px;
}

.selection-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #0284c7;
    font-size: 0.875rem;
    font-weight: 500;
}

.selection-badge i {
    font-size: 1rem;
}

.btn-select-all {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 0.875rem;
    background: white;
    border: 1px solid #0ea5e9;
    border-radius: 8px;
    color: #0284c7;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-select-all:hover {
    background: #0ea5e9;
    color: white;
}

/* Contacts List */
.contacts-list {
    max-height: 400px;
    overflow-y: auto;
    padding-right: 0.5rem;
}

.contacts-list::-webkit-scrollbar {
    width: 6px;
}

.contacts-list::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

.contacts-list::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.contacts-list::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.875rem 1rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
}

.contact-item:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.contact-item:has(.contact-checkbox:checked) {
    background: #f0f9ff;
    border-color: #0ea5e9;
}

.contact-item .contact-checkbox {
    display: none;
}

.contact-checkbox-custom {
    width: 20px;
    height: 20px;
    border: 2px solid #cbd5e1;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s;
}

.contact-checkbox-custom i {
    color: white;
    font-size: 0.75rem;
    opacity: 0;
    transform: scale(0);
    transition: all 0.2s;
}

.contact-checkbox:checked + .contact-checkbox-custom {
    background: #0ea5e9;
    border-color: #0ea5e9;
}

.contact-checkbox:checked + .contact-checkbox-custom i {
    opacity: 1;
    transform: scale(1);
}

.contact-info {
    flex: 1;
    min-width: 0;
}

.contact-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.125rem;
}

.contact-phone {
    font-size: 0.8rem;
    color: #64748b;
}

.contact-badge {
    padding: 0.25rem 0.625rem;
    background: #f1f5f9;
    border-radius: 6px;
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 500;
    flex-shrink: 0;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
}

.empty-state i {
    font-size: 3rem;
    color: #cbd5e1;
    margin-bottom: 1rem;
}

.empty-state h4 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #64748b;
    margin-bottom: 1.5rem;
}

/* Manual Entry */
.manual-entry-section {
    margin-top: 1rem;
}

.info-box {
    display: flex;
    gap: 0.75rem;
    padding: 1rem;
    background: #fef3c7;
    border-radius: 10px;
    margin-top: 1rem;
    font-size: 0.85rem;
    color: #92400e;
}

.info-box i {
    font-size: 1.25rem;
    flex-shrink: 0;
    color: #f59e0b;
}

.info-box code {
    background: #fbbf24;
    padding: 0.125rem 0.375rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 0.8rem;
}

/* Action Buttons */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.5rem;
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
}

.btn-secondary-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.5rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #64748b;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary-modern:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #475569;
}

.btn-primary-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-primary-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.btn-primary-small {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 0.85rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary-small:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

/* Responsive */
@media (max-width: 992px) {
    .campaign-form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .campaign-create-container {
        padding: 1rem;
    }
    
    .page-header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .btn-back {
        width: 100%;
        justify-content: center;
    }
    
    .page-icon-wrapper {
        width: 48px;
        height: 48px;
        font-size: 1.25rem;
    }
    
    .page-main-title {
        font-size: 1.5rem;
    }
    
    .filters-section {
        grid-template-columns: 1fr;
    }
    
    .filter-select {
        min-width: auto;
    }
    
    .form-actions {
        flex-direction: column-reverse;
    }
    
    .btn-secondary-modern,
    .btn-primary-modern {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .page-title-wrapper {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .custom-tab span:not(.tab-badge) {
        display: none;
    }
    
    .custom-tab {
        padding: 0.75rem 0.5rem;
    }
}
</style>

<script>
// Character counter
document.getElementById('message').addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length;
});

// Channel selection handler
document.getElementById('channel').addEventListener('change', function() {
    const senderSelect = document.getElementById('sender_id');
    const senderLabel = document.getElementById('sender-label');
    const templateSelect = document.getElementById('template_id');
    const defaultSender = '{{ $client->sender_id ?? '' }}';
    
    if (this.value === 'whatsapp') {
        senderSelect.value = '';
        senderSelect.removeAttribute('required');
        senderSelect.disabled = true;
        senderLabel.textContent = '(WhatsApp - Not Required)';
    } else {
        senderSelect.disabled = false;
        senderSelect.setAttribute('required', 'required');
        senderLabel.textContent = '(SMS Only)';
        // Select default sender if available
        if (defaultSender && !senderSelect.value) {
            senderSelect.value = defaultSender;
        }
    }
    
    // Filter templates by channel
    filterTemplatesByChannel(this.value);
});

// Template selection handler
document.getElementById('template_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const messageField = document.getElementById('message');
    const charCountField = document.getElementById('charCount');
    
    if (selectedOption.value && selectedOption.dataset.content) {
        // Populate message with template content
        messageField.value = selectedOption.dataset.content;
        charCountField.textContent = selectedOption.dataset.content.length;
        
        // Also update channel if template has one
        const templateChannel = selectedOption.dataset.channel;
        if (templateChannel) {
            const channelSelect = document.getElementById('channel');
            channelSelect.value = templateChannel;
            // Trigger change event to update sender field
            channelSelect.dispatchEvent(new Event('change'));
        }
    } else {
        // Clear message if "Create Custom Message" is selected
        messageField.value = '';
        charCountField.textContent = '0';
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
    const activeTab = document.querySelector('#recipientTabs .custom-tab.active').id;
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
        
        item.style.display = (matchesSearch && matchesDept) ? 'flex' : 'none';
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
    tab.addEventListener('shown.bs.tab', function() {
        // Remove active class from all tabs
        document.querySelectorAll('.custom-tab').forEach(t => t.classList.remove('active'));
        // Add active class to clicked tab
        this.classList.add('active');
        updateRecipients();
    });
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
