<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Process scheduled SMS campaigns
        $schedule->command('sms:process-scheduled')
                 ->everyMinute()
                 ->withoutOverlapping();

        // Check SMS delivery status
        $schedule->command('sms:check-delivery')
                 ->everyFiveMinutes()
                 ->withoutOverlapping();

        // Clean up old SMS logs
        $schedule->command('sms:cleanup')
                 ->daily()
                 ->at('02:00');

        // Update client statistics
        $schedule->command('client:update-stats')
                 ->hourly()
                 ->withoutOverlapping();

        // Sync Onfon balances for clients with auto-sync enabled
        $schedule->command('onfon:sync-balances')
                 ->everyFifteenMinutes()
                 ->withoutOverlapping()
                 ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
