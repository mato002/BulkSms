<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== TESTING API SMS SEND (PROPER) ===\n\n";

// Simulate an API request properly
$data = [
    'client_id' => 1,
    'channel' => 'sms',
    'recipient' => '254728883160',
    'body' => 'Hello! Final API test at ' . date('H:i:s') . '. This is sent via PRADY_TECH API. Reply if you got it!',
    'sender' => 'PRADY_TECH',
];

$jsonContent = json_encode($data);

$request = Illuminate\Http\Request::create(
    '/api/1/messages/send',
    'POST',
    [], // parameters
    [], // cookies
    [], // files
    ['CONTENT_TYPE' => 'application/json'], // server
    $jsonContent // content
);

$request->headers->set('X-API-Key', 'bae377bc-0282-4fc9-a2a1-e338b18da77a');
$request->headers->set('Content-Type', 'application/json');
$request->headers->set('Accept', 'application/json');

echo "📤 Sending SMS via API...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "To: 254728883160\n";
echo "From: PRADY_TECH\n";
echo "Message: " . substr($data['body'], 0, 50) . "...\n\n";

try {
    $response = $kernel->handle($request);
    
    $statusCode = $response->getStatusCode();
    $content = $response->getContent();
    $jsonData = json_decode($content, true);
    
    echo "Response Code: " . $statusCode . "\n";
    echo "Response: " . json_encode($jsonData, JSON_PRETTY_PRINT) . "\n\n";
    
    if ($statusCode >= 200 && $statusCode < 300) {
        echo "✅ SMS SENT SUCCESSFULLY VIA API!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Message ID: " . ($jsonData['id'] ?? 'N/A') . "\n";
        echo "Status: " . ($jsonData['status'] ?? 'N/A') . "\n";
        echo "Provider ID: " . ($jsonData['provider_message_id'] ?? 'N/A') . "\n";
        echo "\n📱 Check phone 254728883160 for the SMS!\n";
    } else {
        echo "❌ SMS FAILED\n";
        echo "Error: " . ($jsonData['message'] ?? 'Unknown error') . "\n";
        
        if (isset($jsonData['errors'])) {
            echo "Validation errors:\n";
            print_r($jsonData['errors']);
        }
    }
    
    $kernel->terminate($request, $response);
    
} catch (\Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// Check monitoring
echo "\n\n=== CHECKING MONITORING SYSTEM ===\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
sleep(1);

$log = \App\Models\ApiLog::latest()->first();
if ($log && $log->created_at->isToday()) {
    echo "✅ Request logged!\n";
    echo "Log ID: " . $log->id . "\n";
    echo "Endpoint: " . $log->endpoint . "\n";
    echo "Status: " . $log->response_status . " (" . ($log->success ? 'SUCCESS' : 'FAILED') . ")\n";
    echo "Response Time: " . round($log->response_time_ms, 2) . "ms\n";
    echo "\n🌐 View at: https://crm.pradytecai.com/api-monitor\n";
}

echo "\n✅ API TEST COMPLETE!\n";

