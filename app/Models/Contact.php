<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'contact',
        'department',
        'custom_fields'
    ];

    protected $casts = [
        'custom_fields' => 'array'
    ];

    /**
     * Get the client that owns the contact.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scope a query to only include contacts for a specific client.
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope a query to only include contacts in a specific department.
     */
    public function scopeInDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Get the tags for this contact
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'contact_tag')
                    ->withTimestamps();
    }

    /**
     * Add a tag to this contact
     */
    public function addTag($tagId)
    {
        if (!$this->tags()->where('tag_id', $tagId)->exists()) {
            $this->tags()->attach($tagId);
            
            // Update tag count
            $tag = Tag::find($tagId);
            if ($tag) {
                $tag->updateContactsCount();
            }
        }
    }

    /**
     * Remove a tag from this contact
     */
    public function removeTag($tagId)
    {
        $this->tags()->detach($tagId);
        
        // Update tag count
        $tag = Tag::find($tagId);
        if ($tag) {
            $tag->updateContactsCount();
        }
    }

    /**
     * Sync tags for this contact
     */
    public function syncTags(array $tagIds)
    {
        $this->tags()->sync($tagIds);
        
        // Update counts for all affected tags
        Tag::whereIn('id', $tagIds)->get()->each->updateContactsCount();
    }

    /**
     * Check if contact has a specific tag
     */
    public function hasTag($tagId)
    {
        return $this->tags()->where('tag_id', $tagId)->exists();
    }

    /**
     * Scope to filter by tag
     */
    public function scopeWithTag($query, $tagId)
    {
        return $query->whereHas('tags', function ($q) use ($tagId) {
            $q->where('tag_id', $tagId);
        });
    }

    /**
     * Scope to filter by multiple tags
     */
    public function scopeWithAnyTag($query, array $tagIds)
    {
        return $query->whereHas('tags', function ($q) use ($tagIds) {
            $q->whereIn('tag_id', $tagIds);
        });
    }
}
