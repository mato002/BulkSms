<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use Illuminate\Support\Str;

class AdditionalSendersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $senders = [
            ['sender_id' => 'FORTRESS', 'name' => 'Fortress Lenders'],
            ['sender_id' => 'FANUKA_LTD', 'name' => 'Fanuka Limited'],
            ['sender_id' => 'DAFACOM_LTD', 'name' => 'Dafacom Limited'],
            ['sender_id' => 'EMPISAI_LTD', 'name' => 'Empisai Limited'],
            ['sender_id' => 'NEWPRO_CAP', 'name' => 'Newpro Capital'],
            ['sender_id' => 'AMPLE_SWISS', 'name' => 'Ample Swiss'],
            ['sender_id' => 'NOBLE_MICRO', 'name' => 'Noble Microfinance'],
            ['sender_id' => 'MALIK', 'name' => 'Malik Services'],
            ['sender_id' => 'NOVA_BRIDGE', 'name' => 'Nova Bridge'],
            ['sender_id' => 'JIRANIHODAR', 'name' => 'Jiranihodar Services'],
            ['sender_id' => 'NKR_A_CLUB', 'name' => 'NKR A Club'],
            ['sender_id' => 'MWEGUNI_LTD', 'name' => 'Mweguni Limited'],
            ['sender_id' => 'ZEN_PHARMA', 'name' => 'Zen Pharmaceuticals'],
            ['sender_id' => 'PAGECAPITAL', 'name' => 'Page Capital'],
            ['sender_id' => 'ALLADI_FREY', 'name' => 'Alladi Frey'],
        ];

        foreach ($senders as $senderData) {
            // Check if sender already exists
            $existingSender = Client::where('sender_id', $senderData['sender_id'])->first();
            
            if (!$existingSender) {
                Client::create([
                    'sender_id' => $senderData['sender_id'],
                    'name' => $senderData['name'],
                    'email' => strtolower($senderData['sender_id']) . '@example.com',
                    'phone' => '254700000000',
                    'balance' => 0.00,
                    'api_key' => Str::uuid(),
                    'status' => 'active',
                    'tier' => 'bronze',
                    'is_test_mode' => false,
                    'webhook_active' => false,
                    'webhook_events' => ['balance_updated', 'topup_completed'],
                ]);
                
                echo "Created sender: {$senderData['sender_id']} - {$senderData['name']}\n";
            } else {
                echo "Sender already exists: {$senderData['sender_id']}\n";
            }
        }
    }
}

