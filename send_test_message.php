<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Messaging\DTO\OutboundMessage;
use App\Services\Messaging\MessageDispatcher;

$dispatcher = app(MessageDispatcher::class);

$message = new OutboundMessage(
    clientId: 1,
    channel: 'sms',
    recipient: '254728883160',
    sender: 'PRADY_TECH',
    body: 'Hi! This is a test from our CRM system. Click the link below to reply to this message.'
);

echo "Sending message to 254728883160...\n";

try {
    $result = $dispatcher->dispatch($message);
    echo "✅ Message sent successfully!\n";
    echo "Message ID: {$result->id}\n";
    echo "Status: {$result->status}\n";
    echo "Provider Message ID: {$result->provider_message_id}\n";
    echo "\n📱 Please check your phone and reply to test two-way communication!\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

