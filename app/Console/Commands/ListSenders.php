<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;

class ListSenders extends Command
{
    protected $signature = 'senders:list';
    protected $description = 'List all senders';

    public function handle()
    {
        $senders = Client::all();

        if ($senders->isEmpty()) {
            $this->warn('No senders found in the database.');
            return 0;
        }

        $this->info('Senders in database:');
        $this->table(
            ['ID', 'Name', 'Sender ID', 'Balance', 'Status', 'API Key (partial)'],
            $senders->map(function ($sender) {
                return [
                    $sender->id,
                    $sender->name,
                    $sender->sender_id,
                    number_format($sender->balance, 2),
                    $sender->status ? 'Active' : 'Inactive',
                    substr($sender->api_key, 0, 20) . '...'
                ];
            })
        );

        return 0;
    }
}

