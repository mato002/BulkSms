<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'type',
        'title',
        'message',
        'icon',
        'color',
        'link',
        'is_read',
        'read_at',
        'metadata'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the client that owns the notification.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include notifications for a specific client.
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): bool
    {
        $this->is_read = true;
        $this->read_at = now();
        return $this->save();
    }

    /**
     * Create a notification for a campaign completion.
     */
    public static function campaignCompleted($clientId, $campaignId, $campaignName, $totalMessages)
    {
        return self::create([
            'client_id' => $clientId,
            'type' => 'campaign_completed',
            'title' => 'Campaign Completed',
            'message' => "Campaign '{$campaignName}' has been completed. {$totalMessages} messages sent.",
            'icon' => 'bi-megaphone-fill',
            'color' => 'success',
            'link' => route('campaigns.show', $campaignId),
            'metadata' => [
                'campaign_id' => $campaignId,
                'total_messages' => $totalMessages
            ]
        ]);
    }

    /**
     * Create a notification for message failures.
     */
    public static function messagesFailed($clientId, $count, $reason = null)
    {
        return self::create([
            'client_id' => $clientId,
            'type' => 'messages_failed',
            'title' => 'Messages Failed',
            'message' => "{$count} message(s) failed to send" . ($reason ? ": {$reason}" : '.'),
            'icon' => 'bi-exclamation-triangle-fill',
            'color' => 'danger',
            'link' => route('messages.index', ['status' => 'failed']),
            'metadata' => [
                'count' => $count,
                'reason' => $reason
            ]
        ]);
    }

    /**
     * Create a system alert notification.
     */
    public static function systemAlert($clientId, $title, $message, $color = 'warning')
    {
        return self::create([
            'client_id' => $clientId,
            'type' => 'system_alert',
            'title' => $title,
            'message' => $message,
            'icon' => 'bi-info-circle-fill',
            'color' => $color,
        ]);
    }
}
