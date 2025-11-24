<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Client;
use App\Models\Transaction;

class StripePaymentService
{
    private $secretKey;
    private $publishableKey;
    private $webhookSecret;
    private $environment;

    public function __construct()
    {
        $this->secretKey = config('stripe.secret_key');
        $this->publishableKey = config('stripe.publishable_key');
        $this->webhookSecret = config('stripe.webhook_secret');
        $this->environment = config('stripe.environment', 'test');
    }

    /**
     * Create a payment intent
     */
    public function createPaymentIntent($amount, $currency = 'kes', $metadata = [])
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->post('https://api.stripe.com/v1/payment_intents', [
                'amount' => $amount * 100, // Convert to cents
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'client_secret' => $data['client_secret'],
                    'payment_intent_id' => $data['id'],
                    'amount' => $data['amount'],
                    'currency' => $data['currency'],
                    'status' => $data['status']
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create payment intent',
                'error' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Stripe Payment Intent Creation Error', [
                'error' => $e->getMessage(),
                'amount' => $amount,
                'metadata' => $metadata
            ]);

            return [
                'success' => false,
                'message' => 'Payment service temporarily unavailable'
            ];
        }
    }

    /**
     * Retrieve a payment intent
     */
    public function retrievePaymentIntent($paymentIntentId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey
            ])->get("https://api.stripe.com/v1/payment_intents/{$paymentIntentId}");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'payment_intent' => $data,
                    'status' => $data['status'],
                    'amount' => $data['amount'],
                    'currency' => $data['currency']
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to retrieve payment intent',
                'error' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Stripe Payment Intent Retrieval Error', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntentId
            ]);

            return [
                'success' => false,
                'message' => 'Payment service temporarily unavailable'
            ];
        }
    }

    /**
     * Create a customer
     */
    public function createCustomer($email, $name = null, $metadata = [])
    {
        try {
            $payload = [
                'email' => $email,
                'metadata' => $metadata
            ];

            if ($name) {
                $payload['name'] = $name;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->post('https://api.stripe.com/v1/customers', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'customer_id' => $data['id'],
                    'customer' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create customer',
                'error' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Stripe Customer Creation Error', [
                'error' => $e->getMessage(),
                'email' => $email,
                'name' => $name
            ]);

            return [
                'success' => false,
                'message' => 'Customer service temporarily unavailable'
            ];
        }
    }

    /**
     * Handle Stripe webhook
     */
    public function handleWebhook($payload, $signature)
    {
        try {
            // Verify webhook signature
            $timestamp = null;
            $signatures = [];
            
            if (isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
                $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
                $elements = explode(',', $sigHeader);
                
                foreach ($elements as $element) {
                    $parts = explode('=', $element, 2);
                    if ($parts[0] === 't') {
                        $timestamp = $parts[1];
                    } elseif ($parts[0] === 'v1') {
                        $signatures[] = $parts[1];
                    }
                }
            }

            $signedPayload = $timestamp . '.' . $payload;
            $expectedSignature = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

            if (!in_array($expectedSignature, $signatures)) {
                Log::warning('Stripe Webhook Signature Verification Failed');
                return [
                    'success' => false,
                    'message' => 'Invalid signature'
                ];
            }

            $event = json_decode($payload, true);
            $eventType = $event['type'] ?? null;

            switch ($eventType) {
                case 'payment_intent.succeeded':
                    return $this->handlePaymentSucceeded($event);
                case 'payment_intent.payment_failed':
                    return $this->handlePaymentFailed($event);
                case 'payment_intent.canceled':
                    return $this->handlePaymentCanceled($event);
                default:
                    return [
                        'success' => true,
                        'message' => 'Event type not handled',
                        'event_type' => $eventType
                    ];
            }

        } catch (\Exception $e) {
            Log::error('Stripe Webhook Processing Error', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return [
                'success' => false,
                'message' => 'Webhook processing failed'
            ];
        }
    }

    /**
     * Handle successful payment
     */
    private function handlePaymentSucceeded($event)
    {
        $paymentIntent = $event['data']['object'];
        
        return [
            'success' => true,
            'event_type' => 'payment_intent.succeeded',
            'payment_intent_id' => $paymentIntent['id'],
            'amount' => $paymentIntent['amount'],
            'currency' => $paymentIntent['currency'],
            'customer_id' => $paymentIntent['customer'] ?? null,
            'metadata' => $paymentIntent['metadata'] ?? []
        ];
    }

    /**
     * Handle failed payment
     */
    private function handlePaymentFailed($event)
    {
        $paymentIntent = $event['data']['object'];
        
        return [
            'success' => false,
            'event_type' => 'payment_intent.payment_failed',
            'payment_intent_id' => $paymentIntent['id'],
            'amount' => $paymentIntent['amount'],
            'currency' => $paymentIntent['currency'],
            'error' => $paymentIntent['last_payment_error'] ?? null,
            'metadata' => $paymentIntent['metadata'] ?? []
        ];
    }

    /**
     * Handle canceled payment
     */
    private function handlePaymentCanceled($event)
    {
        $paymentIntent = $event['data']['object'];
        
        return [
            'success' => false,
            'event_type' => 'payment_intent.canceled',
            'payment_intent_id' => $paymentIntent['id'],
            'amount' => $paymentIntent['amount'],
            'currency' => $paymentIntent['currency'],
            'metadata' => $paymentIntent['metadata'] ?? []
        ];
    }

    /**
     * Get publishable key
     */
    public function getPublishableKey()
    {
        return $this->publishableKey;
    }

    /**
     * Check if Stripe is configured
     */
    public function isConfigured()
    {
        return !empty($this->secretKey) && !empty($this->publishableKey);
    }
}
