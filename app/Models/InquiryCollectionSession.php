<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class InquiryCollectionSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'facebook_page_id',
        'language',
        'inquiry_type',
        'current_step',
        'step_index',
        'collected_data',
        'expires_at',
        'last_activity_at'
    ];

    protected $casts = [
        'collected_data' => 'array',
        'expires_at' => 'datetime',
        'last_activity_at' => 'datetime'
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function facebookPage(): BelongsTo
    {
        return $this->belongsTo(FacebookPage::class);
    }

    // Helper methods
    public function updateActivity(): void
    {
        $this->update([
            'last_activity_at' => now(),
            'expires_at' => now()->addMinutes(30)
        ]);
    }

    public function setCollectedData(string $key, $value): void
    {
        $data = $this->collected_data ?: [];
        $data[$key] = $value;
        $this->update(['collected_data' => $data]);
        $this->updateActivity();
    }

    public function getCollectedData(string $key, $default = null)
    {
        return ($this->collected_data ?: [])[$key] ?? $default;
    }

    public function moveToNextStep(array $availableSteps): bool
    {
        $currentIndex = array_search($this->current_step, $availableSteps);
        
        if ($currentIndex !== false && $currentIndex + 1 < count($availableSteps)) {
            $nextStep = $availableSteps[$currentIndex + 1];
            $this->update([
                'current_step' => $nextStep,
                'step_index' => $currentIndex + 1
            ]);
            $this->updateActivity();
            return true;
        }
        
        return false; // No more steps
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isCompleted(): bool
    {
        return $this->current_step === 'completed';
    }

    public function complete(): void
    {
        $this->update(['current_step' => 'completed']);
    }

    public function getProgressPercentage(array $availableSteps): float
    {
        if ($this->isCompleted()) {
            return 100.0;
        }

        $totalSteps = count($availableSteps);
        if ($totalSteps === 0) {
            return 0.0;
        }

        return round(($this->step_index / $totalSteps) * 100, 1);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now())
                    ->where('current_step', '!=', 'completed');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now())
                    ->orWhere('current_step', 'completed');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForPage($query, int $facebookPageId)
    {
        return $query->where('facebook_page_id', $facebookPageId);
    }

    // Static methods
    public static function startNew(int $customerId, int $facebookPageId, string $inquiryType, string $language = 'en'): self
    {
        // Clean up any existing sessions for this customer+page
        static::where('customer_id', $customerId)
              ->where('facebook_page_id', $facebookPageId)
              ->delete();

        return static::create([
            'customer_id' => $customerId,
            'facebook_page_id' => $facebookPageId,
            'language' => $language,
            'inquiry_type' => $inquiryType,
            'current_step' => 'name',
            'step_index' => 0,
            'collected_data' => [],
            'expires_at' => now()->addMinutes(30),
            'last_activity_at' => now()
        ]);
    }

    public static function findActiveSession(int $customerId, int $facebookPageId): ?self
    {
        return static::where('customer_id', $customerId)
                    ->where('facebook_page_id', $facebookPageId)
                    ->active()
                    ->first();
    }

    public static function cleanupExpired(): int
    {
        return static::expired()->delete();
    }
}