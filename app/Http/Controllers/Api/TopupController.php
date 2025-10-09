<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class TopupController extends Controller
{
    /**
     * Initiate a top-up request
     *
     * @param Request $request
     * @param int $company_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiateTopup(Request $request, $company_id)
    {
        $client = $request->user(); // Set by ApiAuth middleware

        // Validate request
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:100|max:50000',
            'payment_method' => 'required|string|in:mpesa,bank,manual',
            'phone_number' => 'required_if:payment_method,mpesa|string|regex:/^254[0-9]{9}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $amount = $request->amount;
        $paymentMethod = $request->payment_method;
        $phoneNumber = $request->phone_number;

        // Generate transaction reference
        $transactionRef = WalletTransaction::generateTransactionRef();

        // Create transaction record
        $transaction = WalletTransaction::create([
            'client_id' => $client->id,
            'type' => 'credit',
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_phone' => $phoneNumber,
            'transaction_ref' => $transactionRef,
            'status' => 'pending',
            'description' => "Top-up via {$paymentMethod}",
        ]);

        Log::info('Top-up initiated', [
            'client_id' => $client->id,
            'transaction_id' => $transaction->id,
            'amount' => $amount,
            'method' => $paymentMethod
        ]);

        // Handle different payment methods
        if ($paymentMethod === 'mpesa') {
            // M-Pesa STK Push will be handled by MpesaService
            // For now, we'll check if MpesaService exists
            if (class_exists('\App\Services\MpesaService')) {
                try {
                    $mpesaService = app(\App\Services\MpesaService::class);
                    $result = $mpesaService->initiateSTKPush(
                        $phoneNumber,
                        $amount,
                        $transactionRef
                    );

                    if ($result['success']) {
                        $transaction->checkout_request_id = $result['checkout_request_id'];
                        $transaction->status = 'processing';
                        $transaction->save();

                        return response()->json([
                            'status' => 'pending',
                            'message' => 'Please check your phone for M-Pesa prompt',
                            'transaction_id' => $transactionRef,
                            'checkout_request_id' => $result['checkout_request_id'],
                            'amount' => $amount,
                        ]);
                    } else {
                        $transaction->markAsFailed($result['message']);
                        
                        return response()->json([
                            'status' => 'error',
                            'message' => $result['message'] ?? 'Failed to initiate M-Pesa payment',
                            'transaction_id' => $transactionRef,
                        ], 400);
                    }
                } catch (\Exception $e) {
                    $transaction->markAsFailed($e->getMessage());
                    Log::error('M-Pesa initiation error', [
                        'error' => $e->getMessage(),
                        'transaction_id' => $transaction->id
                    ]);

                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to initiate payment. Please try again.',
                        'transaction_id' => $transactionRef,
                    ], 500);
                }
            } else {
                // M-Pesa service not available yet, return pending status
                return response()->json([
                    'status' => 'pending',
                    'message' => 'M-Pesa integration in progress. Please use manual top-up for now.',
                    'transaction_id' => $transactionRef,
                    'amount' => $amount,
                    'instructions' => 'Please send M-Pesa to Paybill: XXXXX, Account: ' . $client->id,
                ]);
            }
        } elseif ($paymentMethod === 'manual') {
            // Manual top-up - requires admin approval
            return response()->json([
                'status' => 'pending',
                'message' => 'Manual top-up request created. Please contact support for payment instructions.',
                'transaction_id' => $transactionRef,
                'amount' => $amount,
                'contact' => 'support@yourplatform.com',
            ]);
        }

        return response()->json([
            'status' => 'pending',
            'message' => 'Top-up request created',
            'transaction_id' => $transactionRef,
            'amount' => $amount,
        ]);
    }

    /**
     * Check top-up status
     *
     * @param Request $request
     * @param int $company_id
     * @param string $transaction_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkTopupStatus(Request $request, $company_id, $transaction_id)
    {
        $client = $request->user();

        // Find transaction
        $transaction = WalletTransaction::where('client_id', $client->id)
            ->where('transaction_ref', $transaction_id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found'
            ], 404);
        }

        return response()->json([
            'transaction_id' => $transaction->transaction_ref,
            'status' => $transaction->status,
            'amount' => $transaction->amount,
            'payment_method' => $transaction->payment_method,
            'mpesa_receipt' => $transaction->mpesa_receipt,
            'created_at' => $transaction->created_at->toISOString(),
            'completed_at' => $transaction->completed_at ? $transaction->completed_at->toISOString() : null,
        ]);
    }

    /**
     * Get transaction history
     *
     * @param Request $request
     * @param int $company_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransactions(Request $request, $company_id)
    {
        $client = $request->user();

        // Get query parameters
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $type = $request->get('type');
        $status = $request->get('status');
        $perPage = min($request->get('per_page', 20), 100);

        // Build query
        $query = WalletTransaction::where('client_id', $client->id);

        // Apply filters
        if ($fromDate || $toDate) {
            $query->dateRange($fromDate, $toDate);
        }

        if ($type) {
            $query->type($type);
        }

        if ($status) {
            $query->status($status);
        }

        // Get paginated results
        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => $transactions->map(function ($txn) {
                return [
                    'id' => $txn->id,
                    'transaction_id' => $txn->transaction_ref,
                    'type' => $txn->type,
                    'amount' => $txn->amount,
                    'payment_method' => $txn->payment_method,
                    'mpesa_receipt' => $txn->mpesa_receipt,
                    'status' => $txn->status,
                    'description' => $txn->description,
                    'created_at' => $txn->created_at->toISOString(),
                    'completed_at' => $txn->completed_at ? $txn->completed_at->toISOString() : null,
                ];
            }),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'last_page' => $transactions->lastPage(),
            ]
        ]);
    }

    /**
     * Check if balance is sufficient
     *
     * @param Request $request
     * @param int $company_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkSufficientBalance(Request $request, $company_id)
    {
        $client = $request->user();

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'is_units' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $amount = $request->amount;
        $isUnits = $request->boolean('is_units', false);

        $hasSufficient = $client->hasSufficientBalance($amount, $isUnits);

        return response()->json([
            'sufficient' => $hasSufficient,
            'current_balance' => $client->balance,
            'current_units' => $client->getBalanceInUnits(),
            'required_amount' => $isUnits ? $client->unitsToKsh($amount) : $amount,
            'message' => $hasSufficient 
                ? 'Balance is sufficient' 
                : 'Insufficient balance. Please top up.',
        ]);
    }

    /**
     * Export transaction history to CSV
     *
     * @param Request $request
     * @param int $company_id
     * @return \Illuminate\Http\Response
     */
    public function exportTransactionsCSV(Request $request, $company_id)
    {
        $client = $request->user();

        // Get query parameters (same as getTransactions)
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $type = $request->get('type');
        $status = $request->get('status');

        // Build query
        $query = WalletTransaction::where('client_id', $client->id);

        // Apply filters
        if ($fromDate || $toDate) {
            $query->dateRange($fromDate, $toDate);
        }

        if ($type) {
            $query->type($type);
        }

        if ($status) {
            $query->status($status);
        }

        // Get all results (no pagination for CSV)
        $transactions = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = "transactions_{$client->sender_id}_" . date('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');

            // CSV Header
            fputcsv($file, [
                'Transaction ID',
                'Type',
                'Amount (KES)',
                'Payment Method',
                'M-Pesa Receipt',
                'Status',
                'Description',
                'Date',
                'Completed At'
            ]);

            // CSV Rows
            foreach ($transactions as $txn) {
                fputcsv($file, [
                    $txn->transaction_ref,
                    ucfirst($txn->type),
                    number_format($txn->amount, 2),
                    ucfirst($txn->payment_method ?? 'N/A'),
                    $txn->mpesa_receipt ?? 'N/A',
                    ucfirst($txn->status),
                    $txn->description ?? '',
                    $txn->created_at->format('Y-m-d H:i:s'),
                    $txn->completed_at ? $txn->completed_at->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}

