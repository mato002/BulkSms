<?php

namespace App\Services;

use App\Models\Client;
use App\Services\Messaging\DTO\OutboundMessage;

class SmsOutboundFormatter
{
    public function __construct(private readonly UrlShortenerService $urlShortener)
    {
    }

    public function format(Client $client, OutboundMessage $outbound, int $messageId): string
    {
        $body = $outbound->body;

        if ($client->shouldAppendReplyLink($outbound->metadata)) {
            $shortUrl = $this->urlShortener->createShortLink($messageId);
            $body .= "\n\nReply: {$shortUrl}";
        }

        if ($client->shouldAppendStopFooter($outbound->metadata) && !$this->hasStopFooter($body)) {
            $body = rtrim($body) . ' ' . config('sms.stop_footer', 'STOP *456*9*5#');
        }

        return $body;
    }

    public function hasStopFooter(string $body): bool
    {
        return (bool) preg_match('/\bSTOP\b/i', $body)
            || str_contains($body, '*456*9*5#')
            || str_contains($body, '*456#');
    }
}
