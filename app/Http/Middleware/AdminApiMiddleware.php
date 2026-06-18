<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminApiMiddleware
{
    /**
     * Restrict to the main platform admin client (PRADY_TECH, client id 1).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $client = $request->user();

        if (!$client instanceof Client || (int) $client->id !== 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Admin API access required. Use the PRADY_TECH (client 1) API key.',
            ], 403);
        }

        return $next($request);
    }
}
