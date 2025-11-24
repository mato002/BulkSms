<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;

class QueueMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:monitor {--queue=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor queue status and job counts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queueName = $this->option('queue') ?? 'default';
        
        $this->info("Queue Monitor - Queue: {$queueName}");
        $this->line('');

        // Get queue size
        try {
            $size = Queue::size($queueName);
            $this->info("Queue Size: {$size} jobs");
        } catch (\Exception $e) {
            $this->error("Could not get queue size: " . $e->getMessage());
        }

        // Get failed jobs count
        $failedCount = DB::table('failed_jobs')->count();
        $this->info("Failed Jobs: {$failedCount}");

        // Get recent failed jobs
        if ($failedCount > 0) {
            $this->line('');
            $this->warn("Recent Failed Jobs:");
            $recentFailed = DB::table('failed_jobs')
                ->orderBy('failed_at', 'desc')
                ->limit(5)
                ->get(['id', 'queue', 'exception', 'failed_at']);

            foreach ($recentFailed as $job) {
                $this->line("  - Job ID: {$job->id} | Queue: {$job->queue} | Failed: {$job->failed_at}");
                $exception = substr($job->exception, 0, 100);
                $this->line("    Exception: {$exception}...");
            }
        }

        // Get pending jobs from database queue
        if (config('queue.default') === 'database') {
            $pendingCount = DB::table('jobs')->count();
            $this->info("Pending Jobs (database): {$pendingCount}");
        }

        $this->line('');
        $this->info("Monitor complete. Run 'php artisan queue:work' to process jobs.");
    }
}




