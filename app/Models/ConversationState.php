<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ConversationState extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'page_customer_id',
        'workflow_id',
        'facebook_page_id',
        'current_step_index',
        'language',
        'status',
        'step_responses',
        'step_retry_counts',
        'temp_data',
        'last_activity_at',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'step_responses' => 'array',
        'step_retry_counts' => 'array',
        'temp_data' => 'array',
        'last_activity_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function pageCustomer(): BelongsTo
    {
        return $this->belongsTo(PageCustomer::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function facebookPage(): BelongsTo
    {
        return $this->belongsTo(FacebookPage::class);
    }

    // Step management
    public function getCurrentStep(): ?array
    {
        return $this->workflow->getStep($this->current_step_index);
    }

    public function getCurrentStepId(): ?string
    {
        $step = $this->getCurrentStep();
        return $step['id'] ?? null;
    }

    public function moveToNextStep(): ?array
    {
        $nextIndex = $this->current_step_index + 1;
        $totalSteps = $this->workflow->getTotalSteps();

        if ($nextIndex < $totalSteps) {
            $this->update([
                'current_step_index' => $nextIndex,
                'last_activity_at' => now()
            ]);
            return $this->getCurrentStep();
        }

        // Workflow completed
        $this->complete();
        return null;
    }

    public function moveToPreviousStep(): ?array
    {
        if ($this->current_step_index > 0) {
            $this->update([
                'current_step_index' => $this->current_step_index - 1,
                'last_activity_at' => now()
            ]);
            return $this->getCurrentStep();
        }
        return null;
    }

    public function jumpToStep(int $stepIndex): ?array
    {
        $totalSteps = $this->workflow->getTotalSteps();
        if ($stepIndex >= 0 && $stepIndex < $totalSteps) {
            $this->update([
                'current_step_index' => $stepIndex,
                'last_activity_at' => now()
            ]);
            return $this->getCurrentStep();
        }
        return null;
    }

    public function jumpToStepById(string $stepId): ?array
    {
        $stepIndex = $this->workflow->getStepIndexById($stepId);
        if ($stepIndex !== null) {
            return $this->jumpToStep($stepIndex);
        }
        return null;
    }

    // Response management
    public function addStepResponse(string $stepId, array $response): void
    {
        $responses = $this->step_responses ?? [];
        $responses[$stepId] = array_merge($responses[$stepId] ?? [], $response);
        
        $this->update([
            'step_responses' => $responses,
            'last_activity_at' => now()
        ]);
    }

    public function getStepResponse(string $stepId): ?array
    {
        return ($this->step_responses ?? [])[$stepId] ?? null;
    }

    public function getAllResponses(): array
    {
        return $this->step_responses ?? [];
    }

    // Retry count management
    public function incrementStepRetryCount(string $stepId): int
    {
        $counts = $this->step_retry_counts ?? [];
        $counts[$stepId] = ($counts[$stepId] ?? 0) + 1;
        
        $this->update(['step_retry_counts' => $counts]);
        return $counts[$stepId];
    }

    public function getStepRetryCount(string $stepId): int
    {
        return ($this->step_retry_counts ?? [])[$stepId] ?? 0;
    }

    public function resetStepRetryCount(string $stepId): void
    {
        $counts = $this->step_retry_counts ?? [];
        unset($counts[$stepId]);
        $this->update(['step_retry_counts' => $counts]);
    }

    // Temporary data management
    public function setTempData(string $key, $value): void
    {
        $tempData = $this->temp_data ?? [];
        $tempData[$key] = $value;
        $this->update(['temp_data' => $tempData]);
    }

    public function getTempData(string $key, $default = null)
    {
        return ($this->temp_data ?? [])[$key] ?? $default;
    }

    public function removeTempData(string $key): void
    {
        $tempData = $this->temp_data ?? [];
        unset($tempData[$key]);
        $this->update(['temp_data' => $tempData]);
    }

    public function clearTempData(): void
    {
        $this->update(['temp_data' => []]);
    }

    // Step status management
    public function setStepStatus(string $stepId, string $status): void
    {
        $this->setTempData("step_{$stepId}_status", $status);
    }

    public function getStepStatus(string $stepId): ?string
    {
        return $this->getTempData("step_{$stepId}_status");
    }

    // Progress tracking
    public function getProgress(): float
    {
        $totalSteps = $this->workflow->getTotalSteps();
        if ($totalSteps === 0) return 100.0;

        return round(($this->current_step_index / $totalSteps) * 100, 2);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'last_activity_at' => now()
        ]);
    }

    public function abandon(): void
    {
        $this->update([
            'status' => 'abandoned',
            'last_activity_at' => now()
        ]);
    }

    public function pause(): void
    {
        $this->update([
            'status' => 'paused',
            'last_activity_at' => now()
        ]);
    }

    public function resume(): void
    {
        $this->update([
            'status' => 'active',
            'last_activity_at' => now()
        ]);
    }

    // Language helpers
    public function getCurrentStepLabel(string $key = 'title'): string
    {
        return $this->workflow->getStepLabel($this->current_step_index, $this->language, $key);
    }

    public function updateActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForPage($query, int $facebookPageId)
    {
        return $query->where('facebook_page_id', $facebookPageId);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('last_activity_at');
    }

    public function scopeStale($query, int $hoursAgo = 24)
    {
        return $query->where('last_activity_at', '<', Carbon::now()->subHours($hoursAgo));
    }
}