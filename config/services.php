<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Configuration (UltraMsg)
    |--------------------------------------------------------------------------
    |
    | Configuration for WhatsApp integration via UltraMsg API
    | Sign up at: https://ultramsg.com
    |
    */
    'whatsapp' => [
        'provider' => env('WHATSAPP_PROVIDER', 'ultramsg'), // ultramsg or whatsapp_cloud
        
        // UltraMsg Configuration (Recommended - Easy Setup)
        'ultramsg' => [
            'instance_id' => env('ULTRAMSG_INSTANCE_ID'),
            'token' => env('ULTRAMSG_TOKEN'),
            'webhook_token' => env('ULTRAMSG_WEBHOOK_TOKEN'),
        ],

        // WhatsApp Cloud API Configuration (Advanced)
        'whatsapp_cloud' => [
            'api_version' => env('WHATSAPP_API_VERSION', 'v21.0'),
            'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
            'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
            'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
            'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
        ],
    ],

];




