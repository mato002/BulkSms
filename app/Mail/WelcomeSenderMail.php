<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeSenderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Client $client;

    public string $apiDocUrl;

    public string $loginUrl;

    /** @var array<string, string> */
    public array $envCredentials;

    /** @var array{name: string, email: string, password: string}|null */
    public ?array $portalUser;

    /**
     * @param  array<string, string>  $envCredentials
     * @param  array{name: string, email: string, password: string}|null  $portalUser
     */
    public function __construct(
        Client $client,
        ?string $apiDocUrl = null,
        ?string $loginUrl = null,
        array $envCredentials = [],
        ?array $portalUser = null
    ) {
        $this->client = $client;
        $this->apiDocUrl = $apiDocUrl ?? rtrim(config('app.url'), '/') . '/api-documentation';
        $this->loginUrl = $loginUrl ?? rtrim(config('app.url'), '/') . '/login';
        $this->envCredentials = $envCredentials ?: [
            'APP_URL' => rtrim(config('app.url'), '/'),
            'API_URL' => rtrim(config('app.url'), '/') . '/api',
            'API_KEY' => $client->api_key,
            'CLIENT_ID' => (string) $client->id,
            'SENDER_ID' => $client->sender_id,
        ];
        $this->portalUser = $portalUser;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . config('app.name') . ' - Your API Credentials',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-sender',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
