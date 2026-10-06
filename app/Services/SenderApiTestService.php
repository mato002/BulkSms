<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SenderApiTestService
{
    /**
     * Run an end-to-end API connectivity test for a sender (no real SMS sent).
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     steps: list<array{label: string, passed: bool, detail: string}>,
     *     postman_tips: list<string>,
     *     endpoints: array<string, string>
     * }
     */
    public function run(Client $client, ?string $testPhone = null): array
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $steps = [];
        $postmanTips = $this->postmanTips();

        $steps[] = $this->step(
            'Sender account is active',
            (bool) $client->status,
            $client->status ? 'Account can authenticate.' : 'Activate the sender before testing.'
        );

        $steps[] = $this->step(
            'API key is configured',
            !empty($client->api_key),
            !empty($client->api_key) ? 'Key is present on this sender.' : 'Regenerate or assign an API key.'
        );

        if (!$client->status || empty($client->api_key)) {
            return $this->result(false, 'Fix sender setup issues before testing the API.', $steps, $postmanTips, $client, $baseUrl);
        }

        $steps[] = $this->step(
            'Wallet has balance',
            (float) $client->balance > 0,
            'KES ' . number_format((float) $client->balance, 2) . ' available.',
            (float) $client->balance <= 0
        );

        $profile = $this->dispatchApiRequest('GET', "/api/{$client->id}/client/profile", $client->api_key);
        $steps[] = $this->step(
            'API authentication — header (GET profile)',
            $profile['ok'],
            $profile['detail']
        );

        $profileQuery = $this->dispatchApiRequest(
            'GET',
            "/api/{$client->id}/client/profile?api_key=" . urlencode($client->api_key),
            null
        );
        $steps[] = $this->step(
            'API authentication — query param (GET profile)',
            $profileQuery['ok'],
            $profileQuery['detail']
        );

        $balance = $this->dispatchApiRequest('GET', "/api/{$client->id}/client/balance", $client->api_key);
        $steps[] = $this->step(
            'Company scope (GET balance)',
            $balance['ok'],
            $balance['detail']
        );

        $payload = [
            'client_id' => $client->id,
            'channel' => 'sms',
            'recipient' => PhoneNumber::withCountryCode($testPhone ?? '254700000000'),
            'sender' => $client->sender_id,
            'body' => 'API connection test — dry run, no SMS sent.',
            'dry_run' => true,
        ];

        $send = $this->dispatchApiRequest(
            'POST',
            "/api/{$client->id}/messages/send",
            $client->api_key,
            $payload
        );

        $steps[] = $this->step(
            'Send endpoint validation (POST dry run)',
            $send['ok'],
            $send['detail']
        );

        $critical = array_filter($steps, fn (array $step) => !$step['passed'] && !($step['warning'] ?? false));
        $success = empty($critical);

        return $this->result(
            $success,
            $success
                ? 'Sender can reach the API successfully. Share the Postman tips below with your client.'
                : 'API test failed. See failed steps and Postman setup tips below.',
            $steps,
            $postmanTips,
            $client,
            $baseUrl
        );
    }

    private function result(
        bool $success,
        string $message,
        array $steps,
        array $postmanTips,
        Client $client,
        string $baseUrl
    ): array {
        return [
            'success' => $success,
            'message' => $message,
            'steps' => $steps,
            'postman_tips' => $postmanTips,
            'endpoints' => [
                'profile' => "{$baseUrl}/api/{$client->id}/client/profile",
                'balance' => "{$baseUrl}/api/{$client->id}/client/balance",
                'send' => "{$baseUrl}/api/{$client->id}/messages/send",
            ],
            'sample_body' => [
                'client_id' => $client->id,
                'channel' => 'sms',
                'recipient' => '254712345678',
                'sender' => $client->sender_id,
                'body' => 'Hello from API',
            ],
        ];
    }

    private function step(string $label, bool $passed, string $detail, bool $warning = false): array
    {
        return compact('label', 'passed', 'detail') + ['warning' => $warning];
    }

    /**
     * @return list<string>
     */
    private function postmanTips(): array
    {
        return [
            'Recommended: header X-API-Key with your full key (including sk_ prefix).',
            'Also supported: query ?api_key=... or ?APIKey=..., or Authorization: Bearer <key>.',
            'URL must include the sender Client ID: /api/{client_id}/messages/send (e.g. /api/155/messages/send).',
            'JSON body must include client_id matching the URL, plus channel, recipient, and body.',
            'Set header Content-Type: application/json and send a raw JSON body.',
            'Optional: add "dry_run": true to validate without sending an SMS.',
        ];
    }

    /**
     * @return array{ok: bool, detail: string, status: int, body: ?array}
     */
    private function dispatchApiRequest(string $method, string $path, ?string $apiKey, ?array $json = null): array
    {
        try {
            $server = ['HTTP_ACCEPT' => 'application/json'];

            if ($json !== null) {
                $content = json_encode($json);
                $server['CONTENT_TYPE'] = 'application/json';
            } else {
                $content = '';
            }

            $request = Request::create($path, $method, [], [], [], $server, $content);
            if ($apiKey !== null) {
                $request->headers->set('X-API-Key', $apiKey);
            }

            $response = app()->handle($request);
            app()->terminate();

            $status = $response->getStatusCode();
            $decoded = json_decode($response->getContent(), true);
            $message = is_array($decoded) ? ($decoded['message'] ?? null) : null;

            if ($status >= 200 && $status < 300) {
                return [
                    'ok' => true,
                    'detail' => $message ?: "HTTP {$status} OK",
                    'status' => $status,
                    'body' => is_array($decoded) ? $decoded : null,
                ];
            }

            return [
                'ok' => false,
                'detail' => $message ?: "HTTP {$status} — " . substr($response->getContent(), 0, 200),
                'status' => $status,
                'body' => is_array($decoded) ? $decoded : null,
            ];
        } catch (\Throwable $e) {
            Log::error('Sender API test request failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'detail' => 'Request error: ' . $e->getMessage(),
                'status' => 0,
                'body' => null,
            ];
        }
    }
}
