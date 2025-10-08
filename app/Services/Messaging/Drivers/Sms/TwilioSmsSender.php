<?php

namespace App\Services\Messaging\Drivers\Sms;

use App\Services\Messaging\Contracts\MessageSender;
use App\Services\Messaging\DTO\OutboundMessage;

class TwilioSmsSender implements MessageSender
{
    public function __construct(private readonly array $credentials)
    {
    }

    public function send(OutboundMessage $message): string
    {
        // TODO: integrate Twilio SDK. For now, stub a provider message id.
        return 'twilio_'.bin2hex(random_bytes(6));
    }

    public function channel(): string
    {
        return 'sms';
    }

    public function provider(): string
    {
        return 'twilio';
    }
}



