<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertPhoneNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'name',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get all active phone numbers for alerts
     */
    public static function getActiveNumbers()
    {
        return static::where('is_active', true)->get();
    }

    /**
     * Get phone numbers as array
     */
    public static function getActiveNumbersArray()
    {
        return static::where('is_active', true)->pluck('phone_number')->toArray();
    }
}
