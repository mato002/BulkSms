@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Page Header -->
            <div class="mb-4">
                <a href="{{ route('wallet.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
                    <i class="bi bi-arrow-left"></i> Back to Wallet
                </a>
                <h1 class="h3 mb-1">💳 Top Up Your Balance</h1>
                <p class="text-muted mb-0">Add funds to your wallet using M-Pesa or manual payment</p>
            </div>

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Current Balance Card -->
            <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="text-white-50 small mb-1">Current Balance</div>
                            <h2 class="mb-1">KES {{ number_format($client->balance, 2) }}</h2>
                            <div class="small">
                                <i class="bi bi-lightning-charge-fill"></i> 
                                {{ number_format($client->getBalanceInUnits()) }} SMS Units Available
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <div class="bg-white bg-opacity-25 d-inline-block px-4 py-2 rounded">
                                <div class="text-white-50 small mb-1">Account</div>
                                <div class="fw-bold">{{ $client->name }}</div>
                                <div class="small">{{ $client->sender_id }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top-up Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('wallet.topup.initiate') }}" method="POST" id="topupForm">
                        @csrf

                        <!-- Amount Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-cash-stack"></i> Select Amount
                            </label>
                            
                            <!-- Quick Amount Buttons -->
                            <div class="row g-2 mb-3">
                                <div class="col-6 col-md-3">
                                    <button type="button" class="btn btn-outline-primary w-100 amount-btn" data-amount="100">
                                        KES 100
                                    </button>
                                </div>
                                <div class="col-6 col-md-3">
                                    <button type="button" class="btn btn-outline-primary w-100 amount-btn" data-amount="500">
                                        KES 500
                                    </button>
                                </div>
                                <div class="col-6 col-md-3">
                                    <button type="button" class="btn btn-outline-primary w-100 amount-btn" data-amount="1000">
                                        KES 1,000
                                    </button>
                                </div>
                                <div class="col-6 col-md-3">
                                    <button type="button" class="btn btn-outline-primary w-100 amount-btn" data-amount="5000">
                                        KES 5,000
                                    </button>
                                </div>
                            </div>

                            <!-- Custom Amount Input -->
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">KES</span>
                                <input type="number" 
                                       class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" 
                                       name="amount" 
                                       min="100" 
                                       max="50000" 
                                       step="10"
                                       placeholder="Enter custom amount"
                                       value="{{ old('amount') }}"
                                       required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Minimum: KES 100 | Maximum: KES 50,000</small>
                            
                            <!-- SMS Units Preview -->
                            <div id="unitsPreview" class="alert alert-info mt-3 d-none">
                                <i class="bi bi-info-circle"></i> 
                                You will receive approximately <strong id="unitsCount">0</strong> SMS units
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-credit-card"></i> Payment Method
                            </label>
                            
                            <div class="row g-3">
                                <!-- M-Pesa Option -->
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="payment_method" id="mpesa" value="mpesa" checked>
                                    <label class="btn btn-outline-success w-100 h-100 p-3 text-start" for="mpesa">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success bg-opacity-10 p-2 rounded me-3">
                                                <i class="bi bi-phone fs-3 text-success"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">M-Pesa</div>
                                                <small class="text-muted">Pay with your phone</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Manual Payment Option -->
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="payment_method" id="manual" value="manual">
                                    <label class="btn btn-outline-info w-100 h-100 p-3 text-start" for="manual">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info bg-opacity-10 p-2 rounded me-3">
                                                <i class="bi bi-person-check fs-3 text-info"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">Manual Payment</div>
                                                <small class="text-muted">Contact support</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- M-Pesa Phone Number -->
                        <div id="phoneNumberSection" class="mb-4">
                            <label for="phone_number" class="form-label fw-semibold">
                                <i class="bi bi-telephone"></i> M-Pesa Phone Number
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">+254</span>
                                <input type="text" 
                                       class="form-control @error('phone_number') is-invalid @enderror" 
                                       id="phone_number" 
                                       name="phone_number" 
                                       placeholder="712345678"
                                       pattern="[0-9]{9}"
                                       maxlength="9"
                                       value="{{ old('phone_number', substr($client->contact ?? '', -9)) }}">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Enter 9 digits without the country code (e.g., 712345678)</small>
                        </div>

                        <!-- Manual Payment Instructions -->
                        <div id="manualInstructions" class="alert alert-info d-none">
                            <h6 class="alert-heading">
                                <i class="bi bi-info-circle"></i> Manual Payment Instructions
                            </h6>
                            <p class="mb-0">After submitting this request, please contact our support team for payment details and bank information.</p>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="bi bi-check-circle"></i> Proceed to Payment
                            </button>
                            <a href="{{ route('wallet.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Information Cards -->
            <div class="row g-3 mt-3">
                <div class="col-md-4">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <div class="text-primary mb-2">
                                <i class="bi bi-shield-check fs-3"></i>
                            </div>
                            <h6 class="fw-semibold">Secure Payment</h6>
                            <small class="text-muted">Your transactions are encrypted and secure</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <div class="text-success mb-2">
                                <i class="bi bi-lightning-charge-fill fs-3"></i>
                            </div>
                            <h6 class="fw-semibold">Instant Credit</h6>
                            <small class="text-muted">Balance updated immediately upon confirmation</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <div class="text-info mb-2">
                                <i class="bi bi-headset fs-3"></i>
                            </div>
                            <h6 class="fw-semibold">24/7 Support</h6>
                            <small class="text-muted">Get help anytime you need it</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .amount-btn {
        height: 60px;
        font-weight: 600;
        transition: all 0.2s;
    }
    .amount-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .amount-btn.active {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
        color: white;
    }
    .btn-check:checked + label {
        border-width: 2px;
        box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.1);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    const amountButtons = document.querySelectorAll('.amount-btn');
    const phoneSection = document.getElementById('phoneNumberSection');
    const phoneInput = document.getElementById('phone_number');
    const manualInstructions = document.getElementById('manualInstructions');
    const mpesaRadio = document.getElementById('mpesa');
    const manualRadio = document.getElementById('manual');
    const unitsPreview = document.getElementById('unitsPreview');
    const unitsCount = document.getElementById('unitsCount');
    const submitBtn = document.getElementById('submitBtn');

    // Reset button state if page reloaded with errors
    submitBtn.disabled = false;
    if (mpesaRadio.checked) {
        submitBtn.innerHTML = '<i class="bi bi-phone"></i> Proceed with M-Pesa';
    } else {
        submitBtn.innerHTML = '<i class="bi bi-person-check"></i> Submit Manual Request';
    }

    // Unit cost (KES per SMS)
    const unitCost = 0.75;

    // Quick amount selection
    amountButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const amount = this.dataset.amount;
            amountInput.value = amount;
            
            // Update active state
            amountButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Update units preview
            updateUnitsPreview(amount);
        });
    });

    // Custom amount input
    amountInput.addEventListener('input', function() {
        // Remove active state from quick buttons
        amountButtons.forEach(b => b.classList.remove('active'));
        
        // Update units preview
        updateUnitsPreview(this.value);
    });

    // Payment method change
    mpesaRadio.addEventListener('change', function() {
        if (this.checked) {
            phoneSection.classList.remove('d-none');
            manualInstructions.classList.add('d-none');
            phoneInput.required = true;
            submitBtn.innerHTML = '<i class="bi bi-phone"></i> Proceed with M-Pesa';
        }
    });

    manualRadio.addEventListener('change', function() {
        if (this.checked) {
            phoneSection.classList.add('d-none');
            manualInstructions.classList.remove('d-none');
            phoneInput.required = false;
            submitBtn.innerHTML = '<i class="bi bi-person-check"></i> Submit Manual Request';
        }
    });

    // Phone number formatting
    phoneInput.addEventListener('input', function() {
        // Only allow numbers
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Auto-format: add country code if needed
        if (this.value.length > 0 && this.value.length === 9) {
            // Valid 9-digit number
            this.setCustomValidity('');
        } else if (this.value.length > 0) {
            this.setCustomValidity('Please enter exactly 9 digits');
        } else {
            this.setCustomValidity('');
        }
    });

    // Update units preview
    function updateUnitsPreview(amount) {
        if (amount && amount >= 100) {
            const units = Math.floor(amount / unitCost);
            unitsCount.textContent = units.toLocaleString();
            unitsPreview.classList.remove('d-none');
        } else {
            unitsPreview.classList.add('d-none');
        }
    }

    // Form submission
    document.getElementById('topupForm').addEventListener('submit', function(e) {
        // If M-Pesa, validate phone number
        if (mpesaRadio.checked) {
            const phone = phoneInput.value;
            if (phone.length !== 9 || !/^[0-9]{9}$/.test(phone)) {
                e.preventDefault();
                phoneInput.classList.add('is-invalid');
                phoneInput.setCustomValidity('Please enter a valid 9-digit phone number');
                return false;
            }
            
            // Format phone number to 254XXXXXXXXX
            const fullPhone = '254' + phone;
            phoneInput.value = fullPhone;
        }

        // Disable submit button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
    });
});
</script>
@endpush
@endsection

