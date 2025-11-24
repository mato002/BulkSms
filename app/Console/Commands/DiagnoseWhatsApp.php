<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DiagnoseWhatsApp extends Command
{
    protected $signature = 'whatsapp:diagnose {--client_id=1}';
    protected $description = 'Diagnose WhatsApp configuration and connectivity issues';

    public function handle()
    {
        $clientId = $this->option('client_id');
        
        $this->info("🔍 WhatsApp Diagnostic Tool");
        $this->info("================================\n");

        // Check 1: Channel Configuration
        $this->info("1️⃣ Checking WhatsApp Channel Configuration...");
        $channel = Channel::where('client_id', $clientId)
            ->where('name', 'whatsapp')
            ->first();

        if (!$channel) {
            $this->error("   ❌ No WhatsApp channel found for client ID: {$clientId}");
            $this->warn("   → Go to /whatsapp/configure to set up WhatsApp");
            return 1;
        }

        $this->info("   ✅ WhatsApp channel found!");
        $this->line("      Provider: " . $channel->provider);
        $this->line("      Active: " . ($channel->active ? 'Yes' : 'No'));
        
        // Check 2: Credentials
        $this->info("\n2️⃣ Checking Credentials...");
        $credentials = is_string($channel->credentials) 
            ? json_decode($channel->credentials, true) 
            : $channel->credentials;

        if (empty($credentials)) {
            $this->error("   ❌ No credentials configured");
            return 1;
        }

        if ($channel->provider === 'ultramsg') {
            $hasInstanceId = !empty($credentials['instance_id']);
            $hasToken = !empty($credentials['token']);
            
            $this->line("      Instance ID: " . ($hasInstanceId ? '✅ Set' : '❌ Missing'));
            $this->line("      Token: " . ($hasToken ? '✅ Set' : '❌ Missing'));
            
            if (!$hasInstanceId || !$hasToken) {
                $this->error("   ❌ Missing required credentials");
                return 1;
            }
        } else {
            $hasPhoneId = !empty($credentials['phone_number_id']);
            $hasToken = !empty($credentials['access_token']);
            
            $this->line("      Phone Number ID: " . ($hasPhoneId ? '✅ Set' : '❌ Missing'));
            $this->line("      Access Token: " . ($hasToken ? '✅ Set' : '❌ Missing'));
            
            if (!$hasPhoneId || !$hasToken) {
                $this->error("   ❌ Missing required credentials");
                return 1;
            }
        }

        // Check 3: Test Connection
        $this->info("\n3️⃣ Testing API Connection...");
        
        try {
            if ($channel->provider === 'ultramsg') {
                $response = Http::timeout(30)
                    ->withOptions(['verify' => false])
                    ->get("https://api.ultramsg.com/{$credentials['instance_id']}/instance/status", [
                        'token' => $credentials['token']
                    ]);

                if ($response->successful()) {
                    $this->info("   ✅ UltraMsg API connection successful!");
                    $data = $response->json();
                    if (isset($data['accountStatus'])) {
                        $this->line("      Account Status: " . $data['accountStatus']);
                    }
                } else {
                    $error = $response->json();
                    $this->error("   ❌ Connection failed: " . ($error['error'] ?? 'Unknown error'));
                    $this->line("      Status Code: " . $response->status());
                }
            } else {
                $response = Http::withToken($credentials['access_token'])
                    ->withOptions(['verify' => false])
                    ->get("https://graph.facebook.com/v21.0/{$credentials['phone_number_id']}");

                if ($response->successful()) {
                    $this->info("   ✅ WhatsApp Cloud API connection successful!");
                    $data = $response->json();
                    if (isset($data['display_phone_number'])) {
                        $this->line("      Phone Number: " . $data['display_phone_number']);
                    }
                } else {
                    $error = $response->json();
                    $this->error("   ❌ Connection failed: " . ($error['error']['message'] ?? 'Unknown error'));
                }
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Connection test failed: " . $e->getMessage());
        }

        // Check 4: Recent Messages
        $this->info("\n4️⃣ Checking Recent WhatsApp Messages...");
        $messages = Message::where('client_id', $clientId)
            ->where('channel', 'whatsapp')
            ->latest()
            ->take(5)
            ->get();

        if ($messages->isEmpty()) {
            $this->warn("   ⚠️  No WhatsApp messages found");
        } else {
            $this->info("   Last 5 messages:");
            foreach ($messages as $msg) {
                $status = $msg->status;
                $icon = match($status) {
                    'sent' => '✅',
                    'failed' => '❌',
                    'sending' => '⏳',
                    default => '❓'
                };
                $this->line("      {$icon} {$msg->recipient} - {$status} - " . $msg->created_at->diffForHumans());
                if ($msg->status === 'failed' && $msg->error_message) {
                    $this->line("         Error: " . $msg->error_message);
                }
            }
        }

        // Check 5: PHP Extensions
        $this->info("\n5️⃣ Checking PHP Extensions...");
        $required = ['curl', 'openssl', 'mbstring', 'json'];
        foreach ($required as $ext) {
            $loaded = extension_loaded($ext);
            $this->line("      {$ext}: " . ($loaded ? '✅ Loaded' : '❌ Missing'));
        }

        $this->info("\n================================");
        $this->info("✅ Diagnosis Complete!\n");

        return 0;
    }
}




