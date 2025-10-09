<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TopupFailed extends Mailable
{
    use Queueable, SerializesModels;

    public $client;
    public $transaction;
    public $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Client $client, WalletTransaction $transaction, string $reason = null)
    {
        $this->client = $client;
        $this->transaction = $transaction;
        $this->reason = $reason ?? 'Payment was cancelled or failed';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '❌ Top-up Failed - Action Required',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.topup-failed',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

