<?php

/**
 * Test Webhook Reply - Simulates an inbound SMS
 * This tests if your webhook handler is working correctly
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\WebhookController;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          TEST WEBHOOK REPLY SIMULATION                        ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Simulate an inbound SMS
$webhookData = [
    'From' => '254728883160',  // Customer phone
    'To' => 'PRADY_TECH',      // Your sender ID
    'Message' => 'Test reply from customer - this is a simulated inbound message!',
    'MessageId' => 'TEST-' . time(),
    'ReceivedTime' => date('Y-m-d H:i:s')
];

echo "📤 SIMULATING INBOUND SMS:\n";
echo str_repeat('─', 64) . "\n";
echo "From: {$webhookData['From']}\n";
echo "To: {$webhookData['To']}\n";
echo "Message: {$webhookData['Message']}\n";
echo "Message ID: {$webhookData['MessageId']}\n";
echo "Time: {$webhookData['ReceivedTime']}\n";
echo "\n";

echo "🔄 Processing through webhook handler...\n";
echo str_repeat('─', 64) . "\n";

try {
    // Create a request object
    $request = Request::create('/api/webhooks/onfon/inbound', 'POST', $webhookData);
    
    // Create webhook controller
    $controller = new WebhookController();
    
    // Process the webhook
    $response = $controller->onfonInbound($request);
    
    echo "\n✅ WEBHOOK PROCESSED SUCCESSFULLY!\n";
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Body: " . $response->getContent() . "\n";
    
    echo "\n";
    echo str_repeat('─', 64) . "\n";
    echo "🔍 CHECKING IF MESSAGE WAS SAVED:\n";
    echo str_repeat('─', 64) . "\n";
    
    // Check if message was saved
    $savedMessage = DB::table('messages')
        ->where('provider_message_id', $webhookData['MessageId'])
        ->first();
    
    if ($savedMessage) {
        echo "✅ Message found in database!\n\n";
        echo "Message ID: {$savedMessage->id}\n";
        echo "Conversation ID: {$savedMessage->conversation_id}\n";
        echo "Direction: {$savedMessage->direction}\n";
        echo "Status: {$savedMessage->status}\n";
        echo "Sender: {$savedMessage->sender}\n";
        echo "Recipient: {$savedMessage->recipient}\n";
        echo "Body: {$savedMessage->body}\n";
        echo "Created: {$savedMessage->created_at}\n";
        
        // Find the conversation
        if ($savedMessage->conversation_id) {
            $conversation = DB::table('conversations')
                ->where('id', $savedMessage->conversation_id)
                ->first();
            
            if ($conversation) {
                echo "\n✅ Conversation updated:\n";
                echo "Conversation ID: {$conversation->id}\n";
                echo "Last Message: {$conversation->last_message_direction}\n";
                echo "Unread Count: {$conversation->unread_count}\n";
                echo "Status: {$conversation->status}\n";
                
                echo "\n📱 VIEW IN INBOX:\n";
                echo "Go to: https://crm.pradytecai.com/inbox/{$conversation->id}\n";
            }
        }
    } else {
        echo "❌ Message NOT found in database!\n";
        echo "Something went wrong with saving.\n";
    }
    
    echo "\n";
    echo str_repeat('═', 64) . "\n";
    echo "✓ TEST COMPLETE\n";
    echo str_repeat('═', 64) . "\n\n";
    
    echo "💡 WHAT TO DO NEXT:\n";
    echo "1. Check your inbox: https://crm.pradytecai.com/inbox\n";
    echo "2. You should see this test message as an inbound reply\n";
    echo "3. Configure Onfon webhooks to send real replies here\n";
    echo "4. Read INBOX_REPLY_FIX_GUIDE.md for complete setup\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR PROCESSING WEBHOOK:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n\n";
}



