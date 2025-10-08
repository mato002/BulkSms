<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $clientId = auth()->user()->client_id ?? session('client_id', 1);

        // Conversation-first summary
        $query = DB::table('conversations')
            ->leftJoin('contacts', 'conversations.contact_id', '=', 'contacts.id')
            ->select(
                'conversations.id',
                'conversations.channel',
                'conversations.status',
                'conversations.last_message_preview',
                'conversations.last_message_direction',
                'conversations.last_message_at',
                'conversations.unread_count',
                'contacts.name as contact_name',
                'contacts.contact as contact_phone'
            )
            ->where('conversations.client_id', $clientId);

        if ($request->filled('channel')) {
            $query->where('conversations.channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('conversations.status', $request->status);
        }

        if ($request->filled('search')) {
            $search = '%'.$request->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('contacts.name', 'like', $search)
                  ->orWhere('contacts.contact', 'like', $search)
                  ->orWhere('conversations.last_message_preview', 'like', $search);
            });
        }

        $conversations = $query->orderByDesc('conversations.last_message_at')
            ->orderByDesc('conversations.id')
            ->paginate(25)
            ->withQueryString();

        return view('messages.index', compact('conversations'));
    }

    public function show(string $id)
    {
        $clientId = session('client_id', 1);
        $message = DB::table('messages')
            ->where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if (!$message) {
            abort(404);
        }

        return view('messages.show', compact('message'));
    }
}
