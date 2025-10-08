<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

$latest = DB::table('messages')
    ->where('direction', 'inbound')
    ->orderByDesc('id')
    ->first();

if ($latest) {
    echo "Latest inbound:\n";
    echo "ID: {$latest->id}\n";
    echo "Conversation: {$latest->conversation_id}\n";
    echo "Sender: {$latest->sender}\n";
    echo "Recipient: {$latest->recipient}\n";
    echo "Status: {$latest->status}\n";
    echo "Created: {$latest->created_at}\n";
    echo "Body: ".substr($latest->body, 0, 200)."\n";
} else {
    echo "No inbound message found yet.\n";
}


