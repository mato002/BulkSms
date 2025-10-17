<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShortLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'message_id',
        'clicks',
        'last_clicked_at',
        'expires_at',
    ];

    protected $casts = [
        'last_clicked_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Relationship to Message
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Generate a unique short code
     */
    public static function generateUniqueCode($length = 4): string
    {
        do {
            // Generate random alphanumeric code (letters and numbers only)
            $code = Str::random($length);
            // Make it alphanumeric only (easier to type if needed)
            $code = substr(str_replace(['_', '-', '+', '/', '='], '', base64_encode(random_bytes($length))), 0, $length);
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Record a click
     */
    public function recordClick(): void
    {
        $this->increment('clicks');
        $this->update(['last_clicked_at' => now()]);
    }

    /**
     * Check if link is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}

