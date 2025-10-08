<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Messaging\DTO\OutboundMessage;
use App\Services\Messaging\MessageDispatcher;

class InboxController extends Controller
{
    /**
     * Show inbox with all conversations
     */
    public function index(Request $request)
    {
        $clientId = session('client_id', 1);

        $query = DB::table('conversations')
            ->join('contacts', 'conversations.contact_id', '=', 'contacts.id')
            ->where('conversations.client_id', $clientId)
            ->select(
                'conversations.*',
                'contacts.name as contact_name',
                'contacts.contact as contact_phone'
            );

        // Filter by status
        if ($request->filled('status')) {
            $query->where('conversations.status', $request->status);
        }

        // Filter by channel
        if ($request->filled('channel')) {
            $query->where('conversations.channel', $request->channel);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contacts.name', 'like', "%{$search}%")
                  ->orWhere('contacts.contact', 'like', "%{$search}%")
                  ->orWhere('conversations.last_message_preview', 'like', "%{$search}%");
            });
        }

        $conversations = $query
            ->orderByDesc('conversations.last_message_at')
            ->paginate(20);

        return view('inbox.index', compact('conversations'));
    }

    /**
     * Show conversation/chat with a contact
     */
    public function show($conversationId)
    {
        $clientId = session('client_id', 1);

        $conversation = DB::table('conversations')
            ->join('contacts', 'conversations.contact_id', '=', 'contacts.id')
            ->where('conversations.id', $conversationId)
            ->where('conversations.client_id', $clientId)
            ->select(
                'conversations.*',
                'contacts.name as contact_name',
                'contacts.contact as contact_phone',
                'contacts.department',
                'contacts.notes as contact_notes'
            )
            ->first();

        if (!$conversation) {
            abort(404);
        }

        // Get all messages in this conversation
        $messages = DB::table('messages')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark as read
        DB::table('conversations')
            ->where('id', $conversationId)
            ->update([
                'unread_count' => 0,
                'updated_at' => now(),
            ]);

        DB::table('messages')
            ->where('conversation_id', $conversationId)
            ->where('direction', 'inbound')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        // Update contact unread count
        DB::table('contacts')
            ->where('id', $conversation->contact_id)
            ->update(['unread_messages' => 0]);

        return view('inbox.chat', compact('conversation', 'messages'));
    }

    /**
     * Send reply in conversation
     */
    public function reply(Request $request, $conversationId, MessageDispatcher $dispatcher)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $clientId = session('client_id', 1);

        $conversation = DB::table('conversations')
            ->join('contacts', 'conversations.contact_id', '=', 'contacts.id')
            ->where('conversations.id', $conversationId)
            ->where('conversations.client_id', $clientId)
            ->select('conversations.*', 'contacts.contact as contact_phone')
            ->first();

        if (!$conversation) {
            abort(404);
        }

        // Get client default sender
        $client = DB::table('clients')->where('id', $clientId)->first();

        // Send message
        $outbound = new OutboundMessage(
            clientId: $clientId,
            channel: $conversation->channel,
            recipient: $conversation->contact_identifier,
            sender: $client->sender_id ?? 'PRADY_TECH',
            body: $validated['message']
        );

        $message = $dispatcher->dispatch($outbound);

        // Update message with conversation_id and direction
        DB::table('messages')
            ->where('id', $message->id)
            ->update([
                'conversation_id' => $conversationId,
                'direction' => 'outbound',
            ]);

        // Update conversation
        DB::table('conversations')
            ->where('id', $conversationId)
            ->update([
                'last_message_preview' => substr($validated['message'], 0, 100),
                'last_message_direction' => 'outbound',
                'last_message_at' => now(),
                'updated_at' => now(),
            ]);

        // Update contact
        DB::table('contacts')
            ->where('id', $conversation->contact_id)
            ->update([
                'last_message_at' => now(),
                'total_messages' => DB::raw('total_messages + 1'),
            ]);

        return redirect()->route('inbox.show', $conversationId)->with('success', 'Message sent!');
    }

    /**
     * Mark conversation as resolved/archived
     */
    public function updateStatus(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,resolved,archived',
        ]);

        $clientId = session('client_id', 1);

        DB::table('conversations')
            ->where('id', $conversationId)
            ->where('client_id', $clientId)
            ->update([
                'status' => $validated['status'],
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Conversation status updated');
    }

    /**
     * Start or continue a conversation with a contact
     */
    public function startWithContact($contactId)
    {
        $clientId = session('client_id', 1);

        // Verify contact belongs to this client
        $contact = DB::table('contacts')
            ->where('id', $contactId)
            ->where('client_id', $clientId)
            ->first();

        if (!$contact) {
            abort(404, 'Contact not found');
        }

        // Check if conversation already exists
        $conversation = DB::table('conversations')
            ->where('contact_id', $contactId)
            ->where('client_id', $clientId)
            ->first();

        // If conversation exists, redirect to it
        if ($conversation) {
            return redirect()->route('inbox.show', $conversation->id);
        }

        // Create new conversation
        $conversationId = DB::table('conversations')->insertGetId([
            'client_id' => $clientId,
            'contact_id' => $contactId,
            'contact_identifier' => $contact->contact,
            'channel' => 'sms', // Default to SMS, can be changed
            'status' => 'open',
            'unread_count' => 0,
            'last_message_preview' => null,
            'last_message_direction' => null,
            'last_message_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('inbox.show', $conversationId)->with('success', 'New conversation started!');
    }
}
