@extends('layouts.app')

@section('title', 'Billing & Payments')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Billing & Payments</h1>
                    <p class="text-muted mb-0">Manage your balance and payment methods</p>
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
        <!-- Top Up Options -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Up Your Balance</h6>
                </div>
                <div class="card-body">
                    <!-- Payment Methods -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="mb-3">Choose Payment Method</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="payment-method-card" data-method="mpesa">
                                        <div class="card h-100 border-2">
                                            <div class="card-body text-center">
                                                <i class="fas fa-mobile-alt fa-3x text-success mb-3"></i>
                                                <h6>M-Pesa</h6>
                                                <p class="text-muted small">Pay via M-Pesa mobile money</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="payment-method-card" data-method="bank">
                                        <div class="card h-100 border-2">
                                            <div class="card-body text-center">
                                                <i class="fas fa-university fa-3x text-primary mb-3"></i>
                                                <h6>Bank Transfer</h6>
                                                <p class="text-muted small">Direct bank transfer</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="payment-method-card" data-method="manual">
                                        <div class="card h-100 border-2">
                                            <div class="card-body text-center">
                                                <i class="fas fa-hand-holding-usd fa-3x text-warning mb-3"></i>
                                                <h6>Manual Top-up</h6>
                                                <p class="text-muted small">Contact support for assistance</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Amount Selection -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="mb-3">Select Amount</h6>
                            <div class="row">
                                <div class="col-md-3 col-6 mb-2">
                                    <button class="btn btn-outline-primary amount-btn w-100" data-amount="500">
                                        KES 500
                                    </button>
                                </div>
                                <div class="col-md-3 col-6 mb-2">
                                    <button class="btn btn-outline-primary amount-btn w-100" data-amount="1000">
                                        KES 1,000
                                    </button>
                                </div>
                                <div class="col-md-3 col-6 mb-2">
                                    <button class="btn btn-outline-primary amount-btn w-100" data-amount="2500">
                                        KES 2,500
                                    </button>
                                </div>
                                <div class="col-md-3 col-6 mb-2">
                                    <button class="btn btn-outline-primary amount-btn w-100" data-amount="5000">
                                        KES 5,000
                                    </button>
                                </div>
                                <div class="col-md-3 col-6 mb-2">
                                    <button class="btn btn-outline-primary amount-btn w-100" data-amount="10000">
                                        KES 10,000
                                    </button>
                                </div>
                                <div class="col-md-3 col-6 mb-2">
                                    <button class="btn btn-outline-primary amount-btn w-100" data-amount="25000">
                                        KES 25,000
                                    </button>
                                </div>
                                <div class="col-md-3 col-6 mb-2">
                                    <button class="btn btn-outline-primary amount-btn w-100" data-amount="50000">
                                        KES 50,000
                                    </button>
                                </div>
                                <div class="col-md-3 col-6 mb-2">
                                    <button class="btn btn-outline-primary amount-btn w-100" data-amount="custom">
                                        Custom
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Amount Input -->
                    <div class="row mb-4" id="custom-amount" style="display: none;">
                        <div class="col-md-6">
                            <label for="custom_amount" class="form-label">Enter Custom Amount (KES)</label>
                            <input type="number" class="form-control" id="custom_amount" min="100" max="1000000" step="100">
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <form id="payment-form" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="phone_number" class="form-label">M-Pesa Phone Number</label>
                                    <input type="tel" class="form-control" id="phone_number" placeholder="+254712345678">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input type="text" class="form-control" id="selected_amount" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-credit-card"></i> Pay Now
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetPaymentForm()">
                                Cancel
                            </button>
                        </div>
                    </form>

                    <!-- Bank Transfer Instructions -->
                    <div id="bank-transfer-info" style="display: none;">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Bank Transfer Instructions</h6>
                            <p>Please transfer the amount to the following account:</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Bank:</strong> Equity Bank<br>
                                    <strong>Account Name:</strong> Matech Technologies<br>
                                    <strong>Account Number:</strong> 1234567890<br>
                                    <strong>Branch:</strong> Nairobi CBD
                                </div>
                                <div class="col-md-6">
                                    <strong>Reference:</strong> {{ $client->sender_id }}-{{ date('Ymd') }}<br>
                                    <strong>Amount:</strong> <span id="bank_amount">KES 0</span><br>
                                    <strong>Currency:</strong> KES
                                </div>
                            </div>
                            <hr>
                            <p class="mb-0">
                                <strong>Note:</strong> After making the transfer, please contact us at 
                                <a href="mailto:mathiasodhis@gmail.com">mathiasodhis@gmail.com</a> 
                                with the transaction details for manual verification.
                            </p>
                        </div>
                    </div>

                    <!-- Manual Top-up Instructions -->
                    <div id="manual-topup-info" style="display: none;">
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-hand-holding-usd"></i> Manual Top-up</h6>
                            <p>For manual top-up assistance, please contact our support team:</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Email:</strong> <a href="mailto:mathiasodhis@gmail.com">mathiasodhis@gmail.com</a><br>
                                    <strong>Phone:</strong> <a href="tel:+254728883160">+254 728 883 160</a>
                                </div>
                                <div class="col-md-6">
                                    <strong>Amount:</strong> <span id="manual_amount">KES 0</span><br>
                                    <strong>Account:</strong> {{ $client->company_name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction History & Info -->
        <div class="col-lg-4">
            <!-- Pricing Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pricing Information</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tier</th>
                                    <th>Price/SMS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="{{ $client->tier === 'low' ? 'table-primary' : '' }}">
                                    <td>Low</td>
                                    <td>KES 2.50</td>
                                </tr>
                                <tr class="{{ $client->tier === 'medium' ? 'table-primary' : '' }}">
                                    <td>Medium</td>
                                    <td>KES 2.00</td>
                                </tr>
                                <tr class="{{ $client->tier === 'high' ? 'table-primary' : '' }}">
                                    <td>High</td>
                                    <td>KES 1.50</td>
                                </tr>
                                <tr class="{{ $client->tier === 'enterprise' ? 'table-primary' : '' }}">
                                    <td>Enterprise</td>
                                    <td>KES 1.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center">
                        <small class="text-muted">
                            Your current tier: <strong>{{ ucfirst($client->tier) }}</strong>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
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
                                <div class="font-weight-bold text-{{ $transaction->amount > 0 ? 'success' : 'danger' }}">
                                    {{ $transaction->amount > 0 ? '+' : '' }}KES {{ number_format($transaction->amount, 2) }}
                                </div>
                                <small class="text-muted">{{ $transaction->status }}</small>
                            </div>
                        </div>
                        @endforeach
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

.payment-method-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-method-card:hover .card {
    border-color: #4e73df !important;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.payment-method-card.selected .card {
    border-color: #4e73df !important;
    background-color: #f8f9ff;
}

.amount-btn.active {
    background-color: #4e73df;
    border-color: #4e73df;
    color: white;
}
</style>

<script>
let selectedMethod = null;
let selectedAmount = 0;

// Payment method selection
document.querySelectorAll('.payment-method-card').forEach(card => {
    card.addEventListener('click', function() {
        // Remove previous selection
        document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('selected'));
        
        // Add selection to clicked card
        this.classList.add('selected');
        
        selectedMethod = this.dataset.method;
        showPaymentForm();
    });
});

// Amount selection
document.querySelectorAll('.amount-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Remove previous selection
        document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
        
        // Add selection to clicked button
        this.classList.add('active');
        
        if (this.dataset.amount === 'custom') {
            document.getElementById('custom-amount').style.display = 'block';
            document.getElementById('custom_amount').focus();
        } else {
            document.getElementById('custom-amount').style.display = 'none';
            selectedAmount = parseFloat(this.dataset.amount);
            updatePaymentForm();
        }
    });
});

