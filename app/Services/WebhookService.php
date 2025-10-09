<?php

namespace App\Services;

use App\Models\Client;
use App\Jobs\SendWebhookJob;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Send a webhook to client's system
     *
     * @param Client $client
     * @param string $event
     * @param array $data
     * @param bool $async - Whether to send asynchronously (via queue)
     * @return bool
     */
    public function send(Client $client, string $event, array $data, bool $async = true)
    {
        // Check if webhooks are configured and active
        if (!$this->canSendWebhook($client, $event)) {
            Log::debug('Webhook not sent - not configured or inactive', [
                'client_id' => $client->id,
                'event' => $event
            ]);
            return false;
        }

        // Prepare webhook payload
        $payload = [
            'event' => $event,
            'client_id' => $client->id,
            'timestamp' => now()->toIso8601String(),
            'data' => $data
        ];

        Log::info('Sending webhook', [
            'client_id' => $client->id,
            'event' => $event,
            'url' => $client->webhook_url
        ]);

        // Send webhook
        if ($async) {
            // Dispatch to queue for asynchronous sending
            SendWebhookJob::dispatch($client, $event, $payload);
            return true;
        } else {
            // Send synchronously
            return $this->sendWebhookNow($client, $payload);
        }
    }

    /**
     * Send webhook immediately (synchronous)
     *
     * @param Client $client
     * @param array $payload
     * @return bool
     */
    public function sendWebhookNow(Client $client, array $payload)
    {
        try {
            $signature = $this->generateSignature($payload, $client->webhook_secret);

            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Event' => $payload['event'],
                    'User-Agent' => 'BulkSMS-Webhook/1.0'
                ])
                ->post($client->webhook_url, $payload);

            if ($response->successful()) {
                Log::info('Webhook sent successfully', [
                    'client_id' => $client->id,
                    'event' => $payload['event'],
                    'status' => $response->status()
                ]);
                return true;
            } else {
                Log::warning('Webhook failed', [
                    'client_id' => $client->id,
                    'event' => $payload['event'],
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Webhook exception', [
                'client_id' => $client->id,
                'event' => $payload['event'],
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if webhook can be sent
     *
     * @param Client $client
     * @param string $event
     * @return bool
     */
    public function canSendWebhook(Client $client, string $event): bool
    {
        // Check if webhook is active
        if (!$client->webhook_active) {
            return false;
        }

        // Check if webhook URL is configured
        if (empty($client->webhook_url)) {
            return false;
        }

        // Check if this event is subscribed
        if (!empty($client->webhook_events)) {
            if (!in_array($event, $client->webhook_events)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate HMAC signature for webhook
     *
     * @param array $payload
     * @param string $secret
     * @return string
     */
    public function generateSignature(array $payload, string $secret): string
    {
        $jsonPayload = json_encode($payload);
        return hash_hmac('sha256', $jsonPayload, $secret);
    }

    /**
     * Verify webhook signature
     *
     * @param string $payload
     * @param string $signature
     * @param string $secret
     * @return bool
     */
    public function verifySignature(string $payload, string $signature, string $secret): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Send balance updated webhook
     *
     * @param Client $client
     * @param float $oldBalance
     * @param float $newBalance
     * @param string $transactionId
     * @return bool
     */
    public function sendBalanceUpdated(Client $client, float $oldBalance, float $newBalance, string $transactionId = null)
    {
        return $this->send($client, 'balance.updated', [
            'old_balance' => $oldBalance,
            'new_balance' => $newBalance,
            'amount_added' => $newBalance - $oldBalance,
            'transaction_id' => $transactionId,
            'currency' => 'KES'
        ]);
    }

    /**
     * Send message delivered webhook
     *
     * @param Client $client
     * @param array $messageData
     * @return bool
     */
    public function sendMessageDelivered(Client $client, array $messageData)
    {
        return $this->send($client, 'message.delivered', $messageData);
    }

    /**
     * Send message failed webhook
     *
     * @param Client $client
     * @param array $messageData
     * @return bool
     */
    public function sendMessageFailed(Client $client, array $messageData)
    {
        return $this->send($client, 'message.failed', $messageData);
    }

    /**
     * Send top-up completed webhook
     *
     * @param Client $client
     * @param array $topupData
     * @return bool
     */
    public function sendTopupCompleted(Client $client, array $topupData)
    {
        return $this->send($client, 'topup.completed', $topupData);
    }

    /**
     * Send top-up failed webhook
     *
     * @param Client $client
     * @param array $topupData
     * @return bool
     */
    public function sendTopupFailed(Client $client, array $topupData)
    {
        return $this->send($client, 'topup.failed', $topupData);
    }

    /**
     * Get list of available webhook events
     *
     * @return array
     */
    public static function getAvailableEvents(): array
    {
        return [
            'balance.updated' => 'Triggered when balance changes',
            'message.sent' => 'Triggered when a message is sent',
            'message.delivered' => 'Triggered when a message is delivered',
            'message.failed' => 'Triggered when a message fails',
            'topup.completed' => 'Triggered when a top-up is successful',
            'topup.failed' => 'Triggered when a top-up fails',
        ];
    }
}

