<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact',
        'sender_id',
        'company_name',
        'balance',
        'price_per_unit',
        'onfon_balance',
        'onfon_last_sync',
        'auto_sync_balance',
        'api_key',
        'status',
        'tier',
        'is_test_mode',
        'settings',
        'webhook_url',
        'webhook_secret',
        'webhook_events',
        'webhook_active',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'price_per_unit' => 'decimal:4',
        'onfon_balance' => 'decimal:2',
        'onfon_last_sync' => 'datetime',
        'auto_sync_balance' => 'boolean',
        'status' => 'boolean',
        'is_test_mode' => 'boolean',
        'settings' => 'array',
        'webhook_events' => 'array',
        'webhook_active' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Invalidate cache when client is updated
        static::updated(function ($client) {
            \App\Services\Cache\ClientSettingsCache::invalidate($client->id);
        });

        static::deleted(function ($client) {
            \App\Services\Cache\ClientSettingsCache::invalidate($client->id);
        });
    }

    /**
     * Get the contacts for the client.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get the SMS messages for the client.
     */
    public function sms(): HasMany
    {
        return $this->hasMany(Sms::class);
    }

    /**
     * Get the campaigns for the client.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Get the users for the client.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the transactions for the client.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the SMS channel for the client.
     */
    public function smsChannel()
    {
        return $this->hasOne(Channel::class)->where('name', 'sms');
    }

    /**
     * Get all channels for the client.
     */
    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }

    /**
     * Get tags for the client.
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * Get notification settings for the client.
     */
    public function notificationSettings()
    {
        return $this->hasOne(NotificationSetting::class);
    }

    /**
     * Get balance in units (balance ÷ price_per_unit)
     */
    public function getBalanceInUnits(): float
    {
        $pricePerUnit = $this->price_per_unit ?? 1.00;
        if ($pricePerUnit <= 0) {
            return 0;
        }
        return round($this->balance / $pricePerUnit, 2);
    }

    /**
     * Convert units to KSH (units × price_per_unit)
     */
    public function unitsToKsh(float $units): float
    {
        return round($units * ($this->price_per_unit ?? 1.00), 2);
    }

    /**
     * Convert KSH to units (ksh ÷ price_per_unit)
     */
    public function kshToUnits(float $ksh): float
    {
        $pricePerUnit = $this->price_per_unit ?? 1.00;
        if ($pricePerUnit <= 0) {
            return 0;
        }
        return round($ksh / $pricePerUnit, 2);
    }

    /**
     * Check if client has sufficient balance (in KSH or units)
     */
    public function hasSufficientBalance(float $amount, bool $isUnits = false): bool
    {
        $amountInKsh = $isUnits ? $this->unitsToKsh($amount) : $amount;
        return $this->balance >= $amountInKsh;
    }

    /**
     * Check if client has sufficient units
     */
    public function hasSufficientUnits(float $units): bool
    {
        return $this->getBalanceInUnits() >= $units;
    }

    /**
     * Deduct balance (in KSH or units)
     */
    public function deductBalance(float $amount, bool $isUnits = false): bool
    {
        $amountInKsh = $isUnits ? $this->unitsToKsh($amount) : $amount;
        
        if ($this->hasSufficientBalance($amountInKsh)) {
            $this->balance -= $amountInKsh;
            return $this->save();
        }
        return false;
    }

    /**
     * Add balance (in KSH or units)
     */
    public function addBalance(float $amount, bool $isUnits = false): bool
    {
        $amountInKsh = $isUnits ? $this->unitsToKsh($amount) : $amount;
        $this->balance += $amountInKsh;
        return $this->save();
    }

    /**
     * Get company name (alias for sender_id for backward compatibility)
     */
    public function getCompanyNameAttribute(): string
    {
        return $this->attributes['company_name'] ?? $this->attributes['sender_id'] ?? '';
    }

    /**
     * Get sender ID (alias for company_name for backward compatibility)
     */
    public function getSenderIdAttribute(): string
    {
        return $this->attributes['sender_id'] ?? $this->attributes['company_name'] ?? '';
    }
}
