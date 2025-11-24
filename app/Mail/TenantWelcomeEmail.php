<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Client;
use App\Models\User;

class TenantWelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $client;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(Client $client, User $user)
    {
        $this->client = $client;
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to BulkSMS CRM - Registration Successful')
            ->view('emails.tenant-welcome')
            ->with([
                'client' => $this->client,
                'user' => $this->user,
                'loginUrl' => route('login'),
                'apiDocsUrl' => route('api.documentation'),
            ]);
    }
}


