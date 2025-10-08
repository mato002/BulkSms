<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sms extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'recipient',
        'message',
        'sender_id',
        'status',
        'message_id',
        'cost',
        'sent_at',
        'delivered_at',
        'gateway_response'
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'gateway_response' => 'array'
    ];

    /**
     * Get the client that owns the SMS.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scope a query to only include SMS for a specific client.
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope a query to only include SMS with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Mark SMS as sent.
     */
    public function markAsSent(string $messageId = null): bool
    {
        $this->status = 'sent';
        $this->message_id = $messageId;
        $this->sent_at = now();
        return $this->save();
    }

    /**
     * Mark SMS as delivered.
     */
    public function markAsDelivered(): bool
    {
        $this->status = 'delivered';
        $this->delivered_at = now();
        return $this->save();
    }

    /**
     * Mark SMS as failed.
     */
    public function markAsFailed(): bool
    {
        $this->status = 'failed';
        return $this->save();
    }
}
