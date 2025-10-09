<?php

echo "Testing M-Pesa STK Push to: 254728883160\n";
echo "Amount: KES 100\n\n";

$ch = curl_init('http://127.0.0.1:8000/api/1/wallet/topup');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'amount' => 100, // Minimum KES 100
    'payment_method' => 'mpesa',
    'phone_number' => '254728883160'
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: {$httpCode}\n";
echo "Response:\n";
print_r(json_decode($response, true));
echo "\n";

if ($httpCode == 200) {
    echo "✅ SUCCESS! Check phone 254728883160 for M-Pesa popup!\n";
} else {
    echo "❌ FAILED - See response above\n";
}

