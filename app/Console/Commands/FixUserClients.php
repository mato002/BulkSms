<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Client;
use Illuminate\Console\Command;

class FixUserClients extends Command
{
    protected $signature = 'users:fix-clients';
    protected $description = 'Ensure all users have a client_id set';

    public function handle()
    {
        $users = User::whereNull('client_id')->orWhere('client_id', 0)->get();

        if ($users->isEmpty()) {
            $this->info('All users already have a client_id set.');
            return 0;
        }

        // Get the default client (ID 1) or create one
        $defaultClient = Client::find(1);
        
        if (!$defaultClient) {
            $this->error('Default client (ID: 1) not found. Please run: php artisan db:seed --class=ClientsSeeder');
            return 1;
        }

        foreach ($users as $user) {
            $user->client_id = $defaultClient->id;
            $user->save();
            $this->info("Updated user {$user->email} with client_id: {$defaultClient->id}");
        }

        $this->info('All users now have a client_id set!');
        return 0;
    }
}

