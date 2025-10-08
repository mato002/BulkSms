<?php

namespace App\Services;

use App\Models\Sms;
use App\Models\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $gatewayUrl;
    protected $apiKey;
    protected $username;

    public function __construct()
    {
        $this->gatewayUrl = config('sms.gateway_url');
        $this->apiKey = config('sms.api_key');
        $this->username = config('sms.username');
    }

    /**
     * Send SMS to a single recipient
     */
    public function sendSms(Client $client, string $recipient, string $message, string $senderId): array
    {
        try {
            // Check client balance
            if (!$client->hasSufficientBalance(0.75)) {
                return [
                    'status' => 'failed',
                    'message' => 'Insufficient balance',
                    'recipient' => $recipient,
                    'cost' => 0
                ];
            }

            // Create SMS record
            $sms = Sms::create([
                'client_id' => $client->id,
                'recipient' => $recipient,
                'message' => $message,
                'sender_id' => $senderId,
                'status' => 'pending',
                'cost' => 0.75
            ]);

            // Send to gateway
            $response = $this->callSmsGateway($recipient, $message, $senderId);

            if ($response['status'] === 200) {
                $sms->markAsSent($response['message_id']);
                $client->deductBalance(0.75);
                
                return [
                    'status' => 'success',
                    'message_id' => $response['message_id'],
                    'recipient' => $recipient,
                    'cost' => 0.75
                ];
            } else {
                $sms->markAsFailed();
                
                return [
                    'status' => 'failed',
                    'message' => $response['message'] ?? 'SMS sending failed',
                    'recipient' => $recipient,
                    'cost' => 0
                ];
            }
        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            
            return [
                'status' => 'failed',
                'message' => 'Internal server error',
                'recipient' => $recipient,
                'cost' => 0
            ];
        }
    }

    /**
     * Call SMS gateway
     */
    protected function callSmsGateway(string $recipient, string $message, string $senderId): array
    {
        try {
            // Determine which gateway to use based on sender ID
            if (in_array($senderId, config('sms.onfon_senders', []))) {
                return $this->callOnfonGateway($recipient, $message, $senderId);
            } elseif (in_array($senderId, config('sms.moja_senders', []))) {
                return $this->callMojaGateway($recipient, $message, $senderId);
            } else {
                return $this->callMobitechGateway($recipient, $message, $senderId);
            }
        } catch (\Exception $e) {
            Log::error('Gateway call failed: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'Gateway error'
            ];
        }
    }

    /**
     * Call Mobitech gateway
     */
    protected function callMobitechGateway(string $recipient, string $message, string $senderId): array
    {
        $data = [
            'api_key' => $this->apiKey,
            'username' => $this->username,
            'sender_id' => $senderId,
            'message' => $message,
            'phone' => $recipient
        ];

        $response = Http::timeout(30)->post($this->gatewayUrl, $data);
        
        if ($response->successful()) {
            $result = $response->json();
            return [
                'status' => 200,
                'message_id' => $result[0]['message_id'] ?? uniqid(),
                'message' => 'SMS sent successfully'
            ];
        }

        return [
            'status' => 500,
            'message' => 'Gateway request failed'
        ];
    }

    /**
     * Call OnfonMedia gateway
     */
    protected function callOnfonGateway(string $recipient, string $message, string $senderId): array
    {
        $data = [
            'ApiKey' => config('sms.onfon_api_key'),
            'ClientId' => config('sms.onfon_client_id'),
            'IsUnicode' => 1,
            'IsFlash' => 1,
            'SenderId' => $senderId,
            'MessageParameters' => [
                [
                    'Number' => $recipient,
                    'Text' => $message
                ]
            ]
        ];

        $response = Http::timeout(30)->post(config('sms.onfon_url'), $data);
        
        if ($response->successful()) {
            $result = $response->json();
            if ($result['ErrorCode'] == 0) {
                return [
                    'status' => 200,
                    'message_id' => uniqid(),
                    'message' => 'SMS sent successfully'
                ];
            }
        }

        return [
            'status' => 500,
            'message' => 'OnfonMedia gateway failed'
        ];
    }

    /**
     * Call MojaSMS gateway
     */
    protected function callMojaGateway(string $recipient, string $message, string $senderId): array
    {
        $data = [
            'from' => $senderId,
            'to' => $recipient,
            'message' => $message
        ];

        $response = Http::timeout(30)->post(config('sms.moja_url'), $data);
        
        if ($response->successful()) {
            $result = $response->json();
            if (isset($result['id'])) {
                return [
                    'status' => 200,
                    'message_id' => $result['id'],
                    'message' => 'SMS sent successfully'
                ];
            }
        }

        return [
            'status' => 500,
            'message' => 'MojaSMS gateway failed'
        ];
    }

    /**
     * Check SMS delivery status
     */
    public function checkDeliveryStatus(string $messageId): array
    {
        try {
            $response = Http::timeout(30)->post(config('sms.delivery_url'), [
                'api_key' => $this->apiKey,
                'username' => $this->username,
                'message_id' => $messageId
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'status' => 'success',
                    'delivery_status' => $result['status'] ?? 'unknown',
                    'delivered_at' => $result['delivered_at'] ?? null
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Failed to check delivery status'
            ];
        } catch (\Exception $e) {
            Log::error('Delivery status check failed: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Internal server error'
            ];
        }
    }

    /**
     * Get account balance
     */
    public function getAccountBalance(): array
    {
        try {
            $response = Http::timeout(30)->post(config('sms.balance_url'), [
                'api_key' => $this->apiKey,
                'username' => $this->username
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'status' => 'success',
                    'balance' => $result['balance'] ?? 0
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Failed to get balance'
            ];
        } catch (\Exception $e) {
            Log::error('Balance check failed: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Internal server error'
            ];
        }
    }
}
