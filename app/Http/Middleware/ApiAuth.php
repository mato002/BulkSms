<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Client;

class ApiAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
        
        if (!$apiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'API key required'
            ], 401);
        }

        $client = Client::where('api_key', $apiKey)
                       ->where('status', true)
                       ->first();

        if (!$client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid API key'
            ], 401);
        }

        // Set the authenticated client
        $request->setUserResolver(function () use ($client) {
            return $client;
        });

        return $next($request);
    }
}
