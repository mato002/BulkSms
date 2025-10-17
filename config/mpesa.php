<?php

return [

    /*
    |--------------------------------------------------------------------------
    | M-Pesa Environment
    |--------------------------------------------------------------------------
    |
    | Specify whether to use sandbox or production environment
    | Values: 'sandbox' or 'production'
    |
    */

    'env' => env('MPESA_ENV', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Consumer Key
    |--------------------------------------------------------------------------
    |
    | Your M-Pesa Daraja API Consumer Key
    | Get this from: https://developer.safaricom.co.ke
    |
    */

    'consumer_key' => env('MPESA_CONSUMER_KEY', 'Yt36YTWRLf1CL3RW47GidbAXtW1OcO4m7U5VuvA6x84BdoQV'),

    /*
    |--------------------------------------------------------------------------
    | Consumer Secret
    |--------------------------------------------------------------------------
    |
    | Your M-Pesa Daraja API Consumer Secret
    |
    */

    'consumer_secret' => env('MPESA_CONSUMER_SECRET', 'p3o13LwjC48GjBGdvcnpptuQc90OSlHJvBeTwkXyJBNQFGJQnqN5gws4gf6frGdh'),

    /*
    |--------------------------------------------------------------------------
    | Passkey
    |--------------------------------------------------------------------------
    |
    | Your M-Pesa STK Push Passkey
    |
    */

    'passkey' => env('MPESA_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'),

    /*
    |--------------------------------------------------------------------------
    | Shortcode / Business Number
    |--------------------------------------------------------------------------
    |
    | Your M-Pesa Paybill or Till Number
    |
    */

    'shortcode' => env('MPESA_SHORTCODE', '174379'), // Default: Sandbox shortcode

    /*
    |--------------------------------------------------------------------------
    | Transaction Type
    |--------------------------------------------------------------------------
    |
    | Type of transaction
    | - CustomerPayBillOnline: For Paybill
    | - CustomerBuyGoodsOnline: For Till Number
    |
    */

    'transaction_type' => env('MPESA_TRANSACTION_TYPE', 'CustomerPayBillOnline'),

    /*
    |--------------------------------------------------------------------------
    | Callback URL
    |--------------------------------------------------------------------------
    |
    | URL that M-Pesa will send payment notifications to
    |
    */

    'callback_url' => env('MPESA_CALLBACK_URL', 'https://duke-nonvolcanic-constrainedly.ngrok-free.dev/api/webhooks/mpesa/callback'),

    /*
    |--------------------------------------------------------------------------
    | Timeout URL
    |--------------------------------------------------------------------------
    |
    | URL that M-Pesa will call when request times out
    |
    */

    'timeout_url' => env('MPESA_TIMEOUT_URL', 'https://duke-nonvolcanic-constrainedly.ngrok-free.dev/api/webhooks/mpesa/timeout'),

    /*
    |--------------------------------------------------------------------------
    | Initiator Name
    |--------------------------------------------------------------------------
    |
    | Name of the API operator (for B2C and other transactions)
    |
    */

    'initiator_name' => env('MPESA_INITIATOR_NAME', ''),

    /*
    |--------------------------------------------------------------------------
    | Initiator Password
    |--------------------------------------------------------------------------
    |
    | Password for the initiator
    |
    */

    'initiator_password' => env('MPESA_INITIATOR_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | API URLs
    |--------------------------------------------------------------------------
    |
    | M-Pesa Daraja API endpoints
    |
    */

    'urls' => [
        'sandbox' => [
            'auth' => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'stk_push' => 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            'stk_query' => 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query',
            'c2b_register' => 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl',
            'b2c' => 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest',
            'transaction_status' => 'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query',
        ],
        'production' => [
            'auth' => 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'stk_push' => 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            'stk_query' => 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query',
            'c2b_register' => 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl',
            'b2c' => 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest',
            'transaction_status' => 'https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query',
        ],
    ],

];

