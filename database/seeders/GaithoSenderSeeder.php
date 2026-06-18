<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GaithoSenderSeeder extends Seeder
{
    public function run(): void
    {
        $onfonCredentials = [
            'api_key' => 'VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak=',
            'client_id' => 'e27847c1-a9fe-4eef-b60d-ddb291b175ab',
            'access_key_header' => '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB',
        ];

        $senderId = 'GAITHO_AGY';
        $targetId = 3;

        Client::where('sender_id', $senderId)
            ->where('id', '!=', $targetId)
            ->each(fn (Client $duplicate) => $duplicate->delete());

        $existing = Client::find($targetId);
        $apiKey = $existing?->api_key ?? $this->generateApiKey();

        $client = Client::updateOrCreate(
            ['id' => $targetId],
            [
                'name' => 'Gaitho',
                'contact' => '254700000003',
                'sender_id' => $senderId,
                'company_name' => 'GAITHO_AGY',
                'api_key' => $apiKey,
                'balance' => $existing?->balance ?? 0,
                'price_per_unit' => 0.60,
                'status' => true,
                'tier' => 'bronze',
                'settings' => array_merge($existing?->settings ?? [], [
                    'append_reply_link' => false,
                    'append_stop_footer' => false,
                ]),
            ]
        );

        Channel::updateOrCreate(
            [
                'client_id' => $client->id,
                'name' => 'sms',
                'provider' => 'onfon',
            ],
            [
                'credentials' => array_merge($onfonCredentials, [
                    'default_sender' => $senderId,
                ]),
                'active' => true,
            ]
        );

        $this->command?->info('Gaitho sender ready.');
        $this->command?->line("  Client ID:  {$client->id}");
        $this->command?->line("  Sender ID:  {$client->sender_id}");
        $this->command?->line("  API Key:    {$client->api_key}");
        $this->command?->line('  API URL:    https://crm.pradytecai.com/api/3/messages/send');
    }

    private function generateApiKey(): string
    {
        do {
            $apiKey = 'sk_' . Str::random(32);
        } while (Client::where('api_key', $apiKey)->exists());

        return $apiKey;
    }
}
