<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'low_balance_enabled',
        'low_balance_threshold',
        'failed_delivery_enabled',
        'failed_delivery_threshold',
        'daily_summary_enabled',
        'daily_summary_time',
        'weekly_summary_enabled',
        'weekly_summary_day',
        'campaign_complete_enabled',
        'large_campaign_warning_enabled',
        'large_campaign_threshold',
        'notify_via_email',
        'notify_via_sms',
        'notify_via_browser',
    ];

    protected $casts = [
        'low_balance_enabled' => 'boolean',
        'low_balance_threshold' => 'decimal:2',
        'failed_delivery_enabled' => 'boolean',
        'failed_delivery_threshold' => 'integer',
        'daily_summary_enabled' => 'boolean',
        'weekly_summary_enabled' => 'boolean',
        'campaign_complete_enabled' => 'boolean',
        'large_campaign_warning_enabled' => 'boolean',
        'large_campaign_threshold' => 'integer',
        'notify_via_email' => 'boolean',
        'notify_via_sms' => 'boolean',
        'notify_via_browser' => 'boolean',
    ];

    /**
     * Get the client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create notification settings for a client
     */
    public static function getForClient($clientId, $userId = null)
    {
        return static::firstOrCreate([
            'client_id' => $clientId,
            'user_id' => $userId,
        ]);
    }
}


