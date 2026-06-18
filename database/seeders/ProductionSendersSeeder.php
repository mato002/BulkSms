<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductionSendersSeeder extends Seeder
{
    public function run(): void
    {
        $onfonCredentials = [
            'api_key' => 'VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak=',
            'client_id' => 'e27847c1-a9fe-4eef-b60d-ddb291b175ab',
            'access_key_header' => '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB',
        ];

        $senders = [
            [
                'id' => 1,
                'name' => 'Default Client',
                'contact' => 'admin@example.com',
                'sender_id' => 'PRADY_TECH',
                'company_name' => 'PRADY_TECH',
                'api_key' => 'ea55cb72-a734-48b2-87a6-8d0ea1d397de',
                'default_sender' => 'PRADY_TECH',
            ],
            [
                'id' => 2,
                'name' => 'Fortress Limited',
                'contact' => '254700000000',
                'sender_id' => 'FORTRESS',
                'company_name' => 'FORTRESS',
                'api_key' => 'USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh',
                'default_sender' => 'FORTRESS',
            ],
            [
                'id' => 3,
                'name' => 'Gaitho',
                'contact' => '254700000003',
                'sender_id' => 'GAITHO_AGY',
                'company_name' => 'GAITHO_AGY',
                'api_key' => null,
                'default_sender' => 'GAITHO_AGY',
                'price_per_unit' => 0.60,
            ],
        ];

        foreach ($senders as $sender) {
            $defaultSender = $sender['default_sender'];
            $targetId = $sender['id'];
            unset($sender['default_sender']);

            // Remove other rows that share this sender_id on a different id (avoid duplicates)
            Client::where('sender_id', $sender['sender_id'])
                ->where('id', '!=', $targetId)
                ->each(function (Client $duplicate) {
                    $duplicate->delete();
                });

            $existing = Client::find($targetId);
            if (empty($sender['api_key'])) {
                $sender['api_key'] = $existing?->api_key ?? $this->generateApiKey();
            }

            $pricePerUnit = $sender['price_per_unit'] ?? $existing?->price_per_unit ?? 1.00;
            unset($sender['price_per_unit']);

            $client = Client::updateOrCreate(
                ['id' => $targetId],
                array_merge($sender, [
                    'balance' => $existing?->balance ?? ($targetId <= 2 ? 100.00 : 0),
                    'price_per_unit' => $pricePerUnit,
                    'status' => true,
                    'tier' => 'bronze',
                    'settings' => [],
                ])
            );

            Channel::updateOrCreate(
                [
                    'client_id' => $client->id,
                    'name' => 'sms',
                    'provider' => 'onfon',
                ],
                [
                    'credentials' => array_merge($onfonCredentials, [
                        'default_sender' => $defaultSender,
                    ]),
                    'active' => true,
                ]
            );
        }
    }

    private function generateApiKey(): string
    {
        do {
            $apiKey = 'sk_' . Str::random(32);
        } while (Client::where('api_key', $apiKey)->exists());

        return $apiKey;
    }
}
