<?php

namespace App\Services\Messaging\Drivers\Email;

use App\Services\Messaging\Contracts\MessageSender;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SmtpEmailSender implements MessageSender
{
    private array $credentials;

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    public function send(string $recipient, string $body, ?string $subject = null, array $options = []): array
    {
        try {
            // Configure mail settings dynamically
            config([
                'mail.mailers.smtp.host' => $this->credentials['host'] ?? 'smtp.gmail.com',
                'mail.mailers.smtp.port' => $this->credentials['port'] ?? 587,
                'mail.mailers.smtp.username' => $this->credentials['username'] ?? '',
                'mail.mailers.smtp.password' => $this->credentials['password'] ?? '',
                'mail.mailers.smtp.encryption' => $this->credentials['encryption'] ?? 'tls',
                'mail.from.address' => $this->credentials['from_email'] ?? $this->credentials['username'],
                'mail.from.name' => $this->credentials['from_name'] ?? 'BulkSMS Platform',
            ]);

            // Create email data
            $emailData = [
                'subject' => $subject ?? 'Message from BulkSMS Platform',
                'body' => $body,
                'recipient' => $recipient,
                'from_email' => $this->credentials['from_email'] ?? $this->credentials['username'],
                'from_name' => $this->credentials['from_name'] ?? 'BulkSMS Platform',
            ];

            // Send email using Laravel Mail
            Mail::send('emails.template', $emailData, function ($message) use ($emailData) {
                $message->to($emailData['recipient'])
                        ->subject($emailData['subject'])
                        ->from($emailData['from_email'], $emailData['from_name']);
            });

            return [
                'success' => true,
                'message_id' => uniqid('email_'),
                'status' => 'sent',
                'provider_response' => 'Email sent successfully via SMTP'
            ];

        } catch (\Exception $e) {
            Log::error('SMTP Email sending failed', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
                'credentials' => array_keys($this->credentials)
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed'
            ];
        }
    }

    public function getProviderName(): string
    {
        return 'smtp';
    }

    public function getSupportedFeatures(): array
    {
        return [
            'text' => true,
            'html' => true,
            'attachments' => true,
            'templates' => true,
            'bulk' => true
        ];
    }
}