<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Channel;
use App\Services\OnfonWalletService;

class DashboardController extends Controller
{
    public function index()
    {
        // Get client from session or default to 1
        $clientId = session('client_id', 1);
        $isAdmin = auth()->user() && auth()->user()->isAdmin();

        // Get current client info
        $currentClient = Client::find($clientId);

        // Basic stats
        $stats = [
            'total_messages' => DB::table('messages')->where('client_id', $clientId)->count(),
            'sent' => DB::table('messages')->where('client_id', $clientId)->whereIn('status', ['sent', 'delivered'])->count(),
            'delivered' => DB::table('messages')->where('client_id', $clientId)->where('status', 'delivered')->count(),
            'failed' => DB::table('messages')->where('client_id', $clientId)->where('status', 'failed')->count(),
            'pending' => DB::table('messages')->where('client_id', $clientId)->where('status', 'queued')->count(),
            'total_contacts' => DB::table('contacts')->where('client_id', $clientId)->count(),
            'total_templates' => DB::table('templates')->where('client_id', $clientId)->count(),
            'total_campaigns' => DB::table('campaigns')->where('client_id', $clientId)->count(),
        ];

        // Calculate success rate
        $stats['success_rate'] = $stats['total_messages'] > 0 
            ? round(($stats['sent'] / $stats['total_messages']) * 100, 1) 
            : 0;

        // Messages sent today
        $stats['today_messages'] = DB::table('messages')
            ->where('client_id', $clientId)
            ->whereDate('created_at', today())
            ->count();

        // Total cost
        $stats['total_cost'] = DB::table('messages')
            ->where('client_id', $clientId)
            ->sum('cost');

        // Wallet & Balance info
        $stats['local_balance'] = $currentClient ? $currentClient->balance : 0;
        $stats['balance_units'] = $currentClient ? $currentClient->getBalanceInUnits() : 0;
        $stats['onfon_balance'] = $currentClient ? $currentClient->onfon_balance : 0;
        $stats['price_per_unit'] = $currentClient ? $currentClient->price_per_unit : 0;

        // Admin stats (if admin)
        if ($isAdmin) {
            $stats['total_clients'] = Client::count();
            $stats['active_clients'] = Client::where('status', true)->count();
            $stats['total_users'] = DB::table('users')->count();
            $stats['total_channels'] = Channel::count();
        }

        // Messages by channel (including WhatsApp)
        $stats['sms_count'] = DB::table('messages')->where('client_id', $clientId)->where('channel', 'sms')->count();
        $stats['whatsapp_count'] = DB::table('messages')->where('client_id', $clientId)->where('channel', 'whatsapp')->count();
        $stats['email_count'] = DB::table('messages')->where('client_id', $clientId)->where('channel', 'email')->count();

        // Messages by channel
        $messagesByChannel = DB::table('messages')
            ->select('channel', DB::raw('count(*) as count'))
            ->where('client_id', $clientId)
            ->groupBy('channel')
            ->get();

        // Last 7 days activity for chart
        $dailyActivity = DB::table('messages')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('client_id', $clientId)
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill in missing dates with 0 counts
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartData[] = [
                'date' => now()->subDays($i)->format('M d'),
                'count' => $dailyActivity->get($date)->count ?? 0
            ];
        }

        // Recent campaigns (last 5)
        $recentCampaigns = Campaign::where('client_id', $clientId)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        // Recent activity (messages, campaigns, etc.)
        $recentActivity = $this->getRecentActivity($clientId);

        // Top performing channels
        $channelPerformance = DB::table('messages')
            ->select(
                'channel',
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN status IN ("sent", "delivered") THEN 1 ELSE 0 END) as successful')
            )
            ->where('client_id', $clientId)
            ->groupBy('channel')
            ->get()
            ->map(function ($item) {
                $item->success_rate = $item->total > 0 
                    ? round(($item->successful / $item->total) * 100, 1) 
                    : 0;
                return $item;
            });

        // Get next scheduled campaign
        $nextScheduledCampaign = Campaign::where('client_id', $clientId)
            ->where('status', 'draft')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->first();

        return view('dashboard', compact(
            'stats',
            'messagesByChannel',
            'chartData',
            'recentCampaigns',
            'recentActivity',
            'channelPerformance',
            'nextScheduledCampaign',
            'currentClient',
            'isAdmin'
        ));
    }

    private function getRecentActivity($clientId)
    {
        $activities = [];

        // Get recent messages (last 5)
        $messages = DB::table('messages')
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($messages as $message) {
            $activities[] = [
                'type' => 'message',
                'icon' => 'bi-envelope',
                'color' => $message->status === 'failed' ? 'danger' : 'success',
                'title' => 'Message ' . ucfirst($message->status),
                'description' => "To {$message->recipient} via " . strtoupper($message->channel),
                'time' => $message->created_at,
            ];
        }

        // Get recent campaigns
        $campaigns = Campaign::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($campaigns as $campaign) {
            $activities[] = [
                'type' => 'campaign',
                'icon' => 'bi-megaphone',
                'color' => 'primary',
                'title' => 'Campaign Created',
                'description' => $campaign->name,
                'time' => $campaign->created_at,
            ];
        }

        // Sort by time
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activities, 0, 8);
    }
}

