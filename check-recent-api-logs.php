<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ApiLog;
use App\Models\Client;

echo "========================================\n";
echo "Recent API Logs (Last 10 requests)\n";
echo "========================================\n\n";

$logs = ApiLog::with('client')->latest()->take(10)->get();

if ($logs->isEmpty()) {
    echo "No API logs found.\n";
    exit;
}

foreach ($logs as $log) {
    echo "[" . $log->created_at . "] " . $log->method . " " . $log->endpoint . "\n";
    echo "  Client: " . ($log->client ? $log->client->company_name : 'N/A') . " (ID: " . $log->client_id . ")\n";
    echo "  Status: " . $log->response_status . " (" . ($log->success ? 'SUCCESS' : 'FAILED') . ")\n";
    echo "  Response Time: " . $log->response_time_ms . "ms\n";
    
    if ($log->error_message) {
        echo "  ❌ Error: " . $log->error_message . "\n";
    }
    
    if ($log->request_body) {
        echo "  Request Body:\n";
        echo "    " . json_encode($log->request_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }
    
    if ($log->response_body) {
        echo "  Response Body:\n";
        $response = is_array($log->response_body) ? $log->response_body : json_decode($log->response_body, true);
        if ($response) {
            echo "    " . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        } else {
            echo "    " . $log->response_body . "\n";
        }
    }
    
    echo "---\n\n";
}







