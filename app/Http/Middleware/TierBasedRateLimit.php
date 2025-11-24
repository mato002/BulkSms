<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\Cache\RateLimitCache;
use Symfony\Component\HttpFoundation\Response;

class TierBasedRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $client = $request->user();
        
        if (!$client) {
            return $next($request);
        }

        // Get rate limit based on tier
        $limits = $this->getTierLimits();
        $tier = $client->tier ?? 'bronze';
        $maxAttempts = $limits[$tier] ?? 60;

        // Create unique key for this client
        $key = 'api_rate_limit:' . $client->id;

        // Use cached rate limit counter
        $current = RateLimitCache::increment($key, 60);
        
        // Check rate limit
        if (RateLimitCache::isExceeded($key, $maxAttempts)) {
            $seconds = RateLimitCache::timeUntilReset($key, 60);
            
            return response()->json([
                'status' => 'error',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
                'message' => "Too many requests. Please try again in {$seconds} seconds.",
                'retry_after' => $seconds,
                'tier' => $tier,
                'limit' => $maxAttempts . ' requests per minute'
            ], 429)->header('Retry-After', $seconds);
        }

        // Add rate limit headers to response
        $response = $next($request);
        
        $remaining = RateLimitCache::remaining($key, $maxAttempts);
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $remaining);
        $response->headers->set('X-RateLimit-Reset', now()->addMinute()->timestamp);

        return $response;
    }

    /**
     * Get rate limit configuration by tier
     *
     * @return array
     */
    protected function getTierLimits(): array
    {
        return [
            'bronze' => 60,      // 60 requests per minute
            'silver' => 120,     // 120 requests per minute
            'gold' => 300,       // 300 requests per minute
            'platinum' => 1000,  // 1000 requests per minute
        ];
    }
}

