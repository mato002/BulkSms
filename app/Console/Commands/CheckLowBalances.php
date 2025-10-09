<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Mail\LowBalanceAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CheckLowBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balance:check-low {--threshold=100 : Balance threshold for alerts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for clients with low balance and send alert emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = (float) $this->option('threshold');

        $this->info("Checking for clients with balance below KES {$threshold}...");

        // Get clients with low balance and active status
        $lowBalanceClients = Client::where('status', true)
            ->where('balance', '<=', $threshold)
            ->where('balance', '>', 0) // Exclude zero balances
            ->get();

        if ($lowBalanceClients->isEmpty()) {
            $this->info('No clients with low balance found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$lowBalanceClients->count()} client(s) with low balance.");

        $sentCount = 0;
        $skippedCount = 0;

        foreach ($lowBalanceClients as $client) {
            // Check if we've already sent an alert recently (within last 24 hours)
            $cacheKey = "low_balance_alert_sent_{$client->id}";
            
            if (Cache::has($cacheKey)) {
                $this->line("Skipping {$client->name} - alert already sent recently");
                $skippedCount++;
                continue;
            }

            // Validate email
            if (!$client->contact || !filter_var($client->contact, FILTER_VALIDATE_EMAIL)) {
                $this->warn("Skipping {$client->name} - no valid email address");
                $skippedCount++;
                continue;
            }

            try {
                // Send low balance alert
                Mail::to($client->contact)->send(new LowBalanceAlert($client, $threshold));

                // Cache that we've sent an alert (expires in 24 hours)
                Cache::put($cacheKey, true, now()->addHours(24));

                $this->line("✓ Sent alert to {$client->name} ({$client->contact}) - Balance: KES {$client->balance}");
                $sentCount++;

                Log::info('Low balance alert sent', [
                    'client_id' => $client->id,
                    'client_name' => $client->name,
                    'balance' => $client->balance,
                    'threshold' => $threshold
                ]);

            } catch (\Exception $e) {
                $this->error("✗ Failed to send alert to {$client->name}: {$e->getMessage()}");
                
                Log::error('Failed to send low balance alert', [
                    'client_id' => $client->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("- Alerts sent: {$sentCount}");
        $this->info("- Skipped: {$skippedCount}");
        $this->info("- Total processed: {$lowBalanceClients->count()}");

        return Command::SUCCESS;
    }
}

