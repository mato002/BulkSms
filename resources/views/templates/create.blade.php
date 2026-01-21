@extends('layouts.app')

@section('content')
@include('components.modern-page-styles')

<div class="modern-page-container">
    <!-- Page Header -->
    <div class="modern-page-header">
        <div class="page-header-content">
            <div class="page-title-wrapper">
                <div class="page-icon-wrapper">
                    <i class="bi bi-file-plus"></i>
                </div>
                <div>
                    <h1 class="page-main-title">Create Template</h1>
                    <p class="page-subtitle">Create a new reusable message template</p>
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
            <form action="{{ route('templates.store') }}" method="POST">
                @csrf
                
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
                                   value="{{ old('name') }}" 
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
                                <option value="sms" {{ old('channel') === 'sms' ? 'selected' : '' }}>📱 SMS</option>
                                <option value="whatsapp" {{ old('channel') === 'whatsapp' ? 'selected' : '' }}>💬 WhatsApp</option>
                                <option value="email" {{ old('channel') === 'email' ? 'selected' : '' }}>✉️ Email</option>
                            </select>
                            @error('channel')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12" id="subject-field" style="display: none;">
                        <div class="modern-form-group">
                            <label for="subject" class="modern-label">
                                Subject <span class="text-muted">(Email/WhatsApp Only)</span>
                            </label>
                            <input type="text" 
                                   class="modern-input @error('subject') is-invalid @enderror" 
                                   id="subject" 
                                   name="subject" 
                                   value="{{ old('subject') }}"
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
                                      required>{{ old('body') }}</textarea>
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
                        <span>Create Template</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sample Templates Card -->
    <div class="modern-card mt-4">
        <div class="modern-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h3 class="modern-card-title mb-0">
                <i class="bi bi-lightbulb me-2"></i>Sample Templates by Channel
            </h3>
            <span class="text-muted small">Select a channel to unlock relevant starter messages</span>
        </div>
        <div class="modern-card-body">
            <div id="sample-templates-list" class="sample-templates-grid">
                <div class="sample-placeholder">
                    <i class="bi bi-arrow-up-circle"></i>
                    <p>Select a channel above to see curated starter templates.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $sampleTemplates = [
        'sms' => [
            [
                'name' => 'otp_code',
                'title' => 'One-Time Password',
                'category' => 'Transactional',
                'subject' => null,
                'body' => 'Your verification code is {{code}}. It expires in 10 minutes.'
            ],
            [
                'name' => 'payment_reminder',
                'title' => 'Payment Reminder',
                'category' => 'Utility',
                'subject' => null,
                'body' => 'Hi {{name}}, your payment of {{amount}} is due on {{due_date}}.'
            ],
            [
                'name' => 'delivery_update',
                'title' => 'Delivery Update',
                'category' => 'Operations',
                'subject' => null,
                'body' => 'Package {{tracking_number}} is out for delivery today. Reply HELP for support.'
            ],
        ],
        'whatsapp' => [
            [
                'name' => 'wa_greeting',
                'title' => 'Concierge Greeting',
                'category' => 'Marketing',
                'subject' => 'Hello {{name}}',
                'body' => 'Hi {{name}} 👋, thanks for connecting with {{brand}}. How can we help today?'
            ],
            [
                'name' => 'wa_order_update',
                'title' => 'Order Update',
                'category' => 'Utility',
                'subject' => 'Order Update',
                'body' => 'Order {{order_no}} is now {{status}}. Track any time via {{tracking_url}}.'
            ],
            [
                'name' => 'wa_feedback',
                'title' => 'Feedback Request',
                'category' => 'Engagement',
                'subject' => 'How did we do?',
                'body' => 'Hey {{name}}, hope you loved your {{product}}! Mind rating us at {{feedback_link}}?'
            ],
        ],
        'email' => [
            [
                'name' => 'welcome_email',
                'title' => 'Welcome Email',
                'category' => 'Onboarding',
                'subject' => 'Welcome to {{brand}}, {{name}}! 🎉',
                'body' => '<h1>Welcome, {{name}}!</h1><p>We are thrilled to have you at {{brand}}.</p>'
            ],
            [
                'name' => 'password_reset',
                'title' => 'Password Reset',
                'category' => 'Security',
                'subject' => 'Reset your password',
                'body' => '<p>Hi {{name}},</p><p>Click <a href="{{reset_link}}">here</a> to reset your password.</p>'
            ],
            [
                'name' => 'billing_receipt',
                'title' => 'Billing Receipt',
                'category' => 'Finance',
                'subject' => 'Receipt for invoice {{invoice_no}}',
                'body' => '<p>Hello {{name}},</p><p>Thanks for your payment of {{amount}} on {{date}}.</p>'
            ],
        ],
    ];
