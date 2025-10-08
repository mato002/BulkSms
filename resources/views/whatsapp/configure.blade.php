@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="mb-4">
                <h2 class="mb-1"><i class="bi bi-gear"></i> Configure WhatsApp (UltraMsg)</h2>
                <p class="text-muted mb-0">Set up WhatsApp using UltraMsg - Quick & Easy! ⚡</p>
            </div>

            <!-- Setup Guide Card -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-rocket-takeoff"></i> Quick Setup (5 Minutes!)</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2">Go to <a href="https://ultramsg.com" target="_blank" class="fw-bold">UltraMsg.com</a> and create a free account</li>
                        <li class="mb-2">Click <strong>"Create Instance"</strong> to set up your WhatsApp instance</li>
                        <li class="mb-2">Scan the QR code with your WhatsApp to connect</li>
                        <li class="mb-2">Copy your <strong>Instance ID</strong> and <strong>Token</strong> from the dashboard</li>
                        <li class="mb-0">Paste them below and save - You're done! 🎉</li>
                    </ol>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle"></i> <strong>Note:</strong> UltraMsg offers a free tier to get started. Works with your existing WhatsApp number!
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <h6><i class="bi bi-exclamation-circle"></i> Please fix the following errors:</h6>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Configuration Form -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-key"></i> UltraMsg Credentials</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('whatsapp.configure.save') }}" method="POST">
                        @csrf
                        <input type="hidden" name="provider" value="ultramsg">

                        <div class="mb-4">
                            <label for="instance_id" class="form-label">
                                Instance ID <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control @error('instance_id') is-invalid @enderror" 
                                id="instance_id" 
                                name="instance_id" 
                                value="{{ old('instance_id', $whatsappChannel?->provider === 'ultramsg' ? $whatsappChannel->getCredential('instance_id') : '') }}"
                                placeholder="instance12345"
                                required
                            >
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Found in your UltraMsg dashboard (e.g., instance12345)
                            </small>
                            @error('instance_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="token" class="form-label">
                                API Token <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input 
                                    type="password" 
                                    class="form-control @error('token') is-invalid @enderror" 
                                    id="token" 
                                    name="token" 
                                    value="{{ old('token', $whatsappChannel?->provider === 'ultramsg' ? $whatsappChannel->getCredential('token') : '') }}"
                                    placeholder="your-api-token-here"
                                    required
                                >
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                                @error('token')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Your API token from UltraMsg settings
                            </small>
                        </div>

                        <div class="mb-4">
                            <label for="webhook_token" class="form-label">
                                Webhook Token <small class="text-muted">(Auto-generated if empty)</small>
                            </label>
                            <input 
                                type="text" 
                                class="form-control @error('webhook_token') is-invalid @enderror" 
                                id="webhook_token" 
                                name="webhook_token" 
                                value="{{ old('webhook_token', $whatsappChannel?->getConfig('webhook_token')) }}"
                                placeholder="optional-custom-webhook-token"
                            >
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Use this token when setting up webhooks in UltraMsg
                            </small>
                            @error('webhook_token')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="bi bi-webhook"></i> Webhook URL (Optional)</h6>
                            <p class="mb-2">To receive incoming messages, configure this URL in UltraMsg:</p>
                            <code>{{ url('/webhook/whatsapp') }}</code>
                            <p class="mb-0 mt-2">
                                <small>Go to UltraMsg Dashboard → Settings → Webhooks → Enter this URL</small>
                            </p>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('whatsapp.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save"></i> Save Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Helpful Links -->
            <div class="card mt-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-link-45deg"></i> Helpful Resources</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <a href="https://ultramsg.com" target="_blank" class="text-decoration-none">
                                <i class="bi bi-house"></i> UltraMsg Homepage
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="https://docs.ultramsg.com" target="_blank" class="text-decoration-none">
                                <i class="bi bi-book"></i> UltraMsg Documentation
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="https://docs.ultramsg.com/api/post/messages/chat" target="_blank" class="text-decoration-none">
                                <i class="bi bi-chat-dots"></i> Send Message API
                            </a>
                        </li>
                        <li class="mb-0">
                            <a href="https://docs.ultramsg.com/api/webhooks" target="_blank" class="text-decoration-none">
                                <i class="bi bi-webhook"></i> Webhook Setup Guide
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Comparison Card -->
            <div class="card mt-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Why UltraMsg?</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success">✅ Advantages</h6>
                            <ul class="small">
                                <li>5-minute setup (no Meta verification)</li>
                                <li>Works with your WhatsApp number</li>
                                <li>No template approvals needed</li>
                                <li>Instant message sending</li>
                                <li>Free tier available</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-info">📌 Best For</h6>
                            <ul class="small">
                                <li>Getting started quickly</li>
                                <li>Testing WhatsApp features</li>
                                <li>Small to medium businesses</li>
                                <li>MVP and prototypes</li>
                                <li>Flexible messaging</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePassword() {
    const input = document.getElementById('token');
    const icon = document.getElementById('toggleIcon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
@endpush

@endsection
