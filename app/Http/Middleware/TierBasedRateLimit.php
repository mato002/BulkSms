<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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

        // Check rate limit
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'status' => 'error',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
                'message' => "Too many requests. Please try again in {$seconds} seconds.",
                'retry_after' => $seconds,
                'tier' => $tier,
                'limit' => $maxAttempts . ' requests per minute'
            ], 429)->header('Retry-After', $seconds);
        }

        // Hit the rate limiter
        RateLimiter::hit($key, 60); // 60 seconds = 1 minute

        // Add rate limit headers to response
        $response = $next($request);
        
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $maxAttempts - RateLimiter::attempts($key));
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

