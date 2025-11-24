<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Channel;
use App\Models\Client;

echo "========================================\n";
echo "Switching Matech to Mobitech Gateway\n";
echo "========================================\n\n";

$client = Client::find(6); // Matech

if (!$client) {
    echo "Client not found!\n";
    exit;
}

// Get Mobitech config
$mobitechConfig = config('sms.gateways.mobitech', []);

// Update or create channel with Mobitech
$channel = Channel::updateOrCreate(
    [
        'client_id' => 6,
        'name' => 'sms',
    ],
    [
        'provider' => 'mobitech',
        'active' => true,
        'credentials' => [
            'api_key' => $mobitechConfig['api_key'] ?? env('SMS_API_KEY', ''),
            'username' => $mobitechConfig['username'] ?? env('SMS_USERNAME', ''),
            'sender_id' => $client->sender_id, // MATECHTE
            'gateway_url' => $mobitechConfig['url'] ?? env('SMS_GATEWAY_URL', ''),
        ],
        'config' => [
            'uses_system_gateway' => true,
            'auto_created' => false,
            'switched_from_onfon' => true,
        ],
    ]
);

echo "✅ Channel updated successfully!\n\n";
echo "Channel ID: " . $channel->id . "\n";
echo "Provider: " . $channel->provider . "\n";
echo "Active: " . ($channel->active ? 'Yes' : 'No') . "\n";
echo "Sender ID: " . $client->sender_id . "\n";
echo "\n";
echo "Matech will now use Mobitech gateway instead of Onfon.\n";
echo "This should allow MATECHTE sender ID to work.\n";







