<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sms;

class CleanupSmsLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sms:cleanup {--days=90 : Number of days to keep SMS logs}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old SMS logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Cleaning up SMS logs older than {$days} days...");

        $deleted = Sms::where('created_at', '<', $cutoffDate)
                      ->where('status', 'delivered')
                      ->delete();

        $this->info("Deleted {$deleted} old SMS logs.");
    }
}
