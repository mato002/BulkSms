<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\Campaign;

class SampleCampaignsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Get all clients
        $clients = Client::query()->get();
        if ($clients->isEmpty()) {
            // Create a default client if none
            $clientId = DB::table('clients')->insertGetId([
                'name' => 'Default Client',
                'email' => 'client@example.com',
                'phone' => '+0000000000',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $clients = Client::query()->where('id', $clientId)->get();
        }

        $campaigns = [
            [
                'name' => 'Welcome Series',
                'message' => 'Welcome {{name}}! Thanks for joining {{brand}}. Get started: {{link}}',
                'sender_id' => 'BULKSMS',
                'recipients' => json_encode(['+254712345678', '+254723456789', '+254734567890']),
                'status' => 'sent',
                'total_recipients' => 150,
                'sent_count' => 120,
                'delivered_count' => 115,
                'failed_count' => 5,
                'total_cost' => 90.00,
                'sent_at' => $now->subDays(2),
            ],
            [
                'name' => 'Product Launch',
                'message' => '🚀 New Product Alert! {{product_name}} is now available. Order now: {{order_link}}',
                'sender_id' => 'WHATSAPP',
                'recipients' => json_encode(['+254712345678', '+254723456789']),
                'status' => 'sent',
                'total_recipients' => 500,
                'sent_count' => 500,
                'delivered_count' => 485,
                'failed_count' => 15,
                'total_cost' => 0.00,
                'sent_at' => $now->subDays(5),
            ],
            [
                'name' => 'Payment Reminder',
                'message' => 'Hi {{name}}, payment of {{amount}} is due on {{due_date}}. Pay now: {{payment_link}}',
                'sender_id' => 'BULKSMS',
                'recipients' => json_encode(['+254712345678', '+254723456789']),
                'status' => 'sent',
                'total_recipients' => 75,
                'sent_count' => 60,
                'delivered_count' => 58,
                'failed_count' => 2,
                'total_cost' => 45.00,
                'sent_at' => $now->subDays(1),
            ],
            [
                'name' => 'Holiday Sale',
                'message' => '🎉 Black Friday Sale! Get {{discount}}% off on all items. Shop now: {{shop_link}}',
                'sender_id' => 'WHATSAPP',
                'recipients' => json_encode(['+254712345678', '+254723456789']),
                'status' => 'sent',
                'total_recipients' => 1000,
                'sent_count' => 1000,
                'delivered_count' => 950,
                'failed_count' => 50,
                'total_cost' => 0.00,
                'sent_at' => $now->subDays(7),
            ],
            [
                'name' => 'Newsletter',
                'message' => 'Weekly Update: {{content}} Read more: {{newsletter_link}}',
                'sender_id' => 'EMAIL',
                'recipients' => json_encode(['user1@example.com', 'user2@example.com']),
                'status' => 'scheduled',
                'total_recipients' => 200,
                'sent_count' => 0,
                'delivered_count' => 0,
                'failed_count' => 0,
                'total_cost' => 0.00,
                'scheduled_at' => $now->addDays(1),
            ],
            [
                'name' => 'Order Confirmation',
                'message' => 'Order {{order_no}} confirmed! Total: {{total}}. Track: {{tracking_link}}',
                'sender_id' => 'BULKSMS',
                'recipients' => json_encode(['+254712345678', '+254723456789']),
                'status' => 'sent',
                'total_recipients' => 300,
                'sent_count' => 250,
                'delivered_count' => 245,
                'failed_count' => 5,
                'total_cost' => 187.50,
                'sent_at' => $now->subHours(6),
            ],
            [
                'name' => 'Customer Feedback',
                'message' => 'Hi {{name}}! How was your experience? Rate us: {{feedback_link}}',
                'sender_id' => 'WHATSAPP',
                'recipients' => json_encode(['+254712345678', '+254723456789']),
                'status' => 'draft',
                'total_recipients' => 100,
                'sent_count' => 0,
                'delivered_count' => 0,
                'failed_count' => 0,
                'total_cost' => 0.00,
            ],
            [
                'name' => 'Appointment Reminder',
                'message' => 'Reminder: Your appointment is on {{date}} at {{time}}. Location: {{location}}',
                'sender_id' => 'BULKSMS',
                'recipients' => json_encode(['+254712345678', '+254723456789']),
                'status' => 'sent',
                'total_recipients' => 80,
                'sent_count' => 70,
                'delivered_count' => 68,
                'failed_count' => 2,
                'total_cost' => 52.50,
                'sent_at' => $now->subHours(3),
            ],
            [
                'name' => 'Event Invitation',
                'message' => 'You\'re invited! {{event_name}} on {{date}}. RSVP: {{rsvp_link}}',
                'sender_id' => 'WHATSAPP',
                'recipients' => json_encode(['+254712345678', '+254723456789']),
                'status' => 'sent',
                'total_recipients' => 400,
                'sent_count' => 400,
                'delivered_count' => 380,
                'failed_count' => 20,
                'total_cost' => 0.00,
                'sent_at' => $now->subDays(3),
            ],
            [
                'name' => 'Survey Request',
                'message' => 'Help us improve! Take our 2-minute survey: {{survey_link}}',
                'sender_id' => 'EMAIL',
                'recipients' => json_encode(['user1@example.com', 'user2@example.com']),
                'status' => 'scheduled',
                'total_recipients' => 250,
                'sent_count' => 0,
                'delivered_count' => 0,
                'failed_count' => 0,
                'total_cost' => 0.00,
                'scheduled_at' => $now->addDays(2),
            ],
        ];

        foreach ($clients as $client) {
            foreach ($campaigns as $campaignData) {
                $this->upsertCampaign($client->id, $campaignData);
            }
        }
    }

    private function upsertCampaign(int $clientId, array $data): void
    {
        $now = now();
        DB::table('campaigns')->updateOrInsert(
            [
                'client_id' => $clientId,
                'name' => $data['name'],
            ],
            array_merge($data, [
                'client_id' => $clientId,
                'created_at' => $now,
                'updated_at' => $now,
            ])
        );
    }
}
