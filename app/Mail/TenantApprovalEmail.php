<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Client;
use App\Models\User;

class TenantApprovalEmail extends Mailable
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
        return $this->subject('New Tenant Registration - Approval Required')
            ->view('emails.tenant-approval')
            ->with([
                'client' => $this->client,
                'user' => $this->user,
                'approvalUrl' => route('admin.senders.show', $this->client->id),
            ]);
    }
}


