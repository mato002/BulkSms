<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Log;

class ApiAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $apiKey = $this->resolveApiKey($request);
        $client = null;
        $success = false;
        $errorMessage = null;
        
        // Attempt authentication
        if (!$apiKey) {
            $errorMessage = 'API key required';
            $this->logRequest($request, null, $apiKey, 401, $errorMessage, $startTime, false);
            
            return response()->json([
                'status' => 'error',
                'message' => $errorMessage
            ], 401);
        }

        $client = Client::where('api_key', $apiKey)
                       ->where('status', true)
                       ->first();

        if (!$client) {
            $errorMessage = 'Invalid API key';
            $this->logRequest($request, null, $apiKey, 401, $errorMessage, $startTime, false);
            
            return response()->json([
                'status' => 'error',
                'message' => $errorMessage
            ], 401);
        }

        // Set the authenticated client
        $request->setUserResolver(function () use ($client) {
            return $client;
        });

        // Log successful authentication and continue
        $response = $next($request);
        
        // Calculate response time
        $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        
        // Log the successful request
        $this->logRequest(
            $request, 
            $client, 
            $apiKey, 
            $response->getStatusCode(), 
            null, 
            $startTime, 
            true,
            $response
        );

        return $response;
    }

    private function resolveApiKey(Request $request): ?string
    {
        foreach (['X-API-Key', 'x-api-key', 'X-API-KEY', 'APIKey', 'Api-Key', 'api-key'] as $header) {
            $key = $this->normalizeApiKey($request->header($header));
            if ($key !== null) {
                return $key;
            }
        }

        foreach (['api_key', 'APIKey', 'api-key', 'API-Key', 'apikey'] as $param) {
            $key = $this->normalizeApiKey($request->query($param));
            if ($key !== null) {
                return $key;
            }
        }

        $authorization = $request->header('Authorization', '');
        if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
            return $this->normalizeApiKey($matches[1]);
        }

        return null;
    }

    private function normalizeApiKey(mixed $key): ?string
    {
        if ($key === null || $key === '') {
            return null;
        }

        $key = ltrim(trim((string) $key), ':');

        return $key !== '' ? $key : null;
    }

    /**
     * Log the API request
     */
    private function logRequest(
        Request $request, 
        ?Client $client, 
        ?string $apiKey, 
        int $statusCode, 
        ?string $errorMessage, 
        float $startTime,
        bool $success,
        $response = null
    ) {
        try {
            $responseTime = (microtime(true) - $startTime) * 1000; // milliseconds
            
            // Prepare request body (mask sensitive data)
            $requestBody = $request->all();
            if (isset($requestBody['password'])) {
                $requestBody['password'] = '***MASKED***';
            }
            foreach (['api_key', 'APIKey', 'api-key', 'API-Key', 'apikey'] as $credentialField) {
                if (isset($requestBody[$credentialField])) {
                    $requestBody[$credentialField] = '***MASKED***';
                }
            }
            
            // Prepare headers (mask sensitive headers)
            $headers = $request->headers->all();
            if (isset($headers['x-api-key'])) {
                $headers['x-api-key'] = ['***MASKED***'];
            }
            if (isset($headers['authorization'])) {
                $headers['authorization'] = ['***MASKED***'];
            }
            
            // Prepare response body (limit size)
            $responseBody = null;
            if ($response) {
                $content = $response->getContent();
                $decoded = json_decode($content, true);
                if ($decoded && json_last_error() === JSON_ERROR_NONE) {
                    $responseBody = $decoded;
                } else {
                    $responseBody = ['raw' => substr($content, 0, 1000)]; // Limit to 1000 chars
                }
            }

            ApiLog::create([
                'client_id' => $client?->id,
                'api_key' => $apiKey ? substr($apiKey, 0, 10) . '***' : null, // Mask API key
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_headers' => $headers,
                'request_body' => $requestBody,
                'response_status' => $statusCode,
                'response_body' => $responseBody,
                'response_time_ms' => round($responseTime, 2),
                'success' => $success,
                'error_message' => $errorMessage,
            ]);

            // Also log to Laravel log for immediate visibility
            if ($success) {
                Log::info('API Request', [
                    'client' => $client?->name,
                    'endpoint' => $request->path(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'response_time' => round($responseTime, 2) . 'ms',
                ]);
            } else {
                Log::warning('API Request Failed', [
                    'endpoint' => $request->path(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'error' => $errorMessage,
                ]);
            }
        } catch (\Exception $e) {
            // Don't let logging errors break the application
            Log::error('Failed to log API request: ' . $e->getMessage());
        }
    }
}
