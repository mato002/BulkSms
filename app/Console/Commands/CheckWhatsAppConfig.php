<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Models\Client;
use Illuminate\Console\Command;

class CheckWhatsAppConfig extends Command
{
    protected $signature = 'whatsapp:check-config {client_id?}';
    protected $description = 'Check WhatsApp configuration for a client';

    public function handle()
    {
        $clientId = $this->argument('client_id') ?? 1;
        
        $client = Client::find($clientId);
        
        if (!$client) {
            $this->error("Client ID {$clientId} not found!");
            return 1;
        }

        $this->info("Checking WhatsApp config for: {$client->name}");
        
        $channel = Channel::where('client_id', $clientId)
            ->where('name', 'whatsapp')
            ->first();

        if (!$channel) {
            $this->warn('❌ No WhatsApp channel configured for this client');
            $this->info('To configure, go to: /whatsapp/configure');
            return 1;
        }

        $this->info('✅ WhatsApp channel found');
        $this->info("Provider: {$channel->provider}");
        $this->info("Status: " . ($channel->is_active ? 'Active' : 'Inactive'));
        
        $credentials = is_string($channel->credentials) 
            ? json_decode($channel->credentials, true) 
            : $channel->credentials;
            
        if ($credentials && is_array($credentials)) {
            $this->info('Credentials configured: Yes');
            $this->info('Keys present: ' . implode(', ', array_keys($credentials)));
        } else {
            $this->warn('Credentials configured: No');
        }

        return 0;
    }
}

