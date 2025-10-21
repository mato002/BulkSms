<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'message',
        'sender_id',
        'channel',
        'template_id',
        'recipients',
        'status',
        'scheduled_at',
        'sent_at',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'failed_count',
        'total_cost'
    ];

    protected $casts = [
        'recipients' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'processed_at' => 'datetime',
        'is_scheduled' => 'boolean',
        'recurrence_settings' => 'array',
        'total_cost' => 'decimal:2'
    ];

    /**
     * Get the client that owns the campaign.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the template that the campaign uses.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Get the SMS messages for the campaign.
     */
    public function sms(): HasMany
    {
        return $this->hasMany(Sms::class);
    }

    /**
     * Scope a query to only include campaigns for a specific client.
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope a query to only include campaigns with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Mark campaign as sent.
     */
    public function markAsSent(): bool
    {
        $this->status = 'sent';
        $this->sent_at = now();
        return $this->save();
    }

    /**
     * Update campaign statistics.
     */
    public function updateStats(): void
    {
        $this->sent_count = $this->sms()->where('status', 'sent')->count();
        $this->delivered_count = $this->sms()->where('status', 'delivered')->count();
        $this->failed_count = $this->sms()->where('status', 'failed')->count();
        $this->total_cost = $this->sms()->sum('cost');
        $this->save();
    }

    /**
     * Scope for scheduled campaigns
     */
    public function scopeScheduled($query)
    {
        return $query->where('is_scheduled', true)
                    ->whereNotNull('scheduled_at')
                    ->whereNull('processed_at');
    }

    /**
     * Scope for campaigns due to be sent
     */
    public function scopeDue($query)
    {
        return $query->scheduled()
                    ->where('scheduled_at', '<=', now());
    }

    /**
     * Check if campaign is due to be sent
     */
    public function isDue(): bool
    {
        return $this->is_scheduled 
            && $this->scheduled_at 
            && $this->scheduled_at <= now() 
            && !$this->processed_at;
    }

    /**
     * Mark campaign as processed
     */
    public function markAsProcessed(): bool
    {
        $this->processed_at = now();
        return $this->save();
    }

    /**
     * Check if campaign is recurring
     */
    public function isRecurring(): bool
    {
        return !empty($this->recurrence);
    }

    /**
     * Get next occurrence for recurring campaign
     */
    public function getNextOccurrence()
    {
        if (!$this->isRecurring() || !$this->scheduled_at) {
            return null;
        }

        $current = $this->scheduled_at;

        switch ($this->recurrence) {
            case 'daily':
                return $current->addDay();
            case 'weekly':
                return $current->addWeek();
            case 'monthly':
                return $current->addMonth();
            default:
                return null;
        }
    }
}
