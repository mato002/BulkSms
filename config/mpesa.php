<?php

return [
    /*
    |--------------------------------------------------------------------------
    | M-Pesa Daraja Configuration
    |--------------------------------------------------------------------------
    */

    'environment' => env('MPESA_ENV', env('MPESA_ENVIRONMENT', 'sandbox')),
    'env' => env('MPESA_ENV', env('MPESA_ENVIRONMENT', 'sandbox')),

    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),

    'shortcode' => env('MPESA_SHORTCODE'),
    'paybill' => env('MPESA_PAYBILL', env('MPESA_SHORTCODE')),
    'passkey' => env('MPESA_PASSKEY'),

    'transaction_type' => env('MPESA_TRANSACTION_TYPE', 'CustomerPayBillOnline'),

    /*
    |--------------------------------------------------------------------------
    | Callback URLs (register these in Daraja portal)
    |--------------------------------------------------------------------------
    */

    'stk_callback_url' => env('MPESA_STK_CALLBACK_URL', env('MPESA_CALLBACK_URL', env('APP_URL') . '/api/mpesa/stk/callback')),

    // Back-compat alias used by MpesaService / MpesaPaymentGateway
    'callback_url' => env('MPESA_STK_CALLBACK_URL', env('MPESA_CALLBACK_URL', env('APP_URL') . '/api/mpesa/stk/callback')),

    'timeout_url' => env('MPESA_STK_TIMEOUT_URL', env('APP_URL') . '/api/webhooks/mpesa/timeout'),

    'c2b' => [
        'confirmation_url' => env('MPESA_CONFIRMATION_URL', env('APP_URL') . '/api/mpesa/c2b/confirm'),
        'validation_url' => env('MPESA_VALIDATION_URL', env('APP_URL') . '/api/mpesa/c2b/validate'),
    ],

    'b2c' => [
        'result_url' => env('MPESA_RESULT_URL', env('APP_URL') . '/api/mpesa/b2c/result'),
        'timeout_url' => env('MPESA_TIMEOUT_URL', env('APP_URL') . '/api/mpesa/b2c/timeout'),
    ],

    'reversal' => [
        'result_url' => env('MPESA_REVERSAL_RESULT_URL', env('APP_URL') . '/api/mpesa/reversal/result'),
        'timeout_url' => env('MPESA_REVERSAL_TIMEOUT_URL', env('APP_URL') . '/api/mpesa/reversal/timeout'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Daraja API endpoints
    |--------------------------------------------------------------------------
    */

    'urls' => [
        'sandbox' => [
            'auth' => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'stk_push' => 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            'stk_query' => 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query',
            'c2b_register' => 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl',
            'b2c' => 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest',
            'reversal' => 'https://sandbox.safaricom.co.ke/mpesa/reversal/v1/request',
        ],
        'production' => [
            'auth' => 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'stk_push' => 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            'stk_query' => 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query',
            'c2b_register' => 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl',
            'b2c' => 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest',
            'reversal' => 'https://api.safaricom.co.ke/mpesa/reversal/v1/request',
        ],
    ],
];
