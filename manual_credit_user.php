<?php

/**
 * Manual User Credit Script
 * Use this while waiting for M-Pesa production approval
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Client;
use App\Models\WalletTransaction;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          MANUAL USER CREDIT - BulkSMS Platform                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "This script allows you to manually credit user accounts\n";
echo "Use this while waiting for M-Pesa production approval.\n\n";

// Get user input
echo "Enter user phone number (e.g., 254728883160): ";
$phoneNumber = trim(fgets(STDIN));

// Find user
$user = Client::where('contact', $phoneNumber)
    ->orWhere('contact', 'LIKE', '%' . substr($phoneNumber, -9))
    ->first();

if (!$user) {
    echo "\n❌ User not found with phone number: $phoneNumber\n";
    echo "Please check the number and try again.\n\n";
    exit(1);
}

echo "\n✅ User Found:\n";
echo "   Name: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   Current Balance: KES " . number_format($user->balance, 2) . "\n";
echo "   SMS Units: " . number_format($user->getBalanceInUnits()) . "\n\n";

echo "Enter amount to credit (KES): ";
$amount = trim(fgets(STDIN));

if (!is_numeric($amount) || $amount <= 0) {
    echo "\n❌ Invalid amount. Please enter a positive number.\n\n";
    exit(1);
}

echo "\nEnter M-Pesa transaction code (optional, press Enter to skip): ";
$mpesaCode = trim(fgets(STDIN));
if (empty($mpesaCode)) {
    $mpesaCode = 'MANUAL-' . strtoupper(uniqid());
}

echo "\n" . str_repeat('─', 64) . "\n";
echo "📋 CONFIRMATION:\n";
echo str_repeat('─', 64) . "\n";
echo "User: {$user->name} ({$user->contact})\n";
echo "Current Balance: KES " . number_format($user->balance, 2) . "\n";
echo "Amount to Credit: KES " . number_format($amount, 2) . "\n";
echo "New Balance: KES " . number_format($user->balance + $amount, 2) . "\n";
echo "Transaction Code: {$mpesaCode}\n";
echo str_repeat('─', 64) . "\n";

echo "\nProceed with credit? (yes/no): ";
$confirm = trim(fgets(STDIN));

if (strtolower($confirm) !== 'yes' && strtolower($confirm) !== 'y') {
    echo "\n❌ Credit cancelled.\n\n";
    exit(0);
}

try {
    // Add balance
    $user->addBalance($amount);
    
    // Create transaction record
    WalletTransaction::create([
        'client_id' => $user->id,
        'type' => 'credit',
        'amount' => $amount,
        'balance_before' => $user->balance - $amount,
        'balance_after' => $user->balance,
        'reference' => $mpesaCode,
        'description' => 'Manual top-up - M-Pesa payment verified',
        'status' => 'completed',
        'payment_method' => 'manual_mpesa',
        'mpesa_receipt_number' => $mpesaCode
    ]);
    
    echo "\n✅ SUCCESS!\n\n";
    echo "User credited successfully:\n";
    echo "   New Balance: KES " . number_format($user->balance, 2) . "\n";
    echo "   SMS Units: " . number_format($user->getBalanceInUnits()) . "\n";
    echo "   Transaction: {$mpesaCode}\n\n";
    
    echo "The user can now send " . number_format($user->getBalanceInUnits()) . " SMS messages!\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo str_repeat('═', 64) . "\n";
echo "✓ Transaction completed at " . date('Y-m-d H:i:s') . "\n";
echo str_repeat('═', 64) . "\n\n";



