<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'facebook_user_id',
        'name',
        'phone',
        'email',
        'address',
        'city',
        'area',
        'profile_data',
        'interaction_stats',
        'tags',
        'custom_fields',
        'first_interaction',
        'last_interaction',
        'interaction_count',
        'status',
    ];

    protected $casts = [
        'profile_data' => 'array',
        'interaction_stats' => 'array',
        'tags' => 'array',
        'custom_fields' => 'array',
        'first_interaction' => 'datetime',
        'last_interaction' => 'datetime',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function messages()
    {
        return $this->hasMany(CustomerMessage::class);
    }

    // Get Facebook page based on customer's source page ID
    public function getFacebookPageAttribute()
    {
        $sourcePageId = $this->profile_data['source_page_id'] ?? null;
        if (!$sourcePageId) return null;
        
        return \App\Models\FacebookPage::where('page_id', $sourcePageId)
            ->where('client_id', $this->client_id)
            ->first();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function updateInteraction(): void
    {
        $this->increment('interaction_count');
        $this->update([
            'last_interaction' => now(),
            'first_interaction' => $this->first_interaction ?? now(),
        ]);
    }
}
