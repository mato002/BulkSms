<?php

namespace App\Services\Messaging\Drivers\Email;

use App\Services\Messaging\Contracts\MessageSender;
use App\Services\Messaging\DTO\OutboundMessage;

class SmtpEmailSender implements MessageSender
{
    public function __construct(private readonly array $credentials)
    {
    }

    public function send(OutboundMessage $message): string
    {
        // TODO: integrate with mailer or provider API. For now, stub id.
        return 'smtp_'.bin2hex(random_bytes(6));
    }

    public function channel(): string
    {
        return 'email';
    }

    public function provider(): string
    {
        return 'smtp';
    }
}



