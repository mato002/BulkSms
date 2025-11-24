<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'transaction_type',
        'payment_method',
        'amount',
        'currency',
        'status',
        'reference',
        'external_reference',
        'description',
        'metadata',
        'payment_details',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'payment_details' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the client that owns the transaction.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user that initiated the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for failed transactions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for topup transactions
     */
    public function scopeTopup($query)
    {
        return $query->where('transaction_type', 'topup');
    }

    /**
     * Generate unique transaction reference
     */
    public static function generateReference($prefix = 'TXN')
    {
        do {
            $reference = $prefix . date('Ymd') . strtoupper(substr(uniqid(), -6));
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Mark transaction as completed
     */
    public function markAsCompleted($externalReference = null)
    {
        $this->update([
            'status' => 'completed',
            'external_reference' => $externalReference,
            'processed_at' => now(),
        ]);

        // Add balance to client
        if ($this->transaction_type === 'topup') {
            $this->client->addBalance($this->amount);
            
            // Auto-activate client after successful payment if they were inactive
            if (!$this->client->status) {
                $this->client->update(['status' => true]);
                
                // Log the activation
                \Log::info('Client auto-activated after payment', [
                    'client_id' => $this->client->id,
                    'transaction_id' => $this->id,
                    'amount' => $this->amount,
                ]);
            }
        }
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed($reason = null)
    {
        $this->update([
            'status' => 'failed',
            'description' => $reason ? $this->description . ' - ' . $reason : $this->description,
            'processed_at' => now(),
        ]);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute()
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'completed' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            'canceled' => 'secondary',
            default => 'secondary',
        };
    }
}
