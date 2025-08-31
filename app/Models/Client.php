<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'status',
        'plan_type',
        'subscription_expires_at',
        'profile_data',
        'settings',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
        'profile_data' => 'array',
        'settings' => 'array',
        'password' => 'hashed',
    ];

    // Relationships
    public function facebookPages()
    {
        return $this->hasMany(FacebookPage::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function workflows()
    {
        return $this->hasMany(Workflow::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function sendPulseConfig()
    {
        return $this->hasOne(SendPulseConfig::class);
    }

    public function customerSegments()
    {
        return $this->hasMany(CustomerSegment::class);
    }

    public function pageCustomers()
    {
        return $this->hasManyThrough(PageCustomer::class, FacebookPage::class);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPremium(): bool
    {
        return in_array($this->plan_type, ['premium', 'enterprise']);
    }

    public function isFree(): bool
    {
        return $this->plan_type === 'free';
    }

    public function isSubscriptionActive(): bool
    {
        if ($this->isFree()) {
            return true;
        }

        return $this->subscription_expires_at && $this->subscription_expires_at->isFuture();
    }

    public function getTrialDaysRemaining(): int
    {
        $trialDays = 10;
        return max(0, $trialDays - $this->created_at->diffInDays(now()));
    }

    public function isTrialExpired(): bool
    {
        return $this->getTrialDaysRemaining() <= 0;
    }

    public function hasReachedFreeLimits(): bool
    {
        if ($this->isPremium()) {
            return false;
        }

        // Check if free trial period is over (7-10 days)
        if ($this->isTrialExpired()) {
            return true;
        }

        // Check Facebook page limit
        if ($this->hasReachedPageLimit()) {
            return true;
        }

        // Check subscriber limit (10-20)
        $subscriberLimit = 20;
        if ($this->customers()->count() > $subscriberLimit) {
            return true;
        }

        // Check message limit (50)
        $messageLimit = 50;
        $messageCount = CustomerMessage::where('client_id', $this->id)
            ->where('message_type', 'outgoing')
            ->count();
        
        if ($messageCount > $messageLimit) {
            return true;
        }

        return false;
    }

    public function getFacebookPageLimit(): int
    {
        return match($this->plan_type) {
            'free' => 1,
            'premium' => 5,
            'enterprise' => 999, // Effectively unlimited
            default => 1
        };
    }

    public function hasReachedPageLimit(): bool
    {
        if ($this->plan_type === 'enterprise') {
            return false;
        }

        return $this->facebookPages()->count() >= $this->getFacebookPageLimit();
    }

    public function canAddNewPage(): bool
    {
        // For free/trial users, check if they're within trial period and haven't reached page limit
        if ($this->isFree()) {
            return !$this->hasReachedPageLimit() && !$this->isTrialExpired();
        }
        
        // For paid users, check subscription status and page limit
        return !$this->hasReachedPageLimit() && $this->isSubscriptionActive();
    }

    public function getRemainingPageSlots(): int
    {
        $limit = $this->getFacebookPageLimit();
        $current = $this->facebookPages()->count();
        return max(0, $limit - $current);
    }
}
