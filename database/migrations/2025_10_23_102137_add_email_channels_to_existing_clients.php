<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add email channels for all existing clients
        $clients = DB::table('clients')->get();
        
        foreach ($clients as $client) {
            // Check if email channel already exists
            $existingEmailChannel = DB::table('channels')
                ->where('client_id', $client->id)
                ->where('name', 'email')
                ->first();
            
            if (!$existingEmailChannel) {
                DB::table('channels')->insert([
                    'client_id' => $client->id,
                    'name' => 'email',
                    'provider' => 'smtp',
                    'active' => true,
                    'credentials' => json_encode([
                        'host' => 'smtp.gmail.com',
                        'port' => 587,
                        'username' => '',
                        'password' => '',
                        'encryption' => 'tls',
                        'from_email' => $client->contact ?? 'noreply@example.com',
                        'from_name' => $client->name ?? 'BulkSMS Platform'
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove email channels
        DB::table('channels')
            ->where('name', 'email')
            ->where('provider', 'smtp')
            ->delete();
    }
};