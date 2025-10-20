<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         FORTRESS CLIENT SETUP & CONFIGURATION             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Fortress configuration from environment
$fortressConfig = [
    'client_id' => 2,
    'name' => 'Fortress',
    'sender_id' => 'FORTRESS',
    'api_key' => 'USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh',
];

echo "1️⃣ Checking Fortress Client...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$client = \App\Models\Client::find($fortressConfig['client_id']);

if ($client) {
    echo "✅ Fortress client exists\n";
    echo "   Current Name: " . $client->name . "\n";
    echo "   Current API Key: " . substr($client->api_key ?? 'NONE', 0, 20) . "...\n\n";
    
    // Update if needed
    $updated = false;
    if ($client->name !== $fortressConfig['name']) {
        $client->name = $fortressConfig['name'];
        $updated = true;
    }
    if ($client->sender_id !== $fortressConfig['sender_id']) {
        $client->sender_id = $fortressConfig['sender_id'];
        $updated = true;
    }
    if ($client->api_key !== $fortressConfig['api_key']) {
        $client->api_key = $fortressConfig['api_key'];
        $updated = true;
    }
    if (!$client->status) {
        $client->status = true;
        $updated = true;
    }
    if ($client->balance < 100) {
        $client->balance = 1000;
        $updated = true;
    }
    
    if ($updated) {
        $client->save();
        echo "✅ Client updated!\n\n";
    } else {
        echo "✅ Client already configured correctly!\n\n";
    }
} else {
    echo "❌ Fortress client not found. Creating...\n\n";
    
    $client = new \App\Models\Client();
    $client->id = $fortressConfig['client_id'];
    $client->name = $fortressConfig['name'];
    $client->sender_id = $fortressConfig['sender_id'];
    $client->api_key = $fortressConfig['api_key'];
    $client->status = true;
    $client->balance = 1000;
    $client->price_per_unit = 1.00;
    $client->save();
    
    echo "✅ Fortress client created!\n\n";
}

echo "2️⃣ Checking SMS Channel for Fortress...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$channel = \App\Models\Channel::where('client_id', $fortressConfig['client_id'])
    ->where('name', 'sms')
    ->first();

if ($channel) {
    echo "✅ SMS channel exists\n";
    echo "   Provider: " . $channel->provider . "\n";
    echo "   Active: " . ($channel->active ? 'Yes' : 'No') . "\n\n";
} else {
    echo "❌ SMS channel not found. Creating...\n\n";
    
    $channel = new \App\Models\Channel();
    $channel->client_id = $fortressConfig['client_id'];
    $channel->name = 'sms';
    $channel->provider = 'onfon';
    $channel->credentials = [
        'api_key' => env('ONFON_API_KEY', 'VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak='),
        'client_id' => env('ONFON_CLIENT_ID', 'e27847c1-a9fe-4eef-b60d-ddb291b175ab'),
        'access_key_header' => env('ONFON_ACCESS_KEY', '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB'),
        'default_sender' => $fortressConfig['sender_id'],
    ];
    $channel->active = true;
    $channel->save();
    
    echo "✅ SMS channel created!\n\n";
}

echo "✅ FORTRESS CLIENT SETUP COMPLETE!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "📋 Fortress Configuration:\n";
echo "   Client ID:  " . $client->id . "\n";
echo "   Name:       " . $client->name . "\n";
echo "   Sender ID:  " . $client->sender_id . "\n";
echo "   API Key:    " . substr($client->api_key, 0, 20) . "...\n";
echo "   Balance:    KSH " . $client->balance . "\n";
echo "   Status:     " . ($client->status ? '✅ Active' : '❌ Inactive') . "\n\n";

echo "🎯 Ready to send SMS via Fortress API!\n\n";

