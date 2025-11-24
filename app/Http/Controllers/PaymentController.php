<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Client;
use App\Models\Transaction;
use App\Services\Payment\MpesaPaymentGateway;
use App\Services\Payment\StripePaymentService;

class PaymentController extends Controller
{
    private $mpesaService;
    private $stripeService;

    public function __construct()
    {
        $this->mpesaService = new MpesaPaymentGateway();
        $this->stripeService = new StripePaymentService();
    }

    /**
     * Show payment page
     */
    public function index()
    {
        $user = Auth::user();
        $client = $user->client;
        
        if (!$client) {
            return redirect()->route('login')->with('error', 'No client associated with your account.');
        }

        // Get recent transactions
        $transactions = Transaction::where('client_id', $client->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('tenant.payment', compact('user', 'client', 'transactions'));
    }

    /**
     * Initiate M-Pesa payment
     */
    public function initiateMpesaPayment(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|min:10|max:15',
            'amount' => 'required|numeric|min:1|max:70000',
        ]);

        $user = Auth::user();
        $client = $user->client;

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'No client associated with your account.'
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            // Create transaction record
            $transaction = Transaction::create([
                'client_id' => $client->id,
                'user_id' => $user->id,
                'transaction_type' => 'topup',
                'payment_method' => 'mpesa',
                'amount' => $validated['amount'],
                'currency' => 'KES',
                'status' => 'pending',
                'reference' => Transaction::generateReference('MP'),
                'description' => "M-Pesa top-up for {$client->company_name}",
                'payment_details' => [
                    'phone_number' => $validated['phone_number'],
                    'formatted_phone' => $this->mpesaService->formatPhoneNumber($validated['phone_number']),
                ],
                'metadata' => [
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                ],
            ]);

            // Format phone number
            $formattedPhone = $this->mpesaService->formatPhoneNumber($validated['phone_number']);

            // Initiate STK Push
            $result = $this->mpesaService->initiateSTKPush(
                $formattedPhone,
                $validated['amount'],
                $transaction->reference,
                "Top-up for {$client->company_name}"
            );

