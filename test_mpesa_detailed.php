<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\MpesaService;

// Clear screen
echo "\033[2J\033[;H";

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║     M-PESA DIAGNOSTIC TEST - BulkSMS Platform                 ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Step 1: Configuration Check
echo "📋 STEP 1: Configuration Check\n";
echo str_repeat('─', 64) . "\n";

$configs = [
    'Environment' => config('mpesa.env'),
    'Shortcode' => config('mpesa.shortcode'),
    'Consumer Key' => config('mpesa.consumer_key') ? substr(config('mpesa.consumer_key'), 0, 25) . '...' : 'NOT SET',
    'Consumer Secret' => config('mpesa.consumer_secret') ? 'SET (hidden)' : 'NOT SET',
    'Passkey' => config('mpesa.passkey') ? 'SET (hidden)' : 'NOT SET',
    'Callback URL' => config('mpesa.callback_url'),
];

foreach ($configs as $key => $value) {
    printf("%-18s: %s\n", $key, $value);
}

// IMPORTANT: Sandbox Warning
if (config('mpesa.env') === 'sandbox') {
    echo "\n";
    echo "⚠️  ⚠️  ⚠️  CRITICAL INFORMATION ⚠️  ⚠️  ⚠️\n";
    echo str_repeat('─', 64) . "\n";
    echo "YOU ARE USING SANDBOX MODE!\n\n";
    echo "Sandbox Limitations:\n";
    echo "❌ STK push prompts DO NOT go to REAL phones\n";
    echo "❌ You CANNOT receive the payment popup on 0728883160\n";
    echo "❌ Sandbox only works with M-Pesa Sandbox Testing App\n";
    echo "❌ This is for development/testing ONLY\n\n";
    
    echo "Why you can't see the popup:\n";
    echo "• Safaricom's sandbox doesn't send real STK push prompts\n";
    echo "• You need the M-Pesa Sandbox app (not available for all)\n";
    echo "• OR you need to use PRODUCTION credentials\n\n";
    
    echo "✅ To receive popups on your real phone (0728883160):\n";
    echo "1. Apply for production M-Pesa credentials from Safaricom\n";
    echo "2. Get approved (usually takes 1-2 weeks)\n";
    echo "3. Update .env with production credentials\n";
    echo "4. Change MPESA_ENV=production\n";
    echo str_repeat('─', 64) . "\n";
} else {
    echo "\n✅ PRODUCTION MODE - STK push should work on real phones\n";
    
    if (config('mpesa.shortcode') === '174379') {
        echo "\n⚠️  WARNING: Using sandbox shortcode in production mode!\n";
        echo "   Update MPESA_SHORTCODE to your production shortcode.\n";
    }
}

// Step 2: Test Authentication
echo "\n\n📡 STEP 2: Testing M-Pesa Authentication\n";
echo str_repeat('─', 64) . "\n";

$mpesa = new MpesaService();
$token = $mpesa->getAccessToken();

if ($token) {
    echo "✅ Authentication SUCCESSFUL!\n";
    echo "Access Token: " . substr($token, 0, 40) . "...\n";
    echo "\nYour credentials are VALID and working.\n";
} else {
    echo "❌ Authentication FAILED!\n";
    echo "Your M-Pesa credentials are incorrect or invalid.\n";
    echo "Check your .env file and verify:\n";
    echo "  - MPESA_CONSUMER_KEY\n";
    echo "  - MPESA_CONSUMER_SECRET\n";
    die("\n⛔ Cannot proceed without valid authentication.\n\n");
}

// Step 3: Test STK Push
echo "\n\n📱 STEP 3: Testing STK Push Request\n";
echo str_repeat('─', 64) . "\n";

$phoneNumber = '254728883160';
$amount = 10;
$reference = 'TEST-' . time();
$description = 'Diagnostic Test - BulkSMS Platform';

echo "Phone Number: $phoneNumber\n";
echo "Amount: KES $amount\n";
echo "Reference: $reference\n";
echo "Description: $description\n\n";

echo "Sending STK push request...\n";

$result = $mpesa->initiateSTKPush(
    $phoneNumber,
    $amount,
    $reference,
    $description
);

echo "\n" . str_repeat('─', 64) . "\n";
echo "📊 RESULT:\n";
echo str_repeat('─', 64) . "\n";

