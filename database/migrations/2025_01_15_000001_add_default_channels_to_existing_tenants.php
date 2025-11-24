<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('channels') || !Schema::hasTable('clients')) {
            return;
        }

        // Get system Onfon credentials from config
        $onfonConfig = config('sms.gateways.onfon', []);
        $onfonApiKey = $onfonConfig['api_key'] ?? env('ONFON_API_KEY', '');
        $onfonClientId = $onfonConfig['client_id'] ?? env('ONFON_CLIENT_ID', '');
        $onfonAccessKey = env('ONFON_ACCESS_KEY_HEADER', '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB');
        $onfonUrl = $onfonConfig['url'] ?? 'https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS';

        // Get all clients that don't have an SMS channel
        $clientsWithoutSmsChannel = DB::table('clients')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('channels')
                    ->whereColumn('channels.client_id', 'clients.id')
                    ->where('channels.name', 'sms');
            })
            ->get();

        $created = 0;
        $now = now();

        foreach ($clientsWithoutSmsChannel as $client) {
            try {
                // Check if channel already exists (double-check)
                $existing = DB::table('channels')
                    ->where('client_id', $client->id)
                    ->where('name', 'sms')
                    ->first();

                if ($existing) {
                    continue;
                }

                // Create default SMS channel
                DB::table('channels')->insert([
                    'client_id' => $client->id,
                    'name' => 'sms',
                    'provider' => 'onfon',
                    'credentials' => json_encode([
                        'api_key' => $onfonApiKey,
                        'client_id' => $onfonClientId,
                        'access_key_header' => $onfonAccessKey,
                        'default_sender' => $client->sender_id ?? 'DEFAULT',
                        'base_url' => $onfonUrl,
                    ]),
                    'active' => true,
                    'config' => json_encode([
                        'uses_system_gateway' => true,
                        'auto_created' => true,
                        'created_by_migration' => true,
                    ]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $created++;
                
                Log::info('Created default SMS channel for existing client', [
                    'client_id' => $client->id,
                    'sender_id' => $client->sender_id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create SMS channel for client', [
                    'client_id' => $client->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($created > 0) {
            Log::info("Migration created {$created} default SMS channels for existing tenants");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove auto-created channels (only those created by migration)
        // Note: This is a safety measure. In practice, you may not want to remove channels
        if (Schema::hasTable('channels')) {
            // Get channels with migration flag and delete them
            $channels = DB::table('channels')
                ->where('name', 'sms')
                ->where('provider', 'onfon')
                ->get();
            
            foreach ($channels as $channel) {
                $config = json_decode($channel->config, true);
                if (isset($config['created_by_migration']) && $config['created_by_migration'] === true) {
                    DB::table('channels')->where('id', $channel->id)->delete();
                }
            }
        }
    }
};

