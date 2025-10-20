<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          INBOX MESSAGES DIAGNOSTIC                            ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Get latest conversations
echo "📋 LATEST CONVERSATIONS:\n";
echo str_repeat('─', 64) . "\n";

$conversations = DB::table('conversations')
    ->join('contacts', 'conversations.contact_id', '=', 'contacts.id')
    ->select(
        'conversations.id',
        'conversations.channel',
        'conversations.status',
        'conversations.last_message_direction',
        'conversations.last_message_at',
        'contacts.name as contact_name',
        'contacts.contact as contact_phone'
    )
    ->orderByDesc('conversations.last_message_at')
    ->limit(5)
    ->get();

foreach ($conversations as $conv) {
    echo "ID: {$conv->id} | {$conv->contact_name} ({$conv->contact_phone})\n";
    echo "   Channel: {$conv->channel} | Status: {$conv->status}\n";
    echo "   Last: {$conv->last_message_direction} at {$conv->last_message_at}\n";
    
    // Get message count
    $messageCount = DB::table('messages')
        ->where('conversation_id', $conv->id)
        ->count();
    
    $inboundCount = DB::table('messages')
        ->where('conversation_id', $conv->id)
        ->where('direction', 'inbound')
        ->count();
    
    $outboundCount = DB::table('messages')
        ->where('conversation_id', $conv->id)
        ->where('direction', 'outbound')
        ->count();
    
    echo "   Messages: {$messageCount} total ({$inboundCount} in, {$outboundCount} out)\n";
    echo "\n";
}

// Check for messages without conversation_id
echo "\n📊 MESSAGES WITHOUT CONVERSATION:\n";
echo str_repeat('─', 64) . "\n";

$orphanMessages = DB::table('messages')
    ->whereNull('conversation_id')
    ->orderByDesc('created_at')
    ->limit(10)
    ->get();

if ($orphanMessages->count() > 0) {
    echo "⚠️  Found {$orphanMessages->count()} messages without conversation_id:\n\n";
    
    foreach ($orphanMessages as $msg) {
        $direction = $msg->direction ? $msg->direction : 'NO DIRECTION';
        echo "ID: {$msg->id} | {$msg->channel} | {$direction}\n";
        echo "   From: {$msg->sender} → To: {$msg->recipient}\n";
        echo "   Status: {$msg->status} | Created: {$msg->created_at}\n";
        echo "   Body: " . substr($msg->body, 0, 50) . "...\n\n";
    }
    
    echo "💡 These messages were sent but not linked to conversations!\n";
    echo "   This is likely causing your chat display issue.\n\n";
} else {
    echo "✅ All messages are properly linked to conversations.\n\n";
}

// Check for messages with wrong direction
echo "\n📊 MESSAGES WITH MISSING DIRECTION:\n";
echo str_repeat('─', 64) . "\n";

$messagesNoDirection = DB::table('messages')
    ->whereNull('direction')
    ->orWhere('direction', '')
    ->orderByDesc('created_at')
    ->limit(10)
    ->get();

if ($messagesNoDirection->count() > 0) {
    echo "⚠️  Found {$messagesNoDirection->count()} messages without direction:\n\n";
    
    foreach ($messagesNoDirection as $msg) {
        $convId = $msg->conversation_id ? $msg->conversation_id : 'NULL';
        echo "ID: {$msg->id} | Conv: {$convId}\n";
        echo "   From: {$msg->sender} → To: {$msg->recipient}\n";
        echo "   Created: {$msg->created_at}\n";
        echo "   Body: " . substr($msg->body, 0, 50) . "...\n\n";
    }
    
    echo "💡 These messages have no direction set!\n";
    echo "   They won't display properly in chat.\n\n";
} else {
    echo "✅ All messages have proper direction set.\n\n";
}

// Get latest messages from a specific conversation
echo "\n📨 LATEST MESSAGES IN MOST RECENT CONVERSATION:\n";
echo str_repeat('─', 64) . "\n";

$latestConv = DB::table('conversations')
    ->orderByDesc('last_message_at')
    ->first();

if ($latestConv) {
    echo "Conversation ID: {$latestConv->id}\n\n";
    
    $messages = DB::table('messages')
        ->where('conversation_id', $latestConv->id)
        ->orderByDesc('created_at')
        ->limit(5)
        ->get();
    
    foreach ($messages as $msg) {
        $arrow = $msg->direction === 'outbound' ? '→' : '←';
        $dirLabel = $msg->direction === 'outbound' ? 'YOU' : 'THEM';
        
        echo "{$dirLabel} {$arrow} [{$msg->status}] {$msg->created_at}\n";
        echo "   " . substr($msg->body, 0, 60) . "\n\n";
    }
} else {
    echo "No conversations found.\n";
}

echo str_repeat('═', 64) . "\n";
echo "🔍 DIAGNOSIS COMPLETE\n";
echo str_repeat('═', 64) . "\n\n";

