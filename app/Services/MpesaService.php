<?php

namespace App\Services;

use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    protected $consumerKey;
    protected $consumerSecret;
    protected $passkey;
    protected $shortcode;
    protected $env;
    protected $callbackUrl;
    protected $timeoutUrl;
    protected $urls;

    public function __construct()
    {
        $this->consumerKey = config('mpesa.consumer_key');
        $this->consumerSecret = config('mpesa.consumer_secret');
        $this->passkey = config('mpesa.passkey');
        $this->shortcode = config('mpesa.shortcode');
        $this->env = config('mpesa.env', 'sandbox');
        $this->callbackUrl = config('mpesa.callback_url');
        $this->timeoutUrl = config('mpesa.timeout_url');
        $this->urls = config('mpesa.urls')[$this->env];
    }

    /**
     * Get M-Pesa access token
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        // Check if token is cached
        $cacheKey = 'mpesa_access_token_' . $this->env;
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->withOptions(['verify' => false]) // Disable SSL verification for development
                ->get($this->urls['auth']);

            if ($response->successful()) {
                $data = $response->json();
                $accessToken = $data['access_token'];
                $expiresIn = $data['expires_in'] ?? 3600;

                // Cache token for slightly less than expiry time
                Cache::put($cacheKey, $accessToken, now()->addSeconds($expiresIn - 60));

                return $accessToken;
            }

            Log::error('M-Pesa auth failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('M-Pesa auth exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Generate password for STK Push
     *
     * @param string $timestamp
     * @return string
     */
    protected function generatePassword($timestamp = null)
    {
        $timestamp = $timestamp ?? date('YmdHis');
        return base64_encode($this->shortcode . $this->passkey . $timestamp);
    }

    /**
     * Initiate STK Push
     *
     * @param string $phoneNumber - Phone number in 254XXXXXXXXX format
     * @param float $amount - Amount to charge
     * @param string $accountReference - Unique transaction reference
     * @param string $transactionDesc - Optional description
     * @return array
     */
    public function initiateSTKPush($phoneNumber, $amount, $accountReference, $transactionDesc = null)
    {
        // Validate inputs
        if (empty($this->consumerKey) || empty($this->consumerSecret) || empty($this->passkey)) {
            return [
                'success' => false,
                'message' => 'M-Pesa credentials not configured. Please contact administrator.'
            ];
        }

        // Get access token
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return [
                'success' => false,
                'message' => 'Failed to authenticate with M-Pesa API'
            ];
        }

        // Format phone number (ensure it starts with 254)
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);

        // Generate timestamp and password
        $timestamp = date('YmdHis');
        $password = $this->generatePassword($timestamp);

        // Prepare request payload
        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => config('mpesa.transaction_type', 'CustomerPayBillOnline'),
            'Amount' => (int) $amount,
            'PartyA' => $phoneNumber, // Phone number sending money
            'PartyB' => $this->shortcode, // Organization receiving money
            'PhoneNumber' => $phoneNumber, // Phone number to receive STK push
            'CallBackURL' => $this->callbackUrl,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc ?? 'Top-up payment'
        ];

        try {
            $response = Http::withToken($accessToken)
                ->withOptions(['verify' => false]) // Disable SSL verification for development
                ->post($this->urls['stk_push'], $payload);

            $data = $response->json();

            Log::info('M-Pesa STK Push initiated', [
                'phone' => $phoneNumber,
                'amount' => $amount,
                'reference' => $accountReference,
                'response' => $data
            ]);

            // Check response
            if ($response->successful() && isset($data['ResponseCode']) && $data['ResponseCode'] == '0') {
                return [
                    'success' => true,
                    'message' => $data['CustomerMessage'] ?? 'STK Push sent successfully',
                    'checkout_request_id' => $data['CheckoutRequestID'],
                    'merchant_request_id' => $data['MerchantRequestID'],
                    'response_code' => $data['ResponseCode']
                ];
            }

            return [
                'success' => false,
                'message' => $data['errorMessage'] ?? $data['ResponseDescription'] ?? 'Failed to initiate payment',
                'response_code' => $data['ResponseCode'] ?? $data['errorCode'] ?? 'unknown'
            ];

        } catch (\Exception $e) {
            Log::error('M-Pesa STK Push exception', [
                'error' => $e->getMessage(),
                'phone' => $phoneNumber,
                'amount' => $amount
            ]);

            return [
                'success' => false,
                'message' => 'Failed to connect to M-Pesa. Please try again.'
            ];
        }
    }

    /**
     * Query STK Push status
     *
     * @param string $checkoutRequestId
     * @return array
     */
    public function querySTKStatus($checkoutRequestId)
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return [
                'success' => false,
                'message' => 'Failed to authenticate with M-Pesa API'
            ];
        }

        $timestamp = date('YmdHis');
        $password = $this->generatePassword($timestamp);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId
        ];

        try {
            $response = Http::withToken($accessToken)
                ->withOptions(['verify' => false]) // Disable SSL verification for development
                ->post($this->urls['stk_query'], $payload);

            $data = $response->json();

            Log::info('M-Pesa STK Query response', ['data' => $data]);

            if ($response->successful() && isset($data['ResponseCode'])) {
                return [
                    'success' => true,
                    'result_code' => $data['ResultCode'] ?? null,
                    'result_desc' => $data['ResultDesc'] ?? null,
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => $data['errorMessage'] ?? 'Failed to query status'
            ];

        } catch (\Exception $e) {
            Log::error('M-Pesa STK Query exception', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Failed to query payment status'
            ];
        }
    }

    /**
     * Format phone number to 254XXXXXXXXX
     *
     * @param string $phoneNumber
     * @return string
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        return PhoneNumber::withCountryCode($phoneNumber);
    }

    /**
     * Handle M-Pesa callback
     *
     * @param array $callbackData
     * @return array
     */
    public function handleCallback($callbackData)
    {
        try {
            Log::info('M-Pesa callback received', ['data' => $callbackData]);

            // Extract callback data
            $body = $callbackData['Body'] ?? $callbackData;
            $stkCallback = $body['stkCallback'] ?? null;

            if (!$stkCallback) {
                return [
                    'success' => false,
                    'message' => 'Invalid callback format'
                ];
            }

            $resultCode = $stkCallback['ResultCode'];
            $resultDesc = $stkCallback['ResultDesc'];
            $checkoutRequestId = $stkCallback['CheckoutRequestID'];

            // Check if payment was successful
            if ($resultCode == 0) {
                // Extract payment details
                $callbackMetadata = $stkCallback['CallbackMetadata']['Item'] ?? [];
                
                $metadata = [];
                foreach ($callbackMetadata as $item) {
                    $metadata[$item['Name']] = $item['Value'] ?? null;
                }

                return [
                    'success' => true,
                    'result_code' => $resultCode,
                    'result_desc' => $resultDesc,
                    'checkout_request_id' => $checkoutRequestId,
                    'merchant_request_id' => $stkCallback['MerchantRequestID'] ?? null,
                    'amount' => $metadata['Amount'] ?? 0,
                    'mpesa_receipt' => $metadata['MpesaReceiptNumber'] ?? null,
                    'transaction_date' => $metadata['TransactionDate'] ?? null,
                    'phone_number' => $metadata['PhoneNumber'] ?? null,
                ];
            } else {
                // Payment failed or was cancelled
                return [
                    'success' => false,
                    'result_code' => $resultCode,
                    'result_desc' => $resultDesc,
                    'checkout_request_id' => $checkoutRequestId,
                ];
            }

        } catch (\Exception $e) {
            Log::error('M-Pesa callback processing error', [
                'error' => $e->getMessage(),
                'data' => $callbackData
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process callback'
            ];
        }
    }

    /**
     * Handle timeout callback
     *
     * @param array $timeoutData
     * @return array
     */
    public function handleTimeout($timeoutData)
    {
        Log::warning('M-Pesa timeout received', ['data' => $timeoutData]);

        $body = $timeoutData['Body'] ?? $timeoutData;
        $stkCallback = $body['stkCallback'] ?? null;

        if ($stkCallback) {
            return [
                'success' => false,
                'result_code' => 'timeout',
                'checkout_request_id' => $stkCallback['CheckoutRequestID'] ?? null,
                'message' => 'Payment request timed out'
            ];
        }

        return [
            'success' => false,
            'message' => 'Timeout data invalid'
        ];
    }
}

