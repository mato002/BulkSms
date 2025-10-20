<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\MpesaService;

echo "\n";
echo "=================================================\n";
echo "    TESTING M-PESA STK PUSH\n";
echo "=================================================\n\n";

$mpesa = new MpesaService();

// Test parameters
$phoneNumber = '254728883160';  // Your phone number
$amount = 10;  // Test amount (KES 10)
$accountReference = 'TEST-' . time();
$transactionDesc = 'Test Top-up - BulkSms CRM';

echo "Phone Number: $phoneNumber\n";
echo "Amount: KES $amount\n";
echo "Reference: $accountReference\n";
echo "Description: $transactionDesc\n";
echo "\n";

echo "Environment: " . config('mpesa.env') . "\n";
echo "Shortcode: " . config('mpesa.shortcode') . "\n";
echo "Consumer Key: " . substr(config('mpesa.consumer_key'), 0, 20) . "...\n";
echo "\n";

echo "Initiating STK Push...\n";
echo str_repeat('-', 50) . "\n";

$result = $mpesa->initiateSTKPush(
    $phoneNumber,
    $amount,
    $accountReference,
    $transactionDesc
);

echo "\n";
echo "Result:\n";
echo str_repeat('-', 50) . "\n";

if ($result['success']) {
    echo "✅ SUCCESS!\n\n";
    echo "Status: STK Push sent successfully\n";
    echo "Message: " . ($result['message'] ?? 'N/A') . "\n";
    echo "Checkout Request ID: " . ($result['checkout_request_id'] ?? 'N/A') . "\n";
    echo "Merchant Request ID: " . ($result['merchant_request_id'] ?? 'N/A') . "\n";
    echo "\n";
    echo "📱 Check your phone (0728883160) for M-Pesa prompt!\n";
    echo "\n";
    echo "💡 Note: If using sandbox, the prompt may not appear on a real phone.\n";
    echo "   Use the M-Pesa sandbox app to test, or use production credentials.\n";
} else {
    echo "❌ FAILED!\n\n";
    echo "Error: " . ($result['message'] ?? 'Unknown error') . "\n";
    echo "Response Code: " . ($result['response_code'] ?? 'N/A') . "\n";
    echo "\n";
    
    // Common issues and solutions
    echo "Common Issues:\n";
    echo "1. Invalid credentials - Check config/mpesa.php\n";
    echo "2. Sandbox limitations - Real phones won't receive sandbox prompts\n";
    echo "3. Network/SSL issues - Check internet connection\n";
    echo "4. Invalid phone number - Must be 254XXXXXXXXX format\n";
}

echo "\n=================================================\n";

// Test access token separately
echo "\nTesting M-Pesa Authentication...\n";
echo str_repeat('-', 50) . "\n";

$token = $mpesa->getAccessToken();

if ($token) {
    echo "✅ Authentication successful!\n";
    echo "Access Token: " . substr($token, 0, 30) . "...\n";
} else {
    echo "❌ Authentication failed!\n";
    echo "Please check your M-Pesa credentials in config/mpesa.php\n";
}

echo "\n=================================================\n";
echo "Test complete!\n";
echo "=================================================\n\n";



