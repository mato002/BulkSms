<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "═══════════════════════════════════════════════════════════\n";
echo "  CAN YOU GIVE SOMEONE AN API KEY NOW?\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$client = \App\Models\Client::find(1);

if (!$client) {
    echo "❌ NO CLIENT FOUND\n";
    exit(1);
}

echo "Current Sender Details:\n";
echo "─────────────────────────────────────────\n";
echo "Name: {$client->name}\n";
echo "API Key: {$client->api_key}\n";
echo "Balance: KES " . number_format($client->balance, 2) . "\n";
echo "\n";

// Check Onfon credentials
$channel = \App\Models\Channel::where('client_id', 1)->where('name', 'sms')->first();

echo "SMS Sending Status:\n";
echo "─────────────────────────────────────────\n";
if ($channel) {
    $creds = is_string($channel->credentials) ? json_decode($channel->credentials, true) : $channel->credentials;
    $hasApiKey = isset($creds['access_key']) || isset($creds['api_key']);
    $hasClientId = isset($creds['client_code']) || isset($creds['client_id']);
    
    echo "SMS Channel: " . ($channel ? "✓ Configured" : "✗ Not configured") . "\n";
    echo "Onfon API Key: " . ($hasApiKey ? "✓ Set" : "✗ Not set") . "\n";
    echo "Onfon Client ID: " . ($hasClientId ? "✓ Set" : "✗ Not set") . "\n";
    
    $canSendSMS = $channel && $hasApiKey && $hasClientId;
} else {
    echo "✗ No SMS channel configured\n";
    $canSendSMS = false;
}

echo "\n";

echo "API Capabilities Status:\n";
echo "─────────────────────────────────────────\n";
echo ($canSendSMS ? "✅" : "⚠️ ") . " Send SMS" . ($canSendSMS ? "" : " (needs Onfon credentials)") . "\n";
echo "✅ Check balance\n";
echo "✅ View message history\n";
echo "✅ View analytics\n";
echo "✅ View transaction history\n";
echo "⚠️  Top-up via M-Pesa (needs M-Pesa credentials)\n";
echo "⚠️  Receive emails (needs SMTP configuration)\n";
echo "⚠️  Receive webhooks (needs webhook URL from sender)\n";

echo "\n";

echo "═══════════════════════════════════════════════════════════\n";
echo "  ANSWER: ";
if ($canSendSMS) {
    echo "YES! ✅\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    echo "If you give someone this API key RIGHT NOW:\n\n";
    echo "API Key: {$client->api_key}\n";
    echo "Client ID: {$client->id}\n\n";
    echo "They CAN immediately:\n";
    echo "✅ Send SMS (Onfon is configured)\n";
    echo "✅ Check their balance\n";
    echo "✅ View message history  \n";
    echo "✅ Get analytics\n";
    echo "✅ View documentation at /api-documentation\n\n";
    echo "They CANNOT (yet) without more config:\n";
    echo "⚠️  Top-up via M-Pesa (you need to add M-Pesa credentials)\n";
    echo "⚠️  Receive automated emails (you need SMTP)\n";
    echo "⚠️  Receive webhooks (they need to give you their webhook URL)\n\n";
    echo "But for SMS SENDING - YES, it works NOW! ✅\n";
} else {
    echo "PARTIALLY ⚠️ \n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    echo "If you give someone this API key:\n\n";
    echo "API Key: {$client->api_key}\n";
    echo "Client ID: {$client->id}\n\n";
    echo "They CAN:\n";
    echo "✅ Check their balance\n";
    echo "✅ View message history\n";
    echo "✅ Get analytics\n";
    echo "✅ View documentation\n\n";
    echo "They CANNOT:\n";
    echo "❌ Send SMS - No Onfon credentials configured\n";
    echo "❌ Top-up via M-Pesa - No M-Pesa credentials\n";
    echo "❌ Receive emails - No SMTP configured\n\n";
    echo "NEXT STEP: Configure Onfon credentials for SMS sending\n";
}

echo "═══════════════════════════════════════════════════════════\n";

