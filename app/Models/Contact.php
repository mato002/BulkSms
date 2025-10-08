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
}
