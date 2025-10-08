<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OnfonWalletService
{
    /**
     * Get balance from Onfon Media wallet
     */
    public function getBalance(Client $client): array
    {
        $credentials = $this->getOnfonCredentials($client);
        
        if (!$credentials) {
            return [
                'success' => false,
                'message' => 'Onfon credentials not configured'
            ];
        }

        try {
            $response = Http::timeout(30)
                ->withOptions(['verify' => false]) // Disable SSL verification for development
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'AccessKey' => $credentials['access_key_header'] ?? '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB',
                ])
                ->post('https://api.onfonmedia.co.ke/v1/balance/GetBalance', [
                    'ApiKey' => $credentials['api_key'],
                    'ClientId' => $credentials['client_id'],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Onfon returns: {"Balance": 1234.56, "Units": 1234, "Currency": "KES"}
                return [
                    'success' => true,
                    'balance' => $data['Balance'] ?? 0,
                    'units' => $data['Units'] ?? 0,
                    'currency' => $data['Currency'] ?? 'KES',
                ];
            }

            Log::error('Onfon balance fetch failed', [
                'client_id' => $client->id,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to fetch balance from Onfon: ' . ($response->json()['Message'] ?? 'Unknown error')
            ];

        } catch (\Exception $e) {
            Log::error('Onfon balance exception', [
                'client_id' => $client->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sync balance from Onfon to client
     */
    public function syncBalance(Client $client): array
    {
        $result = $this->getBalance($client);

        if (!$result['success']) {
            return $result;
        }

        $oldBalance = $client->onfon_balance ?? 0;
        $newBalance = $result['balance'];
        $difference = $newBalance - $oldBalance;

        // Update client's Onfon balance
        $client->onfon_balance = $newBalance;
        $client->onfon_last_sync = now();
        $client->save();

        Log::info('Onfon balance synced', [
            'client_id' => $client->id,
            'old_balance' => $oldBalance,
            'new_balance' => $newBalance,
            'difference' => $difference
        ]);

        return [
            'success' => true,
            'old_balance' => $oldBalance,
            'new_balance' => $newBalance,
            'difference' => $difference,
            'units' => $result['units'] ?? 0,
        ];
    }

    /**
     * Test Onfon connection
     */
    public function testConnection(Client $client): array
    {
        $credentials = $this->getOnfonCredentials($client);
        
        if (!$credentials) {
            return [
                'success' => false,
                'message' => 'Onfon credentials not configured'
            ];
        }

        try {
            $response = Http::timeout(30)
                ->withOptions(['verify' => false]) // Disable SSL verification for development
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'AccessKey' => $credentials['access_key_header'] ?? '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB',
                ])
                ->post('https://api.onfonmedia.co.ke/v1/balance/GetBalance', [
                    'ApiKey' => $credentials['api_key'],
                    'ClientId' => $credentials['client_id'],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'message' => '✅ Connection successful! Onfon wallet is accessible.',
                    'balance' => $data['Balance'] ?? 0,
                    'units' => $data['Units'] ?? 0,
                    'currency' => $data['Currency'] ?? 'KES',
                ];
            }

            return [
                'success' => false,
                'message' => '❌ Connection failed: ' . ($response->json()['Message'] ?? 'Unknown error')
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '❌ Connection error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get transaction history from Onfon
     */
    public function getTransactionHistory(Client $client, ?string $fromDate = null, ?string $toDate = null): array
    {
        $credentials = $this->getOnfonCredentials($client);
        
        if (!$credentials) {
            return [
                'success' => false,
                'message' => 'Onfon credentials not configured'
            ];
        }

        try {
            $fromDate = $fromDate ?? now()->subDays(30)->format('Y-m-d');
            $toDate = $toDate ?? now()->format('Y-m-d');

            $response = Http::timeout(30)
                ->withOptions(['verify' => false]) // Disable SSL verification for development
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'AccessKey' => $credentials['access_key_header'] ?? '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB',
                ])
                ->post('https://api.onfonmedia.co.ke/v1/reports/GetTransactionHistory', [
                    'ApiKey' => $credentials['api_key'],
                    'ClientId' => $credentials['client_id'],
                    'FromDate' => $fromDate,
                    'ToDate' => $toDate,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'transactions' => $data['Transactions'] ?? []
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to fetch transactions: ' . ($response->json()['Message'] ?? 'Unknown error')
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get Onfon credentials from client settings
     */
    private function getOnfonCredentials(Client $client): ?array
    {
        $settings = $client->settings ?? [];
        
        if (!isset($settings['onfon_credentials'])) {
            return null;
        }

        $credentials = $settings['onfon_credentials'];

        if (empty($credentials['api_key']) || empty($credentials['client_id'])) {
            return null;
        }

        return $credentials;
    }
}
