<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;

class CreateTestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:test {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test notification for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }
        } else {
            // Get the first user if no user_id specified
            $user = User::first();
            if (!$user) {
                $this->error("No users found in the database.");
                return 1;
            }
        }

        $clientId = $user->client_id ?? 1;

        $this->info("Creating test notification for user: {$user->name} (ID: {$user->id}, Client ID: {$clientId})");

        // Create a test notification
        $notification = Notification::create([
            'client_id' => $clientId,
            'user_id' => $user->id,
            'type' => 'test_notification',
            'title' => 'Test Notification',
            'message' => 'This is a test notification created at ' . now()->format('Y-m-d H:i:s'),
            'icon' => 'bi-bell-fill',
            'color' => 'primary',
            'link' => route('dashboard'),
            'is_read' => false,
        ]);

        $this->info("✅ Test notification created successfully!");
        $this->line("  - ID: {$notification->id}");
        $this->line("  - Title: {$notification->title}");
        $this->line("  - Message: {$notification->message}");
        $this->line("  - Client ID: {$notification->client_id}");
        $this->line("  - User ID: {$notification->user_id}");

        return 0;
    }
}


