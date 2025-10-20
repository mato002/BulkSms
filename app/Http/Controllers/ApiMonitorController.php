<?php

namespace App\Http\Controllers;

use App\Models\ApiLog;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiMonitorController extends Controller
{
    /**
     * Display API monitoring dashboard
     */
    public function index(Request $request)
    {
        $query = ApiLog::with('client')->latest();
        
        // Filter by client
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        
        // Filter by success/failure
        if ($request->filled('status')) {
            $query->where('success', $request->status === 'success');
        }
        
        // Filter by endpoint
        if ($request->filled('endpoint')) {
            $query->where('endpoint', 'like', '%' . $request->endpoint . '%');
        }
        
        // Filter by method
        if ($request->filled('method')) {
            $query->where('method', strtoupper($request->method));
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $logs = $query->paginate(50);
        $clients = Client::all();
        
        // Get statistics
        $stats = [
            'total_today' => ApiLog::whereDate('created_at', today())->count(),
            'successful_today' => ApiLog::whereDate('created_at', today())->where('success', true)->count(),
            'failed_today' => ApiLog::whereDate('created_at', today())->where('success', false)->count(),
            'avg_response_time' => round(ApiLog::whereDate('created_at', today())->avg('response_time_ms'), 2),
            'total_this_week' => ApiLog::where('created_at', '>=', now()->startOfWeek())->count(),
            'total_this_month' => ApiLog::where('created_at', '>=', now()->startOfMonth())->count(),
        ];
        
        return view('api-monitor.index', compact('logs', 'clients', 'stats'));
    }

    /**
     * Show details of a specific API request
     */
    public function show($id)
    {
        $log = ApiLog::with('client')->findOrFail($id);
        return view('api-monitor.show', compact('log'));
    }

    /**
     * Get API statistics
     */
    public function statistics(Request $request)
    {
        $clientId = $request->get('client_id');
        $period = $request->get('period', 'week'); // day, week, month, year
        
        $query = ApiLog::query();
        
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        
        // Apply period filter
        $query->where('created_at', '>=', match($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfWeek(),
        });
        
        // Get statistics
        $stats = [
            'total_requests' => $query->count(),
            'successful_requests' => (clone $query)->where('success', true)->count(),
            'failed_requests' => (clone $query)->where('success', false)->count(),
            'avg_response_time' => round((clone $query)->avg('response_time_ms'), 2),
            'max_response_time' => round((clone $query)->max('response_time_ms'), 2),
            'min_response_time' => round((clone $query)->min('response_time_ms'), 2),
        ];
        
        // Get requests by endpoint
        $endpointStats = (clone $query)
            ->select('endpoint', DB::raw('count(*) as total'))
            ->groupBy('endpoint')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
        
        // Get requests by hour (for today)
        $hourlyStats = ApiLog::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('count(*) as total')
            )
            ->whereDate('created_at', today())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
        
        return response()->json([
            'stats' => $stats,
            'by_endpoint' => $endpointStats,
            'by_hour' => $hourlyStats,
        ]);
    }

    /**
     * Get real-time API activity (for live monitoring)
     */
    public function activity()
    {
        $recentLogs = ApiLog::with('client')
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'client' => $log->client?->name ?? 'Unknown',
                    'endpoint' => $log->endpoint,
                    'method' => $log->method,
                    'status' => $log->response_status,
                    'success' => $log->success,
                    'response_time' => $log->response_time_ms,
                    'time' => $log->created_at->diffForHumans(),
                    'ip' => $log->ip_address,
                ];
            });
        
        return response()->json($recentLogs);
    }

    /**
     * Clear old logs (optional cleanup feature)
     */
    public function cleanup(Request $request)
    {
        $days = $request->get('days', 30);
        
        $deleted = ApiLog::where('created_at', '<', now()->subDays($days))->delete();
        
        return redirect()->back()->with('success', "Deleted {$deleted} old log entries (older than {$days} days)");
    }
}

