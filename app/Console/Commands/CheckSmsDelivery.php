<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sms;
use App\Services\SmsService;

class CheckSmsDelivery extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sms:check-delivery';

    /**
     * The console command description.
     */
    protected $description = 'Check SMS delivery status';

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
        $this->info('Checking SMS delivery status...');

        $sms = Sms::where('status', 'sent')
                  ->whereNotNull('message_id')
                  ->where('created_at', '>=', now()->subHours(24))
                  ->get();

        $checked = 0;
        $delivered = 0;

        foreach ($sms as $smsRecord) {
            try {
                $result = $this->smsService->checkDeliveryStatus($smsRecord->message_id);
                
                if ($result['status'] === 'success' && $result['delivery_status'] === 'delivered') {
                    $smsRecord->markAsDelivered();
                    $delivered++;
                }

                $checked++;

            } catch (\Exception $e) {
                $this->error("Failed to check delivery for SMS {$smsRecord->id}: " . $e->getMessage());
            }
        }

        $this->info("Checked {$checked} SMS messages, {$delivered} delivered.");
    }
}
