<?php

namespace App\Services\Cache;

use App\Models\Client;
use Illuminate\Support\Facades\Cache;

class ClientSettingsCache
{
    private const CACHE_PREFIX = 'client_settings:';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get cached client settings
     */
    public static function get(int $clientId): ?array
    {
        return Cache::get(self::CACHE_PREFIX . $clientId);
    }

    /**
     * Cache client settings
     */
    public static function put(int $clientId, array $settings): void
    {
        Cache::put(
            self::CACHE_PREFIX . $clientId,
            $settings,
            now()->addSeconds(self::CACHE_TTL)
        );
    }

    /**
     * Get or remember client settings
     */
    public static function remember(int $clientId, callable $callback): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . $clientId,
            now()->addSeconds(self::CACHE_TTL),
            $callback
        );
    }

    /**
     * Forget cached client settings
     */
    public static function forget(int $clientId): void
    {
        Cache::forget(self::CACHE_PREFIX . $clientId);
    }

    /**
     * Get client with cached settings
     */
    public static function getClientWithSettings(int $clientId): ?Client
    {
        $client = Client::find($clientId);
        
        if (!$client) {
            return null;
        }

        // Cache settings if not already cached
        $settings = self::remember($clientId, function () use ($client) {
            return [
                'balance' => $client->balance,
                'price_per_unit' => $client->price_per_unit,
                'sender_id' => $client->sender_id,
                'status' => $client->status,
                'tier' => $client->tier,
                'settings' => $client->settings,
                'webhook_url' => $client->webhook_url,
                'webhook_active' => $client->webhook_active,
            ];
        });

        return $client;
    }

    /**
     * Invalidate cache when client is updated
     */
    public static function invalidate(int $clientId): void
    {
        self::forget($clientId);
    }
}




