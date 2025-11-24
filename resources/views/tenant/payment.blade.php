@extends('layouts.app')

@section('title', 'Payment & Top-up')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Payment & Top-up</h1>
                    <p class="text-muted mb-0">Add funds to your account using M-Pesa or Stripe</p>
                </div>
                <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Balance Overview -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Current Balance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">KES {{ number_format($client->balance, 2) }}</div>
                            <div class="text-xs text-muted">{{ number_format($client->getBalanceInUnits()) }} units</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Messages Sent</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($client->sms()->count()) }}</div>
                            <div class="text-xs text-muted">Total Messages</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Price Per SMS</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">KES {{ number_format($client->price_per_unit, 2) }}</div>
                            <div class="text-xs text-muted">{{ ucfirst($client->tier) }} Tier</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tag fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Payment Methods -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Choose Payment Method</h6>
                </div>
                <div class="card-body">
                    <!-- Payment Method Tabs -->
                    <ul class="nav nav-tabs" id="paymentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="mpesa-tab" data-bs-toggle="tab" data-bs-target="#mpesa" type="button" role="tab">
                                <i class="fas fa-mobile-alt"></i> M-Pesa
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="stripe-tab" data-bs-toggle="tab" data-bs-target="#stripe" type="button" role="tab">
                                <i class="fas fa-credit-card"></i> Stripe
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual" type="button" role="tab">
                                <i class="fas fa-hand-holding-usd"></i> Manual
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="paymentTabsContent">
                        <!-- M-Pesa Tab -->
                        <div class="tab-pane fade show active" id="mpesa" role="tabpanel">
                            <div class="mt-4">
                                <form id="mpesa-form">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="mpesa_phone" class="form-label">
                                                    <i class="fas fa-mobile-alt"></i> M-Pesa Phone Number *
                                                </label>
                                                <input type="tel" class="form-control" id="mpesa_phone" name="phone_number" 
                                                       placeholder="+254712345678" required>
                                                <small class="form-text text-muted">Enter your M-Pesa registered phone number</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="mpesa_amount" class="form-label">
                                                    <i class="fas fa-money-bill"></i> Amount (KES) *
                                                </label>
                                                <input type="number" class="form-control" id="mpesa_amount" name="amount" 
                                                       min="1" max="70000" step="1" required>
                                                <small class="form-text text-muted">Minimum: KES 1, Maximum: KES 70,000</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <button type="submit" class="btn btn-success btn-lg" id="mpesa-submit">
                                            <i class="fas fa-mobile-alt"></i> Pay with M-Pesa
                                        </button>
                                    </div>
                                </form>
                                
                                <div id="mpesa-status" class="mt-3" style="display: none;">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle"></i> Payment Status</h6>
                                        <p id="mpesa-status-message">Processing your payment...</p>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                 role="progressbar" style="width: 100%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stripe Tab -->
                        <div class="tab-pane fade" id="stripe" role="tabpanel">
                            <div class="mt-4">
                                <form id="stripe-form">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="stripe_amount" class="form-label">
                                                    <i class="fas fa-money-bill"></i> Amount (KES) *
                                                </label>
                                                <input type="number" class="form-control" id="stripe_amount" name="amount" 
                                                       min="1" max="1000000" step="1" required>
                                                <small class="form-text text-muted">Minimum: KES 1, Maximum: KES 1,000,000</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Payment Method</label>
                                                <div id="stripe-payment-element">
                                                    <!-- Stripe Elements will be inserted here -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <button type="submit" class="btn btn-primary btn-lg" id="stripe-submit">
                                            <i class="fas fa-credit-card"></i> Pay with Stripe
                                        </button>
                                    </div>
                                </form>
                                
                                <div id="stripe-status" class="mt-3" style="display: none;">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle"></i> Payment Status</h6>
                                        <p id="stripe-status-message">Processing your payment...</p>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                 role="progressbar" style="width: 100%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Tab -->
                        <div class="tab-pane fade" id="manual" role="tabpanel">
                            <div class="mt-4">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-hand-holding-usd"></i> Manual Top-up</h6>
                                    <p>For manual top-up assistance, please contact our support team:</p>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="manual_amount" class="form-label">Amount (KES)</label>
                                            <input type="number" class="form-control" id="manual_amount" min="100" max="1000000" step="100">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Contact Information</label>
                                            <div class="form-control-plaintext">
                                                <strong>Email:</strong> <a href="mailto:mathiasodhis@gmail.com">mathiasodhis@gmail.com</a><br>
                                                <strong>Phone:</strong> <a href="tel:+254728883160">+254 728 883 160</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> Instructions</h6>
                                    <ol>
                                        <li>Contact our support team with your desired amount</li>
                                        <li>Provide your account details: <strong>{{ $client->company_name }}</strong></li>
                                        <li>Make payment via bank transfer or mobile money</li>
                                        <li>Send proof of payment to our support team</li>
                                        <li>Your account will be credited within 24 hours</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction History & Info -->
        <div class="col-lg-4">
            <!-- Quick Amounts -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Amounts</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-2">
                            <button class="btn btn-outline-primary btn-sm w-100 amount-btn" data-amount="500">KES 500</button>
                        </div>
                        <div class="col-6 mb-2">
                            <button class="btn btn-outline-primary btn-sm w-100 amount-btn" data-amount="1000">KES 1,000</button>
                        </div>
                        <div class="col-6 mb-2">
                            <button class="btn btn-outline-primary btn-sm w-100 amount-btn" data-amount="2500">KES 2,500</button>
                        </div>
                        <div class="col-6 mb-2">
                            <button class="btn btn-outline-primary btn-sm w-100 amount-btn" data-amount="5000">KES 5,000</button>
                        </div>
                        <div class="col-6 mb-2">
                            <button class="btn btn-outline-primary btn-sm w-100 amount-btn" data-amount="10000">KES 10,000</button>
                        </div>
                        <div class="col-6 mb-2">
                            <button class="btn btn-outline-primary btn-sm w-100 amount-btn" data-amount="25000">KES 25,000</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6>
                </div>
                <div class="card-body">
                    @if($transactions->count() > 0)
                        @foreach($transactions as $transaction)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="font-weight-bold">{{ $transaction->description }}</div>
                                <small class="text-muted">{{ $transaction->created_at->format('M d, Y H:i') }}</small>
                            </div>
                            <div class="text-end">
                                <div class="font-weight-bold text-{{ $transaction->status_badge_class }}">
                                    {{ $transaction->amount > 0 ? '+' : '' }}KES {{ number_format($transaction->amount, 2) }}
                                </div>
                                <span class="badge bg-{{ $transaction->status_badge_class }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                        <div class="text-center">
                            <a href="#" class="btn btn-sm btn-outline-primary" onclick="loadMoreTransactions()">Load More</a>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-receipt fa-2x text-gray-300 mb-2"></i>
                            <p class="text-muted mb-0">No transactions yet</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Support -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Need Help?</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Having trouble with payments? Our support team is here to help.
                    </p>
                    <div class="d-grid gap-2">
                        <a href="mailto:mathiasodhis@gmail.com" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope"></i> Email Support
                        </a>
                        <a href="tel:+254728883160" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-phone"></i> Call Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stripe Script -->
