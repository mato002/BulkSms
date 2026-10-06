<?php

namespace App\Services;

use App\Mail\WelcomeSenderMail;
use App\Models\Client;
use App\Services\Messaging\DTO\OutboundMessage;
use App\Services\Messaging\MessageDispatcher;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SenderOnboardingService
{
    /**
     * Send onboarding credentials to the client's contact (email or phone)
     * and to the portal user email when a dashboard account was created.
     *
     * @param  array{name: string, email: string, password: string}|null  $portalUser
     * @return array{email: bool, sms: bool, recipients: list<string>}
     */
    public function notify(Client $client, ?array $portalUser = null): array
    {
        $result = ['email' => false, 'sms' => false, 'recipients' => []];

        $contact = trim((string) $client->contact);
        $apiDocUrl = $this->apiDocumentationUrl();
        $loginUrl = $this->loginUrl();

        if ($contact !== '' && $this->looksLikeEmail($contact)) {
            if ($this->sendEmail($client, $contact, $apiDocUrl, $loginUrl, $portalUser)) {
                $result['email'] = true;
                $result['recipients'][] = $contact;
            }
        }

        if ($contact !== '' && $this->looksLikePhone($contact)) {
            if ($this->sendSms($client, $contact, $apiDocUrl, $loginUrl, $portalUser)) {
                $result['sms'] = true;
                $result['recipients'][] = $contact;
            }
        }

        if ($portalUser && $this->looksLikeEmail($portalUser['email'])) {
            $userEmail = $portalUser['email'];
            if (!in_array($userEmail, $result['recipients'], true)) {
                if ($this->sendEmail($client, $userEmail, $apiDocUrl, $loginUrl, $portalUser)) {
                    $result['email'] = true;
                    $result['recipients'][] = $userEmail;
                }
            }
        }

        if (!empty($result['recipients'])) {
            $this->recordOnboardingSent($client, $result);
        }

        return $result;
    }

    /**
     * @return array{sent_at: ?string, channels: list<string>, recipients: list<string>}
     */
    public function onboardingMeta(Client $client): array
    {
        $settings = $client->settings ?? [];

        return [
            'sent_at' => $settings['onboarding_sent_at'] ?? null,
            'channels' => $settings['onboarding_channels'] ?? [],
            'recipients' => $settings['onboarding_recipients'] ?? [],
        ];
    }

    /**
     * @param  array{email: bool, sms: bool, recipients: list<string>}  $result
     */
    public function recordOnboardingSent(Client $client, array $result): void
    {
        $settings = $client->settings ?? [];
        $settings['onboarding_sent_at'] = now()->toIso8601String();
        $settings['onboarding_channels'] = array_values(array_unique(array_filter([
            $result['email'] ? 'email' : null,
            $result['sms'] ? 'sms' : null,
        ])));
        $settings['onboarding_recipients'] = $result['recipients'];

        $client->update(['settings' => $settings]);
    }

    /**
     * @return array{name: string, email: string, password?: string}|null
     */
    public function portalUserForClient(Client $client, ?string $password = null): ?array
    {
        $user = $client->users()->first();
        if (!$user) {
            return null;
        }

        $portalUser = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        if ($password !== null && $password !== '') {
            $portalUser['password'] = $password;
        }

        return $portalUser;
    }

    public function curlExample(Client $client): string
    {
        $baseUrl = rtrim(config('app.url'), '/');

        return <<<CURL
curl -X POST {$baseUrl}/api/{$client->id}/messages/send \\
  -H "X-API-Key: {$client->api_key}" \\
  -H "Content-Type: application/json" \\
  -d '{
    "client_id": {$client->id},
    "channel": "sms",
    "recipient": "254712345678",
    "sender": "{$client->sender_id}",
    "body": "Hello from {$client->sender_id}!",
    "dry_run": true
  }'
CURL;
    }

    /**
     * Plain-text message formatted for manual copy-paste into WhatsApp.
     *
     * @param  array{name: string, email: string, password?: string}|null  $portalUser
     */
    public function whatsappMessage(Client $client, ?array $portalUser = null): string
    {
        $appName = config('app.name');
        $env = $this->envCredentials($client);
        $apiDocUrl = $this->apiDocumentationUrl();
        $loginUrl = $this->loginUrl();

        $lines = [
            "Welcome to {$appName}!",
            '',
            'Your API account is ready. Use these details to integrate:',
            '',
            "Sender ID: {$client->sender_id}",
            "Client ID: {$client->id}",
            "API Key: {$client->api_key}",
            'Balance: KES ' . number_format((float) $client->balance, 2),
            '',
            "API URL: {$env['API_URL']}",
            "API Docs: {$apiDocUrl}",
            '',
            'Add to your .env file:',
            $this->envBlock($client),
        ];

        if ($portalUser) {
            $lines[] = '';
            $lines[] = 'Dashboard login:';
            $lines[] = "URL: {$loginUrl}";
            $lines[] = "Email: {$portalUser['email']}";
            if (!empty($portalUser['password'])) {
                $lines[] = "Password: {$portalUser['password']}";
            } else {
                $lines[] = 'Use your existing portal password.';
            }
        }

        $lines[] = '';
        $lines[] = 'Keep your API key private.';

        return implode("\n", $lines);
    }

    public function apiDocumentationUrl(): string
    {
        return URL::route('api.documentation', [], true);
    }

    public function loginUrl(): string
    {
        return URL::route('login', [], true);
    }

    /**
     * @return array<string, string>
     */
    public function envCredentials(Client $client): array
    {
        $baseUrl = rtrim(config('app.url'), '/');

        return [
            'APP_URL' => $baseUrl,
            'API_URL' => $baseUrl . '/api',
            'API_KEY' => $client->api_key,
            'CLIENT_ID' => (string) $client->id,
            'SENDER_ID' => $client->sender_id,
        ];
    }

    public function envBlock(Client $client): string
    {
        $lines = [];
        foreach ($this->envCredentials($client) as $key => $value) {
            $lines[] = $key . '=' . $value;
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array{name: string, email: string, password: string}|null  $portalUser
     */
    private function sendEmail(
        Client $client,
        string $recipient,
        string $apiDocUrl,
        string $loginUrl,
        ?array $portalUser
    ): bool {
        try {
            Mail::to($recipient)->send(new WelcomeSenderMail(
                $client,
                $apiDocUrl,
                $loginUrl,
                $this->envCredentials($client),
                $portalUser
            ));

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send sender onboarding email', [
                'client_id' => $client->id,
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param  array{name: string, email: string, password: string}|null  $portalUser
     */
    private function sendSms(
        Client $client,
        string $recipient,
        string $apiDocUrl,
        string $loginUrl,
        ?array $portalUser
    ): bool {
        $billClient = $this->resolveBillingClient();
        if (!$billClient) {
            Log::warning('Sender onboarding SMS skipped: no billing client configured', [
                'client_id' => $client->id,
            ]);

            return false;
        }

        $phone = PhoneNumber::withCountryCode($recipient);
        $messages = $this->buildSmsMessages($client, $apiDocUrl, $loginUrl, $portalUser);

        try {
            $dispatcher = app(MessageDispatcher::class);

            foreach ($messages as $body) {
                $dispatcher->dispatch(new OutboundMessage(
                    clientId: $billClient->id,
                    channel: 'sms',
                    recipient: $phone,
                    sender: $billClient->sender_id,
                    body: $body,
                    metadata: [
                        'type' => 'sender_onboarding',
                        'onboarded_client_id' => $client->id,
                    ],
                ));
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send sender onboarding SMS', [
                'client_id' => $client->id,
                'recipient' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param  array{name: string, email: string, password: string}|null  $portalUser
     * @return list<string>
     */
    private function buildSmsMessages(
        Client $client,
        string $apiDocUrl,
        string $loginUrl,
        ?array $portalUser
    ): array {
        $appName = config('app.name');
        $env = $this->envCredentials($client);

        $primary = implode("\n", array_filter([
            "Welcome to {$appName}!",
            "Sender: {$client->sender_id}",
            "Client ID: {$client->id}",
            "API Key: {$client->api_key}",
            'Balance: KES ' . number_format((float) $client->balance, 2),
            "API URL: {$env['API_URL']}",
            "Docs: {$apiDocUrl}",
        ]));

        $messages = [$primary];

        if ($portalUser && !empty($portalUser['password'])) {
            $messages[] = implode("\n", [
                'Dashboard login:',
                "URL: {$loginUrl}",
                "Email: {$portalUser['email']}",
                "Password: {$portalUser['password']}",
            ]);
        } elseif ($portalUser) {
            $messages[] = implode("\n", [
                'Dashboard login:',
                "URL: {$loginUrl}",
                "Email: {$portalUser['email']}",
                'Use your existing portal password.',
            ]);
        }

        return $messages;
    }

    private function resolveBillingClient(): ?Client
    {
        $configuredId = (int) config('sms.onboarding_bill_client_id', 0);
        if ($configuredId > 0) {
            return Client::query()
                ->where('id', $configuredId)
                ->where('status', true)
                ->first();
        }

        return Client::query()
            ->where('sender_id', 'PRADY_TECH')
            ->where('status', true)
            ->first();
    }

    private function looksLikeEmail(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function looksLikePhone(string $value): bool
    {
        if (str_contains($value, '@')) {
            return false;
        }

        return strlen(PhoneNumber::sanitize($value)) >= 9;
    }
}
