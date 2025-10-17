<?php

/**
 * Test Sender API - PRADY_TECH
 * 
 * This script tests the API endpoints to verify that PRADY_TECH
 * can send SMS through our platform
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Client;
use Illuminate\Support\Facades\Http;

echo "=================================================\n";
echo "    SENDER API TEST - PRADY_TECH\n";
echo "=================================================\n\n";

// Get PRADY_TECH client
$client = Client::where('sender_id', 'PRADY_TECH')->first();

if (!$client) {
    echo "❌ ERROR: PRADY_TECH client not found!\n";
    echo "Run: php generate_api_credentials.php first\n\n";
    exit(1);
}

echo "✅ Found client: {$client->name}\n";
echo "   Client ID: {$client->id}\n";
echo "   API Key: {$client->api_key}\n";
echo "   Balance: KSH " . number_format($client->balance, 2) . "\n";
echo "   Units: " . number_format($client->getBalanceInUnits(), 2) . "\n\n";

$baseUrl = env('APP_URL', 'http://localhost');
$apiKey = $client->api_key;
$clientId = $client->id;

// Test 1: Health Check
echo "=================================================\n";
echo "TEST 1: API Health Check\n";
echo "=================================================\n\n";

try {
    $response = Http::get("{$baseUrl}/api/health");
    
    if ($response->successful()) {
        echo "✅ API is running\n";
        echo "Response: " . $response->body() . "\n\n";
    } else {
        echo "❌ API health check failed\n";
        echo "Status: {$response->status()}\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n\n";
}

// Test 2: Authentication
echo "=================================================\n";
echo "TEST 2: API Authentication\n";
echo "=================================================\n\n";

try {
    $response = Http::withHeaders([
        'X-API-Key' => $apiKey,
    ])->get("{$baseUrl}/api/{$clientId}/client/balance");
    
    if ($response->successful()) {
        echo "✅ Authentication successful\n";
        $data = $response->json();
        echo "Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    } else {
        echo "❌ Authentication failed\n";
        echo "Status: {$response->status()}\n";
        echo "Response: {$response->body()}\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n\n";
}

// Test 3: Check Balance
echo "=================================================\n";
echo "TEST 3: Check Balance\n";
echo "=================================================\n\n";

try {
    $response = Http::withHeaders([
        'X-API-Key' => $apiKey,
    ])->get("{$baseUrl}/api/{$clientId}/client/balance");
    
    if ($response->successful()) {
        echo "✅ Balance check successful\n";
        $data = $response->json();
        echo "Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    } else {
        echo "❌ Balance check failed\n";
        echo "Status: {$response->status()}\n";
        echo "Response: {$response->body()}\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n\n";
}

// Test 4: Send SMS (if balance available)
echo "=================================================\n";
echo "TEST 4: Send Test SMS\n";
echo "=================================================\n\n";

$testRecipient = env('TEST_PHONE_NUMBER', '254700000000');

echo "Test recipient: {$testRecipient}\n";
echo "Note: Change TEST_PHONE_NUMBER in .env for real testing\n\n";

if ($client->balance > 0 || $client->getBalanceInUnits() > 0) {
    echo "Current balance: KSH " . number_format($client->balance, 2) . " ({$client->getBalanceInUnits()} units)\n\n";
    
    echo "Enter 'yes' to send a test SMS, or press Enter to skip: ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim($line) === 'yes') {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$baseUrl}/api/{$clientId}/messages/send", [
                'channel' => 'sms',
                'recipient' => $testRecipient,
                'body' => 'Test message from PRADY_TECH API at ' . date('H:i:s'),
                'sender' => 'PRADY_TECH',
            ]);
            
            if ($response->successful()) {
                echo "✅ SMS sent successfully!\n";
                $data = $response->json();
                echo "Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
            } else {
                echo "❌ SMS sending failed\n";
                echo "Status: {$response->status()}\n";
                echo "Response: {$response->body()}\n\n";
            }
        } catch (Exception $e) {
            echo "❌ Error: {$e->getMessage()}\n\n";
        }
    } else {
        echo "⏭️  Test SMS skipped\n\n";
    }
} else {
    echo "⚠️  No balance available. Add balance to test sending SMS.\n";
    echo "   Current balance: KSH " . number_format($client->balance, 2) . "\n\n";
}

// Test 5: SMS History
echo "=================================================\n";
echo "TEST 5: SMS History\n";
echo "=================================================\n\n";

try {
    $response = Http::withHeaders([
        'X-API-Key' => $apiKey,
    ])->get("{$baseUrl}/api/{$clientId}/sms/history");
    
    if ($response->successful()) {
        echo "✅ SMS history retrieved\n";
        $data = $response->json();
        echo "Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    } else {
        echo "❌ SMS history failed\n";
        echo "Status: {$response->status()}\n";
        echo "Response: {$response->body()}\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n\n";
}

// Test 6: SMS Statistics
echo "=================================================\n";
echo "TEST 6: SMS Statistics\n";
echo "=================================================\n\n";

try {
    $response = Http::withHeaders([
        'X-API-Key' => $apiKey,
    ])->get("{$baseUrl}/api/{$clientId}/sms/statistics");
    
    if ($response->successful()) {
        echo "✅ SMS statistics retrieved\n";
        $data = $response->json();
        echo "Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    } else {
        echo "❌ SMS statistics failed\n";
        echo "Status: {$response->status()}\n";
        echo "Response: {$response->body()}\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n\n";
}

// Test 7: Invalid API Key
echo "=================================================\n";
echo "TEST 7: Invalid API Key (Security Test)\n";
echo "=================================================\n\n";

try {
    $response = Http::withHeaders([
        'X-API-Key' => 'invalid_api_key_12345',
    ])->get("{$baseUrl}/api/{$clientId}/client/balance");
    
    if ($response->status() === 401) {
        echo "✅ Invalid API key correctly rejected\n";
        echo "Response: {$response->body()}\n\n";
    } else {
        echo "⚠️  Security issue: Invalid API key not rejected properly\n";
        echo "Status: {$response->status()}\n";
        echo "Response: {$response->body()}\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n\n";
}

// Summary
echo "=================================================\n";
echo "    TEST SUMMARY\n";
echo "=================================================\n\n";

echo "Client: {$client->name}\n";
echo "API Key: {$client->api_key}\n";
echo "Balance: KSH " . number_format($client->balance, 2) . "\n";
echo "Units: " . number_format($client->getBalanceInUnits(), 2) . "\n";
echo "Status: " . ($client->status ? 'Active ✅' : 'Inactive ❌') . "\n\n";

echo "API Base URL: {$baseUrl}\n\n";

echo "Next Steps:\n";
echo "1. Add balance to the account if needed\n";
echo "2. Share API credentials with PRADY_TECH\n";
echo "3. Test with real phone number\n";
echo "4. Monitor usage in admin dashboard\n\n";

echo "For full documentation, see: SENDER_API_DOCUMENTATION.md\n\n";

