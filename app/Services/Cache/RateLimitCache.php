<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RateLimitCache
{
    private const CACHE_PREFIX = 'rate_limit:';
    private const DEFAULT_TTL = 60; // 1 minute

    /**
     * Get current rate limit count for a key
     */
    public static function get(string $key): int
    {
        return (int) Cache::get(self::CACHE_PREFIX . $key, 0);
    }

    /**
     * Increment rate limit counter
     */
    public static function increment(string $key, int $ttl = self::DEFAULT_TTL): int
    {
        $cacheKey = self::CACHE_PREFIX . $key;
        $current = Cache::get($cacheKey, 0);
        $newValue = $current + 1;
        
        Cache::put($cacheKey, $newValue, now()->addSeconds($ttl));
        
        return $newValue;
    }

    /**
     * Check if rate limit is exceeded
     */
    public static function isExceeded(string $key, int $limit): bool
    {
        return self::get($key) >= $limit;
    }

    /**
     * Reset rate limit counter
     */
    public static function reset(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
    }

    /**
     * Get remaining attempts
     */
    public static function remaining(string $key, int $limit): int
    {
        $current = self::get($key);
        return max(0, $limit - $current);
    }

    /**
     * Get time until reset (in seconds)
     */
    public static function timeUntilReset(string $key, int $ttl = self::DEFAULT_TTL): int
    {
        $cacheKey = self::CACHE_PREFIX . $key;

        if (
            config('cache.default') === 'redis'
            && class_exists(\Redis::class)
        ) {
            try {
                $remaining = Redis::ttl($cacheKey);
                if ($remaining > 0) {
                    return $remaining;
                }
            } catch (\Throwable $exception) {
                Log::warning('Failed to read TTL from Redis', [
                    'key' => $cacheKey,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return $ttl;
    }
}

