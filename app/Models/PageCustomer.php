<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageCustomer extends Model
{
    protected $fillable = [
        'facebook_page_id',
        'customer_id',
        'facebook_user_id',
        'page_specific_data',
        'first_interaction',
        'last_interaction',
        'interaction_count',
        'status'
    ];

    protected $casts = [
        'page_specific_data' => 'array',
        'first_interaction' => 'datetime',
        'last_interaction' => 'datetime',
        'interaction_count' => 'integer'
    ];

    /**
     * Get the Facebook page for this page customer relationship
     */
    public function facebookPage(): BelongsTo
    {
        return $this->belongsTo(FacebookPage::class);
    }

    /**
     * Get the customer for this page customer relationship
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all customer messages for this page customer relationship
     */
    public function customerMessages()
    {
        return $this->hasMany(CustomerMessage::class);
    }

    /**
     * Get all conversation states for this page customer relationship
     */
    public function conversationStates()
    {
        return $this->hasMany(ConversationState::class);
    }

    /**
     * Get all orders for this page customer relationship
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Find or create a page customer relationship
     */
    public static function findOrCreateForPage(FacebookPage $facebookPage, Customer $customer, ?string $facebookUserId = null): self
    {
        return static::firstOrCreate(
            [
                'facebook_page_id' => $facebookPage->id,
                'customer_id' => $customer->id
            ],
            [
                'facebook_user_id' => $facebookUserId,
                'first_interaction' => now(),
                'last_interaction' => now(),
                'interaction_count' => 1,
                'status' => 'active'
            ]
        );
    }

    /**
     * Update interaction stats
     */
    public function recordInteraction(): void
    {
        $this->update([
            'last_interaction' => now(),
            'interaction_count' => $this->interaction_count + 1
        ]);
    }
}
