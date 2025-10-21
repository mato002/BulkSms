<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\User;
use App\Models\NotificationSetting;
use App\Notifications\LowBalanceNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckLowBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balance:check-low';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for clients with low balances and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for low balances...');

        $settings = NotificationSetting::where('low_balance_enabled', true)->get();

        if ($settings->isEmpty()) {
            $this->info('No clients have low balance alerts enabled.');
            return 0;
        }

        $alertCount = 0;

        foreach ($settings as $setting) {
            $client = $setting->client;
            
            if (!$client) {
                continue;
            }

            // Check if balance is below threshold
            if ($client->balance < $setting->low_balance_threshold) {
                $this->info("Low balance detected for: {$client->name}");
                $this->info("  Balance: KES " . number_format($client->balance, 2));
                $this->info("  Threshold: KES " . number_format($setting->low_balance_threshold, 2));

                // Get users to notify
                $users = User::where('client_id', $client->id)->get();

                foreach ($users as $user) {
                    try {
                        // Check if already notified recently (within 24 hours)
                        $recentNotification = $user->notifications()
                            ->where('type', LowBalanceNotification::class)
                            ->where('created_at', '>', now()->subHours(24))
                            ->first();

                        if (!$recentNotification) {
                            $user->notify(new LowBalanceNotification(
                                $client,
                                $client->balance,
                                $setting->low_balance_threshold
                            ));

                            $this->info("  ✓ Notified: {$user->email}");
                            $alertCount++;
                        } else {
                            $this->info("  ⊘ Already notified recently: {$user->email}");
                        }
                    } catch (\Exception $e) {
                        $this->error("  ✗ Failed to notify {$user->email}: {$e->getMessage()}");
                        Log::error('Failed to send low balance notification', [
                            'user_id' => $user->id,
                            'client_id' => $client->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        $this->info("✓ Done! Sent {$alertCount} alert(s).");
        return 0;
    }
}
