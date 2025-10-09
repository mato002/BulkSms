<?php

namespace App\Jobs;

use App\Models\Client;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $client;
    public $event;
    public $payload;
    
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60; // Wait 60 seconds between retries

    /**
     * Create a new job instance.
     */
    public function __construct(Client $client, string $event, array $payload)
    {
        $this->client = $client;
        $this->event = $event;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(WebhookService $webhookService): void
    {
        Log::info('Processing webhook job', [
            'client_id' => $this->client->id,
            'event' => $this->event,
            'attempt' => $this->attempts()
        ]);

        try {
            $success = $webhookService->sendWebhookNow($this->client, $this->payload);

            if (!$success && $this->attempts() < $this->tries) {
                // If failed and haven't exhausted retries, release back to queue
                $this->release($this->backoff);
            }

        } catch (\Exception $e) {
            Log::error('Webhook job failed', [
                'client_id' => $this->client->id,
                'event' => $this->event,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            // Retry if we haven't exhausted attempts
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff);
            } else {
                // All retries exhausted
                $this->fail($e);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Webhook job permanently failed', [
            'client_id' => $this->client->id,
            'event' => $this->event,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // TODO: Notify admin about permanently failed webhook
        // TODO: Consider disabling webhook for this client after too many failures
    }
}

