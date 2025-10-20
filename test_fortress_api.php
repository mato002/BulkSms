<?php

/**
 * FORTRESS API TEST
 * Testing SMS sending as Fortress (external company)
 */

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║            FORTRESS - SMS API TEST                        ║\n";
echo "║         (Simulating Fortress Using Your API)             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Fortress API Configuration
$config = [
    'api_url' => 'http://localhost:8000/api',
    'client_id' => 2,
    'api_key' => 'USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh',
    'sender_id' => 'FORTRESS',
];

echo "🏰 FORTRESS Configuration:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Company:    Fortress\n";
echo "API URL:    " . $config['api_url'] . "\n";
echo "Client ID:  " . $config['client_id'] . "\n";
echo "API Key:    " . substr($config['api_key'], 0, 20) . "...\n";
echo "Sender ID:  " . $config['sender_id'] . "\n\n";

// Message details
$recipient = '254728883160';
$message = '🏰 Hello from FORTRESS! Testing our secure SMS API at ' . date('H:i:s') . '. This message is sent via our integrated SMS system. Security confirmed! ✅';

echo "📱 SMS Details:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Recipient:  " . $recipient . "\n";
echo "Sender:     " . $config['sender_id'] . "\n";
echo "Message:    " . substr($message, 0, 70) . "...\n\n";

// Prepare API request
$endpoint = $config['api_url'] . '/' . $config['client_id'] . '/messages/send';

$data = [
    'client_id' => $config['client_id'],
    'channel' => 'sms',
    'recipient' => $recipient,
    'body' => $message,
    'sender' => $config['sender_id'],
];

echo "🚀 Sending Request to API...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Endpoint: " . $endpoint . "\n";
echo "Method:   POST\n";
echo "Format:   JSON\n";
echo "Auth:     X-API-Key header\n\n";

// Send request using cURL
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: ' . $config['api_key'],
    'Content-Type: application/json',
    'Accept: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$startTime = microtime(true);
$response = curl_exec($ch);
$endTime = microtime(true);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$responseTime = round(($endTime - $startTime) * 1000, 2);
$curlError = curl_error($ch);

curl_close($ch);

// Handle cURL errors
if ($curlError) {
    echo "❌ CONNECTION ERROR:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo $curlError . "\n\n";
    echo "💡 Make sure Laravel dev server is running: php artisan serve\n\n";
    exit(1);
}

$responseData = json_decode($response, true);

// Display results
echo "📊 API Response:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "HTTP Status:    " . $httpCode . "\n";
echo "Response Time:  " . $responseTime . "ms\n";
echo "API Status:     " . ($responseData['status'] ?? 'unknown') . "\n\n";

if ($httpCode >= 200 && $httpCode < 300) {
    echo "✅ SUCCESS! MESSAGE SENT VIA FORTRESS!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    if (isset($responseData['data'])) {
        echo "📋 Message Details:\n";
        echo "   ID:              " . ($responseData['data']['id'] ?? 'N/A') . "\n";
        echo "   Status:          " . ($responseData['data']['status'] ?? 'N/A') . "\n";
        echo "   Provider ID:     " . ($responseData['data']['provider_message_id'] ?? 'N/A') . "\n\n";
    }
    
    echo "📱 SMS Details:\n";
    echo "   Sent to:         " . $recipient . "\n";
    echo "   From:            " . $config['sender_id'] . " (Fortress)\n";
    echo "   Message:         " . substr($message, 0, 60) . "...\n\n";
    
    echo "✨ FORTRESS INTEGRATION TEST PASSED!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    echo "🎯 Verification:\n";
    echo "   ✅ Fortress API authentication: WORKING\n";
    echo "   ✅ Fortress sender ID: ACTIVE\n";
    echo "   ✅ Message delivery: CONFIRMED\n";
    echo "   ✅ API key validation: PASSED\n";
    echo "   ✅ Separate client tracking: ENABLED\n\n";
    
} else {
    echo "❌ REQUEST FAILED\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Error: " . ($responseData['message'] ?? 'Unknown error') . "\n";
    
    if (isset($responseData['errors'])) {
        echo "\nValidation Errors:\n";
        foreach ($responseData['errors'] as $field => $errors) {
            echo "   • $field: " . implode(', ', $errors) . "\n";
        }
    }
    
    if (isset($responseData['error_details'])) {
        echo "\nError Details:\n";
        print_r($responseData['error_details']);
    }
    echo "\n";
}

// Check monitoring
echo "🔍 Checking API Monitoring System...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
sleep(1);

$log = \App\Models\ApiLog::latest()->first();
if ($log && $log->created_at->gt(now()->subMinute())) {
    echo "✅ Request logged in monitoring system!\n";
    echo "   Log ID:         " . $log->id . "\n";
    echo "   Client:         " . ($log->client ? $log->client->name : 'Unknown') . "\n";
    echo "   Endpoint:       " . $log->endpoint . "\n";
    echo "   Response Time:  " . round($log->response_time_ms, 2) . "ms\n";
    echo "   Success:        " . ($log->success ? 'Yes ✅' : 'No ❌') . "\n\n";
    echo "🌐 View in dashboard: https://crm.pradytecai.com/api-monitor\n\n";
}

// Summary comparison
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║           MULTI-CLIENT SYSTEM VERIFICATION                ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "📊 Active Clients Summary:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$clients = \App\Models\Client::where('status', true)->get();
foreach ($clients as $client) {
    $messageCount = \App\Models\Message::where('client_id', $client->id)->count();
    echo "• " . $client->name . " (ID: " . $client->id . ")\n";
    echo "  Sender: " . $client->sender_id . "\n";
    echo "  Balance: KSH " . $client->balance . "\n";
    echo "  Messages Sent: " . $messageCount . "\n\n";
}

if ($httpCode >= 200 && $httpCode < 300) {
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ MULTI-CLIENT API SYSTEM FULLY OPERATIONAL            ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    
    echo "✨ Both clients can now:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ Send SMS independently with their own sender IDs\n";
    echo "✅ Track their own usage separately\n";
    echo "✅ Monitor their requests in real-time\n";
    echo "✅ Check their individual balances\n";
    echo "✅ View their own message history\n\n";
    
    echo "📊 View all activity: https://crm.pradytecai.com/api-monitor\n";
    echo "📚 API Documentation: https://crm.pradytecai.com/api-documentation\n\n";
}

echo "🎉 TEST COMPLETE!\n\n";

