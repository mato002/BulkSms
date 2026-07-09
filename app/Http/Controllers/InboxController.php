<?php

namespace App\Http\Controllers;

use App\Support\ListFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Messaging\DTO\OutboundMessage;
use App\Services\Messaging\MessageDispatcher;
use App\Support\CsvExporter;

class InboxController extends Controller
{
    private function resolveClientId(): int
    {
        return (int) (auth()->user()->client_id ?? session('client_id', 1));
    }

    /**
     * Show inbox with all conversations
     */
    public function index(Request $request)
    {
        $clientId = $this->resolveClientId();

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

        $conversations = ListFilters::paginate(
            $query->orderByDesc('conversations.last_message_at'),
            $request,
            25
        );

        return view('inbox.index', compact('conversations'));
    }

    /**
     * Show conversation/chat with a contact
     */
    public function show($conversationId)
    {
        $data = $this->loadConversation($conversationId);
        if (isset($data['error'])) {
            return $this->conversationNotFoundResponse($data['error']);
        }

        return view('inbox.chat', $data);
    }

    /**
     * Turbo frame partial for live chat refresh (no layout).
     */
    public function panel($conversationId)
    {
        $data = $this->loadConversation($conversationId);
        if (isset($data['error'])) {
            return $this->conversationNotFoundResponse($data['error'], 'chat-panel');
        }

        return view('inbox.partials.chat-panel-frame', $data);
    }

    /**
     * Load conversation and messages for the current client.
     */
    private function loadConversation($conversationId): array
    {
        $clientId = $this->resolveClientId();

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
            return ['error' => 'Conversation not found or you do not have access to it.'];
        }

        $messages = DB::table('messages')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();

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

        DB::table('contacts')
            ->where('id', $conversation->contact_id)
            ->update(['unread_messages' => 0]);

        return compact('conversation', 'messages');
    }

    private function conversationNotFoundResponse(string $message, ?string $frame = null)
    {
        $turboFrame = $frame ?? request()->header('Turbo-Frame');

        if ($turboFrame === 'chat-panel') {
            return response()->view('inbox.partials.chat-panel-error', compact('message'));
        }

        if ($turboFrame === 'main-content') {
            return response()->view('inbox.partials.chat-not-found', compact('message'));
        }

        abort(404, $message);
    }

    private function redirectToConversation(int $conversationId, ?string $flashKey = null, ?string $flashMessage = null)
    {
        $route = request()->header('Turbo-Frame') === 'chat-panel'
            ? route('inbox.panel', $conversationId)
            : route('inbox.show', $conversationId);

        $redirect = redirect()->to($route);

        if ($flashKey && $flashMessage) {
            $redirect->with($flashKey, $flashMessage);
        }

        return $redirect;
    }

    /**
     * Send reply in conversation
     */
    public function reply(Request $request, $conversationId, MessageDispatcher $dispatcher)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $clientId = $this->resolveClientId();

        $conversation = DB::table('conversations')
            ->join('contacts', 'conversations.contact_id', '=', 'contacts.id')
            ->where('conversations.id', $conversationId)
            ->where('conversations.client_id', $clientId)
            ->select('conversations.*', 'contacts.contact as contact_phone')
            ->first();

        if (!$conversation) {
            return $this->conversationNotFoundResponse('Conversation not found or you do not have access to it.');
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

        try {
            $message = $dispatcher->dispatch($outbound);
        } catch (\RuntimeException $e) {
            return $this->redirectToConversation($conversationId, 'error', $e->getMessage());
        }

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

        if ($message->status === 'failed') {
            return $this->redirectToConversation(
                $conversationId,
                'error',
                'Message failed to send: ' . ($message->error_message ?: 'Unknown error')
            );
        }

        return $this->redirectToConversation($conversationId, 'success', 'Message sent!');
    }

    /**
     * Mark conversation as resolved/archived
     */
    public function updateStatus(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,resolved,archived',
        ]);

        $clientId = $this->resolveClientId();

        DB::table('conversations')
            ->where('id', $conversationId)
            ->where('client_id', $clientId)
            ->update([
                'status' => $validated['status'],
                'updated_at' => now(),
            ]);

        return $this->redirectToConversation($conversationId, 'success', 'Conversation status updated');
    }

    /**
     * Start or continue a conversation with a contact
     */
    public function startWithContact($contactId)
    {
        $clientId = $this->resolveClientId();

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

    public function export(Request $request)
    {
        $clientId = $this->resolveClientId();

        $query = DB::table('conversations')
            ->join('contacts', 'conversations.contact_id', '=', 'contacts.id')
            ->where('conversations.client_id', $clientId)
            ->select(
                'conversations.id',
                'contacts.name as contact_name',
                'contacts.contact as contact_phone',
                'conversations.channel',
                'conversations.status',
                'conversations.unread_count',
                'conversations.last_message_preview',
                'conversations.last_message_at'
            );

        if ($request->filled('status')) {
            $query->where('conversations.status', $request->status);
        }

        if ($request->filled('channel')) {
            $query->where('conversations.channel', $request->channel);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contacts.name', 'like', "%{$search}%")
                    ->orWhere('contacts.contact', 'like', "%{$search}%")
                    ->orWhere('conversations.last_message_preview', 'like', "%{$search}%");
            });
        }

        $rows = $query->orderByDesc('conversations.last_message_at')->get();

        return CsvExporter::download(
            'inbox_' . date('Y-m-d') . '.csv',
            ['ID', 'Contact', 'Phone', 'Channel', 'Status', 'Unread', 'Last Message', 'Last Activity'],
            function () use ($rows) {
                foreach ($rows as $row) {
                    yield [
                        $row->id,
                        $row->contact_name,
                        $row->contact_phone,
                        $row->channel,
                        $row->status,
                        $row->unread_count,
                        $row->last_message_preview ?? '',
                        $row->last_message_at ?? '',
                    ];
                }
            }
        );
    }
}
