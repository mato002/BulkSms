<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 30 companies with API keys
        for ($i = 1; $i <= 30; $i++) {
            Client::create([
                'name' => "Company {$i}",
                'contact' => "25471234567{$i}",
                'sender_id' => "COMPANY{$i}",
                'balance' => 1000.00,
                'api_key' => "bs_" . bin2hex(random_bytes(16)),
                'status' => true,
                'settings' => [
                    'max_sms_per_day' => 10000,
                    'allowed_sender_ids' => ["COMPANY{$i}"],
                    'rate_limit' => 60
                ]
            ]);
        }
    }
}
