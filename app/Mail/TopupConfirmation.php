<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TopupConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $client;
    public $transaction;

    /**
     * Create a new message instance.
     */
    public function __construct(Client $client, WalletTransaction $transaction)
    {
        $this->client = $client;
        $this->transaction = $transaction;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Top-up Successful - KES ' . number_format($this->transaction->amount, 2) . ' Added',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.topup-confirmation',
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

