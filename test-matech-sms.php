<?php

/**
 * Test script to send SMS for Matech after Onfon fixes the issue
 * Usage: php test-matech-sms.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Client;
use App\Services\Messaging\DTO\OutboundMessage;
use App\Services\Messaging\MessageDispatcher;

echo "========================================\n";
echo "Testing Matech SMS After Onfon Fix\n";
echo "========================================\n\n";

$client = Client::find(6); // Matech

if (!$client) {
    echo "❌ Matech client not found!\n";
    exit(1);
}

echo "Client: " . $client->company_name . "\n";
echo "Sender ID: " . $client->sender_id . "\n";
echo "Status: " . ($client->status ? 'Active' : 'Inactive') . "\n\n";

// Test message
$recipient = "254728883160"; // Change to your test number
$message = "Test message from " . $client->company_name . " using sender ID " . $client->sender_id;

echo "Sending test SMS...\n";
echo "To: " . $recipient . "\n";
echo "Message: " . $message . "\n\n";

try {
    $dispatcher = app(MessageDispatcher::class);
    
    $outbound = new OutboundMessage(
        clientId: $client->id,
        channel: 'sms',
        recipient: $recipient,
        sender: $client->sender_id,
        body: $message
    );
    
    $result = $dispatcher->dispatch($outbound);
    
    echo "✅ SUCCESS!\n";
    echo "Message ID: " . $result->id . "\n";
    echo "Status: " . $result->status . "\n";
    
    if ($result->provider_message_id) {
        echo "Provider Message ID: " . $result->provider_message_id . "\n";
    }
    
    if ($result->error_message) {
        echo "⚠️  Error: " . $result->error_message . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ FAILED!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

echo "\n";
echo "========================================\n";
echo "Test completed. Check your phone!\n";
echo "========================================\n";







