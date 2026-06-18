<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Client;
use Illuminate\Database\Seeder;

class CleanupDummySendersSeeder extends Seeder
{
    /**
     * Remove dummy senders and deduplicate PRADY_TECH / FORTRESS.
     * Keeps exactly one record per sender ID on client IDs 1 and 2.
     */
    public function run(): void
    {
        // Remove test companies from ClientSeeder (COMPANY1, COMPANY2, ...)
        $dummy = Client::where('sender_id', 'like', 'COMPANY%')->get();
        foreach ($dummy as $client) {
            $this->command?->line("Removing dummy: {$client->sender_id} ({$client->name})");
            $client->delete();
        }

        $canonical = [
            'PRADY_TECH' => 1,
            'FORTRESS' => 2,
        ];

        foreach ($canonical as $senderId => $canonicalId) {
            $this->deduplicateSender($senderId, $canonicalId);
        }

        $this->command?->info('Cleanup complete. Expected senders: PRADY_TECH (id 1), FORTRESS (id 2).');
    }

    private function deduplicateSender(string $senderId, int $canonicalId): void
    {
        $clients = Client::where('sender_id', $senderId)->orderBy('id')->get();

        if ($clients->count() <= 1) {
            return;
        }

        $keeper = $clients->firstWhere('id', $canonicalId) ?? $clients->first();

        // Merge balances from duplicates into the keeper
        $extraBalance = $clients->where('id', '!=', $keeper->id)->sum('balance');
        if ($extraBalance > 0) {
            $keeper->balance = (float) $keeper->balance + (float) $extraBalance;
            $keeper->save();
            $this->command?->line("Merged KES {$extraBalance} into {$senderId} (id {$keeper->id})");
        }

        foreach ($clients->where('id', '!=', $keeper->id') as $duplicate) {
            $this->command?->line("Removing duplicate {$senderId}: {$duplicate->name} (id {$duplicate->id})");
            $duplicate->delete();
        }

        // Ensure SMS channel exists for the keeper
        $onfonCredentials = [
            'api_key' => 'VKft5j+GOeSXYSlk+sADT/nx5UMVpcmengSPk9Ou4Ak=',
            'client_id' => 'e27847c1-a9fe-4eef-b60d-ddb291b175ab',
            'access_key_header' => '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB',
            'default_sender' => $senderId,
        ];

        Channel::updateOrCreate(
            [
                'client_id' => $keeper->id,
                'name' => 'sms',
                'provider' => 'onfon',
            ],
            [
                'credentials' => $onfonCredentials,
                'active' => true,
            ]
        );
    }
}
