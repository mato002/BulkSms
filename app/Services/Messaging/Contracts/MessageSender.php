<?php

namespace App\Services\Messaging\Contracts;

use App\Services\Messaging\DTO\OutboundMessage;

interface MessageSender
{
    /**
     * Send a message via this sender's channel/provider.
     * Should return a provider message id on success.
     */
    public function send(OutboundMessage $message): string;

    /** Return the channel handled by this sender: sms|whatsapp|email */
    public function channel(): string;

    /** Return the provider key, e.g., twilio|whatsapp_cloud|smtp */
    public function provider(): string;
}



