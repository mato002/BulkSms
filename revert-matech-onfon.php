<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Channel;
use App\Models\Client;

echo "========================================\n";
echo "Reverting Matech back to Onfon\n";
echo "========================================\n\n";

$client = Client::find(6); // Matech

// Get Onfon config
$onfonConfig = config('sms.gateways.onfon', []);

// Update channel back to Onfon
$channel = Channel::updateOrCreate(
    [
        'client_id' => 6,
        'name' => 'sms',
    ],
    [
        'provider' => 'onfon',
        'active' => true,
        'credentials' => [
            'api_key' => $onfonConfig['api_key'] ?? env('ONFON_API_KEY', ''),
            'client_id' => $onfonConfig['client_id'] ?? env('ONFON_CLIENT_ID', ''),
            'access_key_header' => env('ONFON_ACCESS_KEY_HEADER', '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB'),
            'default_sender' => $client->sender_id, // MATECHTE
            'base_url' => $onfonConfig['url'] ?? 'https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS',
        ],
        'config' => [
            'uses_system_gateway' => true,
            'auto_created' => false,
        ],
    ]
);

echo "✅ Channel reverted to Onfon!\n\n";
echo "Channel ID: " . $channel->id . "\n";
echo "Provider: " . $channel->provider . "\n";
echo "Active: " . ($channel->active ? 'Yes' : 'No') . "\n";
echo "Sender ID: " . $client->sender_id . "\n";