if ($result['success']) {
    echo "✅ STK Push Request SENT SUCCESSFULLY!\n\n";
    echo "Status: Request accepted by M-Pesa\n";
    echo "Message: " . ($result['message'] ?? 'N/A') . "\n";
    echo "Checkout Request ID: " . ($result['checkout_request_id'] ?? 'N/A') . "\n";
    echo "Merchant Request ID: " . ($result['merchant_request_id'] ?? 'N/A') . "\n";
    echo "Response Code: " . ($result['response_code'] ?? 'N/A') . "\n";
    
    if (config('mpesa.env') === 'sandbox') {
        echo "\n" . str_repeat('─', 64) . "\n";
        echo "⚠️  IMPORTANT:\n";
        echo "The request was SENT successfully, BUT...\n";
        echo "You will NOT receive a popup on your phone because:\n";
        echo "• You're in SANDBOX mode\n";
        echo "• Sandbox doesn't send to real phones\n";
        echo "• You need PRODUCTION credentials to receive real popups\n";
        echo str_repeat('─', 64) . "\n";
    } else {
        echo "\n📱 Check your phone (0728883160) for M-Pesa popup!\n";
        echo "The popup should appear within 10-30 seconds.\n";
    }
} else {
    echo "❌ STK Push Request FAILED!\n\n";
    echo "Error Message: " . ($result['message'] ?? 'Unknown error') . "\n";
    echo "Response Code: " . ($result['response_code'] ?? 'N/A') . "\n";
    
    echo "\n" . str_repeat('─', 64) . "\n";
    echo "🔍 Common Issues:\n";
    echo "1. Invalid credentials\n";
    echo "2. Network/SSL issues\n";
    echo "3. Invalid phone number format\n";
    echo "4. Insufficient permissions on M-Pesa account\n";
    echo "5. Sandbox limitations (if in sandbox mode)\n";
    echo str_repeat('─', 64) . "\n";
}

// Step 4: Summary and Next Steps
echo "\n\n" . str_repeat('═', 64) . "\n";
echo "📝 SUMMARY & NEXT STEPS\n";
echo str_repeat('═', 64) . "\n";

if (config('mpesa.env') === 'sandbox') {
    echo "\n🎯 YOUR SITUATION:\n";
    echo "• Your M-Pesa integration is WORKING\n";
    echo "• BUT you're in SANDBOX mode\n";
    echo "• Sandbox doesn't send to real phones\n\n";
    
    echo "💡 SOLUTIONS:\n\n";
    
    echo "Option 1: Switch to Production (RECOMMENDED)\n";
    echo str_repeat('─', 64) . "\n";
    echo "1. Apply for production credentials:\n";
    echo "   https://developer.safaricom.co.ke/\n";
    echo "2. Wait for approval (1-2 weeks)\n";
    echo "3. Update your .env file:\n";
    echo "   MPESA_ENV=production\n";
    echo "   MPESA_CONSUMER_KEY=your_production_key\n";
    echo "   MPESA_CONSUMER_SECRET=your_production_secret\n";
    echo "   MPESA_PASSKEY=your_production_passkey\n";
    echo "   MPESA_SHORTCODE=your_paybill_number\n\n";
    
    echo "Option 2: Use M-Pesa Sandbox App (Limited)\n";
    echo str_repeat('─', 64) . "\n";
    echo "• Download M-Pesa Sandbox app (if available)\n";
    echo "• Use test credentials from Safaricom\n";
    echo "• Only works for testing, not real transactions\n\n";
    
    echo "Option 3: Manual Payment Alternative\n";
    echo str_repeat('─', 64) . "\n";
    echo "• Enable manual payment option in your system\n";
    echo "• Accept payments via Paybill/Till Number manually\n";
    echo "• Update balance manually until M-Pesa production ready\n\n";
    
} else {
    echo "\n✅ You're in PRODUCTION mode.\n";
    echo "If the test passed, you should receive the popup.\n";
    echo "If not, check:\n";
    echo "• Phone number is correct\n";
    echo "• Phone has network coverage\n";
    echo "• M-Pesa service is active on the phone\n";
    echo "• Check Laravel logs: storage/logs/laravel.log\n";
}

echo str_repeat('═', 64) . "\n";
echo "Test completed: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat('═', 64) . "\n\n";

// Log location
echo "📁 Check detailed logs at: storage/logs/laravel.log\n\n";



