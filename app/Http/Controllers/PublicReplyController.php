<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicReplyController extends Controller
{
    /**
     * Show the reply form (accessed via link in SMS)
     */
    public function showReplyForm($token)
    {
        // Decode the token to get message ID
        $messageId = $this->decodeToken($token);
        
        if (!$messageId) {
            abort(404, 'Invalid reply link');
        }

        // Get the original message
        $message = DB::table('messages')->find($messageId);
        
        if (!$message) {
            abort(404, 'Message not found');
        }

        return view('public.reply', compact('message', 'token'));
    }

    /**
     * Handle the reply submission
     */
    public function submitReply(Request $request, $token)
    {
        $validated = $request->validate([
            'reply' => 'required|string|max:1000',
        ]);

        // Decode the token to get message ID
        $messageId = $this->decodeToken($token);
        
        if (!$messageId) {
            return back()->with('error', 'Invalid reply link');
        }

        // Get the original message
        $originalMessage = DB::table('messages')->find($messageId);
        
        if (!$originalMessage) {
            return back()->with('error', 'Message not found');
        }

        // Find or create contact
        $contact = DB::table('contacts')
            ->where('contact', $originalMessage->recipient)
            ->first();

        if (!$contact) {
            // Auto-create contact
            $contactId = DB::table('contacts')->insertGetId([
                'client_id' => $originalMessage->client_id,
                'name' => $originalMessage->recipient,
                'contact' => $originalMessage->recipient,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $contactId = $contact->id;
        }

        // Find or create conversation
        $conversation = DB::table('conversations')
            ->where('contact_id', $contactId)
            ->where('channel', $originalMessage->channel)
            ->first();

        if (!$conversation) {
            $conversationId = DB::table('conversations')->insertGetId([
                'client_id' => $originalMessage->client_id,
                'contact_id' => $contactId,
                'channel' => $originalMessage->channel,
                'contact_identifier' => $originalMessage->recipient,
                'last_message_preview' => substr($validated['reply'], 0, 100),
                'last_message_direction' => 'inbound',
                'last_message_at' => now(),
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
                    'last_message_preview' => substr($validated['reply'], 0, 100),
                    'last_message_direction' => 'inbound',
                    'last_message_at' => now(),
                    'unread_count' => DB::raw('unread_count + 1'),
                    'updated_at' => now(),
                ]);
        }

        // Save the inbound reply
        DB::table('messages')->insert([
            'client_id' => $originalMessage->client_id,
            'conversation_id' => $conversationId,
            'channel' => $originalMessage->channel,
            'direction' => 'inbound',
            'provider' => 'web_reply',
            'sender' => $originalMessage->recipient,
            'recipient' => $originalMessage->sender,
            'body' => $validated['reply'],
            'status' => 'received',
            'is_read' => false,
            'created_at' => now(),
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

        return view('public.reply-success');
    }

    /**
     * Encode message ID into a short token
     */
    public static function encodeToken($messageId)
    {
        // Simple base64 encoding with URL-safe characters
        return rtrim(strtr(base64_encode($messageId), '+/', '-_'), '=');
    }

    /**
     * Decode token back to message ID
     */
    private function decodeToken($token)
    {
        try {
            $decoded = base64_decode(strtr($token, '-_', '+/'));
            return is_numeric($decoded) ? (int)$decoded : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
