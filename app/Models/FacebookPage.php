<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacebookPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'page_id',
        'page_name',
        'access_token',
        'page_data',
        'is_connected',
        'last_sync',
    ];

    protected $hidden = [
        'access_token',
    ];

    protected $casts = [
        'page_data' => 'array',
        'is_connected' => 'boolean',
        'last_sync' => 'datetime',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function workflows()
    {
        return $this->hasMany(Workflow::class);
    }

    public function activeWorkflow()
    {
        return $this->hasOne(Workflow::class)->where('is_active', true)->latest('published_at');
    }

    public function conversationStates()
    {
        return $this->hasMany(ConversationState::class);
    }

    /**
     * Get all page customer relationships
     */
    public function pageCustomers()
    {
        return $this->hasMany(PageCustomer::class);
    }

    /**
     * Get all customers who have interacted with this page
     */
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'page_customers')
                    ->withPivot(['facebook_user_id', 'first_interaction', 'last_interaction', 'interaction_count', 'status'])
                    ->withTimestamps();
    }

    // Helper methods
    public function isConnected(): bool
    {
        return $this->is_connected && !empty($this->access_token);
    }

    public function disconnect(): void
    {
        $this->update([
            'is_connected' => false,
            'access_token' => null,
            'last_sync' => null,
        ]);
    }

    public function updateSyncTime(): void
    {
        $this->update(['last_sync' => now()]);
    }

    public function getProfilePicture(): ?string
    {
        return $this->page_data['picture'] ?? null;
    }

    public function getCategory(): ?string
    {
        return $this->page_data['category'] ?? null;
    }

    public function hasPermission(string $permission): bool
    {
        $tasks = $this->page_data['tasks'] ?? [];
        return in_array($permission, $tasks);
    }

    public function canManageMessages(): bool
    {
        return $this->hasPermission('MESSAGING') && $this->isConnected();
    }

    public function getConnectionStatus(): string
    {
        if (!$this->isConnected()) {
            return 'disconnected';
        }

        if (!$this->last_sync) {
            return 'connected';
        }

        // Check if last sync was more than 24 hours ago
        if ($this->last_sync->diffInHours(now()) > 24) {
            return 'sync_needed';
        }

        return 'active';
    }

    public function getConnectionStatusColor(): string
    {
        return match($this->getConnectionStatus()) {
            'active' => 'success',
            'connected' => 'info',
            'sync_needed' => 'warning',
            'disconnected' => 'danger',
            default => 'secondary'
        };
    }
}