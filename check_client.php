<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECKING CLIENT SETUP ===\n\n";

// Check if client exists
$client = \App\Models\Client::find(1);

if ($client) {
    echo "✅ Client Found:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "ID: " . $client->id . "\n";
    echo "Name: " . $client->name . "\n";
    echo "Sender ID: " . ($client->sender_id ?? 'N/A') . "\n";
    echo "API Key: " . ($client->api_key ? substr($client->api_key, 0, 20) . '...' : 'NOT SET') . "\n";
    echo "Status: " . ($client->status ? '✅ Active' : '❌ Inactive') . "\n";
    echo "Balance: " . ($client->balance ?? 0) . "\n";
    echo "\n";
    
    // Check if API key matches
    $expectedKey = 'bae377bc-0282-4fc9-a2a1-e338b18da77a';
    if ($client->api_key === $expectedKey) {
        echo "✅ API Key matches expected key!\n";
    } else {
        echo "❌ API Key doesn't match!\n";
        echo "Expected: " . $expectedKey . "\n";
        echo "Actual: " . ($client->api_key ?? 'NULL') . "\n";
        echo "\n🔧 Fixing API key...\n";
        
        $client->api_key = $expectedKey;
        $client->status = true;
        $client->save();
        
        echo "✅ API key updated successfully!\n";
    }
} else {
    echo "❌ Client ID 1 not found. Creating...\n\n";
    
    $client = new \App\Models\Client();
    $client->id = 1;
    $client->name = 'Prady Technologies';
    $client->sender_id = 'PRADY_TECH';
    $client->api_key = 'bae377bc-0282-4fc9-a2a1-e338b18da77a';
    $client->status = true;
    $client->balance = 1000;
    $client->price_per_unit = 1.00;
    $client->save();
    
    echo "✅ Client created successfully!\n";
}

echo "\n🎯 Ready to test API!\n";
echo "Use this command:\n";
echo 'curl -X POST https://crm.pradytecai.com/api/1/messages/send \\' . "\n";
echo '  -H "X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a" \\' . "\n";
echo '  -H "Content-Type: application/json" \\' . "\n";
echo '  -d \'{"client_id": 1, "channel": "sms", "recipient": "254728883160", "body": "Test", "sender": "PRADY_TECH"}\'' . "\n";

