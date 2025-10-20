<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'api_key',
        'endpoint',
        'method',
        'ip_address',
        'user_agent',
        'request_headers',
        'request_body',
        'response_status',
        'response_body',
        'response_time_ms',
        'success',
        'error_message',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'request_body' => 'array',
        'response_body' => 'array',
        'success' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the client that made the API request
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scope to get successful requests
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope to get failed requests
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope to get requests by endpoint
     */
    public function scopeByEndpoint($query, $endpoint)
    {
        return $query->where('endpoint', 'like', '%' . $endpoint . '%');
    }

    /**
     * Scope to get requests by method
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('method', strtoupper($method));
    }

    /**
     * Scope to get requests by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get the average response time
     */
    public static function averageResponseTime($clientId = null)
    {
        $query = self::query();
        
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        
        return $query->avg('response_time_ms');
    }

    /**
     * Get total requests count
     */
    public static function totalRequests($clientId = null, $period = 'today')
    {
        $query = self::query();
        
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        
        return match($period) {
            'today' => $query->whereDate('created_at', today())->count(),
            'week' => $query->where('created_at', '>=', now()->subWeek())->count(),
            'month' => $query->where('created_at', '>=', now()->subMonth())->count(),
            default => $query->count(),
        };
    }
}

