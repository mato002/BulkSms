<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'slug',
        'color',
        'description',
        'contacts_count',
    ];

    protected $casts = [
        'contacts_count' => 'integer',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    /**
     * Get the client that owns the tag
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the contacts with this tag
     */
    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_tag');
    }

    /**
     * Update the contacts count
     */
    public function updateContactsCount()
    {
        $this->contacts_count = $this->contacts()->count();
        $this->save();
    }

    /**
     * Scope by client
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}


