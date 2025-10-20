<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== SENDING TEST SMS VIA PRADY_TECH ===\n\n";

// Create a test request
$request = Illuminate\Http\Request::create(
    '/api/1/messages/send',
    'POST',
    [
        'client_id' => 1,
        'channel' => 'sms',
        'recipient' => '254728883160',
        'body' => 'Hello! Test message from Prady Tech API sent at ' . date('H:i:s') . '. Monitoring system is working!',
        'sender' => 'PRADY_TECH',
    ],
    [],
    [],
    [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_API_KEY' => 'bae377bc-0282-4fc9-a2a1-e338b18da77a',
    ]
);

$request->headers->set('X-API-Key', 'bae377bc-0282-4fc9-a2a1-e338b18da77a');
$request->headers->set('Content-Type', 'application/json');

try {
    echo "📤 Sending SMS to 254728883160...\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $response = $kernel->handle($request);
    
    $statusCode = $response->getStatusCode();
    $content = json_decode($response->getContent(), true);
    
    echo "Status Code: " . $statusCode . "\n";
    echo "Response: " . json_encode($content, JSON_PRETTY_PRINT) . "\n\n";
    
    if ($statusCode >= 200 && $statusCode < 300) {
        echo "✅ SMS SENT SUCCESSFULLY!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Message ID: " . ($content['id'] ?? 'N/A') . "\n";
        echo "Status: " . ($content['status'] ?? 'N/A') . "\n";
        echo "Recipient: 254728883160\n";
        echo "\n📱 Check your phone for the SMS!\n";
    } else {
        echo "❌ SMS FAILED\n";
        echo "Error: " . ($content['message'] ?? 'Unknown error') . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Check if it was logged
echo "\n\n=== CHECKING IF REQUEST WAS LOGGED ===\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
sleep(1); // Wait a moment for logging to complete

$log = \App\Models\ApiLog::latest()->first();

if ($log) {
    echo "✅ Request was logged in monitoring system!\n";
    echo "Log ID: " . $log->id . "\n";
    echo "Time: " . $log->created_at->format('Y-m-d H:i:s') . "\n";
    echo "Endpoint: " . $log->endpoint . "\n";
    echo "Status: " . $log->response_status . " (" . ($log->success ? 'SUCCESS' : 'FAILED') . ")\n";
    echo "Response Time: " . round($log->response_time_ms, 2) . "ms\n";
    
    if ($log->error_message) {
        echo "Error: " . $log->error_message . "\n";
    }
    
    echo "\n🌐 View in dashboard: https://crm.pradytecai.com/api-monitor\n";
} else {
    echo "❌ No log found (logging might be disabled or failed)\n";
}

$kernel->terminate($request, $response);

