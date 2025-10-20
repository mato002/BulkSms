<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║       MULTI-CLIENT DEMO - SENDING TO BOTH NUMBERS         ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$recipients = [
    '254722295194',
    '254728883160'
];

$clients = [
    [
        'name' => 'Prady Technologies',
        'client_id' => 1,
        'api_key' => 'bae377bc-0282-4fc9-a2a1-e338b18da77a',
        'sender_id' => 'PRADY_TECH',
        'emoji' => '🎯'
    ],
    [
        'name' => 'Fortress',
        'client_id' => 2,
        'api_key' => 'USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh',
        'sender_id' => 'FORTRESS',
        'emoji' => '🏰'
    ]
];

$results = [];
$messageCount = 0;

foreach ($clients as $client) {
    echo "═══════════════════════════════════════════════════════════\n";
    echo "{$client['emoji']} {$client['name']} ({$client['sender_id']})\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    foreach ($recipients as $recipient) {
        $messageCount++;
        $message = "{$client['emoji']} Hello from {$client['name']}! This is message #{$messageCount} sent at " . date('H:i:s') . ". Testing multi-client SMS system!";
        
        echo "📤 Sending to {$recipient}...\n";
        
        // Prepare request
        $endpoint = 'http://localhost:8000/api/' . $client['client_id'] . '/messages/send';
        
        $data = [
            'client_id' => $client['client_id'],
            'channel' => 'sms',
            'recipient' => $recipient,
            'body' => $message,
            'sender' => $client['sender_id'],
        ];
        
        // Send via cURL
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $client['api_key'],
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $startTime = microtime(true);
        $response = curl_exec($ch);
        $endTime = microtime(true);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseTime = round(($endTime - $startTime) * 1000, 2);
        
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        
        // Display result
        if ($httpCode >= 200 && $httpCode < 300) {
            echo "   ✅ SUCCESS! Message sent\n";
            echo "   Message ID: " . ($responseData['data']['id'] ?? 'N/A') . "\n";
            echo "   Provider ID: " . substr($responseData['data']['provider_message_id'] ?? 'N/A', 0, 30) . "...\n";
            echo "   Response Time: {$responseTime}ms\n";
            
            $results[] = [
                'client' => $client['name'],
                'sender' => $client['sender_id'],
                'recipient' => $recipient,
                'status' => 'SUCCESS',
                'message_id' => $responseData['data']['id'] ?? null,
            ];
        } else {
            echo "   ❌ FAILED\n";
            echo "   Error: " . ($responseData['message'] ?? 'Unknown') . "\n";
            
            $results[] = [
                'client' => $client['name'],
                'sender' => $client['sender_id'],
                'recipient' => $recipient,
                'status' => 'FAILED',
                'error' => $responseData['message'] ?? 'Unknown',
            ];
        }
        
        echo "\n";
        sleep(1); // Small delay between messages
    }
}

// Summary
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    SUMMARY REPORT                          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "📊 Messages Sent:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$successCount = 0;
$failCount = 0;

foreach ($results as $result) {
    $icon = $result['status'] === 'SUCCESS' ? '✅' : '❌';
    echo "{$icon} {$result['client']} → {$result['recipient']}\n";
    echo "   Sender: {$result['sender']}\n";
    echo "   Status: {$result['status']}\n";
    if ($result['status'] === 'SUCCESS') {
        echo "   Message ID: {$result['message_id']}\n";
        $successCount++;
    } else {
        echo "   Error: {$result['error']}\n";
        $failCount++;
    }
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Total Sent: {$messageCount}\n";
echo "Successful: {$successCount} ✅\n";
echo "Failed: {$failCount} " . ($failCount > 0 ? '❌' : '✅') . "\n\n";

// Check monitoring
echo "🔍 Checking API Monitor...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
sleep(2);

$recentLogs = \App\Models\ApiLog::latest()->limit(4)->get();

foreach ($recentLogs as $log) {
    $clientName = $log->client ? $log->client->name : 'Unknown';
    echo "• Log #{$log->id} - {$clientName} ({$log->endpoint}) ";
    echo ($log->success ? '✅' : '❌') . "\n";
}

echo "\n🌐 View all in dashboard: https://crm.pradytecai.com/api-monitor\n\n";

// Phone numbers receiving messages
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║              PHONES RECEIVING MESSAGES                     ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "📱 254722295194 should receive:\n";
echo "   1. Message from PRADY_TECH 🎯\n";
echo "   2. Message from FORTRESS 🏰\n\n";

echo "📱 254728883160 should receive:\n";
echo "   1. Message from PRADY_TECH 🎯\n";
echo "   2. Message from FORTRESS 🏰\n\n";

echo "✨ Check both phones to verify multi-client system!\n\n";

if ($successCount === $messageCount) {
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║  🎉 ALL MESSAGES SENT SUCCESSFULLY!                       ║\n";
    echo "║  Multi-Client System: FULLY OPERATIONAL ✅                ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
}

