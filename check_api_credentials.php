<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         API CREDENTIALS COMPARISON                        ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Check Prady Tech in clients table
echo "1️⃣ PRADY TECH - Clients Table (What we use for API):\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$client = \App\Models\Client::where('sender_id', 'PRADY_TECH')->first();
if ($client) {
    echo "Client ID:    " . $client->id . "\n";
    echo "Name:         " . $client->name . "\n";
    echo "Sender ID:    " . $client->sender_id . "\n";
    echo "API Key:      " . $client->api_key . "\n";
    echo "Balance:      KSH " . $client->balance . "\n\n";
} else {
    echo "❌ Not found in clients table\n\n";
}

// Check Prady Tech in senders table
echo "2️⃣ PRADY TECH - Senders Table (What shows on senders page):\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$sender = DB::table('senders')->where('sender_id', 'PRADY_TECH')->first();
if ($sender) {
    echo "ID:           " . $sender->id . "\n";
    echo "Name:         " . $sender->name . "\n";
    echo "Sender ID:    " . $sender->sender_id . "\n";
    echo "Service:      " . $sender->service . "\n";
    
    if ($sender->api_credentials) {
        $creds = json_decode($sender->api_credentials, true);
        echo "\nAPI Credentials stored in senders table:\n";
        if (isset($creds['api_key'])) {
            echo "  API Key:      " . $creds['api_key'] . "\n";
        }
        if (isset($creds['client_id'])) {
            echo "  Client ID:    " . $creds['client_id'] . "\n";
        }
        if (isset($creds['access_key'])) {
            echo "  Access Key:   " . $creds['access_key'] . "\n";
        }
    } else {
        echo "  No API credentials in senders table\n";
    }
    echo "\n";
} else {
    echo "❌ Not found in senders table\n\n";
}

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                  IMPORTANT EXPLANATION                    ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "🔑 TWO DIFFERENT API CREDENTIALS:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "1. API shown in SENDERS table (Onfon credentials):\n";
echo "   - These are YOUR Onfon Media API credentials\n";
echo "   - Used by YOUR system to send SMS via Onfon\n";
echo "   - NOT given to external companies\n";
if ($sender && $sender->api_credentials) {
    $creds = json_decode($sender->api_credentials, true);
    if (isset($creds['api_key'])) {
        echo "   - Example: " . substr($creds['api_key'], 0, 30) . "...\n";
    }
}
echo "\n";

echo "2. API shown in CLIENTS table (Your CRM API):\n";
echo "   - These are credentials for external companies\n";
echo "   - Used BY Prady Tech to call YOUR CRM\n";
echo "   - This is what you give to organizations\n";
if ($client) {
    echo "   - Example: " . $client->api_key . "\n";
}
echo "\n";

echo "📊 FLOW DIAGRAM:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Prady Tech App\n";
echo "     ↓ (uses API key: " . ($client ? substr($client->api_key, 0, 20) . "..." : "N/A") . ")\n";
echo "     ↓\n";
echo "YOUR CRM (crm.pradytecai.com)\n";
echo "     ↓ (uses Onfon credentials from senders table)\n";
echo "     ↓\n";
echo "Onfon Media API\n";
echo "     ↓\n";
echo "SMS Delivered ✅\n\n";

echo "✅ SUMMARY:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "The API credentials shown on senders page are ONFON credentials.\n";
echo "The API credentials for Prady Tech to use are in CLIENTS table.\n";
echo "They are DIFFERENT and serve DIFFERENT purposes!\n\n";

