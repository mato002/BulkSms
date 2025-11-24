@extends('layouts.app')

@section('title', 'Support')

@section('content')
<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h1 class="mb-4">Support</h1>
                    <p class="lead text-muted mb-4">
                        We're here to help! Get in touch with our support team or find answers to common questions.
                    </p>

                    <div class="row mt-5">
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h3 class="h5 mb-3">
                                        <i class="fas fa-envelope text-primary me-2"></i>
                                        Email Support
                                    </h3>
                                    <p class="text-muted mb-3">
                                        Send us an email and we'll get back to you within 24 hours.
                                    </p>
                                    <a href="mailto:support@matechtechnologies.com" class="btn btn-primary btn-sm">
                                        <i class="fas fa-envelope me-1"></i> support@matechtechnologies.com
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h3 class="h5 mb-3">
                                        <i class="fas fa-phone text-success me-2"></i>
                                        Phone Support
                                    </h3>
                                    <p class="text-muted mb-3">
                                        Call us during business hours for immediate assistance.
                                    </p>
                                    <a href="tel:+254728883160" class="btn btn-success btn-sm">
                                        <i class="fas fa-phone me-1"></i> +254 728 883 160
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h3 class="h5 mb-3">
                                        <i class="fas fa-book text-info me-2"></i>
                                        Documentation
                                    </h3>
                                    <p class="text-muted mb-3">
                                        Browse our comprehensive documentation for guides and tutorials.
                                    </p>
                                    <a href="{{ route('documentation') }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-book me-1"></i> View Documentation
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h3 class="h5 mb-3">
                                        <i class="fas fa-question-circle text-warning me-2"></i>
                                        FAQ
                                    </h3>
                                    <p class="text-muted mb-3">
                                        Find answers to frequently asked questions about our platform.
                                    </p>
                                    <button class="btn btn-warning btn-sm" disabled>
                                        <i class="fas fa-question-circle me-1"></i> Coming Soon
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <h2 class="h4 mb-3">Business Hours</h2>
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <p class="mb-2"><strong>Monday - Friday:</strong> 8:00 AM - 6:00 PM EAT</p>
                                <p class="mb-2"><strong>Saturday:</strong> 9:00 AM - 1:00 PM EAT</p>
                                <p class="mb-0"><strong>Sunday:</strong> Closed</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h2 class="h4 mb-3">Common Issues</h2>
                        <div class="accordion" id="supportAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                        How do I reset my password?
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#supportAccordion">
                                    <div class="accordion-body">
                                        Click on "Forgot Password" on the login page and enter your email address. You'll receive a password reset link via email.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                        How do I top up my account?
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#supportAccordion">
                                    <div class="accordion-body">
                                        Navigate to the Billing section in your dashboard and click "Top Up". You can pay via M-Pesa or other supported payment methods.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                        How do I integrate the API?
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#supportAccordion">
                                    <div class="accordion-body">
                                        Visit our <a href="{{ route('api.documentation') }}">API Documentation</a> page for detailed integration guides and code examples.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



