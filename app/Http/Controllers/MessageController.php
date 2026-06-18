<?php

namespace App\Http\Controllers;

use App\Support\CsvExporter;
use App\Support\ListFilters;
use App\Services\SmsSegmenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $clientId = auth()->user()->client_id ?? session('client_id', 1);

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

        $conversations = ListFilters::paginate(
            $query->orderByDesc('conversations.last_message_at')->orderByDesc('conversations.id'),
            $request,
            25
        );

        return view('messages.index', compact('conversations'));
    }

    public function show(Request $request, string $id)
    {
        $query = DB::table('messages')->where('id', $id);

        if (!auth()->user()->isAdmin()) {
            $clientId = auth()->user()->client_id ?? session('client_id', 1);
            $query->where('client_id', $clientId);
        }

        $message = $query->first();

        if (!$message) {
            abort(404);
        }

        $segments = null;
        if ($message->channel === 'sms' && filled($message->body)) {
            $segments = SmsSegmenter::segmentInfo($message->body);
        }

        $client = DB::table('clients')->where('id', $message->client_id)->first();
        $returnUrl = $request->query('return');

        return view('messages.show', compact('message', 'segments', 'client', 'returnUrl'));
    }

    /**
     * Show messages with sender filtering and analytics
     */
    public function allMessages(Request $request)
    {
        $clientId = auth()->user()->client_id ?? session('client_id', 1);
        $isViewingAll = auth()->check() && auth()->user()->isAdmin() && ($request->get('view_all', '1') !== '0');

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

        [$query, $statsQuery] = $this->buildAllMessagesQueries($request, $clientId, $isViewingAll);

        $stats = [
            'total_messages' => $statsQuery->count(),
            'sent' => (clone $statsQuery)->whereIn('status', ['sent', 'delivered'])->count(),
            'failed' => (clone $statsQuery)->where('status', 'failed')->count(),
            'pending' => (clone $statsQuery)->whereIn('status', ['queued', 'sending'])->count(),
            'total_cost' => (clone $statsQuery)->whereIn('status', ['sent', 'delivered'])->sum('cost'),
        ];

        $messages = ListFilters::paginate(
            $query->orderByDesc('messages.created_at'),
            $request,
            25
        );

        $senderAnalytics = $this->buildSenderAnalytics($request, $clientId, $isViewingAll);

        return view('messages.all', compact('messages', 'senders', 'stats', 'senderAnalytics'));
    }

    public function exportAll(Request $request)
    {
        $clientId = auth()->user()->client_id ?? session('client_id', 1);
        $isViewingAll = auth()->check() && auth()->user()->isAdmin() && ($request->get('view_all', '1') !== '0');

        [$query] = $this->buildAllMessagesQueries($request, $clientId, $isViewingAll);
        $rows = $query->orderByDesc('messages.created_at')->limit(10000)->get();

        return CsvExporter::download(
            'messages_' . date('Y-m-d') . '.csv',
            ['Date', 'Sender', 'Recipient', 'Channel', 'Status', 'Cost (KES)', 'Body'],
            function () use ($rows) {
                foreach ($rows as $message) {
                    yield [
                        $message->created_at,
                        $message->sender ?? '',
                        $message->recipient ?? '',
                        $message->channel ?? '',
                        $message->status ?? '',
                        number_format($message->cost ?? 0, 2, '.', ''),
                        $message->body ?? '',
                    ];
                }
            }
        );
    }

    public function exportConversations(Request $request)
    {
        $clientId = auth()->user()->client_id ?? session('client_id', 1);

        $query = DB::table('conversations')
            ->leftJoin('contacts', 'conversations.contact_id', '=', 'contacts.id')
            ->select(
                'conversations.id',
                'contacts.name as contact_name',
                'contacts.contact as contact_phone',
                'conversations.channel',
                'conversations.status',
                'conversations.unread_count',
                'conversations.last_message_preview',
                'conversations.last_message_at'
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

        $rows = $query->orderByDesc('conversations.last_message_at')->get();

        return CsvExporter::download(
            'conversations_' . date('Y-m-d') . '.csv',
            ['ID', 'Contact', 'Phone', 'Channel', 'Status', 'Unread', 'Last Message', 'Last Activity'],
            function () use ($rows) {
                foreach ($rows as $row) {
                    yield [
                        $row->id,
                        $row->contact_name ?? '',
                        $row->contact_phone ?? '',
                        $row->channel ?? '',
                        $row->status ?? '',
                        $row->unread_count ?? 0,
                        $row->last_message_preview ?? '',
                        $row->last_message_at ?? '',
                    ];
                }
            }
        );
    }

    private function buildAllMessagesQueries(Request $request, int $clientId, bool $isViewingAll): array
    {
        $query = DB::table('messages')->select('messages.*');

        if (!$isViewingAll) {
            $query->where('messages.client_id', $clientId);
        }

        if ($request->filled('sender')) {
            $query->whereRaw('LOWER(messages.sender) = LOWER(?)', [$request->sender]);
        }

        if ($request->filled('channel')) {
            $query->where('messages.channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('messages.status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('messages.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('messages.created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = '%'.$request->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('messages.recipient', 'like', $search)
                  ->orWhere('messages.body', 'like', $search)
                  ->orWhere('messages.sender', 'like', $search);
            });
        }

        return [$query, clone $query];
    }

    private function buildSenderAnalytics(Request $request, int $clientId, bool $isViewingAll)
    {
        $analyticsBase = DB::table('clients')
            ->select('clients.sender_id')
            ->whereNotNull('clients.sender_id')
            ->where('clients.sender_id', '!=', '');

        if (!$isViewingAll) {
            $analyticsBase->where('clients.id', $clientId);
        }

        $analyticsQuery = $analyticsBase
            ->leftJoin('messages', function ($join) use ($clientId, $isViewingAll) {
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

        return $analyticsQuery
            ->groupBy('clients.sender_id')
            ->orderByDesc('total_messages')
            ->get();
    }
}