<script src="https://js.stripe.com/v3/"></script>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.amount-btn.active {
    background-color: #4e73df;
    border-color: #4e73df;
    color: white;
}

#stripe-payment-element {
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    background-color: #f8f9fa;
}
</style>

<script>
let stripe = null;
let elements = null;
let paymentElement = null;

// Initialize Stripe when Stripe tab is clicked
document.getElementById('stripe-tab').addEventListener('click', function() {
    if (!stripe) {
        initializeStripe();
    }
});

// Amount buttons
document.querySelectorAll('.amount-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const amount = this.dataset.amount;
        
        // Remove active class from all buttons
        document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
        
        // Add active class to clicked button
        this.classList.add('active');
        
        // Set amount in both forms
        document.getElementById('mpesa_amount').value = amount;
        document.getElementById('stripe_amount').value = amount;
    });
});

// Phone number formatting for M-Pesa
document.getElementById('mpesa_phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.startsWith('254')) {
        value = '+' + value;
    } else if (value.startsWith('0')) {
        value = '+254' + value.substring(1);
    } else if (value && !value.startsWith('+')) {
        value = '+254' + value;
    }
    e.target.value = value;
});

// M-Pesa form submission
document.getElementById('mpesa-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = document.getElementById('mpesa-submit');
    const statusDiv = document.getElementById('mpesa-status');
    const statusMessage = document.getElementById('mpesa-status-message');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    // Show status
    statusDiv.style.display = 'block';
    statusMessage.textContent = 'Initiating M-Pesa payment...';
    
    fetch('{{ route("tenant.payments.mpesa.initiate") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            phone_number: formData.get('phone_number'),
            amount: parseFloat(formData.get('amount'))
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusMessage.textContent = data.message;
            
            // Start polling for status
            pollPaymentStatus(data.transaction_id, 'mpesa');
        } else {
            statusMessage.textContent = 'Error: ' + data.message;
            statusDiv.querySelector('.alert').className = 'alert alert-danger';
            
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-mobile-alt"></i> Pay with M-Pesa';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        statusMessage.textContent = 'An error occurred. Please try again.';
        statusDiv.querySelector('.alert').className = 'alert alert-danger';
        
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-mobile-alt"></i> Pay with M-Pesa';
    });
});

