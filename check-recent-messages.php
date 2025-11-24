<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Message;
use App\Models\ApiLog;
use App\Models\Client;

echo "========================================\n";
echo "Recent Messages & API Activity\n";
echo "========================================\n\n";

// Check recent messages
echo "--- Recent Messages (Last 10) ---\n";
$messages = Message::with('client')->latest()->take(10)->get();

if ($messages->isEmpty()) {
    echo "No messages found.\n\n";
} else {
    foreach ($messages as $msg) {
        echo "[" . $msg->created_at . "] " . strtoupper($msg->channel) . " to " . $msg->recipient . "\n";
        echo "  Client: " . ($msg->client ? $msg->client->company_name : 'N/A') . " (ID: " . $msg->client_id . ")\n";
        echo "  Status: " . $msg->status . "\n";
        if ($msg->error_message) {
            echo "  ❌ Error: " . $msg->error_message . "\n";
        }
        if ($msg->provider_message_id) {
            echo "  Provider ID: " . $msg->provider_message_id . "\n";
        }
        echo "  Body: " . substr($msg->body, 0, 50) . "...\n";
        echo "---\n";
    }
}

echo "\n";

// Check recent API logs
echo "--- Recent API Requests (Last 5) ---\n";
$apiLogs = ApiLog::with('client')->latest()->take(5)->get();

if ($apiLogs->isEmpty()) {
    echo "No API logs found.\n\n";
} else {
    foreach ($apiLogs as $log) {
        echo "[" . $log->created_at . "] " . $log->method . " " . $log->endpoint . "\n";
        echo "  Client: " . ($log->client ? $log->client->company_name : 'N/A') . "\n";
        echo "  Status: " . $log->response_status . " (" . ($log->success ? 'SUCCESS' : 'FAILED') . ")\n";
        if ($log->error_message) {
            echo "  ❌ Error: " . $log->error_message . "\n";
        }
        if ($log->response_body && is_array($log->response_body)) {
            if (isset($log->response_body['status'])) {
                echo "  Response: " . $log->response_body['status'] . " - " . ($log->response_body['message'] ?? '') . "\n";
            }
        }
        echo "---\n";
    }
}

echo "\n";

// Check for Matech client
echo "--- Matech Client Info ---\n";
$matech = Client::where('company_name', 'LIKE', '%Matech%')
    ->orWhere('id', 8)
    ->first();

if ($matech) {
    echo "Client ID: " . $matech->id . "\n";
    echo "Company: " . $matech->company_name . "\n";
    echo "Status: " . ($matech->status ? 'Active' : 'Inactive') . "\n";
    echo "Balance: KES " . number_format($matech->balance, 2) . "\n";
    echo "API Key: " . substr($matech->api_key, 0, 20) . "...\n";
    echo "Sender ID: " . $matech->sender_id . "\n";
} else {
    echo "Matech client not found.\n";
}







