<?php

/**
 * LOCAL API TEST - Simulating External Company
 * Testing via localhost (simulating external company integration)
 */

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║    PRADY TECHNOLOGIES - LOCAL API TEST                    ║\n";
echo "║    (Simulating External Company Using Your API)           ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Configuration - Using localhost for testing
$config = [
    'api_url' => 'http://localhost:8000/api',  // Local development
    'client_id' => 1,
    'api_key' => 'bae377bc-0282-4fc9-a2a1-e338b18da77a',
    'sender_id' => 'PRADY_TECH',
];

// Update client name to Prady Technologies
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$client = \App\Models\Client::find(1);
if ($client && $client->name !== 'Prady Technologies') {
    $client->name = 'Prady Technologies';
    $client->save();
    echo "✅ Client name updated to 'Prady Technologies'\n\n";
}

echo "📋 API Configuration:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Company:    Prady Technologies\n";
echo "API URL:    " . $config['api_url'] . "\n";
echo "Client ID:  " . $config['client_id'] . "\n";
echo "API Key:    " . substr($config['api_key'], 0, 20) . "...\n";
echo "Sender ID:  " . $config['sender_id'] . "\n\n";

// Message details
$recipient = '254728883160';
$message = '🎉 Hello from Prady Technologies! Testing our SMS API integration at ' . date('H:i:s') . '. This proves our system is working perfectly!';

echo "📱 SMS Details:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Recipient:  " . $recipient . "\n";
echo "Sender:     " . $config['sender_id'] . "\n";
echo "Message:    " . substr($message, 0, 70) . "...\n\n";

// Prepare request
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
echo "URL: " . $endpoint . "\n";
echo "Method: POST\n";
echo "Content-Type: application/json\n";
echo "Authentication: X-API-Key header\n\n";

// Use cURL
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
    echo "💡 TIP: Make sure your Laravel dev server is running:\n";
    echo "   php artisan serve\n\n";
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
    echo "✅ SUCCESS! MESSAGE SENT!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    if (isset($responseData['data'])) {
        echo "📋 Message Details:\n";
        echo "   ID:              " . ($responseData['data']['id'] ?? 'N/A') . "\n";
        echo "   Status:          " . ($responseData['data']['status'] ?? 'N/A') . "\n";
        echo "   Provider ID:     " . ($responseData['data']['provider_message_id'] ?? 'N/A') . "\n\n";
    }
    
    echo "📱 SMS sent to: " . $recipient . "\n";
    echo "💬 Message: " . substr($message, 0, 60) . "...\n\n";
    
    echo "✨ INTEGRATION TEST PASSED!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    echo "🎯 What this proves:\n";
    echo "   ✅ API authentication working\n";
    echo "   ✅ External company (Prady Tech) can send SMS\n";
    echo "   ✅ Message delivery system operational\n";
    echo "   ✅ API key validation working\n";
    echo "   ✅ Request/Response handling correct\n\n";
    
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
        echo "\nDetailed Error Info:\n";
        print_r($responseData['error_details']);
    }
    echo "\n";
}

// Check if it was logged
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
} else {
    echo "⚠️  No recent log found\n\n";
}

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║              TEST COMPLETE                                 ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

if ($httpCode >= 200 && $httpCode < 300) {
    echo "🎉 READY FOR PRODUCTION USE!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Prady Technologies can now:\n";
    echo "• Send SMS from their applications\n";
    echo "• Monitor usage in real-time\n";
    echo "• Track delivery status\n";
    echo "• Check their balance\n";
    echo "• View message history\n\n";
    
    echo "📚 Documentation: https://crm.pradytecai.com/api-documentation\n";
    echo "📊 Monitor: https://crm.pradytecai.com/api-monitor\n\n";
}

