<?php

return [
    /*
    |--------------------------------------------------------------------------
    | M-Pesa Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for M-Pesa payment integration
    |
    */

    'environment' => env('MPESA_ENVIRONMENT', env('MPESA_ENV', 'sandbox')), // sandbox or production
    // Back-compat key used elsewhere in codebase
    'env' => env('MPESA_ENV', env('MPESA_ENVIRONMENT', 'sandbox')),
    
    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    
    'shortcode' => env('MPESA_SHORTCODE'),
    'passkey' => env('MPESA_PASSKEY'),
    
    'callback_url' => env('MPESA_CALLBACK_URL', env('APP_URL') . '/api/payments/mpesa/callback'),
    
    'timeout_url' => env('MPESA_TIMEOUT_URL', env('APP_URL') . '/api/payments/mpesa/timeout'),

    // URL map expected by legacy MpesaService
    'urls' => [
        'sandbox' => [
            'auth' => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'stk_push' => 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            'stk_query' => 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query',
        ],
        'production' => [
            'auth' => 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'stk_push' => 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            'stk_query' => 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query',
        ],
    ],
];