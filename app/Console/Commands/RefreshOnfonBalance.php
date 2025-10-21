<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\SmsService;
use App\Models\Client;
use App\Models\AdminSetting;
use App\Models\AlertPhoneNumber;

class RefreshOnfonBalance extends Command
{
    protected $signature = 'onfon:refresh-balance';
    protected $description = 'Refresh Onfon balance and send low balance alerts';

    public function handle()
    {
        $this->info('Refreshing Onfon balance...');

        try {
            // Get credentials from config (uses .env values)
            $apiKey = config('sms.gateways.onfon.api_key');
            $clientId = config('sms.gateways.onfon.client_id');

            // Skip if credentials are not configured
            if (empty($apiKey) || $apiKey === 'your_onfon_api_key_here') {
                $this->warn('⚠️ Onfon credentials not configured in .env file');
                return 1;
            }

            // Get current balance from Onfon API
            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'AccessKey' => '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB',
                ])
                ->get('https://api.onfonmedia.co.ke/v1/sms/Balance', [
                    'ApiKey' => $apiKey,
                    'ClientId' => $clientId,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['Data'][0]['Credits'])) {
                    $currentBalance = (float) $data['Data'][0]['Credits'];
                    
                    // Store balance in cache for dashboard (15 minutes)
                    cache()->put('onfon_system_balance', $currentBalance, now()->addMinutes(15));
                    
                    $this->info("✅ Balance refreshed: {$currentBalance} units");
                    
                    // Check for low balance (get from admin settings)
                    $threshold = AdminSetting::get('low_balance_threshold', 1000);
                    
                    if ($currentBalance < $threshold) {
                        $this->warn("⚠️ Low balance detected: {$currentBalance} units (threshold: {$threshold})");
                        
                        // Send low balance alert
                        $this->sendLowBalanceAlert($currentBalance, $threshold);
                    }
                    
                    return 0;
                }
            }

            $this->error('❌ Failed to fetch balance from Onfon API');
            Log::error('Onfon balance refresh failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return 1;

        } catch (\Exception $e) {
            $this->error('❌ Exception: ' . $e->getMessage());
            Log::error('Onfon balance refresh exception: ' . $e->getMessage());
            return 1;
        }
    }

    private function sendLowBalanceAlert($currentBalance, $threshold)
    {
        try {
            // Get all active phone numbers
            $phoneNumbers = AlertPhoneNumber::getActiveNumbers();
            
            if ($phoneNumbers->count() == 0) {
                $this->warn("⚠️ No active phone numbers configured for alerts");
                return;
            }

            // Get PRADY_TECH client for sending SMS
            $pradyClient = Client::where('sender_id', 'PRADY_TECH')->first();
            
            if (!$pradyClient) {
                $this->warn("⚠️ PRADY_TECH client not found");
                return;
            }

            $message = "⚠️ LOW ONFON BALANCE ALERT\n\n";
            $message .= "Current Balance: {$currentBalance} units\n";
            $message .= "Threshold: {$threshold} units\n";
            $message .= "Time: " . now()->format('Y-m-d H:i:s') . "\n\n";
            $message .= "Please top up your Onfon account to continue SMS services.";

            $successCount = 0;
            $failCount = 0;

            foreach ($phoneNumbers as $phoneNumber) {
                $result = $this->sendOnfonSms($phoneNumber->phone_number, $message);
                
                if ($result['success']) {
                    $successCount++;
                    $displayName = $phoneNumber->name ?: $phoneNumber->phone_number;
                    $this->info("📱 Low balance SMS sent to {$displayName}: {$phoneNumber->phone_number}");
                    Log::info('Low balance SMS sent', [
                        'phone' => $phoneNumber->phone_number,
                        'name' => $phoneNumber->name,
                        'balance' => $currentBalance,
                        'threshold' => $threshold
                    ]);
                } else {
                    $failCount++;
                    $this->error("❌ Failed to send SMS to {$phoneNumber->phone_number}: " . ($result['message'] ?? 'Unknown error'));
                }
            }

            $this->info("📊 SMS Alert Summary: {$successCount} sent, {$failCount} failed");

        } catch (\Exception $e) {
            $this->error("❌ Failed to send low balance SMS: " . $e->getMessage());
            Log::error('Low balance SMS failed: ' . $e->getMessage());
        }
    }

    private function sendOnfonSms($phone, $message)
    {
        try {
            // Get credentials from config
            $apiKey = config('sms.gateways.onfon.api_key');
            $clientId = config('sms.gateways.onfon.client_id');

            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'AccessKey' => '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB',
                ])
                ->post('https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS', [
                    'ApiKey' => $apiKey,
                    'ClientId' => $clientId,
                    'IsUnicode' => 1,
                    'IsFlash' => 0,
                    'SenderId' => 'PRADY_TECH',
                    'MessageParameters' => [
                        [
                            'Number' => $phone,
                            'Text' => $message,
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message_id' => $data['MessageId'] ?? uniqid(),
                    'message' => 'SMS sent successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to send SMS: ' . ($response->json()['Message'] ?? 'Unknown error')
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'SMS sending error: ' . $e->getMessage()
            ];
        }
    }
}
