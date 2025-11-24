<?php

namespace App\Services\Payment;

use App\Models\Client;
use App\Models\Transaction;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaPaymentGateway
{
    private $consumerKey;
    private $consumerSecret;
    private $shortcode;
    private $passkey;
    private $environment;
    private $callbackUrl;

    public function __construct()
    {
        $this->consumerKey = config('mpesa.consumer_key');
        $this->consumerSecret = config('mpesa.consumer_secret');
        $this->shortcode = config('mpesa.shortcode');
        $this->passkey = config('mpesa.passkey');
        $this->environment = config('mpesa.environment', 'sandbox');
        $this->callbackUrl = config('mpesa.callback_url');
    }

    /**
     * Generate access token for M-Pesa API
     */
    public function generateAccessToken()
    {
        $url = $this->environment === 'production' 
            ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
            : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);

        try {
            $http = Http::withHeaders([
                'Authorization' => 'Basic ' . $credentials,
                'Content-Type' => 'application/json'
            ])->timeout(15);

            // In non-production environments, disable SSL verification to avoid Windows/local CA issues
            if ($this->environment !== 'production') {
                $http = $http->withOptions(['verify' => false]);
            }

            $response = $http->get($url);

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'] ?? null;
            }

            Log::error('M-Pesa Access Token Error', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('M-Pesa Access Token Exception', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Initiate STK Push payment
     */
    public function initiateSTKPush($phoneNumber, $amount, $accountReference, $transactionDesc)
    {
        $accessToken = $this->generateAccessToken();
        
        if (!$accessToken) {
            return [
                'success' => false,
                'message' => 'Failed to generate access token'
            ];
        }

        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $url = $this->environment === 'production'
            ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
            : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phoneNumber,
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => $this->callbackUrl,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post($url, $payload);

            $data = $response->json();

            if ($response->successful() && isset($data['ResponseCode']) && $data['ResponseCode'] == '0') {
                return [
                    'success' => true,
                    'checkout_request_id' => $data['CheckoutRequestID'],
                    'merchant_request_id' => $data['MerchantRequestID'],
                    'response_code' => $data['ResponseCode'],
                    'response_description' => $data['ResponseDescription'],
                    'customer_message' => $data['CustomerMessage']
                ];
            }

            return [
                'success' => false,
                'message' => $data['errorMessage'] ?? 'Payment initiation failed',
                'response' => $data
            ];

        } catch (\Exception $e) {
            Log::error('M-Pesa STK Push Exception', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return [
                'success' => false,
                'message' => 'Payment service temporarily unavailable'
            ];
        }
    }

    /**
     * Query STK Push transaction status
     */
    public function querySTKPushStatus($checkoutRequestId)
    {
        $accessToken = $this->generateAccessToken();
        
        if (!$accessToken) {
            return [
                'success' => false,
                'message' => 'Failed to generate access token'
            ];
        }

        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $url = $this->environment === 'production'
            ? 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query'
            : 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query';

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post($url, $payload);

            $data = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'result_code' => $data['ResultCode'] ?? null,
                    'result_desc' => $data['ResultDesc'] ?? null,
                    'response' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to query transaction status',
                'response' => $data
            ];

        } catch (\Exception $e) {
            Log::error('M-Pesa STK Query Exception', [
                'error' => $e->getMessage(),
                'checkout_request_id' => $checkoutRequestId
            ]);

            return [
                'success' => false,
                'message' => 'Query service temporarily unavailable'
            ];
        }
    }

    /**
     * Handle M-Pesa callback
     */
    public function handleCallback($callbackData)
    {
        try {
            $body = $callbackData['Body'] ?? [];
            $stkCallback = $body['stkCallback'] ?? [];
            
            $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
            $resultCode = $stkCallback['ResultCode'] ?? null;
            $resultDesc = $stkCallback['ResultDesc'] ?? null;
            
            if ($resultCode == 0) {
                // Payment successful
                $callbackMetadata = $stkCallback['CallbackMetadata'] ?? [];
                $metadataItems = $callbackMetadata['Item'] ?? [];
                
                $amount = null;
                $mpesaReceiptNumber = null;
                $transactionDate = null;
                $phoneNumber = null;
                
                foreach ($metadataItems as $item) {
                    switch ($item['Name']) {
                        case 'Amount':
                            $amount = $item['Value'];
                            break;
                        case 'MpesaReceiptNumber':
                            $mpesaReceiptNumber = $item['Value'];
                            break;
                        case 'TransactionDate':
                            $transactionDate = $item['Value'];
                            break;
                        case 'PhoneNumber':
                            $phoneNumber = $item['Value'];
                            break;
                    }
                }
                
                return [
                    'success' => true,
                    'checkout_request_id' => $checkoutRequestId,
                    'amount' => $amount,
                    'mpesa_receipt_number' => $mpesaReceiptNumber,
                    'transaction_date' => $transactionDate,
                    'phone_number' => $phoneNumber,
                    'result_code' => $resultCode,
                    'result_desc' => $resultDesc
                ];
            } else {
                // Payment failed
                return [
                    'success' => false,
                    'checkout_request_id' => $checkoutRequestId,
                    'result_code' => $resultCode,
                    'result_desc' => $resultDesc,
                    'message' => 'Payment failed: ' . $resultDesc
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('M-Pesa Callback Processing Error', [
                'error' => $e->getMessage(),
                'callback_data' => $callbackData
            ]);
            
            return [
                'success' => false,
                'message' => 'Callback processing failed'
            ];
        }
    }

    /**
     * Format phone number for M-Pesa
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        return PhoneNumber::withCountryCode($phoneNumber);
    }
}
