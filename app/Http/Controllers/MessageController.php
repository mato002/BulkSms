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

    /**
     * Show messages with sender filtering and analytics
     */
    public function allMessages(Request $request)
    {
        $clientId = auth()->user()->client_id ?? session('client_id', 1);

        // Determine scope: all tenants (admin + view_all) or current tenant
        // Admins see ALL tenants by default. Use ?view_all=0 to scope to current tenant.
        $isViewingAll = auth()->check() && auth()->user()->isAdmin() && ($request->get('view_all', '1') !== '0');

        // Build authoritative sender list from clients.sender_id
        $clientSendersQuery = DB::table('clients')
            ->whereNotNull('sender_id')
            ->where('sender_id', '!=', '');

        if (!$isViewingAll) {
            $clientSendersQuery->where('clients.id', $clientId);
        }

        $senders = $clientSendersQuery
            ->distinct()
            ->pluck('sender_id')
            ->sort()
            ->values();

        // Build the query for messages
        $query = DB::table('messages')
            ->select('messages.*');
        
        // Filter by client unless viewing all
        if (!$isViewingAll) {
            $query->where('messages.client_id', $clientId);
        }

        // Apply sender filter
        if ($request->filled('sender')) {
            // Case-insensitive match with clients.sender_id
            $query->whereRaw('LOWER(messages.sender) = LOWER(?)', [$request->sender]);
        }

        // Apply channel filter
        if ($request->filled('channel')) {
            $query->where('messages.channel', $request->channel);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('messages.status', $request->status);
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('messages.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('messages.created_at', '<=', $request->date_to);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = '%'.$request->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('messages.recipient', 'like', $search)
                  ->orWhere('messages.body', 'like', $search)
                  ->orWhere('messages.sender', 'like', $search);
            });
        }

        // Clone query for statistics (before pagination)
        $statsQuery = clone $query;

        // Get statistics
        $stats = [
            'total_messages' => $statsQuery->count(),
            'sent' => (clone $statsQuery)->whereIn('status', ['sent', 'delivered'])->count(),
            'failed' => (clone $statsQuery)->where('status', 'failed')->count(),
            'pending' => (clone $statsQuery)->whereIn('status', ['queued', 'sending'])->count(),
            'total_cost' => (clone $statsQuery)->whereIn('status', ['sent', 'delivered'])->sum('cost'),
        ];

        // Get messages with pagination
        $messages = $query->orderByDesc('messages.created_at')
            ->paginate(25)
            ->withQueryString();

        // Sender analytics sourced from clients, left-joining messages by case-insensitive sender match
        $analyticsBase = DB::table('clients')
            ->select('clients.sender_id')
            ->whereNotNull('clients.sender_id')
            ->where('clients.sender_id', '!=', '');

        if (!$isViewingAll) {
            $analyticsBase->where('clients.id', $clientId);
        }

        $analyticsQuery = $analyticsBase
            ->leftJoin('messages', function ($join) use ($clientId, $isViewingAll) {
                // Join on LOWER(messages.sender) = LOWER(clients.sender_id)
                $join->on(DB::raw('LOWER(messages.sender)'), '=', DB::raw('LOWER(clients.sender_id)'));
                if (!$isViewingAll) {
                    $join->where('messages.client_id', $clientId);
                }
            })
            ->select(
                DB::raw('clients.sender_id as sender'),
                DB::raw('COUNT(messages.id) as total_messages'),
                DB::raw('SUM(CASE WHEN messages.status IN ("sent", "delivered") THEN 1 ELSE 0 END) as successful_messages'),
                DB::raw('SUM(CASE WHEN messages.status = "failed" THEN 1 ELSE 0 END) as failed_messages'),
                DB::raw('SUM(CASE WHEN messages.status IN ("sent", "delivered") THEN messages.cost ELSE 0 END) as total_earnings')
            );

        // Apply same filters to the analytics (on messages side)
        if ($request->filled('channel')) {
            $analyticsQuery->where('messages.channel', $request->channel);
        }
        if ($request->filled('status')) {
            $analyticsQuery->where('messages.status', $request->status);
        }
        if ($request->filled('date_from')) {
            $analyticsQuery->whereDate('messages.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $analyticsQuery->whereDate('messages.created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = '%'.$request->search.'%';
            $analyticsQuery->where(function ($q) use ($search) {
                $q->where('messages.recipient', 'like', $search)
                  ->orWhere('messages.body', 'like', $search)
                  ->orWhere('clients.sender_id', 'like', $search);
            });
        }

        $senderAnalytics = $analyticsQuery
            ->groupBy('clients.sender_id')
            ->orderByDesc('total_messages')
            ->get();

        // Add debug info to help identify any issues
        $debugInfo = [
            'total_senders_found' => $senders->count(),
            'senders_list' => $senders->toArray(),
            'analytics_count' => $senderAnalytics->count(),
        ];

        return view('messages.all', compact('messages', 'senders', 'stats', 'senderAnalytics', 'debugInfo'));
    }
}
