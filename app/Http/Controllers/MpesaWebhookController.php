<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
use App\Models\Client;
use App\Services\MpesaService;
use App\Services\WebhookService;
use App\Mail\TopupConfirmation;
use App\Mail\TopupFailed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MpesaWebhookController extends Controller
{
    protected $mpesaService;
    protected $webhookService;

    public function __construct(MpesaService $mpesaService, WebhookService $webhookService)
    {
        $this->mpesaService = $mpesaService;
        $this->webhookService = $webhookService;
    }

    /**
     * Handle M-Pesa payment callback
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        // Log the raw callback
        Log::info('M-Pesa callback received', [
            'body' => $request->all(),
            'ip' => $request->ip()
        ]);

        // Process the callback
        $result = $this->mpesaService->handleCallback($request->all());

        if ($result['success']) {
            // Payment was successful
            $this->processSuccessfulPayment($result);
        } else {
            // Payment failed or was cancelled
            $this->processFailedPayment($result);
        }

        // Always respond with success to M-Pesa
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted'
        ]);
    }

    /**
     * Handle M-Pesa timeout callback
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function timeout(Request $request)
    {
        Log::warning('M-Pesa timeout callback received', [
            'body' => $request->all(),
            'ip' => $request->ip()
        ]);

        // Process timeout
        $result = $this->mpesaService->handleTimeout($request->all());

        if (isset($result['checkout_request_id'])) {
            $this->processTimeout($result['checkout_request_id']);
        }

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted'
        ]);
    }

    /**
     * Process successful payment
     *
     * @param array $data
     * @return void
     */
    protected function processSuccessfulPayment($data)
    {
        try {
            // Find transaction by checkout request ID
            $transaction = WalletTransaction::where('checkout_request_id', $data['checkout_request_id'])
                ->where('status', '!=', 'completed')
                ->first();

            if (!$transaction) {
                Log::warning('M-Pesa callback: Transaction not found', [
                    'checkout_request_id' => $data['checkout_request_id']
                ]);
                return;
            }

            // Update transaction
            $transaction->mpesa_receipt = $data['mpesa_receipt'];
            $transaction->status = 'completed';
            $transaction->completed_at = now();
            
            // Store additional metadata
            $metadata = $transaction->metadata ?? [];
            $metadata['mpesa_phone'] = $data['phone_number'] ?? null;
            $metadata['mpesa_transaction_date'] = $data['transaction_date'] ?? null;
            $metadata['result_desc'] = $data['result_desc'] ?? null;
            $transaction->metadata = $metadata;
            
            $transaction->save();

            // Add balance to client
            $client = $transaction->client;
            if ($client) {
                $oldBalance = $client->balance;
                $client->addBalance($transaction->amount, false);
                $newBalance = $client->balance;
                
                // Auto-activate client after successful payment if they were inactive
                if (!$client->status) {
                    $client->update(['status' => true]);
                    
                    Log::info('Client auto-activated after M-Pesa payment', [
                        'client_id' => $client->id,
                        'transaction_id' => $transaction->id,
                        'amount' => $transaction->amount,
                        'mpesa_receipt' => $data['mpesa_receipt'],
                    ]);
                }
                
                Log::info('M-Pesa payment processed successfully', [
                    'transaction_id' => $transaction->id,
                    'client_id' => $client->id,
                    'amount' => $transaction->amount,
                    'mpesa_receipt' => $data['mpesa_receipt'],
                    'old_balance' => $oldBalance,
                    'new_balance' => $newBalance
                ]);

                // Send balance updated webhook
                $this->webhookService->sendBalanceUpdated(
                    $client,
                    $oldBalance,
                    $newBalance,
                    $transaction->transaction_ref
                );

                // Send top-up completed webhook
                $this->webhookService->sendTopupCompleted($client, [
                    'transaction_id' => $transaction->transaction_ref,
                    'amount' => $transaction->amount,
                    'mpesa_receipt' => $data['mpesa_receipt'],
                    'payment_method' => 'mpesa',
                    'status' => 'completed',
                    'completed_at' => now()->toIso8601String()
                ]);

                // Send top-up confirmation email
                try {
                    if ($client->contact && filter_var($client->contact, FILTER_VALIDATE_EMAIL)) {
                        Mail::to($client->contact)->send(new TopupConfirmation($client, $transaction));
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send top-up confirmation email', [
                        'client_id' => $client->id,
                        'transaction_id' => $transaction->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error processing successful M-Pesa payment', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    /**
     * Process failed payment
     *
     * @param array $data
     * @return void
     */
    protected function processFailedPayment($data)
    {
        try {
            // Find transaction
            $transaction = WalletTransaction::where('checkout_request_id', $data['checkout_request_id'])
                ->where('status', '!=', 'completed')
                ->first();

            if (!$transaction) {
                Log::warning('M-Pesa failed callback: Transaction not found', [
                    'checkout_request_id' => $data['checkout_request_id']
                ]);
                return;
            }

            // Mark as failed
            $transaction->status = 'failed';
            
            $metadata = $transaction->metadata ?? [];
            $metadata['failure_reason'] = $data['result_desc'] ?? 'Payment failed';
            $metadata['result_code'] = $data['result_code'] ?? 'unknown';
            $transaction->metadata = $metadata;
            
            $transaction->save();

            Log::info('M-Pesa payment failed', [
                'transaction_id' => $transaction->id,
                'client_id' => $transaction->client_id,
                'amount' => $transaction->amount,
                'reason' => $data['result_desc'] ?? 'Unknown'
            ]);

            // Send top-up failed webhook
            $client = $transaction->client;
            if ($client) {
                $this->webhookService->sendTopupFailed($client, [
                    'transaction_id' => $transaction->transaction_ref,
                    'amount' => $transaction->amount,
                    'payment_method' => 'mpesa',
                    'status' => 'failed',
                    'reason' => $data['result_desc'] ?? 'Payment failed',
                    'failed_at' => now()->toIso8601String()
                ]);

                // Send top-up failed email
                try {
                    if ($client->contact && filter_var($client->contact, FILTER_VALIDATE_EMAIL)) {
                        Mail::to($client->contact)->send(new TopupFailed(
                            $client,
                            $transaction,
                            $data['result_desc'] ?? 'Payment failed'
                        ));
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send top-up failed email', [
                        'client_id' => $client->id,
                        'transaction_id' => $transaction->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error processing failed M-Pesa payment', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    /**
     * Process timeout
     *
     * @param string $checkoutRequestId
     * @return void
     */
    protected function processTimeout($checkoutRequestId)
    {
        try {
            $transaction = WalletTransaction::where('checkout_request_id', $checkoutRequestId)
                ->where('status', '!=', 'completed')
                ->first();

            if ($transaction) {
                $transaction->status = 'failed';
                
                $metadata = $transaction->metadata ?? [];
                $metadata['failure_reason'] = 'Payment request timed out';
                $metadata['result_code'] = 'timeout';
                $transaction->metadata = $metadata;
                
                $transaction->save();

                Log::info('M-Pesa payment timed out', [
                    'transaction_id' => $transaction->id,
                    'client_id' => $transaction->client_id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error processing M-Pesa timeout', [
                'error' => $e->getMessage(),
                'checkout_request_id' => $checkoutRequestId
            ]);
        }
    }
}

