<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     TRACKING WHO IS CALLING EACH SENDER'S API             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$clients = \App\Models\Client::where('status', true)->get();

foreach ($clients as $client) {
    echo "═══════════════════════════════════════════════════════════\n";
    echo "🔍 {$client->name} ({$client->sender_id})\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    echo "📋 API Credentials:\n";
    echo "   Client ID:  {$client->id}\n";
    echo "   API Key:    " . substr($client->api_key, 0, 30) . "...\n";
    echo "   Sender ID:  {$client->sender_id}\n\n";
    
    // Get API logs for this client
    $logs = \App\Models\ApiLog::where('client_id', $client->id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    if ($logs->isEmpty()) {
        echo "   ⚠️  No API calls recorded yet\n";
        echo "   📝 This means no one has used these credentials yet\n\n";
        continue;
    }
    
    echo "📊 API Usage Summary:\n";
    echo "   Total Requests:  " . $logs->count() . "\n";
    echo "   First Call:      " . $logs->last()->created_at->format('Y-m-d H:i:s') . "\n";
    echo "   Last Call:       " . $logs->first()->created_at->format('Y-m-d H:i:s') . "\n\n";
    
    // Get unique IP addresses
    $uniqueIPs = $logs->pluck('ip_address')->unique();
    
    echo "🌐 Callers by IP Address:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    foreach ($uniqueIPs as $ip) {
        $ipLogs = $logs->where('ip_address', $ip);
        $count = $ipLogs->count();
        $lastCall = $ipLogs->first()->created_at->diffForHumans();
        $firstCall = $ipLogs->last()->created_at->format('Y-m-d H:i');
        $successRate = round($ipLogs->where('success', true)->count() / $count * 100, 1);
        
        echo "\n📍 IP: {$ip}\n";
        echo "   Total Calls:    {$count}\n";
        echo "   Success Rate:   {$successRate}%\n";
        echo "   First Seen:     {$firstCall}\n";
        echo "   Last Seen:      {$lastCall}\n";
        
        // Get user agents for this IP
        $userAgents = $ipLogs->pluck('user_agent')->unique()->filter();
        
        if ($userAgents->isNotEmpty()) {
            echo "   \n   🖥️  Applications/Systems used:\n";
            foreach ($userAgents as $ua) {
                // Parse user agent to identify system
                if (stripos($ua, 'curl') !== false) {
                    $system = "cURL (Command line/Script)";
                } elseif (stripos($ua, 'postman') !== false) {
                    $system = "Postman (API Testing)";
                } elseif (stripos($ua, 'php') !== false) {
                    $system = "PHP Application";
                } elseif (stripos($ua, 'python') !== false) {
                    $system = "Python Application";
                } elseif (stripos($ua, 'guzzle') !== false) {
                    $system = "PHP Guzzle Client";
                } elseif (stripos($ua, 'java') !== false) {
                    $system = "Java Application";
                } else {
                    $system = "Unknown System";
                }
                
                echo "      • {$system}\n";
                echo "        User Agent: " . substr($ua, 0, 60) . "...\n";
            }
        }
        
        // Show what endpoints they're calling
        $endpoints = $ipLogs->pluck('endpoint')->unique();
        echo "   \n   📡 Endpoints accessed:\n";
        foreach ($endpoints as $endpoint) {
            $endpointCount = $ipLogs->where('endpoint', $endpoint)->count();
            echo "      • /{$endpoint} ({$endpointCount} calls)\n";
        }
    }
    
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // Recent activity
    echo "🕐 Recent Activity (Last 5 calls):\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $recentLogs = $logs->take(5);
    foreach ($recentLogs as $log) {
        $status = $log->success ? '✅' : '❌';
        $time = $log->created_at->format('Y-m-d H:i:s');
        echo "{$status} {$time} - {$log->ip_address} → {$log->method} /{$log->endpoint}\n";
        if (!$log->success && $log->error_message) {
            echo "   Error: {$log->error_message}\n";
        }
    }
    
    echo "\n\n";
}

// Summary: Who gave credentials to whom
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║          WHO HAS WHICH SENDER CREDENTIALS?                ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "📝 Credentials Distribution:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

foreach ($clients as $client) {
    $logs = \App\Models\ApiLog::where('client_id', $client->id)->get();
    $uniqueIPs = $logs->pluck('ip_address')->unique();
    
    echo "🔑 {$client->sender_id} ({$client->name}):\n";
    echo "   API Key: " . substr($client->api_key, 0, 30) . "...\n";
    
    if ($uniqueIPs->isEmpty()) {
        echo "   Status: ⚠️  Credentials not used yet\n";
        echo "   Given to: Unknown (no activity recorded)\n";
    } else {
        echo "   Status: ✅ Actively being used\n";
        echo "   Used by: {$uniqueIPs->count()} different IP(s)\n";
        echo "   IPs:\n";
        foreach ($uniqueIPs as $ip) {
            $ipLogs = $logs->where('ip_address', $ip);
            $lastUsed = $ipLogs->first()->created_at->diffForHumans();
            echo "      • {$ip} (last used {$lastUsed})\n";
        }
    }
    echo "\n";
}

// Security Monitoring
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║              SECURITY MONITORING                           ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "🔒 Security Status:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Check for suspicious activity
$failedLogs = \App\Models\ApiLog::where('success', false)->get();
$suspiciousIPs = $failedLogs->pluck('ip_address')->countBy();

if ($suspiciousIPs->isEmpty()) {
    echo "✅ No failed authentication attempts\n";
    echo "✅ All API calls are from authorized systems\n\n";
} else {
    echo "⚠️  Failed Authentication Attempts:\n";
    foreach ($suspiciousIPs as $ip => $count) {
        echo "   • {$ip}: {$count} failed attempts\n";
    }
    echo "\n   💡 Consider blocking IPs with multiple failures\n\n";
}

// Check for unusual patterns
$allLogs = \App\Models\ApiLog::latest()->limit(100)->get();
$allIPs = $allLogs->pluck('ip_address')->unique();

echo "📊 Overall API Access:\n";
echo "   Total unique IPs:     {$allIPs->count()}\n";
echo "   Total API calls:      " . \App\Models\ApiLog::count() . "\n";
echo "   Active senders:       " . \App\Models\Client::where('status', true)->count() . "\n\n";

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                  HOW TO USE THIS INFO                      ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "📋 What this shows you:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. Each sender's API credentials\n";
echo "2. Which IP addresses are using each sender\n";
echo "3. What systems/applications are making calls\n";
echo "4. When they first and last called your API\n";
echo "5. Success rate for each IP\n";
echo "6. Which endpoints they're accessing\n\n";

echo "💡 Use Cases:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Track if credentials are being shared\n";
echo "• Identify unauthorized access\n";
echo "• Monitor which systems are actually using each sender\n";
echo "• Detect suspicious patterns\n";
echo "• Know who to contact when issues arise\n\n";

echo "🌐 Real-time dashboard: https://crm.pradytecai.com/api-monitor\n\n";


