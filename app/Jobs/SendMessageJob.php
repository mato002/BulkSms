<?php

namespace App\Jobs;

use App\Services\Messaging\DTO\OutboundMessage;
use App\Services\Messaging\MessageDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    public function __construct(
        public readonly OutboundMessage $message
    ) {
    }

    public function handle(MessageDispatcher $dispatcher): void
    {
        $dispatcher->dispatch($this->message);
    }

    public function failed(\Throwable $exception): void
    {
        // Log the failure
        \Log::error('SendMessageJob failed', [
            'recipient' => $this->message->recipient,
            'channel' => $this->message->channel,
            'error' => $exception->getMessage(),
        ]);
    }
}

