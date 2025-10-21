<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'template_id',
        'channel',
        'provider',
        'sender',
        'recipient',
        'subject',
        'body',
        'status',
        'provider_message_id',
        'cost',
        'metadata',
        'scheduled_at',
        'sent_at',
        'delivered_at',
        'failed_at',
        'error_code',
        'error_message',
    ];

    protected $casts = [
        'metadata' => 'json',
        'cost' => 'decimal:4',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Get the client that owns the message.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the template used for this message.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Scope a query to only include messages from a specific sender.
     */
    public function scopeBySender($query, $sender)
    {
        return $query->where('sender', $sender);
    }

    /**
     * Scope a query to only include messages for a specific client.
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope a query to only include successful messages.
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['sent', 'delivered']);
    }

    /**
     * Check if message was successfully sent/delivered.
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, ['sent', 'delivered']);
    }

    /**
     * Get the channel icon.
     */
    public function getChannelIconAttribute(): string
    {
        return match($this->channel) {
            'sms' => 'bi-phone',
            'whatsapp' => 'bi-whatsapp',
            'email' => 'bi-envelope',
            default => 'bi-chat'
        };
    }

    /**
     * Get formatted cost.
     */
    public function getFormattedCostAttribute(): string
    {
        return 'KSH ' . number_format($this->cost, 2);
    }
}
