<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Campaign;
use App\Services\SmsService;

class ProcessScheduledSms extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sms:process-scheduled';

    /**
     * The console command description.
     */
    protected $description = 'Process scheduled SMS campaigns';

    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        parent::__construct();
        $this->smsService = $smsService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing scheduled SMS campaigns...');

        $campaigns = Campaign::where('status', 'scheduled')
                            ->where('scheduled_at', '<=', now())
                            ->get();

        $processed = 0;

        foreach ($campaigns as $campaign) {
            try {
                $this->info("Processing campaign: {$campaign->name}");

                // Check client balance
                if (!$campaign->client->hasSufficientBalance(count($campaign->recipients) * 0.75)) {
                    $this->error("Insufficient balance for campaign: {$campaign->name}");
                    continue;
                }

                // Send SMS to all recipients
                foreach ($campaign->recipients as $recipient) {
                    $this->smsService->sendSms(
                        $campaign->client,
                        $recipient,
                        $campaign->message,
                        $campaign->sender_id
                    );
                }

                // Mark campaign as sent
                $campaign->markAsSent();
                $campaign->updateStats();

                $processed++;
                $this->info("Campaign sent: {$campaign->name}");

            } catch (\Exception $e) {
                $this->error("Failed to process campaign {$campaign->name}: " . $e->getMessage());
            }
        }

        $this->info("Processed {$processed} scheduled campaigns.");
    }
}
