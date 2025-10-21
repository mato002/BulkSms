<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Jobs\ProcessScheduledCampaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled campaigns that are due to be sent';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for scheduled campaigns...');

        $campaigns = Campaign::due()->get();

        if ($campaigns->isEmpty()) {
            $this->info('No campaigns due to be sent.');
            return 0;
        }

        $this->info("Found {$campaigns->count()} campaign(s) to process.");

        foreach ($campaigns as $campaign) {
            $this->info("Processing campaign: {$campaign->name} (ID: {$campaign->id})");
            
            try {
                // Dispatch job to process the campaign
                ProcessScheduledCampaign::dispatch($campaign);
                
                $this->info("✓ Campaign queued for processing");
            } catch (\Exception $e) {
                $this->error("✗ Failed to queue campaign: {$e->getMessage()}");
                Log::error('Failed to queue scheduled campaign', [
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info('✓ Done!');
        return 0;
    }
}