// Stripe form submission
document.getElementById('stripe-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!stripe || !elements) {
        alert('Stripe is not initialized. Please try again.');
        return;
    }
    
    const submitBtn = document.getElementById('stripe-submit');
    const statusDiv = document.getElementById('stripe-status');
    const statusMessage = document.getElementById('stripe-status-message');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    // Show status
    statusDiv.style.display = 'block';
    statusMessage.textContent = 'Processing payment...';
    
    stripe.confirmPayment({
        elements,
        confirmParams: {
            return_url: window.location.href,
        },
    }).then((result) => {
        if (result.error) {
            statusMessage.textContent = 'Error: ' + result.error.message;
            statusDiv.querySelector('.alert').className = 'alert alert-danger';
            
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-credit-card"></i> Pay with Stripe';
        } else {
            statusMessage.textContent = 'Payment successful!';
            statusDiv.querySelector('.alert').className = 'alert alert-success';
            
            // Reload page after 2 seconds
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    });
});

function initializeStripe() {
    fetch('{{ route("tenant.payments.stripe.publishable-key") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                stripe = Stripe(data.publishable_key);
                
                // Create payment intent
                const amount = document.getElementById('stripe_amount').value || 1000;
                
                fetch('{{ route("tenant.payments.stripe.create-intent") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ amount: parseFloat(amount) })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        elements = stripe.elements({
                            clientSecret: data.client_secret
                        });
                        
                        paymentElement = elements.create('payment');
                        paymentElement.mount('#stripe-payment-element');
                    }
                });
            }
        });
}

function pollPaymentStatus(transactionId, method) {
    const statusDiv = document.getElementById(method + '-status');
    const statusMessage = document.getElementById(method + '-status-message');
    
    const poll = () => {
        fetch(`{{ url('/tenant/payments/status') }}/${transactionId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'completed') {
                    statusMessage.textContent = 'Payment completed successfully!';
                    statusDiv.querySelector('.alert').className = 'alert alert-success';
                    
                    // Reload page after 2 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else if (data.status === 'failed') {
                    statusMessage.textContent = 'Payment failed: ' + data.message;
                    statusDiv.querySelector('.alert').className = 'alert alert-danger';
                    
                    // Re-enable submit button
                    const submitBtn = document.getElementById(method + '-submit');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = method === 'mpesa' ? 
                        '<i class="fas fa-mobile-alt"></i> Pay with M-Pesa' : 
                        '<i class="fas fa-credit-card"></i> Pay with Stripe';
                } else {
                    // Still pending, poll again
                    setTimeout(poll, 3000);
                }
            })
            .catch(error => {
                console.error('Error polling status:', error);
                setTimeout(poll, 5000);
            });
    };
    
    // Start polling
    setTimeout(poll, 2000);
}

function loadMoreTransactions() {
    // Implement load more transactions functionality
    alert('Load more transactions functionality would be implemented here');
}
</script>
@endsection
