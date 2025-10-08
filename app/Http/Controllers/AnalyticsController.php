<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $clientId = session('client_id', 1);
        
        // Date range filter (default: last 30 days)
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Overall stats
        $stats = [
            'total' => DB::table('messages')->where('client_id', $clientId)
                ->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
                ->count(),
            'sent' => DB::table('messages')->where('client_id', $clientId)
                ->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
                ->where('status', 'sent')
                ->count(),
            'delivered' => DB::table('messages')->where('client_id', $clientId)
                ->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
                ->where('status', 'delivered')
                ->count(),
            'failed' => DB::table('messages')->where('client_id', $clientId)
                ->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
                ->where('status', 'failed')
                ->count(),
        ];

        // Messages by channel
        $byChannel = DB::table('messages')
            ->select('channel', DB::raw('count(*) as count'))
            ->where('client_id', $clientId)
            ->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
            ->groupBy('channel')
            ->get();

        // Daily messages (last 7 days for chart)
        $dailyMessages = DB::table('messages')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('client_id', $clientId)
            ->whereBetween('created_at', [now()->subDays(7)->format('Y-m-d'), now()->format('Y-m-d').' 23:59:59'])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Success rate by channel
        $successRateByChannel = DB::table('messages')
            ->select(
                'channel',
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN status IN ("sent", "delivered") THEN 1 ELSE 0 END) as successful')
            )
            ->where('client_id', $clientId)
            ->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
            ->groupBy('channel')
            ->get()
            ->map(function ($item) {
                $item->success_rate = $item->total > 0 ? round(($item->successful / $item->total) * 100, 2) : 0;
                return $item;
            });

        // Total cost
        $totalCost = DB::table('messages')
            ->where('client_id', $clientId)
            ->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
            ->sum('cost');

        // Top recipients
        $topRecipients = DB::table('messages')
            ->select('recipient', DB::raw('count(*) as count'))
            ->where('client_id', $clientId)
            ->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
            ->groupBy('recipient')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return view('analytics.index', compact(
            'stats',
            'byChannel',
            'dailyMessages',
            'successRateByChannel',
            'totalCost',
            'topRecipients',
            'startDate',
            'endDate'
        ));
    }
}
