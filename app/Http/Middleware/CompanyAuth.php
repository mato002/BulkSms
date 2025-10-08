<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Client;

class CompanyAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $companyId = $request->route('company_id');
        $client = $request->user();
        
        if (!$client || $client->id != $companyId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to company data'
            ], 403);
        }
        
        return $next($request);
    }
}
