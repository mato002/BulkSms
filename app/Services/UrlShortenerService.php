<?php

namespace App\Services;

use App\Models\ShortLink;
use App\Models\Message;

class UrlShortenerService
{
    /**
     * Create a short link for a message
     *
     * @param int $messageId
     * @param int|null $expiryDays Number of days until link expires (null = never)
     * @return string The shortened URL
     */
    public function createShortLink(int $messageId, ?int $expiryDays = null): string
    {
        // Check if short link already exists for this message
        $existingLink = ShortLink::where('message_id', $messageId)->first();
        
        if ($existingLink) {
            return $this->buildUrl($existingLink->code);
        }

        // Generate unique code (4 characters for maximum shortness)
        $code = ShortLink::generateUniqueCode(4);

        // Calculate expiry date if provided
        $expiresAt = $expiryDays ? now()->addDays($expiryDays) : null;

        // Create short link record
        ShortLink::create([
            'code' => $code,
            'message_id' => $messageId,
            'expires_at' => $expiresAt,
        ]);

        return $this->buildUrl($code);
    }

    /**
     * Build the full short URL
     *
     * @param string $code
     * @return string
     */
    private function buildUrl(string $code): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        return "{$baseUrl}/x/{$code}";
    }

    /**
     * Get message ID from short code
     *
     * @param string $code
     * @return int|null
     */
    public function getMessageIdFromCode(string $code): ?int
    {
        $shortLink = ShortLink::where('code', $code)->first();
        
        if (!$shortLink) {
            return null;
        }

        // Check if expired
        if ($shortLink->isExpired()) {
            return null;
        }

        // Record the click
        $shortLink->recordClick();

        return $shortLink->message_id;
    }

    /**
     * Get analytics for a short link
     *
     * @param string $code
     * @return array|null
     */
    public function getAnalytics(string $code): ?array
    {
        $shortLink = ShortLink::where('code', $code)
            ->with('message')
            ->first();

        if (!$shortLink) {
            return null;
        }

        return [
            'code' => $shortLink->code,
            'clicks' => $shortLink->clicks,
            'created_at' => $shortLink->created_at,
            'last_clicked_at' => $shortLink->last_clicked_at,
            'expires_at' => $shortLink->expires_at,
            'is_expired' => $shortLink->isExpired(),
            'message_id' => $shortLink->message_id,
        ];
    }
}

