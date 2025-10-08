<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'channel',
        'language',
        'category',
        'subject',
        'body',
        'variables',
        'components',
        'metadata',
        'approved',
        'status',
    ];

    protected $casts = [
        'variables' => 'array',
        'components' => 'array',
        'metadata' => 'array',
        'approved' => 'boolean',
    ];

    /**
     * Get the client that owns this template
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get all messages using this template
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Scope to get templates by channel
     */
    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope to get approved templates
     */
    public function scopeApproved($query)
    {
        return $query->where('approved', true)
            ->orWhere('status', 'approved');
    }

    /**
     * Get template variables as array
     */
    public function getVariablesList(): array
    {
        // Extract {{variable}} placeholders from body
        preg_match_all('/\{\{([^}]+)\}\}/', $this->body, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Replace variables in template body
     */
    public function render(array $variables = []): string
    {
        $body = $this->body;

        foreach ($variables as $key => $value) {
            $body = str_replace("{{{$key}}}", $value, $body);
        }

        return $body;
    }

    /**
     * Check if template is WhatsApp approved
     */
    public function isWhatsAppApproved(): bool
    {
        return $this->channel === 'whatsapp' && 
               ($this->approved || $this->status === 'approved');
    }
}
