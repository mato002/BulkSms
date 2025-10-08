<?php

namespace App\Services\Messaging\DTO;

class OutboundMessage
{
    public function __construct(
        public readonly int $clientId,
        public readonly string $channel, // sms|whatsapp|email
        public readonly string $recipient,
        public readonly ?string $sender = null,
        public readonly ?string $subject = null,
        public readonly string $body = '',
        public readonly ?int $templateId = null,
        public readonly array $metadata = [],
    ) {
    }
}



