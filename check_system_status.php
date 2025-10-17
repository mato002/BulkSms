<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== BULK SMS LARAVEL SYSTEM STATUS ===\n\n";

use App\Models\Client;
use App\Models\Channel;
use App\Models\Message;
use App\Models\Contact;

try {
    // Check database connection
    echo "1. DATABASE CONNECTION\n";
    echo "   Status: ";
    try {
        \DB::connection()->getPdo();
        echo "✅ Connected\n";
        echo "   Database: " . config('database.connections.mysql.database') . "\n\n";
    } catch (\Exception $e) {
        echo "❌ Failed\n";
        echo "   Error: " . $e->getMessage() . "\n\n";
        exit(1);
    }

    // Check clients
    echo "2. CLIENTS (API Users)\n";
    $clients = Client::all();
    echo "   Total: " . $clients->count() . "\n";
    
    if ($clients->count() > 0) {
        echo "\n   Existing Clients:\n";
        foreach ($clients as $client) {
            echo "   -----------------\n";
            echo "   ID: {$client->id}\n";
            echo "   Name: {$client->name}\n";
            echo "   Sender ID: {$client->sender_id}\n";
            echo "   Balance: KSH {$client->balance}\n";
            echo "   Units: " . $client->getBalanceInUnits() . "\n";
            echo "   API Key: " . substr($client->api_key ?? 'Not set', 0, 30) . "...\n";
            echo "   Status: " . ($client->status ? 'Active' : 'Inactive') . "\n";
            echo "   Tier: {$client->tier}\n";
        }
    } else {
        echo "   ⚠️  No clients found. You need to create at least one client.\n";
    }
    echo "\n";

    // Check channels (SMS provider configs)
    echo "3. CHANNELS (SMS Provider Configs)\n";
    $channels = Channel::all();
    echo "   Total: " . $channels->count() . "\n";
    
    if ($channels->count() > 0) {
        echo "\n   Configured Channels:\n";
        foreach ($channels as $channel) {
            $client = Client::find($channel->client_id);
            echo "   -----------------\n";
            echo "   Client: " . ($client ? $client->name : 'Unknown') . "\n";
            echo "   Channel: {$channel->name}\n";
            echo "   Status: " . ($channel->is_active ? 'Active' : 'Inactive') . "\n";
            
            $creds = json_decode($channel->credentials ?? '{}', true);
            if (!empty($creds)) {
                echo "   Credentials configured: Yes\n";
                if (isset($creds['api_key'])) {
                    echo "     - API Key: " . substr($creds['api_key'], 0, 15) . "...\n";
                }
                if (isset($creds['client_id'])) {
                    echo "     - Client ID: " . substr($creds['client_id'], 0, 20) . "...\n";
                }
                if (isset($creds['default_sender'])) {
                    echo "     - Sender: {$creds['default_sender']}\n";
                }
            } else {
                echo "   Credentials configured: ⚠️  No\n";
            }
        }
    } else {
        echo "   ⚠️  No channels configured. SMS sending won't work until configured.\n";
    }
    echo "\n";

    // Check messages
    echo "4. MESSAGES (History)\n";
    $messageCount = Message::count();
    $sentCount = Message::where('status', 'sent')->count();
    $failedCount = Message::where('status', 'failed')->count();
    
    echo "   Total messages: {$messageCount}\n";
    echo "   Sent: {$sentCount}\n";
    echo "   Failed: {$failedCount}\n\n";

    // Check contacts
    echo "5. CONTACTS\n";
    $contactCount = Contact::count();
    echo "   Total contacts: {$contactCount}\n\n";

    // Check API routes
    echo "6. API ENDPOINTS\n";
    echo "   Base URL: " . config('app.url') . "/api\n";
    echo "   Health Check: GET /api/health\n";
    echo "   Send Message: POST /api/{company_id}/messages/send\n";
    echo "   Check Balance: GET /api/{company_id}/client/balance\n";
    echo "   SMS History: GET /api/{company_id}/sms/history\n\n";

    // System readiness check
    echo "7. SYSTEM READINESS\n";
    $ready = true;
    $issues = [];
    
    if ($clients->count() == 0) {
        $ready = false;
        $issues[] = "No clients configured - Need to create at least one client with API key";
    }
    
    if ($channels->count() == 0) {
        $ready = false;
        $issues[] = "No SMS channels configured - Need to configure Onfon or other SMS provider";
    }
    
    $activeClients = $clients->where('status', true)->count();
    if ($activeClients == 0 && $clients->count() > 0) {
        $ready = false;
        $issues[] = "No active clients - All clients are disabled";
    }
    
    $activeChannels = $channels->where('is_active', true)->count();
    if ($activeChannels == 0 && $channels->count() > 0) {
        $ready = false;
        $issues[] = "No active channels - All channels are disabled";
    }
    
    if ($ready) {
        echo "   Status: ✅ READY to provide SMS API\n";
        echo "\n";
        echo "   You can now:\n";
        echo "   - Give API keys to external users\n";
        echo "   - Integrate with your PCIP system\n";
        echo "   - Start sending SMS via API\n";
    } else {
        echo "   Status: ⚠️  NOT READY\n\n";
        echo "   Issues to fix:\n";
        foreach ($issues as $issue) {
            echo "   - {$issue}\n";
        }
    }
    
    echo "\n";
    
    // Next steps
    echo "8. NEXT STEPS\n";
    if (!$ready) {
        if ($clients->count() == 0) {
            echo "   1. Create a client: Run 'php setup_fortress_api.php'\n";
            echo "      OR manually create via: php artisan tinker\n";
        }
        if ($channels->count() == 0) {
            echo "   2. Configure SMS channel with Onfon credentials\n";
        }
    } else {
        echo "   1. Test API: php test_sender_api.php\n";
        echo "   2. Integrate with PCIP system\n";
        echo "   3. Start sending SMS!\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== END OF STATUS CHECK ===\n";

