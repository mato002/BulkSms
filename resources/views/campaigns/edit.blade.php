@extends('layouts.app')

@section('content')
@include('components.modern-page-styles')

<div class="modern-page-container">
    <div class="modern-page-header">
        <div class="page-header-content">
            <div class="page-title-wrapper">
                <div class="page-icon-wrapper">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <div>
                    <h1 class="page-main-title">Edit Campaign</h1>
                    <p class="page-subtitle">Update campaign details and message</p>
                </div>
            </div>
            <a href="{{ route('campaigns.show', $campaign->id) }}" class="btn-secondary-modern">
                <i class="bi bi-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="modern-card">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-info-circle me-2"></i>Campaign Details
            </h3>
        </div>
        <div class="modern-card-body">
            <form action="{{ route('campaigns.update', $campaign->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="modern-form-group">
                            <label for="name" class="modern-label">Campaign Name <span class="required">*</span></label>
                            <input type="text" class="modern-input @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $campaign->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="modern-form-group">
                            <label for="channel" class="modern-label">Channel <span class="required">*</span></label>
                            <select class="modern-select @error('channel') is-invalid @enderror" id="channel" name="channel" required>
                                <option value="sms" {{ old('channel', $campaign->channel ?? 'sms') == 'sms' ? 'selected' : '' }}>📱 SMS</option>
                                <option value="whatsapp" {{ old('channel', $campaign->channel ?? 'sms') == 'whatsapp' ? 'selected' : '' }}>💬 WhatsApp</option>
                            </select>
                            @error('channel')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="modern-form-group">
                            <label for="sender_id" class="modern-label">Sender ID <span id="sender-label">(SMS Only)</span></label>
                            <select class="modern-select @error('sender_id') is-invalid @enderror" id="sender_id" name="sender_id">
                                <option value="">-- Select Sender --</option>
                                @foreach($senders as $sender)
                                    <option value="{{ $sender->sender_id }}" {{ old('sender_id', $campaign->sender_id) == $sender->sender_id ? 'selected' : '' }}>
                                        {{ $sender->name }} - {{ $sender->sender_id }}
                                    </option>
                                @endforeach
                            </select>
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

                    <div class="col-md-6">
                        <div class="modern-form-group">
                            <label for="template_id" class="modern-label">Template <span class="text-muted">(Optional)</span></label>
                            <select class="modern-select @error('template_id') is-invalid @enderror" id="template_id" name="template_id">
                                <option value="">-- Custom Message --</option>
                                @foreach(\App\Models\Template::where('client_id', auth()->user()->client_id)->get() as $template)
                                    <option value="{{ $template->id }}" 
                                            data-channel="{{ $template->channel }}"
                                            data-content="{{ $template->content }}"
                                            {{ old('template_id', $campaign->template_id ?? '') == $template->id ? 'selected' : '' }}>
                                        {{ $template->name }} ({{ ucfirst($template->channel) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('template_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="modern-form-group">
                            <label for="message" class="modern-label">Message <span class="required">*</span></label>
                            <textarea class="modern-textarea @error('message') is-invalid @enderror" id="message" name="message" rows="8" required>{{ old('message', $campaign->message) }}</textarea>
                            <small class="form-hint">Character count: <span id="charCount">{{ strlen($campaign->message) }}</span></small>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> Recipients cannot be edited after campaign creation. 
                            <a href="{{ route('campaigns.show', $campaign->id) }}" class="alert-link">View current recipients</a>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('campaigns.show', $campaign->id) }}" class="btn-secondary-modern">
                        <i class="bi bi-x-circle"></i>
                        <span>Cancel</span>
                    </a>
                    <button type="submit" class="btn-primary-modern">
                        <i class="bi bi-check2-circle"></i>
                        <span>Update Campaign</span>
                    </button>
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
    const senderSelect = document.getElementById('sender_id');
    const senderLabel = document.getElementById('sender-label');
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
        if (defaultSender && !senderSelect.value) {
            senderSelect.value = defaultSender;
        }
    }
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

// Filter templates
function filterTemplatesByChannel(channel) {
    const templateSelect = document.getElementById('template_id');
    const options = templateSelect.querySelectorAll('option');
    options.forEach(option => {
        if (option.value === '') {
            option.style.display = 'block';
        } else {
            option.style.display = (option.dataset.channel === channel) ? 'block' : 'none';
        }
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    const channel = document.getElementById('channel').value;
    filterTemplatesByChannel(channel);
    if (channel === 'whatsapp') {
        document.getElementById('sender_id').disabled = true;
        document.getElementById('sender-label').textContent = '(WhatsApp - Not Required)';
    }
});
</script>
@endsection
