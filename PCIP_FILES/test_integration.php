<?php
/**
 * Test PCIP Integration with BulkSms CRM
 * 
 * Run this file to test the integration before deploying to production
 */

require_once 'BulkSmsHelper.php';

echo "=================================================\n";
echo "  TESTING PCIP + BULKSMS CRM INTEGRATION\n";
echo "=================================================\n\n";

$sms = new BulkSmsHelper();

// Test 1: Check Balance
echo "Test 1: Checking FORTRESS balance...\n";
$balance = $sms->checkBalance();

if ($balance) {
    echo "✅ Connection successful!\n";
    echo "   Balance: KSH " . ($balance['balance'] ?? 'N/A') . "\n";
    echo "   Client: " . ($balance['client_name'] ?? 'FORTRESS') . "\n";
} else {
    echo "❌ Failed to check balance\n";
    echo "   Please verify API credentials and server connection\n";
}
echo "\n";

// Test 2: Validate Phone Numbers
echo "Test 2: Testing phone number validation...\n";
$testNumbers = [
    '0728883160' => true,
    '728883160' => true,
    '254728883160' => true,
    '+254728883160' => true,
    '0712345678' => true,
    '1234' => false,
    'invalid' => false
];

foreach ($testNumbers as $number => $shouldBeValid) {
    $isValid = $sms->isValidPhoneNumber($number);
    $status = ($isValid === $shouldBeValid) ? '✅' : '❌';
    echo "   $status $number - " . ($isValid ? 'Valid' : 'Invalid') . "\n";
}
echo "\n";

// Test 3: Send Test SMS
echo "Test 3: Sending test SMS...\n";
echo "Enter phone number to test (or press Enter to skip): ";
$phone = trim(fgets(STDIN));

if (!empty($phone)) {
    $message = "Test SMS from PCIP via BulkSms CRM (FORTRESS) - " . date('Y-m-d H:i:s');
    
    echo "Sending to: $phone\n";
    echo "Message: $message\n\n";
    
    $result = $sms->sendSms($phone, $message);
    
    if ($result['success']) {
        echo "✅ SMS sent successfully!\n";
        echo "   Message ID: " . $result['message_id'] . "\n";
        echo "   Status: " . $result['status'] . "\n";
        echo "   Provider ID: " . ($result['provider_message_id'] ?? 'N/A') . "\n";
        echo "\n   📱 Check the phone for the SMS!\n";
    } else {
        echo "❌ Failed to send SMS\n";
        echo "   Error: " . $result['error'] . "\n";
        if (isset($result['errors'])) {
            print_r($result['errors']);
        }
    }
} else {
    echo "Skipped.\n";
}
echo "\n";

// Test Summary
echo "=================================================\n";
echo "  TESTING COMPLETE\n";
echo "=================================================\n\n";

echo "Next Steps:\n";
echo "1. If all tests passed, copy BulkSmsHelper.php to your PCIP includes folder\n";
echo "2. Implement SMS sending in your application logic\n";
echo "3. Monitor balance regularly\n";
echo "4. Check sent messages in BulkSms CRM dashboard\n\n";


