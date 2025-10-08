<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'provider',
        'credentials',
        'config',
        'active',
    ];

    protected $casts = [
        'credentials' => 'json',
        'config' => 'json',
        'active' => 'boolean',
    ];

    /**
     * Get the client that owns this channel
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get all messages sent through this channel
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'channel', 'name')
            ->where('provider', $this->provider);
    }

    /**
     * Check if channel is configured and active
     */
    public function isConfigured(): bool
    {
        return !empty($this->credentials) && $this->active;
    }

    /**
     * Get a specific credential value
     */
    public function getCredential(string $key, $default = null)
    {
        return $this->credentials[$key] ?? $default;
    }

    /**
     * Get a specific config value
     */
    public function getConfig(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}
