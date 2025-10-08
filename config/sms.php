<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for different SMS gateways
    |
    */

    'default_gateway' => env('SMS_DEFAULT_GATEWAY', 'mobitech'),

    'gateways' => [
        'mobitech' => [
            'url' => env('SMS_GATEWAY_URL', 'http://bulksms.mobitechtechnologies.com/api/sendsms'),
            'balance_url' => env('SMS_BALANCE_URL', 'https://bulksms.mobitechtechnologies.com/api/account_balance'),
            'delivery_url' => env('SMS_DELIVERY_URL', 'http://bulksms.mobitechtechnologies.com/api/sms_delivery_status'),
            'api_key' => env('SMS_API_KEY', '5fc76bacd1100'),
            'username' => env('SMS_USERNAME', 'prady'),
            'sender_id' => env('SMS_SENDER_ID', 'PRADY_TECH'),
            'cost' => env('SMS_COST', 0.75),
        ],

        'onfon' => [
            'url' => env('ONFON_API_URL', 'https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS'),
            'api_key' => env('ONFON_API_KEY', 'VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak='),
            'client_id' => env('ONFON_CLIENT_ID', 'e27847c1-a9fe-4eef-b60d-ddb291b175ab'),
            'senders' => [
                'FALLEY-MED', 'MWANGAZACLG', 'LOGIC-LINK', 'BriskCredit',
                'DOFAJA_LTD', 'FORTRESS', 'DAKCHES-LTD', 'MILELE_NKR',
                'FAVOURLINE', 'GEOLAND_LTD', 'FANAKA_FSL', 'MWANGAIMARA',
                'AHADI_EPEX', 'MWEGUNI_LTD', 'PRADY_TECH', 'ZEN_PHARMA',
                'PAGECAPITAL', 'NKR_A_CLUB', 'JIRANIHODAR', 'NOVA_BRIDGE',
                'MALIK', 'NOBLE_MICRO', 'AMPLE_SWISS', 'NEWPRO_CAP',
                'DAFACOM_LTD', 'EMPISAI_LTD', 'FANUKA_LTD'
            ]
        ],

        'moja' => [
            'url' => env('MOJA_API_URL', 'https://prady-api-p1.mojasms.dev/api/campaign'),
            'senders' => ['PIXEL_LTD', 'NJORO CLUB', 'MWEGUNI', 'NJORODAYSEC']
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Settings
    |--------------------------------------------------------------------------
    |
    | General SMS settings
    |
    */

    'max_recipients' => env('SMS_MAX_RECIPIENTS', 1000),
    'max_message_length' => env('SMS_MAX_MESSAGE_LENGTH', 160),
    'default_cost' => env('SMS_DEFAULT_COST', 0.75),
    'rate_limit' => env('SMS_RATE_LIMIT', 60), // requests per minute

    /*
    |--------------------------------------------------------------------------
    | Subsidized Rates
    |--------------------------------------------------------------------------
    |
    | Special rates for specific clients
    |
    */

    'subsidized_rates' => [
        7 => 0.7,
        8 => 0.7,
        17 => 0.7,
        19 => 0.65,
        20 => 0.7,
        26 => 0.6,
        51 => 0.65,
        57 => 0.65,
        61 => 0.4,
        64 => 0.65,
        72 => 0.6,
        77 => 0.6,
        82 => 0.55,
        84 => 0.7,
        80 => 0.65,
        99 => 0.54
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Duplicates
    |--------------------------------------------------------------------------
    |
    | Client IDs that are allowed to send duplicate messages
    |
    */

    'allow_duplicates' => [23, 27, 30, 31, 35, 7],

    /*
    |--------------------------------------------------------------------------
    | SMS Bonus
    |--------------------------------------------------------------------------
    |
    | Bonus SMS for new clients
    |
    */

    'bonus_sms' => env('SMS_BONUS', 20),
];
