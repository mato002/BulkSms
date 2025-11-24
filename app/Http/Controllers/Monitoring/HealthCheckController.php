<?php

namespace App\Http\Controllers\Monitoring;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class HealthCheckController extends Controller
{
    public function __invoke(Config $config, Application $app): JsonResponse
    {
        $status = 'ok';
        $checks = [];

        // Database connectivity
        try {
            $start = microtime(true);
            DB::connection()->select('SELECT 1');
            $checks['database'] = [
                'status' => 'ok',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (\Throwable $exception) {
            $status = 'degraded';
            $checks['database'] = [
                'status' => 'error',
                'message' => $exception->getMessage(),
            ];
        }

        // Cache read/write
        try {
            $cacheKey = 'healthcheck:' . uniqid();
            Cache::put($cacheKey, 'ok', 10);
            $cacheValue = Cache::get($cacheKey);
            Cache::forget($cacheKey);

            $checks['cache'] = [
                'status' => $cacheValue === 'ok' ? 'ok' : 'warning',
                'driver' => config('cache.default'),
            ];
        } catch (\Throwable $exception) {
            $status = 'degraded';
            $checks['cache'] = [
                'status' => 'error',
                'message' => $exception->getMessage(),
            ];
        }

        // Queue backlog
        try {
            $queueSize = Queue::size();
            $threshold = (int) $config->get('monitoring.queue_backlog_threshold', 50);

            $checks['queue'] = [
                'status' => $queueSize > $threshold ? 'warning' : 'ok',
                'backlog' => $queueSize,
                'threshold' => $threshold,
                'connection' => config('queue.default'),
            ];

            if ($queueSize > $threshold && $status === 'ok') {
                $status = 'degraded';
            }
        } catch (\Throwable $exception) {
            $checks['queue'] = [
                'status' => 'unknown',
                'message' => $exception->getMessage(),
            ];
        }

        // Disk space
        $diskPath = $app->storagePath();
        $freeSpace = @disk_free_space($diskPath);
        $totalSpace = @disk_total_space($diskPath);

        if ($freeSpace !== false && $totalSpace !== false) {
            $freePercent = $totalSpace > 0 ? round(($freeSpace / $totalSpace) * 100, 2) : null;
            $checks['storage'] = [
                'status' => $freePercent !== null && $freePercent < 10 ? 'warning' : 'ok',
                'free_percent' => $freePercent,
                'free_bytes' => $freeSpace,
                'total_bytes' => $totalSpace,
                'path' => $diskPath,
            ];

            if ($freePercent !== null && $freePercent < 10 && $status === 'ok') {
                $status = 'degraded';
            }
        } else {
            $checks['storage'] = [
                'status' => 'unknown',
                'message' => 'Unable to determine disk space',
            ];
        }

        $httpStatus = $status === 'ok' ? 200 : 503;

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'app' => [
                'name' => $config->get('app.name'),
                'env' => $config->get('app.env'),
                'debug' => $config->get('app.debug'),
                'version' => $config->get('app.version'),
                'laravel_version' => $app::VERSION,
                'php_version' => PHP_VERSION,
            ],
            'checks' => $checks,
        ], $httpStatus);
    }
}





