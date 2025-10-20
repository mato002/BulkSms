<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║       SYSTEMS CONNECTED TO YOUR BULK SMS API              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Get all active clients
$clients = \App\Models\Client::where('status', true)->get();

echo "📊 TOTAL CONNECTED SYSTEMS: " . $clients->count() . "\n\n";

foreach ($clients as $index => $client) {
    $number = $index + 1;
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "#{$number} - {$client->name}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // Basic Info
    echo "📋 Basic Information:\n";
    echo "   Client ID:        {$client->id}\n";
    echo "   Company Name:     {$client->name}\n";
    echo "   Sender ID:        {$client->sender_id}\n";
    echo "   Status:           " . ($client->status ? '✅ Active' : '❌ Inactive') . "\n";
    echo "   API Key:          " . substr($client->api_key, 0, 30) . "...\n";
    echo "   Created:          " . $client->created_at->format('Y-m-d H:i') . "\n\n";
    
    // Financial Info
    echo "💰 Financial Status:\n";
    echo "   Balance:          KSH " . number_format($client->balance, 2) . "\n";
    echo "   Price Per Unit:   KSH " . number_format($client->price_per_unit ?? 1, 2) . "\n";
    $units = $client->price_per_unit > 0 ? $client->balance / $client->price_per_unit : 0;
    echo "   Available Units:  " . number_format($units, 0) . " SMS\n\n";
    
    // Usage Statistics
    $totalMessages = \App\Models\Message::where('client_id', $client->id)->count();
    $todayMessages = \App\Models\Message::where('client_id', $client->id)
        ->whereDate('created_at', today())
        ->count();
    $thisWeekMessages = \App\Models\Message::where('client_id', $client->id)
        ->where('created_at', '>=', now()->startOfWeek())
        ->count();
    $thisMonthMessages = \App\Models\Message::where('client_id', $client->id)
        ->where('created_at', '>=', now()->startOfMonth())
        ->count();
    
    echo "📈 Usage Statistics:\n";
    echo "   Total Messages:   " . number_format($totalMessages) . "\n";
    echo "   Today:            " . number_format($todayMessages) . "\n";
    echo "   This Week:        " . number_format($thisWeekMessages) . "\n";
    echo "   This Month:       " . number_format($thisMonthMessages) . "\n\n";
    
    // API Activity
    $totalApiCalls = \App\Models\ApiLog::where('client_id', $client->id)->count();
    $todayApiCalls = \App\Models\ApiLog::where('client_id', $client->id)
        ->whereDate('created_at', today())
        ->count();
    $failedCalls = \App\Models\ApiLog::where('client_id', $client->id)
        ->where('success', false)
        ->count();
    $successRate = $totalApiCalls > 0 ? round(($totalApiCalls - $failedCalls) / $totalApiCalls * 100, 1) : 0;
    
    echo "🔌 API Activity:\n";
    echo "   Total API Calls:  " . number_format($totalApiCalls) . "\n";
    echo "   Today's Calls:    " . number_format($todayApiCalls) . "\n";
    echo "   Failed Calls:     " . number_format($failedCalls) . "\n";
    echo "   Success Rate:     {$successRate}%\n";
    
    // Last Activity
    $lastLog = \App\Models\ApiLog::where('client_id', $client->id)
        ->latest()
        ->first();
    
    if ($lastLog) {
        echo "   Last Activity:    " . $lastLog->created_at->diffForHumans() . "\n";
    } else {
        echo "   Last Activity:    Never\n";
    }
    
    echo "\n";
    
    // Channel Configuration
    $channels = \App\Models\Channel::where('client_id', $client->id)->get();
    if ($channels->isNotEmpty()) {
        echo "📡 Configured Channels:\n";
        foreach ($channels as $channel) {
            $status = $channel->active ? '✅' : '❌';
            echo "   {$status} {$channel->name} ({$channel->provider})\n";
        }
        echo "\n";
    }
    
    echo "\n";
}

// Overall System Statistics
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║              OVERALL SYSTEM STATISTICS                     ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$totalClients = $clients->count();
$totalBalance = $clients->sum('balance');
$totalMessages = \App\Models\Message::count();
$totalApiCalls = \App\Models\ApiLog::count();
$todayApiCalls = \App\Models\ApiLog::whereDate('created_at', today())->count();
$todayMessages = \App\Models\Message::whereDate('created_at', today())->count();

echo "📊 System Overview:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "   Active Clients:        {$totalClients}\n";
echo "   Total Balance:         KSH " . number_format($totalBalance, 2) . "\n";
echo "   Total Messages Sent:   " . number_format($totalMessages) . "\n";
echo "   Total API Calls:       " . number_format($totalApiCalls) . "\n";
echo "   Today's API Calls:     " . number_format($todayApiCalls) . "\n";
echo "   Today's Messages:      " . number_format($todayMessages) . "\n\n";

// Top Users
echo "🏆 Top 5 Most Active Systems:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$topClients = \App\Models\Client::where('status', true)->get()
    ->map(function($client) {
        $client->messages_count = \App\Models\Message::where('client_id', $client->id)->count();
        return $client;
    })
    ->sortByDesc('messages_count')
    ->take(5);

foreach ($topClients as $index => $client) {
    $medals = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];
    $medal = $medals[$index] ?? '';
    echo "   {$medal} {$client->name}: " . number_format($client->messages_count) . " messages\n";
}

echo "\n";

// Recent Activity
echo "🕐 Recent API Activity (Last 10):\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$recentLogs = \App\Models\ApiLog::with('client')
    ->latest()
    ->limit(10)
    ->get();

foreach ($recentLogs as $log) {
    $status = $log->success ? '✅' : '❌';
    $clientName = $log->client ? $log->client->name : 'Unknown';
    $time = $log->created_at->diffForHumans();
    echo "   {$status} {$clientName} - {$log->endpoint} ({$time})\n";
}

echo "\n";

// Access Information
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                 HOW TO ACCESS THIS INFO                    ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "🌐 Web Dashboard:\n";
echo "   API Monitor:     https://crm.pradytecai.com/api-monitor\n";
echo "   Documentation:   https://crm.pradytecai.com/api-documentation\n\n";

echo "📊 You can also:\n";
echo "   • Filter by specific client in the dashboard\n";
echo "   • Export usage reports\n";
echo "   • Track real-time activity\n";
echo "   • Monitor balance levels\n";
echo "   • View detailed request logs\n\n";

echo "✅ All systems displayed above are currently using YOUR API!\n\n";