// Custom amount input
document.getElementById('custom_amount').addEventListener('input', function() {
    selectedAmount = parseFloat(this.value) || 0;
    updatePaymentForm();
});

function showPaymentForm() {
    if (selectedMethod === 'mpesa') {
        document.getElementById('payment-form').style.display = 'block';
        document.getElementById('bank-transfer-info').style.display = 'none';
        document.getElementById('manual-topup-info').style.display = 'none';
    } else if (selectedMethod === 'bank') {
        document.getElementById('payment-form').style.display = 'none';
        document.getElementById('bank-transfer-info').style.display = 'block';
        document.getElementById('manual-topup-info').style.display = 'none';
        document.getElementById('bank_amount').textContent = 'KES ' + selectedAmount.toLocaleString();
    } else if (selectedMethod === 'manual') {
        document.getElementById('payment-form').style.display = 'none';
        document.getElementById('bank-transfer-info').style.display = 'none';
        document.getElementById('manual-topup-info').style.display = 'block';
        document.getElementById('manual_amount').textContent = 'KES ' + selectedAmount.toLocaleString();
    }
}

function updatePaymentForm() {
    if (selectedAmount > 0) {
        document.getElementById('selected_amount').value = selectedAmount.toLocaleString();
        if (selectedMethod === 'bank') {
            document.getElementById('bank_amount').textContent = 'KES ' + selectedAmount.toLocaleString();
        }
        if (selectedMethod === 'manual') {
            document.getElementById('manual_amount').textContent = 'KES ' + selectedAmount.toLocaleString();
        }
    }
}

function resetPaymentForm() {
    selectedMethod = null;
    selectedAmount = 0;
    document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('selected'));
    document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('payment-form').style.display = 'none';
    document.getElementById('bank-transfer-info').style.display = 'none';
    document.getElementById('manual-topup-info').style.display = 'none';
    document.getElementById('custom-amount').style.display = 'none';
    document.getElementById('custom_amount').value = '';
}

// Phone number formatting
document.getElementById('phone_number').addEventListener('input', function(e) {
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

// Payment form submission
document.getElementById('payment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!selectedMethod || !selectedAmount) {
        alert('Please select a payment method and amount.');
        return;
    }
    
    // Here you would integrate with M-Pesa API
    alert('M-Pesa integration would be implemented here. For now, please contact support for manual processing.');
});
</script>
@endsection


