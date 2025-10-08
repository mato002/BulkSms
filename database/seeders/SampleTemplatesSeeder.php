<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Client;
use App\Models\Template;

class SampleTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Ensure at least one client exists
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

        foreach ($clients as $client) {
            // SMS Templates
            $this->upsertTemplate($client->id, [
                'name' => 'otp_code',
                'channel' => 'sms',
                'category' => 'transactional',
                'subject' => null,
                'body' => 'Your verification code is {{code}}. It expires in 10 minutes.',
                'variables' => json_encode(['code']),
                'approved' => true,
                'status' => 'approved',
            ]);

            $this->upsertTemplate($client->id, [
                'name' => 'payment_reminder',
                'channel' => 'sms',
                'category' => 'utility',
                'subject' => null,
                'body' => 'Hi {{name}}, your payment of {{amount}} is due on {{due_date}}.',
                'variables' => json_encode(['name','amount','due_date']),
                'approved' => true,
                'status' => 'approved',
            ]);

            // WhatsApp Templates
            $this->upsertTemplate($client->id, [
                'name' => 'wa_greeting',
                'channel' => 'whatsapp',
                'language' => 'en',
                'category' => 'marketing',
                'subject' => 'Hello {{name}}',
                'body' => 'Hi {{name}} 👋, thanks for connecting with {{brand}}. How can we help today?',
                'variables' => json_encode(['name','brand']),
                'components' => json_encode([
                    ['type' => 'header', 'format' => 'TEXT', 'text' => 'Hello {{1}}'],
                    ['type' => 'body', 'text' => 'Hi {{1}} 👋, thanks for connecting with {{2}}. How can we help today?']
                ]),
                'approved' => true,
                'status' => 'approved',
            ]);

            $this->upsertTemplate($client->id, [
                'name' => 'wa_order_update',
                'channel' => 'whatsapp',
                'language' => 'en',
                'category' => 'utility',
                'subject' => 'Order Update',
                'body' => 'Order {{order_no}} is now {{status}}. Track: {{tracking_url}}',
                'variables' => json_encode(['order_no','status','tracking_url']),
                'components' => json_encode([
                    ['type' => 'body', 'text' => 'Order {{1}} is now {{2}}. Track: {{3}}']
                ]),
                'approved' => true,
                'status' => 'approved',
            ]);

            // Email Templates
            $this->upsertTemplate($client->id, [
                'name' => 'welcome_email',
                'channel' => 'email',
                'category' => 'onboarding',
                'subject' => 'Welcome to {{brand}}, {{name}}! 🎉',
                'body' => '<h1>Welcome, {{name}}!</h1><p>We are thrilled to have you at {{brand}}.</p>',
                'variables' => json_encode(['name','brand']),
                'approved' => true,
                'status' => 'approved',
            ]);

            $this->upsertTemplate($client->id, [
                'name' => 'password_reset',
                'channel' => 'email',
                'category' => 'security',
                'subject' => 'Reset your password',
                'body' => '<p>Hi {{name}},</p><p>Click <a href="{{reset_link}}">here</a> to reset your password.</p>',
                'variables' => json_encode(['name','reset_link']),
                'approved' => true,
                'status' => 'approved',
            ]);
        }
    }

    private function upsertTemplate(int $clientId, array $data): void
    {
        $now = now();
        DB::table('templates')->updateOrInsert(
            [
                'client_id' => $clientId,
                'name' => $data['name'],
                'channel' => $data['channel'],
            ],
            array_merge($data, [
                'client_id' => $clientId,
                'created_at' => $now,
                'updated_at' => $now,
            ])
        );
    }
}
