<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckLastMessage extends Command
{
    protected $signature = 'check:last-message';
    protected $description = 'Check the latest message status';

    public function handle()
    {
        $msg = DB::table('messages')->orderBy('id', 'desc')->first();
        if (!$msg) {
            $this->error('No messages found');
            return 1;
        }

        $this->info("Message ID: {$msg->id}");
        $this->info("Status: {$msg->status}");
        $this->info("Recipient: {$msg->recipient}");
        $this->info("Provider Message ID: ".($msg->provider_message_id ?? 'null'));
        $this->info("Error: ".($msg->error_message ? substr($msg->error_message, 0, 200) : 'null'));
        $this->info("Created: {$msg->created_at}");

        return 0;
    }
}

