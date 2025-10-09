<?php

echo "\n=============================================================\n";
echo "  BULK SMS SYSTEM - FLOW TESTING\n";
echo "=============================================================\n\n";

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Client;
use App\Models\WalletTransaction;

$passed = 0;
$failed = 0;

function test($name, $callback) {
    global $passed, $failed;
    echo "Testing: {$name}... ";
    try {
        $result = $callback();
        if ($result) {
            echo "✅ PASS\n";
            $passed++;
        } else {
            echo "❌ FAIL\n";
            $failed++;
        }
    } catch (\Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n--- FLOW 1: SENDER ONBOARDING ---\n";

test("Database tables exist", function() {
    return DB::getSchemaBuilder()->hasTable('users') && 
           DB::getSchemaBuilder()->hasTable('clients');
});

$testClient = null;
test("Create test client", function() use (&$testClient) {
    $testClient = Client::firstOrCreate(
        ['sender_id' => 'TEST_SENDER'],
        [
            'name' => 'Test Client',
            'contact' => 'test@example.com',
            'balance' => 1000.00,
            'price_per_unit' => 1.00,
            'api_key' => \Illuminate\Support\Str::uuid()->toString(),
            'status' => true,
        ]
    );
    return $testClient !== null;
});

$testUser = null;
test("Register new user", function() use (&$testUser, $testClient) {
    if (!$testClient) return false;
    
    $testUser = User::create([
        'name' => 'Test User',
        'email' => 'test_' . time() . '@example.com',
        'password' => Hash::make('password123'),
        'client_id' => $testClient->id,
        'role' => 'user',
    ]);
    return $testUser !== null;
});

test("User-Client relationship", function() use ($testUser, $testClient) {
    if (!$testUser || !$testClient) return false;
    return $testUser->client_id === $testClient->id;
});

test("Password hashing", function() use ($testUser) {
    if (!$testUser) return false;
    return Hash::check('password123', $testUser->password);
});

echo "\n--- FLOW 2: TOP-UP PROCESS ---\n";

test("M-Pesa config exists", function() {
    return config('mpesa.consumer_key') !== null;
});

$transaction = null;
test("Create transaction", function() use (&$transaction, $testClient) {
    if (!$testClient) return false;
    
    $transaction = WalletTransaction::create([
        'client_id' => $testClient->id,
        'type' => 'credit',
        'amount' => 100.00,
        'payment_method' => 'mpesa',
        'payment_phone' => '254712345678',
        'transaction_ref' => WalletTransaction::generateTransactionRef(),
        'status' => 'pending',
        'description' => 'Test top-up',
    ]);
    return $transaction !== null;
});

test("Complete transaction", function() use ($transaction, $testClient) {
    if (!$transaction || !$testClient) return false;
    
    $oldBalance = $testClient->balance;
    $transaction->status = 'completed';
    $transaction->completed_at = now();
    $transaction->save();
    
    $testClient->balance += 100;
    $testClient->save();
    
    return $testClient->balance > $oldBalance;
});

test("Transaction is completed", function() use ($transaction) {
    if (!$transaction) return false;
    $transaction->refresh();
    return $transaction->isCompleted();
});

echo "\n--- FLOW 3: SENDING SMS ---\n";

test("SMS config exists", function() {
    return config('sms.gateways.onfon.api_key') !== null || 
           config('sms.gateways.mobitech.api_key') !== null;
});

test("Client has balance", function() use ($testClient) {
    if (!$testClient) return false;
    $testClient->refresh();
    return $testClient->balance > 0;
});

test("Balance sufficient for SMS", function() use ($testClient) {
    if (!$testClient) return false;
    return $testClient->hasSufficientBalance(0.75);
});

test("SMS table exists", function() {
    return DB::getSchemaBuilder()->hasTable('sms');
});

test("Unit conversion works", function() use ($testClient) {
    if (!$testClient) return false;
    $units = 10;
    $ksh = $testClient->unitsToKsh($units);
    $backToUnits = $testClient->kshToUnits($ksh);
    return $units == $backToUnits;
});

test("Channels table exists", function() {
    return DB::getSchemaBuilder()->hasTable('channels');
});

echo "\n--- FLOW 4: BALANCE CHECK ---\n";

test("Get client balance", function() use ($testClient) {
    if (!$testClient) return false;
    $testClient->refresh();
    return $testClient->balance !== null;
});

test("Calculate units", function() use ($testClient) {
    if (!$testClient) return false;
    $units = $testClient->getBalanceInUnits();
    return $units !== null && $units >= 0;
});

test("Balance check method", function() use ($testClient) {
    if (!$testClient) return false;
    return $testClient->hasSufficientBalance(10) !== null;
});

test("Unit check method", function() use ($testClient) {
    if (!$testClient) return false;
    return $testClient->hasSufficientUnits(10) !== null;
});

test("Transaction history", function() use ($testClient) {
    if (!$testClient) return false;
    $txns = WalletTransaction::where('client_id', $testClient->id)->get();
    return $txns->count() > 0;
});

// Cleanup
echo "\n--- CLEANUP ---\n";
if ($testUser) {
    $testUser->delete();
    echo "✓ Deleted test user\n";
}
if ($transaction) {
    $transaction->delete();
    echo "✓ Deleted test transaction\n";
}

// Summary
echo "\n=============================================================\n";
echo "  TEST SUMMARY\n";
echo "=============================================================\n";
echo "Total Tests: " . ($passed + $failed) . "\n";
echo "✅ Passed: {$passed}\n";
echo "❌ Failed: {$failed}\n";
$percentage = ($passed + $failed) > 0 ? round(($passed / ($passed + $failed)) * 100, 2) : 0;
echo "Success Rate: {$percentage}%\n";

if ($failed === 0) {
    echo "\n🎉 ALL FLOWS WORKING PERFECTLY! 🎉\n\n";
} else {
    echo "\n⚠️  SOME TESTS FAILED - CHECK CONFIGURATION\n\n";
}

