<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'facebook_page_id',
        'name',
        'description',
        'definition',
        'supported_languages',
        'default_language',
        'is_active',
        'version',
        'published_at'
    ];

    protected $casts = [
        'definition' => 'array',
        'supported_languages' => 'array',
        'is_active' => 'boolean',
        'published_at' => 'datetime'
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function facebookPage(): BelongsTo
    {
        return $this->belongsTo(FacebookPage::class);
    }

    public function conversationStates(): HasMany
    {
        return $this->hasMany(ConversationState::class);
    }

    // Helper methods
    public function getSteps(): array
    {
        return $this->definition['steps'] ?? [];
    }

    public function getStep(int $index): ?array
    {
        $steps = $this->getSteps();
        return $steps[$index] ?? null;
    }

    public function getStepById(string $stepId): ?array
    {
        $steps = $this->getSteps();
        foreach ($steps as $step) {
            if ($step['id'] === $stepId) {
                return $step;
            }
        }
        return null;
    }

    public function getStepIndexById(string $stepId): ?int
    {
        $steps = $this->getSteps();
        foreach ($steps as $index => $step) {
            if ($step['id'] === $stepId) {
                return $index;
            }
        }
        return null;
    }

    public function getTotalSteps(): int
    {
        return count($this->getSteps());
    }

    public function supportsLanguage(string $language): bool
    {
        return in_array($language, $this->supported_languages);
    }

    public function getStepLabel(int $stepIndex, string $language, string $key = 'title'): string
    {
        $step = $this->getStep($stepIndex);
        if (!$step) return '';

        $labels = $step['labels'] ?? [];
        $languageLabels = $labels[$language] ?? $labels[$this->default_language] ?? $labels['en'] ?? [];
        
        return $languageLabels[$key] ?? '';
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    public function publish(): bool
    {
        return $this->update([
            'published_at' => now(),
            'is_active' => true
        ]);
    }

    public function unpublish(): bool
    {
        return $this->update([
            'published_at' => null,
            'is_active' => false
        ]);
    }

    public function createNewVersion(): self
    {
        $newWorkflow = $this->replicate();
        $newWorkflow->version = $this->version + 1;
        $newWorkflow->published_at = null;
        $newWorkflow->is_active = false;
        $newWorkflow->save();

        return $newWorkflow;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPage($query, int $facebookPageId)
    {
        return $query->where('facebook_page_id', $facebookPageId);
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function scopeLatestVersion($query)
    {
        return $query->orderByDesc('version');
    }
}