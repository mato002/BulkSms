@extends('layouts.app')

@section('title', 'Documentation')

@section('content')
<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h1 class="mb-4">Documentation</h1>
                    <p class="lead text-muted mb-4">
                        Welcome to the BulkSMS documentation. Here you'll find comprehensive guides to help you integrate and use our SMS platform.
                    </p>

                    <div class="row mt-5">
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h3 class="h5 mb-3">
                                        <i class="fas fa-code text-primary me-2"></i>
                                        API Documentation
                                    </h3>
                                    <p class="text-muted">
                                        Learn how to integrate our REST API into your application. Complete with code examples and endpoint references.
                                    </p>
                                    <a href="{{ route('api.documentation') }}" class="btn btn-primary btn-sm">
                                        View API Docs <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h3 class="h5 mb-3">
                                        <i class="fas fa-book text-success me-2"></i>
                                        Getting Started
                                    </h3>
                                    <p class="text-muted">
                                        New to BulkSMS? Start here with our step-by-step guide to creating your first campaign.
                                    </p>
                                    <a href="{{ route('tenant.register') }}" class="btn btn-success btn-sm">
                                        Get Started <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h3 class="h5 mb-3">
                                        <i class="fas fa-users text-info me-2"></i>
                                        Contact Management
                                    </h3>
                                    <p class="text-muted">
                                        Learn how to organize and manage your contacts, import from CSV, and use tags for better segmentation.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h3 class="h5 mb-3">
                                        <i class="fas fa-paper-plane text-warning me-2"></i>
                                        Campaign Creation
                                    </h3>
                                    <p class="text-muted">
                                        Create and send SMS campaigns, schedule messages, and track delivery status in real-time.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <h2 class="h4 mb-3">Quick Links</h2>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <a href="{{ route('support') }}" class="text-decoration-none">Need Help? Contact Support</a>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <a href="{{ route('api.documentation') }}" class="text-decoration-none">API Reference Guide</a>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <a href="{{ route('privacy-policy') }}" class="text-decoration-none">Privacy Policy</a>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <a href="{{ route('terms-of-service') }}" class="text-decoration-none">Terms of Service</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



