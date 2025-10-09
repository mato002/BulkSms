<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get summary analytics
     *
     * @param Request $request
     * @param int $company_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary(Request $request, $company_id)
    {
        $client = $request->user();

        // Today's stats
        $today = now()->startOfDay();
        $todayMessages = Message::where('client_id', $client->id)
            ->whereDate('created_at', $today)
            ->get();

        // This month's stats
        $monthStart = now()->startOfMonth();
        $monthMessages = Message::where('client_id', $client->id)
            ->where('created_at', '>=', $monthStart)
            ->get();

        // This month's top-ups
        $monthTopups = WalletTransaction::where('client_id', $client->id)
            ->where('type', 'credit')
            ->where('status', 'completed')
            ->where('created_at', '>=', $monthStart)
            ->sum('amount');

        return response()->json([
            'today' => [
                'messages_sent' => $todayMessages->count(),
                'messages_delivered' => $todayMessages->where('status', 'delivered')->count(),
                'messages_failed' => $todayMessages->where('status', 'failed')->count(),
                'cost' => $todayMessages->sum('cost'),
            ],
            'this_month' => [
                'messages_sent' => $monthMessages->count(),
                'messages_delivered' => $monthMessages->where('status', 'delivered')->count(),
                'messages_failed' => $monthMessages->where('status', 'failed')->count(),
                'cost' => $monthMessages->sum('cost'),
                'top_ups' => WalletTransaction::where('client_id', $client->id)
                    ->where('type', 'credit')
                    ->where('status', 'completed')
                    ->where('created_at', '>=', $monthStart)
                    ->count(),
                'total_topped_up' => $monthTopups,
            ],
            'all_time' => [
                'total_messages' => Message::where('client_id', $client->id)->count(),
                'total_delivered' => Message::where('client_id', $client->id)
                    ->where('status', 'delivered')->count(),
                'total_failed' => Message::where('client_id', $client->id)
                    ->where('status', 'failed')->count(),
                'total_spent' => Message::where('client_id', $client->id)->sum('cost'),
                'total_topped_up' => WalletTransaction::where('client_id', $client->id)
                    ->where('type', 'credit')
                    ->where('status', 'completed')
                    ->sum('amount'),
            ],
            'current_balance' => $client->balance,
            'current_units' => $client->getBalanceInUnits(),
        ]);
    }

    /**
     * Get daily analytics
     *
     * @param Request $request
     * @param int $company_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function daily(Request $request, $company_id)
    {
        $client = $request->user();

        // Get date range
        $fromDate = $request->get('from', now()->subDays(30)->format('Y-m-d'));
        $toDate = $request->get('to', now()->format('Y-m-d'));

        // Get daily message stats
        $dailyStats = Message::where('client_id', $client->id)
            ->whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as messages_sent'),
                DB::raw('SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as messages_delivered'),
                DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as messages_failed'),
                DB::raw('SUM(cost) as cost')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'data' => $dailyStats->map(function ($stat) {
                return [
                    'date' => $stat->date,
                    'messages_sent' => (int) $stat->messages_sent,
                    'messages_delivered' => (int) $stat->messages_delivered,
                    'messages_failed' => (int) $stat->messages_failed,
                    'delivery_rate' => $stat->messages_sent > 0 
                        ? round(($stat->messages_delivered / $stat->messages_sent) * 100, 2) 
                        : 0,
                    'cost' => (float) $stat->cost,
                ];
            })
        ]);
    }

    /**
     * Get monthly analytics
     *
     * @param Request $request
     * @param int $company_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function monthly(Request $request, $company_id)
    {
        $client = $request->user();

        // Get last 12 months
        $months = $request->get('months', 12);

        $monthlyStats = Message::where('client_id', $client->id)
            ->where('created_at', '>=', now()->subMonths($months))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as messages_sent'),
                DB::raw('SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as messages_delivered'),
                DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as messages_failed'),
                DB::raw('SUM(cost) as cost')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json([
            'period' => "{$months} months",
            'data' => $monthlyStats->map(function ($stat) {
                return [
                    'month' => $stat->month,
                    'messages_sent' => (int) $stat->messages_sent,
                    'messages_delivered' => (int) $stat->messages_delivered,
                    'messages_failed' => (int) $stat->messages_failed,
                    'delivery_rate' => $stat->messages_sent > 0 
                        ? round(($stat->messages_delivered / $stat->messages_sent) * 100, 2) 
                        : 0,
                    'cost' => (float) $stat->cost,
                ];
            })
        ]);
    }

    /**
     * Get channel-wise analytics
     *
     * @param Request $request
     * @param int $company_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function byChannel(Request $request, $company_id)
    {
        $client = $request->user();

        $channelStats = Message::where('client_id', $client->id)
            ->select(
                'channel',
                DB::raw('COUNT(*) as total_messages'),
                DB::raw('SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as delivered'),
                DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed'),
                DB::raw('SUM(cost) as total_cost')
            )
            ->groupBy('channel')
            ->get();

        return response()->json([
            'data' => $channelStats->map(function ($stat) {
                return [
                    'channel' => $stat->channel,
                    'total_messages' => (int) $stat->total_messages,
                    'delivered' => (int) $stat->delivered,
                    'failed' => (int) $stat->failed,
                    'delivery_rate' => $stat->total_messages > 0 
                        ? round(($stat->delivered / $stat->total_messages) * 100, 2) 
                        : 0,
                    'total_cost' => (float) $stat->total_cost,
                    'avg_cost_per_message' => $stat->total_messages > 0 
                        ? round($stat->total_cost / $stat->total_messages, 4) 
                        : 0,
                ];
            })
        ]);
    }

    /**
     * Get wallet analytics
     *
     * @param Request $request
     * @param int $company_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function wallet(Request $request, $company_id)
    {
        $client = $request->user();

        // Get date range
        $fromDate = $request->get('from', now()->subDays(30)->format('Y-m-d'));
        $toDate = $request->get('to', now()->format('Y-m-d'));

        // Daily wallet activity
        $dailyActivity = WalletTransaction::where('client_id', $client->id)
            ->whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN type = "credit" AND status = "completed" THEN amount ELSE 0 END) as credits'),
                DB::raw('SUM(CASE WHEN type = "debit" THEN amount ELSE 0 END) as debits'),
                DB::raw('COUNT(CASE WHEN type = "credit" AND status = "completed" THEN 1 END) as topup_count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Summary
        $totalTopups = WalletTransaction::where('client_id', $client->id)
            ->where('type', 'credit')
            ->where('status', 'completed')
            ->whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate])
            ->sum('amount');

        $totalSpent = Message::where('client_id', $client->id)
            ->whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate])
            ->sum('cost');

        return response()->json([
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'summary' => [
                'total_topped_up' => (float) $totalTopups,
                'total_spent' => (float) $totalSpent,
                'net_change' => (float) ($totalTopups - $totalSpent),
                'current_balance' => $client->balance,
            ],
            'daily_activity' => $dailyActivity->map(function ($activity) {
                return [
                    'date' => $activity->date,
                    'credits' => (float) $activity->credits,
                    'debits' => (float) $activity->debits,
                    'net' => (float) ($activity->credits - $activity->debits),
                    'topup_count' => (int) $activity->topup_count,
                ];
            })
        ]);
    }
}

