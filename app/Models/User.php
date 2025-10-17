<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'client_id',
        'role',
        'avatar',
        'phone',
        'bio',
        'timezone',
        'language',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
        ];
    }

    /**
     * Get the client that owns the user.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a regular user.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Proxy methods to access client's balance methods
     */
    
    /**
     * Get the user's balance (from associated client)
     */
    public function getBalanceAttribute()
    {
        return $this->client ? $this->client->balance : 0;
    }

    /**
     * Get balance in units
     */
    public function getBalanceInUnits(): float
    {
        return $this->client ? $this->client->getBalanceInUnits() : 0;
    }

    /**
     * Convert units to KSH
     */
    public function unitsToKsh(float $units): float
    {
        return $this->client ? $this->client->unitsToKsh($units) : 0;
    }

    /**
     * Convert KSH to units
     */
    public function kshToUnits(float $ksh): float
    {
        return $this->client ? $this->client->kshToUnits($ksh) : 0;
    }

    /**
     * Check if user has sufficient balance
     */
    public function hasSufficientBalance(float $amount, bool $isUnits = false): bool
    {
        return $this->client ? $this->client->hasSufficientBalance($amount, $isUnits) : false;
    }

    /**
     * Check if user has sufficient units
     */
    public function hasSufficientUnits(float $units): bool
    {
        return $this->client ? $this->client->hasSufficientUnits($units) : false;
    }

    /**
     * Get sender ID (from associated client)
     */
    public function getSenderIdAttribute()
    {
        return $this->client ? $this->client->sender_id : '';
    }

    /**
     * Get contact (from associated client)
     */
    public function getContactAttribute()
    {
        return $this->client ? $this->client->contact : '';
    }

    /**
     * Get company name (from associated client)
     */
    public function getCompanyNameAttribute()
    {
        return $this->client ? $this->client->company_name : '';
    }
}





