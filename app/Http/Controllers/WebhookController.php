<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Onfon inbound message (MO - Mobile Originated)
     */
    public function onfonInbound(Request $request)
    {
        Log::info('Onfon Inbound received', $request->all());

        // Onfon inbound typically sends: From, To, Message, MessageId, ReceivedTime
        $from = $request->input('From') ?? $request->input('from');
        $to = $request->input('To') ?? $request->input('to');
        $messageText = $request->input('Message') ?? $request->input('message');
        $messageId = $request->input('MessageId') ?? $request->input('message_id');
        $receivedTime = $request->input('ReceivedTime') ?? $request->input('received_time');

        if ($from && $messageText) {
            // Find or create contact
            $contact = DB::table('contacts')
                ->where('contact', $from)
                ->first();

            if (!$contact) {
                // Auto-create contact from inbound
                $contactId = DB::table('contacts')->insertGetId([
                    'client_id' => 1, // Default client for now
                    'name' => $from,
                    'contact' => $from,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $contactId = $contact->id;
            }

            // Find or create conversation
            $conversation = DB::table('conversations')
                ->where('contact_id', $contactId)
                ->where('channel', 'sms')
                ->first();

            if (!$conversation) {
                $conversationId = DB::table('conversations')->insertGetId([
                    'client_id' => $contact->client_id ?? 1,
                    'contact_id' => $contactId,
                    'channel' => 'sms',
                    'contact_identifier' => $from,
                    'last_message_preview' => substr($messageText, 0, 100),
                    'last_message_direction' => 'inbound',
                    'last_message_at' => $receivedTime ? now()->parse($receivedTime) : now(),
                    'unread_count' => 1,
                    'status' => 'open',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $conversationId = $conversation->id;
                
                // Update conversation
                DB::table('conversations')
                    ->where('id', $conversationId)
                    ->update([
                        'last_message_preview' => substr($messageText, 0, 100),
                        'last_message_direction' => 'inbound',
                        'last_message_at' => $receivedTime ? now()->parse($receivedTime) : now(),
                        'unread_count' => DB::raw('unread_count + 1'),
                        'updated_at' => now(),
                    ]);
            }

            // Save inbound message
            DB::table('messages')->insert([
                'client_id' => $contact->client_id ?? 1,
                'conversation_id' => $conversationId,
                'channel' => 'sms',
                'direction' => 'inbound',
                'provider' => 'onfon',
                'sender' => $from,
                'recipient' => $to,
                'body' => $messageText,
                'status' => 'received',
                'provider_message_id' => $messageId,
                'is_read' => false,
                'created_at' => $receivedTime ? now()->parse($receivedTime) : now(),
                'updated_at' => now(),
            ]);

            // Update contact stats
            DB::table('contacts')
                ->where('id', $contactId)
                ->update([
                    'last_message_at' => now(),
                    'total_messages' => DB::raw('total_messages + 1'),
                    'unread_messages' => DB::raw('unread_messages + 1'),
                    'updated_at' => now(),
                ]);
        }

        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Handle Onfon delivery report webhook
     */
    public function onfonDlr(Request $request)
    {
        Log::info('Onfon DLR received', $request->all());

        // Onfon DLR typically sends: MessageId, Status, DeliveryTime, etc.
        $messageId = $request->input('MessageId') ?? $request->input('message_id');
        $status = $request->input('Status') ?? $request->input('status');
        $deliveryTime = $request->input('DeliveryTime') ?? $request->input('delivery_time');

        if ($messageId) {
            $updateData = ['updated_at' => now()];

            // Map Onfon status to our statuses
            if (in_array(strtolower($status), ['delivered', 'success'])) {
                $updateData['status'] = 'delivered';
                $updateData['delivered_at'] = $deliveryTime ? now()->parse($deliveryTime) : now();
            } elseif (in_array(strtolower($status), ['failed', 'error', 'rejected'])) {
                $updateData['status'] = 'failed';
                $updateData['failed_at'] = now();
                $updateData['error_message'] = $request->input('ErrorMessage') ?? $request->input('error_message');
            }

            DB::table('messages')
                ->where('provider_message_id', $messageId)
                ->update($updateData);
        }

        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Handle WhatsApp webhook (future)
     */
    public function whatsappWebhook(Request $request)
    {
        Log::info('WhatsApp webhook received', $request->all());
        
        // TODO: implement WhatsApp Cloud API webhook parsing
        
        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Handle Email webhook (future)
     */
    public function emailWebhook(Request $request)
    {
        Log::info('Email webhook received', $request->all());
        
        // TODO: implement email provider webhook parsing
        
        return response()->json(['status' => 'received'], 200);
    }
}

