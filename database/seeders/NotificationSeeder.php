<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientId = DB::table('clients')->value('id') ?? 1;

        $notifications = [
            [
                'client_id' => $clientId,
                'user_id' => null,
                'type' => 'campaign_completed',
                'title' => 'Campaign Completed Successfully',
                'message' => 'Your marketing campaign "Summer Sale 2024" has been completed. 1,250 messages sent successfully.',
                'icon' => 'bi-megaphone-fill',
                'color' => 'success',
                'link' => null,
                'is_read' => false,
                'read_at' => null,
                'metadata' => json_encode(['campaign_id' => 1, 'total_messages' => 1250]),
                'created_at' => now()->subMinutes(10),
                'updated_at' => now()->subMinutes(10),
            ],
            [
                'client_id' => $clientId,
                'user_id' => null,
                'type' => 'messages_failed',
                'title' => 'Some Messages Failed',
                'message' => '15 messages failed to send due to invalid phone numbers.',
                'icon' => 'bi-exclamation-triangle-fill',
                'color' => 'danger',
                'link' => null,
                'is_read' => false,
                'read_at' => null,
                'metadata' => json_encode(['count' => 15, 'reason' => 'Invalid phone numbers']),
                'created_at' => now()->subHour(),
                'updated_at' => now()->subHour(),
            ],
            [
                'client_id' => $clientId,
                'user_id' => null,
                'type' => 'system_alert',
                'title' => 'API Rate Limit Warning',
                'message' => 'You are approaching your API rate limit. 90% of your daily quota has been used.',
                'icon' => 'bi-info-circle-fill',
                'color' => 'warning',
                'link' => null,
                'is_read' => true,
                'read_at' => now()->subMinutes(30),
                'metadata' => json_encode(['usage_percent' => 90]),
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subMinutes(30),
            ],
            [
                'client_id' => $clientId,
                'user_id' => null,
                'type' => 'system_alert',
                'title' => 'New Contact Import Complete',
                'message' => 'Successfully imported 500 new contacts from your CSV file.',
                'icon' => 'bi-people-fill',
                'color' => 'primary',
                'link' => null,
                'is_read' => true,
                'read_at' => now()->subHours(3),
                'metadata' => json_encode(['imported_count' => 500]),
                'created_at' => now()->subHours(4),
                'updated_at' => now()->subHours(3),
            ],
            [
                'client_id' => $clientId,
                'user_id' => null,
                'type' => 'campaign_completed',
                'title' => 'WhatsApp Campaign Sent',
                'message' => 'Your WhatsApp campaign "Product Launch" has been sent to 850 recipients.',
                'icon' => 'bi-whatsapp',
                'color' => 'success',
                'link' => null,
                'is_read' => true,
                'read_at' => now()->subHours(5),
                'metadata' => json_encode(['campaign_id' => 2, 'total_messages' => 850]),
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(5),
            ],
        ];

        DB::table('notifications')->insert($notifications);
    }
}
