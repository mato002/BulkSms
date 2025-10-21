<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Http\Controllers\CampaignController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessScheduledCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $campaign;

    /**
     * Create a new job instance.
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing scheduled campaign', [
            'campaign_id' => $this->campaign->id,
            'scheduled_at' => $this->campaign->scheduled_at,
        ]);

        try {
            // Check if campaign is still due
            if (!$this->campaign->isDue()) {
                Log::warning('Campaign is not due yet or already processed', [
                    'campaign_id' => $this->campaign->id,
                ]);
                return;
            }

            // Send the campaign
            $controller = new CampaignController();
            $controller->sendScheduledCampaign($this->campaign);

            // Mark as processed
            $this->campaign->markAsProcessed();

            Log::info('Scheduled campaign processed successfully', [
                'campaign_id' => $this->campaign->id,
            ]);

            // If recurring, create next occurrence
            if ($this->campaign->isRecurring()) {
                $this->createNextOccurrence();
            }

        } catch (\Exception $e) {
            Log::error('Failed to process scheduled campaign', [
                'campaign_id' => $this->campaign->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create next occurrence for recurring campaign
     */
    protected function createNextOccurrence(): void
    {
        $nextDate = $this->campaign->getNextOccurrence();
        
        if (!$nextDate) {
            return;
        }

        // Create a new campaign for the next occurrence
        $newCampaign = $this->campaign->replicate();
        $newCampaign->scheduled_at = $nextDate;
        $newCampaign->processed_at = null;
        $newCampaign->sent_at = null;
        $newCampaign->status = 'draft';
        $newCampaign->sent_count = 0;
        $newCampaign->delivered_count = 0;
        $newCampaign->failed_count = 0;
        $newCampaign->total_cost = 0;
        $newCampaign->save();

        Log::info('Created next occurrence for recurring campaign', [
            'original_campaign_id' => $this->campaign->id,
            'new_campaign_id' => $newCampaign->id,
            'next_scheduled_at' => $nextDate,
        ]);
    }
}


