@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Campaign</h1>
        <a href="{{ route('campaigns.show', $campaign->id) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('campaigns.update', $campaign->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Campaign Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $campaign->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="channel" class="form-label">Channel *</label>
                        <select class="form-select @error('channel') is-invalid @enderror" id="channel" name="channel" required>
                            <option value="sms" {{ old('channel', $campaign->channel ?? 'sms') == 'sms' ? 'selected' : '' }}>
                                <i class="bi bi-chat-dots"></i> SMS
                            </option>
                            <option value="whatsapp" {{ old('channel', $campaign->channel ?? 'sms') == 'whatsapp' ? 'selected' : '' }}>
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </option>
                        </select>
                        @error('channel')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="sender_id" class="form-label">Sender ID <span id="sender-label">(SMS Only)</span></label>
                        <input type="text" class="form-control @error('sender_id') is-invalid @enderror" id="sender_id" name="sender_id" value="{{ old('sender_id', $campaign->sender_id) }}" placeholder="{{ $client->sender_id ?? 'Enter sender ID' }}">
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
                                        {{ old('template_id', $campaign->template_id ?? '') == $template->id ? 'selected' : '' }}>
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
                        <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="8" required>{{ old('message', $campaign->message) }}</textarea>
                        <div class="form-text">Character count: <span id="charCount">{{ strlen($campaign->message) }}</span></div>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> Recipients cannot be edited after campaign creation. 
                            <a href="{{ route('campaigns.show', $campaign->id) }}" class="alert-link">View current recipients</a>
                        </div>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Update Campaign</button>
                        <a href="{{ route('campaigns.show', $campaign->id) }}" class="btn btn-secondary">Cancel</a>
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
    const defaultSender = senderInput.placeholder || '{{ $client->sender_id ?? '' }}';
    
    if (this.value === 'whatsapp') {
        senderInput.value = '';
        senderInput.removeAttribute('required');
        senderLabel.textContent = '(WhatsApp - Not Required)';
    } else {
        if (!senderInput.value) {
            senderInput.value = defaultSender;
        }
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
            option.style.display = 'block';
        } else {
            const templateChannel = option.dataset.channel;
            option.style.display = (templateChannel === channel) ? 'block' : 'none';
        }
    });
    
    const currentOption = templateSelect.options[templateSelect.selectedIndex];
    if (currentOption.value && currentOption.dataset.channel !== channel) {
        templateSelect.value = '';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const channel = document.getElementById('channel').value;
    filterTemplatesByChannel(channel);
    
    // Update sender label based on current channel
    if (channel === 'whatsapp') {
        document.getElementById('sender-label').textContent = '(WhatsApp - Not Required)';
    }
});
</script>
@endsection