@endphp

<script>
const sampleTemplates = @json($sampleTemplates);
const channelSelect = document.getElementById('channel');
const subjectFieldWrapper = document.getElementById('subject-field');
const subjectInput = document.getElementById('subject');
const nameInput = document.getElementById('name');
const bodyInput = document.getElementById('body');
const sampleContainer = document.getElementById('sample-templates-list');

function handleChannelChange() {
    if (channelSelect.value === 'sms' || !channelSelect.value) {
        subjectFieldWrapper.style.display = 'none';
        subjectInput.required = false;
    } else {
        subjectFieldWrapper.style.display = 'block';
        subjectInput.required = false;
    }

    renderSampleTemplates(channelSelect.value);
}

function escapeHtml(value) {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function renderSampleTemplates(channel) {
    if (!channel) {
        sampleContainer.innerHTML = `
            <div class="sample-placeholder">
                <i class="bi bi-arrow-up-circle"></i>
                <p>Select a channel above to see curated starter templates.</p>
            </div>
        `;
        return;
    }

    const templates = sampleTemplates[channel] || [];
    if (!templates.length) {
        sampleContainer.innerHTML = `
            <div class="sample-placeholder">
                <i class="bi bi-exclamation-circle"></i>
                <p>No samples available for this channel yet.</p>
            </div>
        `;
        return;
    }

    sampleContainer.innerHTML = templates.map((template, index) => `
        <div class="sample-template-card">
            <div class="sample-template-header">
                <div>
                    <p class="sample-template-title">${template.title}</p>
                    <span class="sample-template-badge">${template.category}</span>
                </div>
                <button 
                    type="button" 
                    class="btn btn-sm btn-outline-primary use-template-btn"
                    data-channel="${channel}"
                    data-index="${index}">
                    <i class="bi bi-clipboard-check me-1"></i>Use Template
                </button>
            </div>
            <div class="sample-template-body">
                ${template.subject ? `<p class="sample-template-subject"><strong>Subject:</strong> ${escapeHtml(template.subject)}</p>` : ''}
                <pre>${escapeHtml(template.body)}</pre>
            </div>
        </div>
    `).join('');

    document.querySelectorAll('.use-template-btn').forEach(button => {
        button.addEventListener('click', () => {
            const selected = sampleTemplates[button.dataset.channel][button.dataset.index];
            if (!nameInput.value) {
                nameInput.value = selected.name;
            }
            if (selected.subject !== null) {
                subjectInput.value = selected.subject;
            } else {
                subjectInput.value = '';
            }
            bodyInput.value = selected.body;
            bodyInput.focus();
        });
    });
}

channelSelect.addEventListener('change', handleChannelChange);

window.addEventListener('DOMContentLoaded', function() {
    handleChannelChange();
});
</script>

<style>
.sample-templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
}

.sample-template-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    background: #fff;
}

.sample-template-header {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    align-items: flex-start;
}

.sample-template-title {
    font-weight: 600;
    margin: 0;
    color: #1e293b;
}

.sample-template-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 0.75rem;
    font-weight: 500;
}

.sample-template-body pre {
    background: #f8fafc;
    border-radius: 8px;
    padding: 0.75rem;
    margin: 0;
    font-size: 0.85rem;
    white-space: pre-line;
}

.sample-placeholder {
    text-align: center;
    color: #94a3b8;
    padding: 1.5rem;
}

.sample-placeholder i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    display: block;
}

@media (max-width: 576px) {
    .sample-template-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .sample-template-header button {
        width: 100%;
    }
}
</style>
@endsection
