<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUGGING SMS SENDING ISSUE ===\n\n";

// 1. Check if client exists and has balance
echo "1️⃣ Checking Client Configuration...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$client = \App\Models\Client::find(1);
if ($client) {
    echo "✅ Client exists: " . $client->name . "\n";
    echo "Balance: KSH " . $client->balance . "\n";
    echo "Status: " . ($client->status ? 'Active' : 'Inactive') . "\n";
    echo "API Key: " . substr($client->api_key, 0, 20) . "...\n\n";
} else {
    echo "❌ Client not found!\n\n";
    exit(1);
}

// 2. Check channels configuration
echo "2️⃣ Checking Channels Configuration...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$channels = \App\Models\Channel::where('client_id', 1)->get();
if ($channels->isEmpty()) {
    echo "❌ No channels configured for this client!\n";
    echo "Creating SMS channel...\n\n";
    
    // Create SMS channel
    $channel = new \App\Models\Channel();
    $channel->client_id = 1;
    $channel->name = 'sms';
    $channel->provider = 'onfon';
    $channel->credentials = [
        'api_key' => env('ONFON_API_KEY', 'VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak='),
        'client_id' => env('ONFON_CLIENT_ID', 'e27847c1-a9fe-4eef-b60d-ddb291b175ab'),
        'access_key_header' => env('ONFON_ACCESS_KEY', '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB'),
        'default_sender' => 'PRADY_TECH',
    ];
    $channel->active = true;
    $channel->save();
    
    echo "✅ SMS channel created!\n\n";
} else {
    foreach ($channels as $channel) {
        echo "✅ Channel: " . $channel->name . "\n";
        echo "Provider: " . $channel->provider . "\n";
        echo "Active: " . ($channel->active ? 'Yes' : 'No') . "\n";
        echo "Credentials: " . (is_array($channel->credentials) || is_object($channel->credentials) ? 'Configured' : 'Missing') . "\n\n";
    }
}

// 3. Check senders configuration (skipping - using channel config instead)
echo "3️⃣ Sender Configuration...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Using sender from channel configuration: PRADY_TECH\n\n";

// 4. Check message table
echo "4️⃣ Checking Messages Table...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
try {
    $messageCount = \App\Models\Message::count();
    echo "✅ Messages table exists\n";
    echo "Total messages: " . $messageCount . "\n\n";
} catch (\Exception $e) {
    echo "❌ Messages table issue: " . $e->getMessage() . "\n\n";
}

// 5. Test SMS sending
echo "5️⃣ Testing SMS Send...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    // Get the channel
    $channel = \App\Models\Channel::where('client_id', 1)->where('name', 'sms')->first();
    
    if (!$channel) {
        throw new \Exception('SMS channel not found');
    }
    
    echo "Channel found: " . $channel->name . "\n";
    echo "Provider: " . $channel->provider . "\n\n";
    
    // Create a message record
    $message = new \App\Models\Message();
    $message->client_id = 1;
    $message->channel = 'sms';
    $message->recipient = '254728883160';
    $message->body = 'Test SMS from API at ' . date('H:i:s') . '. If you receive this, the system works!';
    $message->sender = 'PRADY_TECH';
    $message->status = 'pending';
    $message->direction = 'outbound';
    $message->save();
    
    echo "✅ Message record created (ID: " . $message->id . ")\n";
    echo "Now dispatching...\n\n";
    
    // Dispatch the message
    $dispatcher = app(\App\Services\Messaging\MessageDispatcher::class);
    
    $outbound = new \App\Services\Messaging\DTO\OutboundMessage(
        clientId: 1,
        channel: 'sms',
        recipient: '254728883160',
        sender: 'PRADY_TECH',
        subject: null,
        body: 'Test SMS from API at ' . date('H:i:s') . '. If you receive this, the system works!',
        templateId: null,
        metadata: []
    );
    
    $result = $dispatcher->dispatch($outbound);
    
    echo "✅ MESSAGE SENT SUCCESSFULLY!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Message ID: " . $result->id . "\n";
    echo "Status: " . $result->status . "\n";
    echo "Provider Message ID: " . $result->provider_message_id . "\n";
    echo "\n📱 Check phone 254728883160 for the SMS!\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR SENDING SMS:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n\n=== DIAGNOSIS COMPLETE ===\n";

