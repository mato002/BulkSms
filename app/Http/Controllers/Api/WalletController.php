<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OnfonWalletService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WalletController extends Controller
{
    public function __construct(private readonly OnfonWalletService $walletService)
    {
    }

    /**
     * Get wallet balance from Onfon Media
     */
    public function balance(Request $request): JsonResponse
    {
        $client = $request->user();
        $result = $this->walletService->getBalance($client);

        if ($result['success']) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'onfon_balance' => $result['balance'],
                    'currency' => $result['currency'] ?? 'KES',
                    'units' => $result['units'] ?? 0,
                    'local_balance' => $client->balance,
                    'local_units' => $client->getBalanceInUnits(),
                    'last_sync' => $client->onfon_last_sync?->toIso8601String(),
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => $result['message'] ?? 'Failed to fetch balance'
        ], 400);
    }

    /**
     * Sync balance from Onfon Media
     */
    public function sync(Request $request): JsonResponse
    {
        $client = $request->user();
        $result = $this->walletService->syncBalance($client);

        if ($result['success']) {
            return response()->json([
                'status' => 'success',
                'message' => 'Balance synchronized successfully',
                'data' => [
                    'old_balance' => $result['old_balance'],
                    'new_balance' => $result['new_balance'],
                    'difference' => $result['difference'],
                    'units' => $result['units'] ?? 0,
                    'synced_at' => $client->onfon_last_sync?->toIso8601String(),
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => $result['message'] ?? 'Failed to sync balance'
        ], 400);
    }

    /**
     * Test connection to Onfon Media
     */
    public function testConnection(Request $request): JsonResponse
    {
        $client = $request->user();
        $result = $this->walletService->testConnection($client);

        if ($result['success']) {
            return response()->json([
                'status' => 'success',
                'message' => $result['message'],
                'data' => [
                    'balance' => $result['balance'] ?? 0,
                    'currency' => $result['currency'] ?? 'KES',
                    'units' => $result['units'] ?? 0,
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => $result['message'] ?? 'Connection test failed'
        ], 400);
    }

    /**
     * Get transaction history
     */
    public function transactions(Request $request): JsonResponse
    {
        $client = $request->user();
        
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        
        $result = $this->walletService->getTransactionHistory($client, $fromDate, $toDate);

        if ($result['success']) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'transactions' => $result['transactions'],
                    'count' => count($result['transactions']),
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => $result['message'] ?? 'Failed to fetch transactions'
        ], 400);
    }

    /**
     * Check if sufficient balance exists for an operation
     */
    public function checkSufficient(Request $request): JsonResponse
    {
        $client = $request->user();
        $amount = $request->get('amount', 0);
        
        $result = $this->walletService->hasSufficientBalance($client, $amount);

        return response()->json([
            'status' => 'success',
            'data' => [
                'sufficient' => $result['sufficient'],
                'current_balance' => $result['current_balance'] ?? $client->balance,
                'required_amount' => $result['required_amount'] ?? $amount,
                'shortfall' => $result['shortfall'] ?? 0,
                'units_available' => $result['units_available'] ?? $client->getBalanceInUnits(),
            ]
        ]);
    }
}

