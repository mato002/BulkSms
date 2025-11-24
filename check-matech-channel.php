<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Channel;
use App\Models\Client;

echo "========================================\n";
echo "Matech Channel Configuration\n";
echo "========================================\n\n";

$client = Client::find(6); // Matech

if (!$client) {
    echo "Client not found!\n";
    exit;
}

echo "Client: " . $client->company_name . " (ID: " . $client->id . ")\n";
echo "Sender ID: " . $client->sender_id . "\n";
echo "Status: " . ($client->status ? 'Active' : 'Inactive') . "\n\n";

// Check SMS channel
$channel = Channel::where('client_id', 6)
    ->where('name', 'sms')
    ->first();

if ($channel) {
    echo "--- SMS Channel Found ---\n";
    echo "Channel ID: " . $channel->id . "\n";
    echo "Provider: " . $channel->provider . "\n";
    echo "Active: " . ($channel->active ? 'Yes' : 'No') . "\n";
    echo "Credentials: " . json_encode($channel->credentials, JSON_PRETTY_PRINT) . "\n";
    echo "Config: " . json_encode($channel->config, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "--- No SMS Channel Found ---\n";
    echo "Channel will be auto-created on first message send.\n";
    echo "Default provider: onfon\n";
}

echo "\n";
echo "========================================\n";
echo "The system routes based on Channel provider,\n";
echo "NOT based on sender ID whitelist.\n";
echo "========================================\n";







