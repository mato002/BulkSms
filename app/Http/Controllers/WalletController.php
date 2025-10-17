<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    protected $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->middleware('auth');
        $this->mpesaService = $mpesaService;
    }

    /**
     * Display wallet page with balance and transaction history
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $client = $user->client; // Get the actual client model
        
        if (!$client) {
            return redirect()->route('dashboard')->with('error', 'No client associated with your account');
        }
        
        // Get transaction history with pagination
        $perPage = $request->get('per_page', 20);
        $transactions = WalletTransaction::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Get summary stats
        $stats = [
            'total_topups' => WalletTransaction::where('client_id', $client->id)
                ->where('type', 'credit')
                ->where('status', 'completed')
                ->sum('amount'),
            'total_spent' => WalletTransaction::where('client_id', $client->id)
                ->where('type', 'debit')
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_topups' => WalletTransaction::where('client_id', $client->id)
                ->where('type', 'credit')
                ->whereIn('status', ['pending', 'processing'])
                ->count(),
        ];

        return view('wallet.index', compact('client', 'transactions', 'stats'));
    }

    /**
     * Show top-up page
     */
    public function topup()
    {
        $user = Auth::user();
        $client = $user->client; // Get the actual client model
        
        if (!$client) {
            return redirect()->route('dashboard')->with('error', 'No client associated with your account');
        }
        
        return view('wallet.topup', compact('client'));
    }

    /**
     * Initiate top-up
     */
    public function initiateTopup(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100|max:50000',
            'payment_method' => 'required|string|in:mpesa,manual',
            'phone_number' => 'required_if:payment_method,mpesa|string|regex:/^254[0-9]{9}$/',
        ]);

        $user = Auth::user();
        $client = $user->client; // Get the actual client model
        
        if (!$client) {
            return back()->with('error', 'No client associated with your account');
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

        // Handle M-Pesa payment
        if ($paymentMethod === 'mpesa') {
            try {
                $result = $this->mpesaService->initiateSTKPush(
                    $phoneNumber,
                    $amount,
                    $transactionRef,
                    "Wallet top-up - {$client->name}"
                );

                if ($result['success']) {
                    $transaction->checkout_request_id = $result['checkout_request_id'];
                    $transaction->status = 'processing';
                    $transaction->save();

                    return redirect()->route('wallet.status', $transactionRef)
                        ->with('success', 'Please check your phone for M-Pesa prompt');
                } else {
                    $transaction->markAsFailed($result['message']);
                    
                    return back()->with('error', $result['message'] ?? 'Failed to initiate M-Pesa payment');
                }
            } catch (\Exception $e) {
                $transaction->markAsFailed($e->getMessage());
                
                return back()->with('error', 'Failed to initiate payment. Please try again.');
            }
        }

        // Manual payment
        return redirect()->route('wallet.status', $transactionRef)
            ->with('info', 'Manual top-up request created. Please contact support for payment instructions.');
    }

    /**
     * Check transaction status
     */
    public function status($transactionRef)
    {
        $user = Auth::user();
        $client = $user->client; // Get the actual client model
        
        if (!$client) {
            return redirect()->route('dashboard')->with('error', 'No client associated with your account');
        }
        
        $transaction = WalletTransaction::where('client_id', $client->id)
            ->where('transaction_ref', $transactionRef)
            ->firstOrFail();

        return view('wallet.status', compact('transaction'));
    }

    /**
     * Sync Onfon balance for current user
     */
    public function syncOnfonBalance(\App\Services\OnfonWalletService $walletService)
    {
        $user = Auth::user();
        $client = $user->client;
        
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'No client associated with your account'
            ], 400);
        }

        $result = $walletService->syncBalance($client);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Balance synced successfully!',
                'data' => [
                    'old_balance' => $result['old_balance'],
                    'new_balance' => $result['new_balance'],
                    'difference' => $result['difference'],
                    'last_sync' => $client->fresh()->onfon_last_sync?->diffForHumans() ?? 'Just now'
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to sync balance from Onfon'
        ], 400);
    }

    /**
     * Get Onfon balance for current user
     */
    public function getOnfonBalance(\App\Services\OnfonWalletService $walletService)
    {
        $user = Auth::user();
        $client = $user->client;
        
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'No client associated with your account'
            ], 400);
        }

        $result = $walletService->getBalance($client);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'balance' => $result['balance'],
                'currency' => $result['currency'] ?? 'KES',
                'units' => $result['units'] ?? 0,
                'last_sync' => $client->onfon_last_sync ? $client->onfon_last_sync->diffForHumans() : 'Never'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to fetch balance'
        ], 400);
    }

    /**
     * Export transactions to CSV
     */
    public function exportTransactions(Request $request)
    {
        $user = Auth::user();
        $client = $user->client; // Get the actual client model
        
        if (!$client) {
            return redirect()->route('dashboard')->with('error', 'No client associated with your account');
        }

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $type = $request->get('type');
        $status = $request->get('status');

        $query = WalletTransaction::where('client_id', $client->id);

        if ($fromDate || $toDate) {
            $query->dateRange($fromDate, $toDate);
        }

        if ($type) {
            $query->type($type);
        }

        if ($status) {
            $query->status($status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

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

        return response()->stream($callback, 200, $headers);
    }
}

