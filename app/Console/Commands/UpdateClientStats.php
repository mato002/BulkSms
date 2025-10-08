<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\Sms;
use App\Models\Campaign;

class UpdateClientStats extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'client:update-stats';

    /**
     * The console command description.
     */
    protected $description = 'Update client statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating client statistics...');

        $clients = Client::all();
        $updated = 0;

        foreach ($clients as $client) {
            try {
                // Update campaign statistics
                $campaigns = Campaign::where('client_id', $client->id)->get();
                
                foreach ($campaigns as $campaign) {
                    $campaign->updateStats();
                }

                $updated++;

            } catch (\Exception $e) {
                $this->error("Failed to update stats for client {$client->id}: " . $e->getMessage());
            }
        }

        $this->info("Updated statistics for {$updated} clients.");
    }
}
