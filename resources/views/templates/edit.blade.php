@extends('layouts.app')

@section('content')
@include('components.modern-page-styles')

<div class="modern-page-container">
    <!-- Page Header -->
    <div class="modern-page-header">
        <div class="page-header-content">
            <div class="page-title-wrapper">
                <div class="page-icon-wrapper">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <div>
                    <h1 class="page-main-title">Edit Template</h1>
                    <p class="page-subtitle">Update template details and content</p>
                </div>
            </div>
            <a href="{{ route('templates.index') }}" class="btn-secondary-modern">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Templates</span>
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="modern-card">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-info-circle me-2"></i>Template Details
            </h3>
        </div>
        <div class="modern-card-body">
            <form action="{{ route('templates.update', $template->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="modern-form-group">
                            <label for="name" class="modern-label">
                                Template Name <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   class="modern-input @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $template->name) }}" 
                                   placeholder="e.g., Welcome Message"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="modern-form-group">
                            <label for="channel" class="modern-label">
                                Channel <span class="required">*</span>
                            </label>
                            <select class="modern-select @error('channel') is-invalid @enderror" 
                                    id="channel" 
                                    name="channel" 
                                    required>
                                <option value="">Select Channel</option>
                                <option value="sms" {{ old('channel', $template->channel) === 'sms' ? 'selected' : '' }}>📱 SMS</option>
                                <option value="whatsapp" {{ old('channel', $template->channel) === 'whatsapp' ? 'selected' : '' }}>💬 WhatsApp</option>
                                <option value="email" {{ old('channel', $template->channel) === 'email' ? 'selected' : '' }}>✉️ Email</option>
                            </select>
                            @error('channel')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12" id="subject-field" style="display: {{ old('channel', $template->channel) === 'sms' ? 'none' : 'block' }}">
                        <div class="modern-form-group">
                            <label for="subject" class="modern-label">
                                Subject <span class="text-muted">(Email/WhatsApp Only)</span>
                            </label>
                            <input type="text" 
                                   class="modern-input @error('subject') is-invalid @enderror" 
                                   id="subject" 
                                   name="subject" 
                                   value="{{ old('subject', $template->subject) }}"
                                   placeholder="Enter subject line">
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-hint">Subject line for email and WhatsApp messages</small>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="modern-form-group">
                            <label for="body" class="modern-label">
                                Message Body <span class="required">*</span>
                            </label>
                            <textarea class="modern-textarea @error('body') is-invalid @enderror" 
                                      id="body" 
                                      name="body" 
                                      rows="8" 
                                      placeholder="Enter your message here..."
                                      required>{{ old('body', $template->body) }}</textarea>
                            <small class="form-hint">
                                <i class="bi bi-info-circle"></i> 
                                Use variables like @{{ name }}, @{{ phone }}. Variables must exist in contact fields.
                            </small>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('templates.index') }}" class="btn-secondary-modern">
                        <i class="bi bi-x-circle"></i>
                        <span>Cancel</span>
                    </a>
                    <button type="submit" class="btn-primary-modern">
                        <i class="bi bi-check2-circle"></i>
                        <span>Update Template</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="modern-card" style="border-color: #fee2e2;">
        <div class="modern-card-header" style="background: #fef2f2; border-color: #fee2e2;">
            <h3 class="modern-card-title" style="color: #dc2626;">
                <i class="bi bi-exclamation-triangle me-2"></i>Danger Zone
            </h3>
        </div>
        <div class="modern-card-body">
            <p class="text-muted mb-3">Once you delete this template, there is no going back. Please be certain.</p>
            <form action="{{ route('templates.destroy', $template->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger-modern" onclick="confirmDelete(event, 'Are you sure you want to delete this template? This action cannot be undone.')">
                    <i class="bi bi-trash"></i>
                    <span>Delete Template</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('channel').addEventListener('change', function() {
    const subjectField = document.getElementById('subject-field');
    if (this.value === 'sms') {
        subjectField.style.display = 'none';
        document.getElementById('subject').required = false;
    } else if (this.value) {
        subjectField.style.display = 'block';
        document.getElementById('subject').required = false;
    } else {
        subjectField.style.display = 'none';
    }
});
</script>
@endsection
