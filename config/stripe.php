<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Stripe payment integration
    |
    */

    'environment' => env('STRIPE_ENVIRONMENT', 'test'), // test or live
    
    'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
    'secret_key' => env('STRIPE_SECRET_KEY'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    
    'webhook_url' => env('STRIPE_WEBHOOK_URL', env('APP_URL') . '/api/payments/stripe/webhook'),
];