            if ($result['success']) {
                // Update transaction with M-Pesa details
                $transaction->update([
                    'external_reference' => $result['checkout_request_id'],
                    'payment_details' => array_merge($transaction->payment_details, [
                        'checkout_request_id' => $result['checkout_request_id'],
                        'merchant_request_id' => $result['merchant_request_id'],
                        'customer_message' => $result['customer_message'],
                    ]),
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => $result['customer_message'],
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'checkout_request_id' => $result['checkout_request_id'],
                ]);
            } else {
                $transaction->markAsFailed($result['message']);
                DB::commit();

                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('M-Pesa Payment Initiation Error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'client_id' => $client->id,
                'amount' => $validated['amount'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Create Stripe payment intent
     */
    public function createStripePaymentIntent(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:1000000',
        ]);

        $user = Auth::user();
        $client = $user->client;

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'No client associated with your account.'
            ], 400);
        }

        if (!$this->stripeService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe payment is not configured.'
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            // Create transaction record
            $transaction = Transaction::create([
                'client_id' => $client->id,
                'user_id' => $user->id,
                'transaction_type' => 'topup',
                'payment_method' => 'stripe',
                'amount' => $validated['amount'],
                'currency' => 'KES',
                'status' => 'pending',
                'reference' => Transaction::generateReference('ST'),
                'description' => "Stripe top-up for {$client->company_name}",
                'metadata' => [
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                ],
            ]);

            // Create Stripe payment intent
            $result = $this->stripeService->createPaymentIntent(
                $validated['amount'],
                'kes',
                [
                    'transaction_id' => $transaction->id,
                    'client_id' => $client->id,
                    'reference' => $transaction->reference,
                ]
            );

            if ($result['success']) {
                // Update transaction with Stripe details
                $transaction->update([
                    'external_reference' => $result['payment_intent_id'],
                    'payment_details' => [
                        'payment_intent_id' => $result['payment_intent_id'],
                        'client_secret' => $result['client_secret'],
                    ],
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'client_secret' => $result['client_secret'],
                    'payment_intent_id' => $result['payment_intent_id'],
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                ]);
            } else {
                $transaction->markAsFailed($result['message']);
                DB::commit();

                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stripe Payment Intent Creation Error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'client_id' => $client->id,
                'amount' => $validated['amount'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(Request $request, $transactionId)
    {
        $user = Auth::user();
        $client = $user->client;

        $transaction = Transaction::where('id', $transactionId)
            ->where('client_id', $client->id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found.'
            ], 404);
        }

        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => true,
                'status' => $transaction->status,
                'message' => 'Transaction is already processed.',
            ]);
        }

        // Check status based on payment method
        if ($transaction->payment_method === 'mpesa' && $transaction->external_reference) {
            $result = $this->mpesaService->querySTKPushStatus($transaction->external_reference);
            
            if ($result['success']) {
                if ($result['result_code'] == 0) {
                    $transaction->markAsCompleted();
                    return response()->json([
                        'success' => true,
                        'status' => 'completed',
                        'message' => 'Payment completed successfully!',
                    ]);
                } else {
                    $transaction->markAsFailed($result['result_desc']);
                    return response()->json([
                        'success' => false,
                        'status' => 'failed',
                        'message' => 'Payment failed: ' . $result['result_desc'],
                    ]);
                }
            }
        } elseif ($transaction->payment_method === 'stripe' && $transaction->external_reference) {
            $result = $this->stripeService->retrievePaymentIntent($transaction->external_reference);
            
            if ($result['success']) {
                if ($result['status'] === 'succeeded') {
                    $transaction->markAsCompleted();
                    return response()->json([
                        'success' => true,
                        'status' => 'completed',
                        'message' => 'Payment completed successfully!',
                    ]);
                } elseif (in_array($result['status'], ['canceled', 'requires_payment_method'])) {
                    $transaction->markAsFailed('Payment canceled or failed');
                    return response()->json([
                        'success' => false,
                        'status' => 'failed',
                        'message' => 'Payment was canceled or failed.',
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'status' => 'pending',
            'message' => 'Payment is still being processed.',
        ]);
    }

    /**
     * Handle M-Pesa callback
     */
    public function handleMpesaCallback(Request $request)
    {
        try {
            $callbackData = $request->all();
            $result = $this->mpesaService->handleCallback($callbackData);

            if ($result['success']) {
                // Find transaction by checkout request ID
                $transaction = Transaction::where('external_reference', $result['checkout_request_id'])
                    ->where('status', 'pending')
                    ->first();

                if ($transaction) {
                    $transaction->markAsCompleted($result['mpesa_receipt_number']);
                    
                    Log::info('M-Pesa Payment Completed', [
                        'transaction_id' => $transaction->id,
                        'client_id' => $transaction->client_id,
                        'amount' => $transaction->amount,
                        'mpesa_receipt' => $result['mpesa_receipt_number'],
                    ]);
                }
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('M-Pesa Callback Error', [
                'error' => $e->getMessage(),
                'callback_data' => $request->all(),
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Handle Stripe webhook
     */
    public function handleStripeWebhook(Request $request)
    {
        try {
            $payload = $request->getContent();
            $signature = $request->header('Stripe-Signature');

            $result = $this->stripeService->handleWebhook($payload, $signature);

            if ($result['success'] && $result['event_type'] === 'payment_intent.succeeded') {
                // Find transaction by payment intent ID
                $transaction = Transaction::where('external_reference', $result['payment_intent_id'])
                    ->where('status', 'pending')
                    ->first();

                if ($transaction) {
                    $transaction->markAsCompleted();
                    
                    Log::info('Stripe Payment Completed', [
                        'transaction_id' => $transaction->id,
                        'client_id' => $transaction->client_id,
                        'amount' => $transaction->amount,
                        'payment_intent_id' => $result['payment_intent_id'],
                    ]);
                }
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Stripe Webhook Error', [
                'error' => $e->getMessage(),
                'payload' => $request->getContent(),
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory(Request $request)
    {
        $user = Auth::user();
        $client = $user->client;

        $transactions = Transaction::where('client_id', $client->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Get Stripe publishable key
     */
    public function getStripePublishableKey()
    {
        return response()->json([
            'success' => true,
            'publishable_key' => $this->stripeService->getPublishableKey(),
        ]);
    }
}
