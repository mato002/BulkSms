<?php

/**
 * EXTERNAL COMPANY SIMULATION
 * 
 * This script simulates an external organization (like Prady Tech)
 * using the Bulk SMS API to send messages.
 * 
 * Company: Prady Technologies
 * Use Case: Sending SMS notifications to customers
 */

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         PRADY TECHNOLOGIES - SMS API CLIENT               ║\n";
echo "║         Testing SMS Send via External API                 ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Configuration (Would be in .env file in production)
$config = [
    'api_url' => 'https://crm.pradytecai.com/api',
    'client_id' => 1,
    'api_key' => 'bae377bc-0282-4fc9-a2a1-e338b18da77a',
    'sender_id' => 'PRADY_TECH',
];

echo "📋 Configuration:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "API URL:    " . $config['api_url'] . "\n";
echo "Client ID:  " . $config['client_id'] . "\n";
echo "API Key:    " . substr($config['api_key'], 0, 20) . "...\n";
echo "Sender ID:  " . $config['sender_id'] . "\n\n";

// Message to send
$recipient = '254728883160';
$message = 'Hello! This is Prady Technologies testing our SMS integration at ' . date('H:i:s') . '. Our API connection is working! 🎉';

echo "📱 Message Details:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "To:      " . $recipient . "\n";
echo "From:    " . $config['sender_id'] . "\n";
echo "Message: " . substr($message, 0, 60) . "...\n\n";

// Prepare API request
$endpoint = $config['api_url'] . '/' . $config['client_id'] . '/messages/send';

$data = [
    'client_id' => $config['client_id'],
    'channel' => 'sms',
    'recipient' => $recipient,
    'body' => $message,
    'sender' => $config['sender_id'],
];

echo "🚀 Sending API Request...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Endpoint: " . $endpoint . "\n";
echo "Method:   POST\n";
echo "Format:   JSON\n\n";

// Initialize cURL
$ch = curl_init($endpoint);

// Set options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing (use true in production)
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: ' . $config['api_key'],
    'Content-Type: application/json',
    'Accept: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Execute request
$startTime = microtime(true);
$response = curl_exec($ch);
$endTime = microtime(true);

// Get request info
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$responseTime = round(($endTime - $startTime) * 1000, 2);

// Check for errors
if (curl_errno($ch)) {
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "❌ cURL ERROR:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo $error . "\n\n";
    exit(1);
}

curl_close($ch);

// Parse response
$responseData = json_decode($response, true);

echo "📊 API Response:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Status Code:    " . $httpCode . "\n";
echo "Response Time:  " . $responseTime . "ms\n";
echo "Status:         " . ($responseData['status'] ?? 'unknown') . "\n\n";

// Display detailed response
if ($httpCode >= 200 && $httpCode < 300) {
    echo "✅ SUCCESS! SMS SENT!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    if (isset($responseData['data'])) {
        echo "Message ID:      " . ($responseData['data']['id'] ?? 'N/A') . "\n";
        echo "Status:          " . ($responseData['data']['status'] ?? 'N/A') . "\n";
        echo "Provider ID:     " . ($responseData['data']['provider_message_id'] ?? 'N/A') . "\n";
    }
    
    echo "\n📱 SMS should arrive at " . $recipient . " shortly!\n";
    echo "🎉 Integration test PASSED!\n\n";
    
} else {
    echo "❌ FAILED!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Error Message: " . ($responseData['message'] ?? 'Unknown error') . "\n";
    
    if (isset($responseData['errors'])) {
        echo "\nValidation Errors:\n";
        foreach ($responseData['errors'] as $field => $errors) {
            echo "  - $field: " . implode(', ', $errors) . "\n";
        }
    }
    
    if (isset($responseData['error_details'])) {
        echo "\nError Details:\n";
        print_r($responseData['error_details']);
    }
    
    echo "\n";
}

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║                  TEST COMPLETE                             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Show next steps
if ($httpCode >= 200 && $httpCode < 300) {
    echo "✨ Next Steps for Prady Technologies:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "1. ✅ API Integration: Working\n";
    echo "2. ✅ Authentication: Successful\n";
    echo "3. ✅ SMS Delivery: Active\n";
    echo "4. 📊 Monitor usage at: https://crm.pradytecai.com/api-monitor\n";
    echo "5. 💰 Check balance regularly\n";
    echo "6. 📚 View full docs: https://crm.pradytecai.com/api-documentation\n\n";
    
    echo "🔗 Integration is PRODUCTION READY!\n\n";
}

