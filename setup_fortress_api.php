<?php

/**
 * Setup FORTRESS Sender API Credentials for PCIP System
 * 
 * This script generates/retrieves API credentials for FORTRESS
 * so the PCIP system can send SMS through our platform
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Client;
use App\Models\Channel;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

echo "=================================================\n";
echo "    FORTRESS API SETUP FOR PCIP SYSTEM\n";
echo "=================================================\n\n";

// Check if FORTRESS exists
$fortress = Client::where('sender_id', 'FORTRESS')->first();

if (!$fortress) {
    echo "❌ FORTRESS client not found. Creating...\n\n";
    
    $fortress = Client::create([
        'name' => 'Fortress Limited',
        'contact' => 'admin@fortress.co.ke',
        'sender_id' => 'FORTRESS',
        'company_name' => 'FORTRESS',
        'balance' => 0,
        'price_per_unit' => 1.00,
        'api_key' => Str::random(32),
        'status' => true,
        'tier' => 'standard',
        'is_test_mode' => false,
        'auto_sync_balance' => true,
    ]);
    
    echo "✅ Created FORTRESS client\n\n";
} else {
    echo "✅ Found existing FORTRESS client\n\n";
    
    // Update API key if needed
    if (empty($fortress->api_key) || strlen($fortress->api_key) < 20) {
        $fortress->api_key = Str::random(32);
        $fortress->save();
        echo "🔄 Generated new API key\n\n";
    }
}

// Ensure SMS channel is configured
$smsChannel = Channel::where('client_id', $fortress->id)
    ->where('name', 'sms')
    ->first();

if (!$smsChannel) {
    echo "❌ SMS channel not found. Creating...\n\n";
    
    $smsChannel = Channel::create([
        'client_id' => $fortress->id,
        'name' => 'sms',
        'provider' => 'onfon',
        'credentials' => json_encode([
            'api_key' => env('ONFON_API_KEY', 'VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak='),
            'client_id' => env('ONFON_CLIENT_ID', 'e27847c1-a9fe-4eef-b60d-ddb291b175ab'),
            'access_key_header' => '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB',
            'default_sender' => 'FORTRESS',
        ]),
        'active' => true,
    ]);
    
    echo "✅ Created SMS channel with Onfon integration\n\n";
} else {
    echo "✅ SMS channel already configured\n\n";
}

// Check/create user account for web access (optional)
$user = $fortress->users()->first();
if (!$user) {
    echo "⚠️  No user account found. Creating one...\n\n";
    
    $user = User::create([
        'name' => 'Fortress Admin',
        'email' => 'admin@fortress.co.ke',
        'password' => Hash::make('fortress123'),
        'client_id' => $fortress->id,
        'role' => 'user',
    ]);
    
    echo "✅ User created for web dashboard access\n";
    echo "   Email: admin@fortress.co.ke\n";
    echo "   Password: fortress123\n\n";
}

// Add balance if needed
if ($fortress->balance < 10) {
    echo "⚠️  Low balance. Adding KSH 100 for testing...\n";
    $fortress->balance = 100.00;
    $fortress->save();
    echo "✅ Balance updated to KSH 100.00\n\n";
}

echo "=================================================\n";
echo "    API CREDENTIALS FOR PCIP SYSTEM\n";
echo "=================================================\n\n";

echo "Client ID:      {$fortress->id}\n";
echo "Sender ID:      {$fortress->sender_id}\n";
echo "API Key:        {$fortress->api_key}\n";
echo "Balance:        KSH " . number_format($fortress->balance, 2) . "\n";
echo "Units:          " . number_format($fortress->getBalanceInUnits(), 2) . "\n";
echo "Price/Unit:     KSH " . number_format($fortress->price_per_unit, 2) . "\n";
echo "Status:         " . ($fortress->status ? 'Active ✅' : 'Inactive ❌') . "\n";
echo "Tier:           {$fortress->tier}\n";

echo "\n=================================================\n";
echo "    API ENDPOINT INFORMATION\n";
echo "=================================================\n\n";

$baseUrl = env('APP_URL', 'http://localhost');

echo "Base URL:       {$baseUrl}\n\n";

echo "Send SMS Endpoint:\n";
echo "  POST {$baseUrl}/api/{$fortress->id}/messages/send\n\n";

echo "Check Balance:\n";
echo "  GET {$baseUrl}/api/{$fortress->id}/client/balance\n\n";

echo "SMS History:\n";
echo "  GET {$baseUrl}/api/{$fortress->id}/sms/history\n\n";

echo "\n=================================================\n";
echo "    PCIP CONFIGURATION\n";
echo "=================================================\n\n";

echo "Add these settings to PCIP system:\n\n";

echo "1. API Configuration:\n";
echo "   - API Base URL: {$baseUrl}/api\n";
echo "   - Client ID: {$fortress->id}\n";
echo "   - API Key: {$fortress->api_key}\n";
echo "   - Sender Name: FORTRESS\n\n";

echo "2. Headers to Include:\n";
echo "   - X-API-Key: {$fortress->api_key}\n";
echo "   - Content-Type: application/json\n\n";

echo "3. Send SMS Request Format:\n";
echo "   POST {$baseUrl}/api/{$fortress->id}/messages/send\n";
echo "   Body: {\n";
echo "     \"channel\": \"sms\",\n";
echo "     \"recipient\": \"254XXXXXXXXX\",\n";
echo "     \"body\": \"Your message here\",\n";
echo "     \"sender\": \"FORTRESS\"\n";
echo "   }\n\n";

echo "=================================================\n";
echo "    EXAMPLE: PHP CODE FOR PCIP\n";
echo "=================================================\n\n";

$phpCode = <<<'PHP'
<?php
// PCIP - SMS Integration with Bulk SMS API

// Configuration
$apiBaseUrl = '%BASE_URL%';
$clientId = %CLIENT_ID%;
$apiKey = '%API_KEY%';

// Function to send SMS
function sendSMS($recipient, $message) {
    global $apiBaseUrl, $clientId, $apiKey;
    
    $url = $apiBaseUrl . '/api/' . $clientId . '/messages/send';
    
    $data = [
        'channel' => 'sms',
        'recipient' => $recipient,
        'body' => $message,
        'sender' => 'FORTRESS'
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-Key: ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        return [
            'success' => true,
            'message_id' => $result['data']['id'] ?? null,
            'response' => $result
        ];
    } else {
        return [
            'success' => false,
            'error' => $response,
            'http_code' => $httpCode
        ];
    }
}

// Function to check balance
function checkBalance() {
    global $apiBaseUrl, $clientId, $apiKey;
    
    $url = $apiBaseUrl . '/api/' . $clientId . '/client/balance';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-Key: ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return null;
}

// Example usage:
// $result = sendSMS('254712345678', 'Test message from PCIP system');
// if ($result['success']) {
//     echo "SMS sent! Message ID: " . $result['message_id'];
// } else {
//     echo "Failed: " . $result['error'];
// }

PHP;

$phpCode = str_replace('%BASE_URL%', $baseUrl, $phpCode);
$phpCode = str_replace('%CLIENT_ID%', $fortress->id, $phpCode);
$phpCode = str_replace('%API_KEY%', $fortress->api_key, $phpCode);

echo $phpCode;

echo "\n=================================================\n";
echo "    TESTING THE INTEGRATION\n";
echo "=================================================\n\n";

echo "Test with cURL:\n\n";
echo "curl -X POST {$baseUrl}/api/{$fortress->id}/messages/send \\\n";
echo "  -H \"X-API-Key: {$fortress->api_key}\" \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\n";
echo "    \"channel\": \"sms\",\n";
echo "    \"recipient\": \"254712345678\",\n";
echo "    \"body\": \"Test from PCIP\",\n";
echo "    \"sender\": \"FORTRESS\"\n";
echo "  }'\n\n";

// Save configuration to file
$configFile = __DIR__ . '/FORTRESS_PCIP_CONFIG.txt';
$configContent = "
=================================================
    FORTRESS API CREDENTIALS FOR PCIP
=================================================

Generated: " . date('Y-m-d H:i:s') . "

Client ID:      {$fortress->id}
Sender ID:      FORTRESS
API Key:        {$fortress->api_key}

=================================================
    API ENDPOINTS
=================================================

Base URL: {$baseUrl}

1. Send SMS:
   POST {$baseUrl}/api/{$fortress->id}/messages/send
   
2. Check Balance:
   GET {$baseUrl}/api/{$fortress->id}/client/balance
   
3. SMS History:
   GET {$baseUrl}/api/{$fortress->id}/sms/history

=================================================
    HEADERS (Required)
=================================================

X-API-Key: {$fortress->api_key}
Content-Type: application/json

=================================================
    SEND SMS REQUEST EXAMPLE
=================================================

{
    \"channel\": \"sms\",
    \"recipient\": \"254712345678\",
    \"body\": \"Your message here\",
    \"sender\": \"FORTRESS\"
}

=================================================
    PCIP INTEGRATION STEPS
=================================================

1. In PCIP system, configure SMS settings:
   - API URL: {$baseUrl}/api
   - Client ID: {$fortress->id}
   - API Key: {$fortress->api_key}
   - Sender: FORTRESS

2. When sending SMS from PCIP:
   - Make POST request to send endpoint
   - Include X-API-Key header
   - Send JSON with recipient and message

3. Check balance before sending:
   - GET request to balance endpoint
   - Returns available balance and units

4. Track sent messages:
   - GET request to history endpoint
   - View all sent SMS with status

=================================================
    SUPPORT
=================================================

For issues or questions, contact system administrator.

Balance: KSH " . number_format($fortress->balance, 2) . "
Units Available: " . number_format($fortress->getBalanceInUnits(), 2) . "
Status: " . ($fortress->status ? 'Active' : 'Inactive') . "

";

file_put_contents($configFile, $configContent);
echo "✅ Configuration saved to: {$configFile}\n\n";

echo "=================================================\n";
echo "    NEXT STEPS\n";
echo "=================================================\n\n";

echo "1. Copy the API credentials above\n";
echo "2. Navigate to PCIP system directory:\n";
echo "   cd C:\\xampp\\htdocs\\PCIP\n\n";
echo "3. Configure PCIP with these settings:\n";
echo "   - API URL: {$baseUrl}/api\n";
echo "   - Client ID: {$fortress->id}\n";
echo "   - API Key: {$fortress->api_key}\n\n";
echo "4. Test the integration using the cURL command above\n";
echo "5. Monitor usage through admin dashboard\n\n";

echo "FORTRESS is ready for PCIP integration! ✅\n\n";

