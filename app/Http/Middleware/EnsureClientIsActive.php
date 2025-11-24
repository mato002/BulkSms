<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureClientIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Skip checks for admins or system client (id=1)
        if (($user->role ?? null) === 'admin' || ($user->client_id ?? 1) === 1) {
            return $next($request);
        }

        $client = $user->client;

        // If no client or already active, proceed
        if (!$client || ($client->status ?? false) === true) {
            return $next($request);
        }

        // Allowlist of paths an inactive tenant may access
        $allowedPatterns = [
            'tenant/onboarding*',
            'tenant/profile*',
            'tenant/billing*',
            'tenant/payment*',
            'tenant/payments/*',
            'tenant/notifications*',
            'tenant/api-docs*',
            'logout',
        ];

        foreach ($allowedPatterns as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        return redirect()->route('tenant.onboarding')
            ->with('error', 'Your account is pending activation. Please complete payment to unlock all features.');
    }
}


