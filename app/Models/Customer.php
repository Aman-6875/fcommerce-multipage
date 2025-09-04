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

    public function conversationStates()
    {
        return $this->hasMany(ConversationState::class);
    }

    public function activeConversation()
    {
        return $this->hasOne(ConversationState::class)->where('status', 'active')->latest('last_activity_at');
    }

    /**
     * Get all page relationships for this customer
     */
    public function pageCustomers()
    {
        return $this->hasMany(PageCustomer::class);
    }

    /**
     * Get all Facebook pages this customer has interacted with
     */
    public function facebookPages()
    {
        return $this->belongsToMany(FacebookPage::class, 'page_customers')
                    ->withPivot(['facebook_user_id', 'first_interaction', 'last_interaction', 'interaction_count', 'status'])
                    ->withTimestamps();
    }

    /**
     * Get page customer relationship for specific page
     */
    public function getPageCustomer(FacebookPage $facebookPage): ?PageCustomer
    {
        return $this->pageCustomers()->where('facebook_page_id', $facebookPage->id)->first();
    }

    // Helper method to get customer's language preference
    public function getLanguage(): string
    {
        $activeConversation = $this->activeConversation;
        return $activeConversation ? $activeConversation->language : 'en';
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

    /**
     * Get customer's Facebook profile picture URL
     */
    public function getFacebookProfilePicture(): ?string
    {
        $profileData = $this->profile_data ?? [];
        $facebookProfile = $profileData['facebook_profile'] ?? null;
        
        return $facebookProfile['profile_pic'] ?? null;
    }

    /**
     * Get customer's Facebook name
     */
    public function getFacebookName(): ?string
    {
        $profileData = $this->profile_data ?? [];
        $facebookProfile = $profileData['facebook_profile'] ?? null;
        
        return $facebookProfile['name'] ?? null;
    }

    /**
     * Check if customer has Facebook profile data
     */
    public function hasFacebookProfile(): bool
    {
        $profileData = $this->profile_data ?? [];
        return isset($profileData['facebook_profile']);
    }
}
