@extends('layouts.app')

@section('title', 'Getting Started - BulkSMS CRM')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-rocket"></i> Welcome to BulkSMS CRM!</h1>
                <p class="text-muted">Let's get you set up and sending messages in just a few minutes.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="onboarding-container">

                <!-- Step 1: Complete Payment -->
                <div class="onboarding-step {{ $client->status ? 'completed' : ($step == 1 ? 'active' : '') }}">
                    <div class="step-header">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h3>Complete Payment</h3>
                            <p>Make your first payment to activate your account and unlock all features</p>
                        </div>
                        @if($client->status)
                            <div class="step-status">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                        @endif
                    </div>
                    @if(!$client->status)
                        <div class="step-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Payment Required:</strong> Your account is currently inactive. Complete payment to access your dashboard and start sending messages.
                            </div>
                            <div class="payment-info">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-credit-card"></i> Payment Methods</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-mobile-alt text-success"></i> M-Pesa</li>
                                            <li><i class="fas fa-credit-card text-primary"></i> Stripe (Card)</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-unlock"></i> After Payment</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-success"></i> Full dashboard access</li>
                                            <li><i class="fas fa-check text-success"></i> API key activation</li>
                                            <li><i class="fas fa-check text-success"></i> Start sending messages</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="step-actions">
                                <a href="{{ route('tenant.payment') }}" class="btn btn-success btn-lg">
                                    <i class="fas fa-credit-card"></i> Make Payment
                                </a>
                                <a href="{{ route('tenant.billing') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-receipt"></i> View Billing Details
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Step 2: Complete Profile -->
                <div class="onboarding-step {{ $step >= 2 ? 'completed' : ($step == 2 ? 'active' : '') }} {{ $step != 2 ? 'clickable' : '' }}" 
                     @if($step != 2) onclick="window.location.href='{{ route('tenant.profile') }}'" @endif>
                    <div class="step-header">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h3>Complete Your Profile</h3>
                            <p>Add your business information and contact details</p>
                        </div>
                        @if($step >= 2)
                            <div class="step-status">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                        @endif
                    </div>
                    @if($step == 2)
                        <div class="step-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Profile Setup Required:</strong> Please complete your business profile to continue.
                            </div>
                            <a href="{{ route('tenant.profile') }}" class="btn btn-primary">
                                <i class="fas fa-user-edit"></i> Complete Profile
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Step 3: Import Contacts -->
                <div class="onboarding-step {{ $step >= 3 ? 'completed' : ($step == 3 ? 'active' : '') }} {{ $step != 3 ? 'clickable' : '' }}" 
                     @if($step != 3) onclick="window.location.href='{{ route('contacts.index') }}'" @endif>
                    <div class="step-header">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h3>Import Your Contacts</h3>
                            <p>Upload your customer database or add contacts manually</p>
                        </div>
                        @if($step >= 3)
                            <div class="step-status">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                        @endif
                    </div>
                    @if($step == 3)
                        <div class="step-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Optional Step:</strong> You can skip this and add contacts later.
                            </div>
                            <div class="step-actions">
                                <a href="{{ route('contacts.index') }}" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Import Contacts
                                </a>
                                <a href="{{ route('tenant.onboarding', ['step' => 4]) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-forward"></i> Skip for Now
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Step 4: Send First Message -->
                <div class="onboarding-step {{ $step >= 4 ? 'completed' : ($step == 4 ? 'active' : '') }} {{ $step != 4 ? 'clickable' : '' }}" 
                     @if($step != 4) onclick="window.location.href='{{ route('messages.index') }}'" @endif>
                    <div class="step-header">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h3>Send Your First Message</h3>
                            <p>Create and send your first message to test the system</p>
                        </div>
                        @if($step >= 4)
                            <div class="step-status">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                        @endif
                    </div>
                    @if($step == 4)
                        <div class="step-body">
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <strong>Almost Done!</strong> Send your first message to complete the setup.
                            </div>
                            <div class="step-actions">
                                <a href="{{ route('messages.index') }}" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Completion -->
                @if($step >= 5)
                    <div class="onboarding-complete">
                        <div class="complete-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h2>🎉 Congratulations!</h2>
                        <p>You've successfully set up your BulkSMS CRM account. You're now ready to start sending messages!</p>
                        <div class="complete-actions">
                            <a href="{{ route('tenant.dashboard') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                            </a>
                            <a href="{{ route('messages.index') }}" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-plus"></i> Send Message
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Help Section -->
            <div class="help-section">
                <h4><i class="fas fa-question-circle"></i> Need Help?</h4>
                <div class="help-links">
                    <a href="{{ route('api.docs') }}" class="help-link">
                        <i class="fas fa-book"></i> API Documentation
                    </a>
                    <a href="mailto:mathiasodhis@gmail.com" class="help-link">
                        <i class="fas fa-envelope"></i> Email Support
                    </a>
                    <a href="tel:+254728883160" class="help-link">
                        <i class="fas fa-phone"></i> Call Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.page-header {
    text-align: center;
    padding: 2rem 0;
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 2rem;
}



.payment-info {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 10px;
    margin: 1rem 0;
}

.payment-info h6 {
    color: #667eea;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.payment-info ul li {
    padding: 0.25rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.payment-info .text-success {
    color: #10b981 !important;
}

.payment-info .text-primary {
    color: #667eea !important;
}

.step-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
    flex-wrap: wrap;
}

.page-header h1 {
    color: #667eea;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.onboarding-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

.onboarding-step {
    margin-bottom: 2rem;
    padding: 1.5rem;
    border-radius: 10px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.onboarding-step.active {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.05);
}

.onboarding-step.completed {
    border-color: #28a745;
    background: rgba(40, 167, 69, 0.05);
}

.onboarding-step.clickable {
    cursor: pointer;
    transition: all 0.3s ease;
}

.onboarding-step.clickable:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #667eea;
}

.step-header {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.step-number {
    width: 50px;
    height: 50px;
    background: #667eea;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}

.onboarding-step.completed .step-number {
    background: #28a745;
}

.step-content {
    flex: 1;
}

.step-content h3 {
    margin-bottom: 0.5rem;
    color: #333;
}

.step-content p {
    color: #666;
    margin: 0;
}

.step-status {
    font-size: 1.5rem;
}

.step-body {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.step-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.onboarding-complete {
    text-align: center;
    padding: 3rem 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    margin-top: 2rem;
}

.complete-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.onboarding-complete h2 {
    margin-bottom: 1rem;
}

.complete-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.help-section {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 10px;
    text-align: center;
}

.help-section h4 {
    color: #667eea;
    margin-bottom: 1rem;
}

.help-links {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.help-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: white;
    color: #667eea;
    text-decoration: none;
    border-radius: 25px;
    border: 2px solid #667eea;
    transition: all 0.3s ease;
}

.help-link:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .step-header {
        flex-direction: column;
        text-align: center;
    }
    
    .step-actions {
        flex-direction: column;
    }
    
    .complete-actions {
        flex-direction: column;
    }
    
    .help-links {
        flex-direction: column;
        align-items: center;
    }
}
</style>
@endsection
