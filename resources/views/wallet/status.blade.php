@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <!-- Status Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    @if($transaction->status === 'completed')
                        <!-- Success -->
                        <div class="text-success mb-4">
                            <i class="bi bi-check-circle-fill" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="mb-2">Payment Successful!</h2>
                        <p class="text-muted mb-4">Your balance has been updated</p>
                        
                    @elseif($transaction->status === 'processing')
                        <!-- Processing -->
                        <div class="text-info mb-4">
                            <div class="spinner-border" style="width: 5rem; height: 5rem;" role="status">
                                <span class="visually-hidden">Processing...</span>
                            </div>
                        </div>
                        <h2 class="mb-2">Processing Payment...</h2>
                        <p class="text-muted mb-4">Please complete the M-Pesa payment on your phone</p>
                        
                    @elseif($transaction->status === 'pending')
                        <!-- Pending -->
                        <div class="text-warning mb-4">
                            <i class="bi bi-clock-fill" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="mb-2">Payment Pending</h2>
                        <p class="text-muted mb-4">Waiting for confirmation</p>
                        
                    @elseif($transaction->status === 'failed')
                        <!-- Failed -->
                        <div class="text-danger mb-4">
                            <i class="bi bi-x-circle-fill" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="mb-2">Payment Failed</h2>
                        <p class="text-muted mb-4">{{ $transaction->failure_reason ?? 'Payment could not be completed' }}</p>
                    @endif

                    <!-- Transaction Details -->
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <div class="row text-start g-3">
                                <div class="col-6">
                                    <small class="text-muted d-block mb-1">Transaction ID</small>
                                    <strong class="font-monospace">{{ $transaction->transaction_ref }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block mb-1">Amount</small>
                                    <strong class="text-primary">KES {{ number_format($transaction->amount, 2) }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block mb-1">Payment Method</small>
                                    <strong>
                                        @if($transaction->payment_method === 'mpesa')
                                            <i class="bi bi-phone text-success"></i> M-Pesa
                                        @else
                                            {{ ucfirst($transaction->payment_method) }}
                                        @endif
                                    </strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block mb-1">Date</small>
                                    <strong>{{ $transaction->created_at->format('M d, Y h:i A') }}</strong>
                                </div>
                                @if($transaction->mpesa_receipt)
                                <div class="col-12">
                                    <small class="text-muted d-block mb-1">M-Pesa Receipt</small>
                                    <strong class="font-monospace">{{ $transaction->mpesa_receipt }}</strong>
                                </div>
                                @endif
                                @if($transaction->payment_phone)
                                <div class="col-12">
                                    <small class="text-muted d-block mb-1">Phone Number</small>
                                    <strong>+{{ $transaction->payment_phone }}</strong>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-grid gap-2">
                        @if($transaction->status === 'processing' || $transaction->status === 'pending')
                            <button type="button" class="btn btn-primary" onclick="window.location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> Refresh Status
                            </button>
                        @endif
                        
                        @if($transaction->status === 'completed')
                            <a href="{{ route('wallet.index') }}" class="btn btn-primary">
                                <i class="bi bi-wallet2"></i> View Wallet
                            </a>
                        @endif
                        
                        @if($transaction->status === 'failed')
                            <a href="{{ route('wallet.topup') }}" class="btn btn-primary">
                                <i class="bi bi-arrow-repeat"></i> Try Again
                            </a>
                        @endif
                        
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-house"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Auto-refresh for processing transactions -->
            @if($transaction->status === 'processing' || $transaction->status === 'pending')
            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="bi bi-arrow-clockwise"></i> 
                    This page will auto-refresh in <span id="countdown">10</span> seconds
                </small>
            </div>
            @endif

            <!-- Help Card -->
            <div class="card border-0 bg-light mt-4">
                <div class="card-body text-center">
                    <h6 class="mb-2">
                        <i class="bi bi-question-circle"></i> Need Help?
                    </h6>
                    <p class="text-muted mb-0 small">
                        If you have any questions about this transaction, please contact our support team.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@if($transaction->status === 'processing' || $transaction->status === 'pending')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let countdown = 10;
    const countdownElement = document.getElementById('countdown');
    
    const timer = setInterval(() => {
        countdown--;
        countdownElement.textContent = countdown;
        
        if (countdown <= 0) {
            clearInterval(timer);
            window.location.reload();
        }
    }, 1000);
});
</script>
@endif
@endpush
@endsection

