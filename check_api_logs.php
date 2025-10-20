<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECKING API LOGS ===\n\n";

$logs = \App\Models\ApiLog::latest()->limit(5)->get();

if ($logs->isEmpty()) {
    echo "❌ No API logs found yet.\n";
    echo "The monitoring system is set up, but no API requests have been logged.\n\n";
} else {
    echo "✅ Found " . $logs->count() . " recent API logs:\n\n";
    
    foreach ($logs as $log) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "📅 Time: " . $log->created_at->format('Y-m-d H:i:s') . " (" . $log->created_at->diffForHumans() . ")\n";
        echo "👤 Client: " . ($log->client ? $log->client->name : 'Unknown') . " (ID: " . ($log->client_id ?? 'N/A') . ")\n";
        echo "🔗 Endpoint: " . $log->endpoint . "\n";
        echo "📡 Method: " . $log->method . "\n";
        echo "🌐 IP Address: " . $log->ip_address . "\n";
        echo "📊 Status: " . $log->response_status . " " . ($log->success ? '✅ SUCCESS' : '❌ FAILED') . "\n";
        echo "⚡ Response Time: " . round($log->response_time_ms, 2) . "ms\n";
        
        if ($log->error_message) {
            echo "❗ Error: " . $log->error_message . "\n";
        }
        
        if ($log->request_body) {
            $body = json_decode(json_encode($log->request_body), true);
            if (isset($body['recipient'])) {
                echo "📱 Recipient: " . $body['recipient'] . "\n";
            }
            if (isset($body['body'])) {
                echo "💬 Message: " . substr($body['body'], 0, 50) . "...\n";
            }
        }
        echo "\n";
    }
}

echo "\n📊 STATISTICS:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Today's Total: " . \App\Models\ApiLog::whereDate('created_at', today())->count() . "\n";
echo "Today's Success: " . \App\Models\ApiLog::whereDate('created_at', today())->where('success', true)->count() . "\n";
echo "Today's Failed: " . \App\Models\ApiLog::whereDate('created_at', today())->where('success', false)->count() . "\n";
$avgTime = \App\Models\ApiLog::whereDate('created_at', today())->avg('response_time_ms');
echo "Avg Response Time: " . ($avgTime ? round($avgTime, 2) . "ms" : "N/A") . "\n";

echo "\n✅ Monitoring system is active and logging requests!\n";
echo "🌐 View dashboard at: https://crm.pradytecai.com/api-monitor\n\n";

